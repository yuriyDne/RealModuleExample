<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

class LogCurrentStatusOnly extends AbstractProcessor
{
    public function execute(OrderInterface $order): ResultInterface
    {
        $currentStatus = $order->getStatus();
        $message = "Order {$order->getIncrementId()} current status is {$currentStatus}. No logic applied";
        $this->getLogger()->debug($message);

        $nextRunInMinutes = 60 * 24; // Run once per day;
        $this->throwRestartException($message, $nextRunInMinutes);
    }
}
