<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory;

use Fisha\OrderFlow\Model\Api\Response;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class GetShippingApiStatus
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory
 */
class GetShippingApiStatus extends GetOrderApiStatus
{
    /**
     * @param OrderInterface $order
     * @return Response
     */
    protected function apiCall(OrderInterface $order)
    {
        return $this->invApiClient->getShipmentStatusResponse(
            $this->getOrderIncrementId($order)
        );
    }
}
