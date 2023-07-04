<?php

namespace Fisha\OrderFlow\Model\Factory;

use Fisha\OrderFlow\Api\Notification\SenderInterface;
use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Magento\Framework\ObjectManagerInterface;

class NotificationSenderFactory
{
    /**
     * @var array
     */
    protected array $instancesByStatus = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DataInterface $configData
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(ProcessorConfig $processorConfig): SenderInterface
    {
        $status = $processorConfig->getProcessStatus();
        if (!array_key_exists($status, $this->instancesByStatus)) {
            $object = $this->objectManager->get($processorConfig->getSendNotificationClass());
            if (!$object instanceof SenderInterface) {
                throw new \LogicException(
                    "Notification sender {{$processorConfig->getSendNotificationClass()}} for status {$processorConfig->getProcessStatus()} must implement SenderInterface"
                );
            }
            $this->instancesByStatus[$status] = $object;
        }
        return $this->instancesByStatus[$status];
    }
}
