<?php

namespace Fisha\OrderFlow\Model\Factory;

use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class ProcessorConfigFactory
{
    const KEY_PROCESS_STATUS = 'processStatus';
    const KEY_POSSIBLE_STATUSES = 'possibleStatuses';
    const KEY_NEXT_STATUS = 'nextStatus';

    const PROCESSOR_ARGUMENTS = [
        self::KEY_PROCESS_STATUS,
        self::KEY_NEXT_STATUS,
        self::KEY_POSSIBLE_STATUSES,
        'failedStatus',
        'processor',
        'attemptsCount',
        'nextRun',
        'nextRunInMinutes',
    ];

    protected array $configByStatus = [];

    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;
    /**
     * @var DataInterface
     */
    protected DataInterface $configData;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param DataInterface $configData
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DataInterface $configData
    ) {
        $this->objectManager = $objectManager;
        $this->configData = $configData;
    }

    public function create(string $status): ProcessorConfig
    {
        if (empty($this->configByStatus)) {
            $configData = $this->configData->get('status_list');
            foreach ($configData as $config) {
                $processorArguments = [];
                foreach (self::PROCESSOR_ARGUMENTS as $argument) {
                    if (array_key_exists($argument, $config)) {
                        $processorArguments[$argument] = $config[$argument];
                    }
                }

                $this->validateProcessorArguments($processorArguments);

                $processorArguments[self::KEY_POSSIBLE_STATUSES] = $this->getPossibleStatuses($processorArguments);
                $processorConfig = $this->objectManager->create(ProcessorConfig::class, $processorArguments);

                $processStatus = $config[self::KEY_PROCESS_STATUS];
                $this->configByStatus[$processStatus] = $processorConfig;
            }
        }

        if (!array_key_exists($status, $this->configByStatus)) {
            throw new LocalizedException(__('status %1 is not configured in orderflow_status.xml file', $status));
        }

        return $this->configByStatus[$status];
    }

    protected function validateProcessorArguments(array $processorArguments)
    {
        if (array_key_exists(self::KEY_POSSIBLE_STATUSES, $processorArguments)
            || array_key_exists(self::KEY_NEXT_STATUS, $processorArguments)
        ) {
            return true;
        }

        $statusesStr = implode(',', [
            self::KEY_POSSIBLE_STATUSES,
            self::KEY_NEXT_STATUS
        ]);
        throw new \LogicException("One of required fields {$statusesStr} in orderflow_status.xml file should be specified for status {$processorArguments[self::KEY_PROCESS_STATUS]}");
    }

    /**
     * @param array $processorArguments
     * @return array
     */
    protected function getPossibleStatuses(array $processorArguments)
    {
        $possibleStatuses = [];

        if (array_key_exists(self::KEY_POSSIBLE_STATUSES, $processorArguments)) {
            $possibleStatuses = explode(',', $processorArguments[self::KEY_POSSIBLE_STATUSES]);
        }
        if (array_key_exists(self::KEY_NEXT_STATUS, $processorArguments)) {
            $possibleStatuses[] = $processorArguments[self::KEY_NEXT_STATUS];
        }

        return $possibleStatuses;
    }
}
