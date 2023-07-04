<?php

namespace Fisha\OrderFlow\Api\Notification;

use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Fisha\OrderFlow\Model\Processor\Result;
use Magento\Sales\Api\Data\OrderInterface;

interface SenderInterface
{
    /**
     * @param OrderInterface $order
     * @param Result $processorResult
     * @param string|null $templateId
     * @return mixed
     */
    public function execute(OrderInterface $order, Result $processorResult, string $templateId = null);

    /**
     * @param string $status
     * @return bool
     */
    public function needSendNotification(string $status): bool;
}
