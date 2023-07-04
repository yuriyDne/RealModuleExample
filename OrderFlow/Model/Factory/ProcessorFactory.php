<?php

namespace Fisha\OrderFlow\Model\Factory;

use Fisha\OrderFlow\Api\ProcessorInterface;
use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Magento\Framework\ObjectManagerInterface;

class ProcessorFactory
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

    public function create(ProcessorConfig $processorConfig): ProcessorInterface
    {
        $status = $processorConfig->getProcessStatus();
        if (!array_key_exists($status, $this->instancesByStatus)) {
            $object = $this->objectManager->get($processorConfig->getProcessor());
            if (!$object instanceof ProcessorInterface) {
                throw new \LogicException(
                    "Processor {{$processorConfig->getProcessor()}} for status {$status} must implement ProcessorInterface"
                );
            }
            $this->instancesByStatus[$status] = $object;
        }

        return $this->instancesByStatus[$status];
    }
}
