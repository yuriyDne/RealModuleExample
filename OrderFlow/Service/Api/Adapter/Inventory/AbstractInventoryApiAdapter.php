<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory;

use Fisha\EdeaIntegration\Api\ConfigInterface as EdeaIntegrationConfigInterface;
use Fisha\OrderFlow\Model\InventoryApi\Client;
use Fisha\OrderFlow\Service\Api\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class AbstractInventoryApiAdapter
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory
 */
abstract class AbstractInventoryApiAdapter
{
    /**
     * @var Client
     */
    protected Client $invApiClient;
    /**
     * @var Logger
     */
    protected Logger $logger;
    /**
     * @var Json
     */
    protected Json $serializer;

    /**
     * @var EdeaIntegrationConfigInterface
     */
    protected EdeaIntegrationConfigInterface $config;

    /**
     * AbstractInventoryApiAdapter constructor.
     *
     * @param Client $invApiClient
     * @param Logger $logger
     * @param Json $serializer
     */
    public function __construct(
        Client $invApiClient,
        Logger $logger,
        Json $serializer,
        EdeaIntegrationConfigInterface $config
    ) {
        $this->invApiClient = $invApiClient;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /**
     * @param Order $order
     * @return string
     */
    public function getOrderIncrementId(OrderInterface $order): string
    {
        $prefix = $this->config->getOrderPrefix();
        return $prefix . $order->getIncrementId();
    }
}
