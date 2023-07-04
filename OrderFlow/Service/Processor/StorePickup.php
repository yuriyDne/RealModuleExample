<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Config\OrderStatus;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Api\Adapter\Inventory\GetShippingApiStatus;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class StorePickup
 * @package Fisha\OrderFlow\Service\Processor
 */
class StorePickup extends AbstractProcessor
{
    const API_STATUS_TO_ORDER_STATUS = [
        0.5 => OrderStatus::STATUS_PICKED, // To prevent zero|empty status api response processing
        1 => OrderStatus::STATUS_PICKUP_SHIPPED,
        2 => OrderStatus::STATUS_PICKUP_RECEIVED,
        3 => OrderStatus::STATUS_PICKUP_CUSTOMER_RECEIVED,
    ];

    const ORDER_STATUS_TO_API_STATUS = [
        OrderStatus::STATUS_PICKED => 0.5,
        OrderStatus::STATUS_PICKUP_SHIPPED => 1,
        OrderStatus::STATUS_PICKUP_RECEIVED => 2,
        OrderStatus::STATUS_PICKUP_CUSTOMER_RECEIVED => 3,
    ];

    /**
     * @var GetShippingApiStatus
     */
    protected GetShippingApiStatus $getShippingApiStatus;

    /**
     * Pickup constructor.
     *
     * @param CommonLogic $commonLogic
     * @param GetShippingApiStatus $getShippingApiStatus
     */
    public function __construct(
        CommonLogic $commonLogic,
        GetShippingApiStatus $getShippingApiStatus
    ) {
        parent::__construct($commonLogic);
        $this->getShippingApiStatus = $getShippingApiStatus;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $currentApiStatus = self::ORDER_STATUS_TO_API_STATUS[$order->getStatus()];
        $shipmentApiStatusResponse = $this->getShippingApiStatus->execute($order);

        $apiStatus = (int) $shipmentApiStatusResponse->getStatus();
        if ($apiStatus <= $currentApiStatus) {
            $this->throwRestartException("Order Shipment status wasn't changed from {$order->getStatus()} in InvApi. Need make API call later");
        }
        if (array_key_exists($apiStatus, self::API_STATUS_TO_ORDER_STATUS) === false) {
            $this->throwFailedStatusException($order, "Invalid InvApi Shipping status: {$shipmentApiStatusResponse->getStatus()}", true);
        }
        $orderNextStatus = self::API_STATUS_TO_ORDER_STATUS[$apiStatus];

        $result = $this->createResult();
        $result->setStatus($orderNextStatus);

        return $result;
    }

}
