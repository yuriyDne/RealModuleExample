<?php

namespace Fisha\OrderFlow\Service\Order;

use Fisha\OrderFlow\Model\Pdf\FilePathResolver;
use LogicException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Exception\CouldNotRefundException;
use Magento\Sales\Exception\DocumentValidationException;
use Magento\Sales\Model\Order\Creditmemo\ItemCreation;
use Magento\Sales\Model\Order\Creditmemo\ItemCreationFactory;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Sales\Model\RefundInvoice;
use Magento\Sales\Model\RefundInvoiceFactory;

use Magento\Sales\Model\RefundOrder;

class Refund
{
    /**
     * @var Creditmemo
     */
    protected Creditmemo $pdfCreditMemo;
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected CreditmemoRepositoryInterface $creditmemoRepository;
    /**
     * @var FilePathResolver
     */
    protected FilePathResolver $filePathResolver;
    /**
     * @var RefundOrder
     */
    protected RefundOrder $refundOrderService;
    /**
     * @var ItemCreationFactory
     */
    protected ItemCreationFactory $creditMemoItemFactory;
    /**
     * @var RefundInvoiceFactory
     */
    private RefundInvoiceFactory $refundInvoiceFactory;

    /**
     * Refund constructor.
     *
     * @param Creditmemo $pdfCreditMemo
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param FilePathResolver $filePathResolver
     * @param ItemCreationFactory $creditMemoItemFactory
     * @param RefundInvoiceFactory $refundInvoiceFactory
     * @param RefundOrder $refundOrderService
     */
    public function __construct(
        Creditmemo $pdfCreditMemo,
        CreditmemoRepositoryInterface $creditmemoRepository,
        FilePathResolver $filePathResolver,
        ItemCreationFactory $creditMemoItemFactory,
        RefundInvoiceFactory $refundInvoiceFactory,
        RefundOrder $refundOrderService
    ) {
        $this->pdfCreditMemo = $pdfCreditMemo;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->filePathResolver = $filePathResolver;
        $this->refundOrderService = $refundOrderService;
        $this->creditMemoItemFactory = $creditMemoItemFactory;
        $this->refundInvoiceFactory = $refundInvoiceFactory;
    }

    /**
     * @param Order $order
     * @param array $items
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @param bool $isOnline
     * @return \Magento\Sales\Model\Order\Creditmemo
     * @throws CouldNotRefundException
     * @throws DocumentValidationException
     */
    public function execute(
        Order $order,
        array $items = [],
        CreditmemoCreationArgumentsInterface $arguments = null,
        $isOnline = true
    ) {
//        if ($isOnline) {
//            throw new \LogicException('Test Error');
//        }
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        if (!$invoice instanceof InvoiceInterface) {
            throw new \LogicException("No invoices for order {$order->getIncrementId()} to refund");
        }

        if (count($items)) {
            $items = $this->convertToCreditMemoItems($items);
        }

        $refundInvoice = $this->refundInvoiceFactory->create();

        $creditMemoId = $refundInvoice->execute(
            $invoice->getEntityId(),
            $items,
            $isOnline,
            false,
            false,
            null,
            $arguments
        );

        return $this->creditmemoRepository->get($creditMemoId);
    }

    /**
     * @param Order $order
     * @param \Magento\Sales\Model\Order\Creditmemo|null $creditMemo
     * @return string
     * @throws \Zend_Pdf_Exception
     */
    public function getCreditMemoPath(Order $order, \Magento\Sales\Model\Order\Creditmemo $creditMemo)
    {
        if ($creditMemo) {
            $creditMemos = [$creditMemo];
        } else {
            $creditMemos = $order->getCreditmemosCollection()->getItems();
        }

        if (count($creditMemos)) {
            $pdf = $this->pdfCreditMemo->getPdf($creditMemos);
            $creditMemoPath = $this->filePathResolver->getCreditMemoPath($order);
            $pdf->save($creditMemoPath);
            return $creditMemoPath;
        }

        throw new LogicException("No credit memos found for order: {$order->getIncrementId()}");
    }

    /**
     * @param array $items
     * @return ItemCreation[]
     */
    protected function convertToCreditMemoItems(array $items)
    {
        $result = [];
        foreach ($items as $orderItemsId => $qty) {
            /** @var ItemCreation $creditMemoItem */
            $creditMemoItem = $this->creditMemoItemFactory->create();
            $creditMemoItem->setOrderItemId($orderItemsId)
                ->setQty($qty);
            $result[] = $creditMemoItem;
        }

        return $result;
    }
}
