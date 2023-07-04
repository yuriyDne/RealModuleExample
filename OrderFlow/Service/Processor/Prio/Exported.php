<?php

namespace Fisha\OrderFlow\Service\Processor\Prio;

use Exception;
use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Api\Pdf\GenerateInvoiceServiceInterface;
use Fisha\OrderFlow\Exception\Processor\ProcessFailedStatusException;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\InvoiceFactory as InvoiceResourceModelFactory;

/**
 * Class Exported
 * @package Fisha\OrderFlow\Service\Processor\Prio
 */
class Exported extends AbstractProcessor
{
    /**
     * @var InvoiceResourceModelFactory
     */
    protected InvoiceResourceModelFactory $invoiceResourceModelFactory;

    /**
     * @var GenerateInvoiceServiceInterface
     */
    protected GenerateInvoiceServiceInterface $generateInvoiceService;

    /**
     * Exported constructor.
     *
     * @param CommonLogic $commonLogic
     * @param InvoiceResourceModelFactory $invoiceResourceModelFactory
     * @param GenerateInvoiceServiceInterface $generateInvoiceService
     */
    public function __construct(
        CommonLogic $commonLogic,
        InvoiceResourceModelFactory $invoiceResourceModelFactory,
        GenerateInvoiceServiceInterface $generateInvoiceService
    ) {
        parent::__construct($commonLogic);
        $this->invoiceResourceModelFactory = $invoiceResourceModelFactory;
        $this->generateInvoiceService = $generateInvoiceService;
    }

    /**
     * @param Order|OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $result = $this->createResult();
        try {
            $this->getLogger()->debug('Start creating Invoice for Order Id :' . $order->getId());
            if ($order->canInvoice()) {
                $invoice = $this->processInvoice($order);
                $this->getLogger()->debug(
                    'Finishing creating Invoice for Order ID:' . $order->getId() . ' Invoice ID: ' . $invoice->getId()
                );
            } elseif (count($order->getInvoiceCollection()) > 0) {
                $this->processAlreadyCaptured($order);
            } else {
                $message = "Order: {$order->getIncrementId()} can't be invoiced";
                $this->throwFailedStatusException($order, $message, true);
            }
            $invoicePath = $this->generateInvoiceService->execute($order);
            $result->setAttachment($invoicePath);
            $result->setMimeType(ResultInterface::MIME_TYPE_PDF);

        } catch (Exception $e) {
            $this->getLogger()->critical($e->getMessage(), ['exception' => $e]);
            throw new ProcessFailedStatusException($e->getMessage());
        }

        return $result;
    }

    /**
     * @param Order|OrderInterface $order
     * @return Order\Invoice
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    protected function processInvoice(OrderInterface $order)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $order->prepareInvoice();
        $invoice->register();
        /** @var Order\Payment $payment */
        $payment = $order->getPayment();

        try {

            if ($payment->getMethodInstance()->canCapture()) {
                $invoice->capture();
            }

            if ((int)$invoice->getState() === Order\Invoice::STATE_PAID) {

                $this->saveInvoice($invoice);
                $this->addCommentToOrder($order, 'Invoice was created automatically');
                $this->getLogger()->debug(
                    'Finishing creating Invoice for Order ID:' . $order->getId() . ' Invoice ID: ' . $invoice->getId()
                );

            } else {
                $order = $this->commonLogic->resetOrderToDefaults($order);
                $this->throwFailedStatusException($order, 'Invoice of Order Id :' . $order->getId() . ' was not paid');
            }

        } catch (Exception $e) {

            $this->getLogger()->error($e->getMessage(), $e->getTrace());

            $this->throwFailedStatusException(
                $order,
                'Error. Order ID:' . $order->getId() . '. ' . $e->getMessage()
            );

        }

        return $invoice;
    }

    /**
     * @param OrderInterface $order
     * @throws CouldNotSaveException
     */
    protected function processAlreadyCaptured(OrderInterface $order)
    {
        $invoiceIds = $order->getInvoiceCollection()->getAllIds();
        $this->addCommentToOrder($order, 'Invoice was already capture');
        $this->getLogger()->debug('Invoice was already capture , Invoice ID: ' . $invoiceIds[0]);
        $this->getLogger()->debug('Finishing creating Invoice for Order ID:' . $order->getId() . ' Invoice ID: ' . $invoiceIds[0]);
    }

    /**
     * @param Order\Invoice $invoice
     * @return mixed
     * @throws AlreadyExistsException
     */
    private function saveInvoice(Order\Invoice $invoice)
    {
        $invoiceResourceModel = $this->invoiceResourceModelFactory->create();

        return $invoiceResourceModel->save($invoice);
    }
}
