<?php

namespace Fisha\OrderFlow\Service\Notification\Sender;

use Fisha\OrderFlow\Api\Notification\SenderInterface;
use Fisha\OrderFlow\Model\Processor\Result;
use Magento\Sales\Api\Data\OrderInterface;

class Sms implements SenderInterface
{
    public function execute(OrderInterface $order, Result $processorResult, string $templateId = null)
    {
        // TODO: Implement execute() method.
    }

    public function needSendNotification(string $status): bool
    {
        // TODO: Implement needSendNotification() method.
    }
}
