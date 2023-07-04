<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request;

use Fisha\Branches\Api\BranchRepositoryInterface;
use Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order\GetItemsData;
use Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order\GetPrefix;
use Fisha\OrderFlow\Service\Order\Shipping\CheckStorePickupMethod;
use Magento\Sales\Model\Order;

/**
 * Class GenerateOrderData
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request
 */
class GenerateOrderData
{
    const STORE_NUMBER_KEY = 'store_order';
    const STORE_PICKUP = 'storepickup_storepickup';
    const STORE_PICKUP_SHIPPING_METHOD_ID = 2;
    const OTHER_SHIPPING_METHOD_ID = 1;

    /**
     * @var GetItemsData
     */
    protected $getItemsData;
    /**
     * @var GetPrefix
     */
    protected $getOrderPrefix;

    /**
     * @var CheckStorePickupMethod
     */
    private CheckStorePickupMethod $checkStorePickupMethod;
    private BranchRepositoryInterface $branchRepository;

    /**
     * GenerateOrderData constructor.
     *
     * @param GetItemsData $getItemsData
     * @param CheckStorePickupMethod $checkStorePickupMethod
     * @param BranchRepositoryInterface $branchRepository
     * @param GetPrefix $getOrderPrefix
     */
    public function __construct(
        GetItemsData $getItemsData,
        CheckStorePickupMethod $checkStorePickupMethod,
        BranchRepositoryInterface $branchRepository,
        GetPrefix $getOrderPrefix
    ) {
        $this->getItemsData = $getItemsData;
        $this->getOrderPrefix = $getOrderPrefix;
        $this->checkStorePickupMethod = $checkStorePickupMethod;
        $this->branchRepository = $branchRepository;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function execute(Order $order)
    {
        $shippingMethodId = $this->getShippingMethodId($order);
        $storeNumber      = $this->getStoreNumber($order, $shippingMethodId);
        $orderIncrementId = $order->getIncrementId();
        $orderData        = [
            'Order_ID'    => $this->getOrderPrefix->execute() . $orderIncrementId,
            'Ship_method' => $shippingMethodId,
            'StoreNumber' => $storeNumber,
        ];

        $items = $this->getItemsData->execute($order);

        $orderData['Items'] = $items;

        return $orderData;
    }

    /**
     * @param Order $order
     * @return int
     */
    protected function getShippingMethodId(Order $order)
    {
        $shippingMethod = $order->getShippingMethod();
        return $this->checkStorePickupMethod->execute($shippingMethod)
            ? self::STORE_PICKUP_SHIPPING_METHOD_ID
            : self::OTHER_SHIPPING_METHOD_ID;
    }

    /**
     * @param Order $order
     * @param int $shippingMethodId
     * @return float|mixed|string|null
     */
    protected function getStoreNumber(Order $order, int $shippingMethodId)
    {
        $result = '';
        if (self::STORE_PICKUP_SHIPPING_METHOD_ID == $shippingMethodId) {
            $shippingAddress = $order->getShippingAddress();
            $branchId = $shippingAddress->getData('storepickup_branch_id');

            if ($branchId > 0) {
                $branch = $this->branchRepository->getById($branchId);
                $result = $branch->getBranchCode();
            }
        }

        return $result;
    }
}
