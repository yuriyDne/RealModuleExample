<?php

namespace Fisha\OrderFlow\Service\Processor\Refund;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Config\OrderStatus;
use Fisha\OrderFlow\Model\Pdf\FilePathResolver;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Order\Refund;
use Fisha\OrderFlow\Model\Processor\Result;
use Fisha\OrderFlow\Service\Order\ResetOrder;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Sales\Model\RefundOrder;

class Transaction extends AbstractProcessor
{
    /**
     * @var CreditmemoRepositoryInterface
     */
    protected CreditmemoRepositoryInterface $creditmemoRepository;
    /**
     * @var Refund
     */
    protected Refund $refundOrderService;
    private ResetOrder $resetOrder;

    /**
     * Transaction constructor.
     *
     * @param CommonLogic $commonLogic
     * @param ResetOrder $resetOrder
     * @param Refund $refundOrderService
     */
    public function __construct(
        CommonLogic $commonLogic,
        ResetOrder $resetOrder,
        Refund $refundOrderService
    ) {
        parent::__construct($commonLogic);
        $this->refundOrderService = $refundOrderService;
        $this->resetOrder = $resetOrder;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        /** @var Order $order */
        if ($order->hasInvoices() === false) {
            $this->throwFailedStatusException(
                $order,
                "Order {$order->getIncrementId()} . ' doesn't have invoice",
                true
            );
        }

        return $this->processRefund($order);
    }

    /**
     * @param Order $order
     * @param array $items
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return Result
     */
    protected function processRefund(Order $order, array $items = [], CreditmemoCreationArgumentsInterface $arguments = null)
    {
        try {
            $result = $this->createResult();
            $this->refundOrderService->execute($order, $items, $arguments);
        } catch (\Exception $e) {
            // Create Offline Credit Memo for manual refund processing
            try {
                $order = $this->reloadOrder($order);
                $this->refundOrderService->execute($order, $items, $arguments, false);

            } catch (\Exception $e) {}
            $order = $this->reloadOrder($order);

            $this->throwFailedStatusException($order, $e->getMessage(), true);
        }

        return $result;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    private function reloadOrder(OrderInterface $order): OrderInterface
    {
        return $this->resetOrder->execute($order);
    }

}
