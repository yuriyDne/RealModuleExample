<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class Cancelled extends AbstractProcessor
{
    /**
     * @param OrderInterface|Order $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $result = $this->createResult();
        if ($order->canCancel()) {
            $order->cancel();
            $result->setStatus($order->getStatus());
        }

        return $result;
    }
}
