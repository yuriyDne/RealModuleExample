<?php

namespace Fisha\OrderFlow\Service\Notification;

use Fisha\OrderFlow\Api\Notification\SenderInterface;
use Fisha\OrderFlow\Model\Config;
use Fisha\OrderFlow\Model\Processor\Result;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class Sender implements SenderInterface
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var array|SenderInterface[]
     */
    protected $senders;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Sender constructor.
     *
     * @param Config $config
     * @param SenderInterface[] $senders
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        array $senders,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->senders = $senders;
        $this->logger = $logger;
    }

    public function execute(OrderInterface $order, Result $processorResult, string $templateId = null)
    {
        $orderStatus = $order->getStatus();
        foreach ($this->senders as $senderType => $sender) {
            if ($this->config->isSenderEnabled($senderType)
                && $sender->needSendNotification($orderStatus)
            ) {
                $templateId = $this->config->getTemplateId($orderStatus, $senderType);
                if (!$templateId) {
                    $this->logger->error("{$senderType} template is not configured for {$orderStatus}");
                }
                try {
                    $sender->execute($order, $processorResult, $templateId);
                } catch (\Exception $e) {
                    $this->logger->error("Error sending {$senderType} notification for order {$order->getIncrementId()}: {$e->getMessage()}");
                }
            }
        }
    }

    public function needSendNotification(string $status): bool
    {
        throw new \LogicException('No call method for this instance');
    }


}
