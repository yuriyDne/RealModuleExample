<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory;

use Fisha\OrderFlow\Model\Api\Response\OrderApiStatus;
use Fisha\OrderFlow\Model\Api\Response\OrderApiStatusFactory;
use Fisha\EdeaIntegration\Api\ConfigInterface as EdeaIntegrationConfigInterface;
use Fisha\OrderFlow\Model\InventoryApi\Client;
use Fisha\OrderFlow\Service\Api\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class GetOrderApiStatus
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory
 */
class GetOrderApiStatus extends AbstractInventoryApiAdapter
{
    const API_TYPE = 'InventoryApi_GetOrderApiStatus';

    /**
     * @var OrderApiStatusFactory
     */
    protected OrderApiStatusFactory $orderStatusFactory;

    /**
     * OrderStatus constructor.
     *
     * @param Client $invApiClient
     * @param OrderApiStatusFactory $orderStatusFactory
     * @param Logger $logger
     * @param EdeaIntegrationConfigInterface $config
     * @param Json $serializer
     */
    public function __construct(
        Client $invApiClient,
        OrderApiStatusFactory $orderStatusFactory,
        Logger $logger,
        Json $serializer,
        EdeaIntegrationConfigInterface $config
    ) {
        parent::__construct($invApiClient, $logger, $serializer, $config);
        $this->orderStatusFactory = $orderStatusFactory;
    }

    /**
     * @param OrderInterface $order
     * @return OrderApiStatus
     */
    public function execute(OrderInterface $order): OrderApiStatus
    {
        $response = $this->apiCall($order);

        $this->logger->execute($order, $response, self::API_TYPE);
        $apiResult = $response->getResponse();

        /** @var OrderApiStatus $orderStatus */
        $orderStatus = $this->orderStatusFactory->create();
        if (isset($apiResult['status']) === false) {
            $apiResult['status'] = '';
        }

        $orderStatus->setStatus($apiResult['status']);

        if (isset($apiResult['order_ID'])) {
            $orderStatus->setOrderIncrementId($apiResult['order_ID']);
        }

        if (!empty($apiResult['cancelledItems'])) {
            $cancelItems = [];

            foreach ($apiResult['cancelledItems'] as $item) {
                $cancelItems[$item['sn']] = $item['qty'];
            }
            $orderStatus->setCanceledItems($cancelItems);
        }

        return $orderStatus;
    }

    /**
     * @param OrderInterface $order
     * @return \Fisha\OrderFlow\Model\Api\Response
     */
    protected function apiCall(OrderInterface $order)
    {
        return $this->invApiClient->getOrderStatusResponse(
            $this->getOrderIncrementId($order)
        );
    }
}
