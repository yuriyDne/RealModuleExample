<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory;

use Fisha\EdeaIntegration\Api\ConfigInterface as EdeaIntegrationConfigInterface;
use Fisha\OrderFlow\Model\InventoryApi\Client;
use Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\GenerateOrderData;
use Fisha\OrderFlow\Service\Api\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

/**
 * Class SendOrderToApi
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory
 */
class SendOrderToApi extends AbstractInventoryApiAdapter
{
    const API_TYPE = 'InventoryApi_SendOrderToApi';
    /**
     * @var GenerateOrderData
     */
    protected $generateOrderData;

    public function __construct(
        Client $invApiClient,
        GenerateOrderData $generateOrderData,
        Logger $logger,
        Json $serializer,
        EdeaIntegrationConfigInterface $config
    ) {
        parent::__construct($invApiClient, $logger, $serializer, $config);
        $this->generateOrderData = $generateOrderData;
    }

    /**
     * @param Order $order
     * @return \Fisha\OrderFlow\Model\Api\Response
     *
     */
    public function execute(Order $order)
    {
        $orderData = $this->generateOrderData->execute($order);
        $apiResponse = $this->invApiClient->getSendOrderResponse($orderData);
        $this->logger->execute($order, $apiResponse, self::API_TYPE);

        $responseBody = $apiResponse->getResponse();
        $isSuccess = !empty($responseBody['status'])
            && $responseBody['status'] == 'success';

        $apiResponse->setIsSuccess($isSuccess);

        return $apiResponse;
    }
}
