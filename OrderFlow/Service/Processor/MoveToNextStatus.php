<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

class MoveToNextStatus extends AbstractProcessor
{
    public function execute(OrderInterface $order): ResultInterface
    {
        $this->getLogger()->info("Order {$order->getIncrementId()} moves to next status by MoveToNextStatus processor");
        return $this->createResult();
    }

}
