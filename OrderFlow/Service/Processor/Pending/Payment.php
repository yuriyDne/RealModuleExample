<?php

namespace Fisha\OrderFlow\Service\Processor\Pending;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class Payment
 * @package Fisha\OrderFlow\Service\Processor\Pending
 */
class Payment extends AbstractProcessor
{
    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $result             = $this->createResult();
        $checkInvoiceStatus = $this->checkInvoiceStatus($order);

        if ($checkInvoiceStatus === false) {
            $errorMessage = "Order {$order->getIncrementId()} doesn't have paid invoices";
            $this->getLogger()->error($errorMessage);
            $this->commonLogic->saveOrderQueueErrorMessage($order, $errorMessage);
            // Keep order status same
            $result->setStatus($order->getStatus());
        } else {
            $this->changeOrderState($order, Order::STATE_PROCESSING);
        }

        return $result;
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function checkInvoiceStatus(Order $order): bool
    {
        $result   = false;
        $invoices = $order->getInvoiceCollection();

        foreach ($invoices as $invoice) {
            if ($invoice->getState() == Order\Invoice::STATE_PAID) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
