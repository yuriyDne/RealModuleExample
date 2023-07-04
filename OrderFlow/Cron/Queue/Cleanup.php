<?php

namespace Fisha\OrderFlow\Cron\Queue;

use Fisha\OrderFlow\Api\QueueServiceInterface;
use Fisha\OrderFlow\Model\Config;

/**
 * Class Cleanup
 * @package Fisha\OrderFlow\Cron\Queue
 */
class Cleanup implements \Fisha\OrderFlow\Api\QueueCronInterface
{
    /**
     * @var QueueServiceInterface
     */
    private QueueServiceInterface $serviceModel;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * Cleanup constructor.
     * @param QueueServiceInterface $serviceModel
     * @param Config $config
     */
    public function __construct(
        QueueServiceInterface $serviceModel,
        Config $config
    ) {
        $this->serviceModel = $serviceModel;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function execute()
    {
        $result = '';

        if ($this->config->isEnabled() === true) {
            $result = $this->getServiceModel()->execute();
        }
        return $result;
    }

    /**
     * @return QueueServiceInterface
     */
    private function getServiceModel() : QueueServiceInterface
    {
        return $this->serviceModel;
    }
}
