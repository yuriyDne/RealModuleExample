<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order;

use Fisha\EdeaIntegration\Api\ConfigInterface;
use Fisha\EdeaIntegration\Api\ConfigInterfaceFactory;
use Magento\Framework\UrlInterface;

/**
 * Class GetPrefix
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order
 */
class GetPrefix
{
    const PRODUCTION_URL = 'aldoshoes.co.il';
    const STAGE_URL = 'aldom2.stg.fisha.cloud';

    /**
     * @var ConfigInterface
     */
    private ?ConfigInterface $config = null;

    /**
     * @var ConfigInterfaceFactory
     */
    private ConfigInterfaceFactory $configInterfaceFactory;

    /**
     * @param UrlInterface $url
     */
    public function __construct(
        UrlInterface $url,
        ConfigInterfaceFactory $configInterfaceFactory
    ) {
        $this->url = $url;
        $this->configInterfaceFactory = $configInterfaceFactory;
    }

    /**
     * @return string
     */
    public function execute()
    {
        return $this->getConfig()->getOrderPrefix();
    }

    /**
     * @return ConfigInterface
     */
    private function getConfig() : ConfigInterface
    {
        if ($this->config instanceof ConfigInterface === false) {
            $this->config = $this->configInterfaceFactory->create();
        }

        return $this->config;
    }
}
