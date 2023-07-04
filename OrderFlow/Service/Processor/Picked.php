<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Order\Shipping\CheckStorePickupMethod;
use Fisha\OrderFlow\Service\Processor\Delivery\Processing;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class Picked
 * @package Fisha\OrderFlow\Service\Processor
 */
class Picked extends AbstractProcessor
{
    /**
     * @var StorePickup
     */
    protected StorePickup $storePickup;
    /**
     * @var CheckStorePickupMethod
     */
    private CheckStorePickupMethod $checkStorePickupMethod;
    /**
     * @var Processing
     */
    private Processing $deliveryProcessing;

    /**
     * Picked constructor.
     *
     * @param CommonLogic $commonLogic
     * @param StorePickup $storePickup
     * @param Processing $deliveryProcessing
     * @param CheckStorePickupMethod $checkStorePickupMethod
     */
    public function __construct(
        CommonLogic            $commonLogic,
        StorePickup            $storePickup,
        Processing             $deliveryProcessing,
        CheckStorePickupMethod $checkStorePickupMethod
    ) {
        parent::__construct($commonLogic);
        $this->storePickup = $storePickup;
        $this->checkStorePickupMethod = $checkStorePickupMethod;
        $this->deliveryProcessing = $deliveryProcessing;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        if ($this->isStorePickupMethod($order)) {
            $result = $this->storePickup->execute($order);
        } else {
            $result = $this->deliveryProcessing->execute($order);
        }

        return $result;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    protected function isStorePickupMethod(OrderInterface $order): bool
    {
        return $this->checkStorePickupMethod->execute($order->getShippingMethod());
    }
}
