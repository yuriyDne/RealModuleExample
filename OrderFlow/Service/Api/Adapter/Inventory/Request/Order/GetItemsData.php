<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order;

use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Inspection\Exception;

/**
 * Class GetItemsData
 * @package Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order
 */
class GetItemsData
{
    const TYPE_PRODUCT_SIMPLE = 'simple';
    const TYPE_PRODUCT_CONFIGURABLE = 'configurable';
    /**
     * @var CommonLogic
     */
    protected $commonLogic;
    /**
     * @var ProductCollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var GetVinSn
     */
    private GetVinSn $getVinSn;

    /**
     * GetItemsData constructor.
     *
     * @param CommonLogic $commonLogic
     * @param GetVinSn $getVinSn
     * @param ProductCollectionFactory $collectionFactory
     */
    public function __construct(
        CommonLogic $commonLogic,
        GetVinSn $getVinSn,
        ProductCollectionFactory $collectionFactory
    ) {
        $this->commonLogic = $commonLogic;
        $this->collectionFactory = $collectionFactory;
        $this->getVinSn = $getVinSn;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function execute(Order $order)
    {
        $items = $this->getItemsData($order);
        if (!$this->validateItems($order)) {
            $items = $items + $this->getBrokenItemsData($order, $items);
        }

        if (count($items) < 1) {
            throw new \LogicException("Order {$order->getIncrementId()} doesn't have items to export");
        }

        return array_values($items);
    }

    /**
     * @param array $skuArray
     * @return Collection
     */
    protected function getProductCollection(array $skuArray)
    {
        /** @var Collection $productCollection */
        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect(['sku','vinv_sn'])
            ->addAttributeToFilter('sku', ['in' => $skuArray])
            ->addAttributeToFilter('status', ['eq' => 1]);

        return $productCollection;
    }

    /**
     * @param Order $order
     * @return array
     */
    protected function getItemsData(Order $order)
    {
        $items = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllItems() as $item) {
            if ('simple' !== $item->getProductType()) {
                continue;
            }

            try {
                $product = $item->getProduct();
                $brandId = $product
                    ? max((int)$product->getBrandId(), 1)
                    : 1;
                $vinSn = $this->getVinSn->execute($item);
                $items[$item->getSku()] = [
                    'Qty'    => $this->getItemQty($item),
                    'SN'     => $item->getSku(),
                    'VinvSN' => (int)$vinSn,
                    'Brand'  => $brandId // By default - BrandId=1 for main store
                ];
            } catch (\Exception $e) {
                $this->commonLogic->getLogger()->error(
                    "Order {$order->getIncrementId()} got an error: " . $e->getMessage(),
                    $e->getTrace()
                );

                throw new \LogicException($e->getMessage());

            }
        }

        return $items;
    }

    /**
     * @param Order\Item $item
     * @return mixed
     */
    protected function getItemQty(Order\Item $item)
    {
        $qty = (int)$item->getQtyOrdered() - (int)$item->getQtyCanceled();
        return max($qty, 0);
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function validateItems(Order $order)
    {
        $result = true;

        $validateArray = [];
        $validateArray[self::TYPE_PRODUCT_SIMPLE] = 0;
        $validateArray[self::TYPE_PRODUCT_CONFIGURABLE] = 0;

        foreach ($order->getAllItems() as $item) {
            $validateArray[$item->getProductType()]++;
        }

        if ($validateArray[self::TYPE_PRODUCT_CONFIGURABLE] > $validateArray[self::TYPE_PRODUCT_SIMPLE]) {
            $result = false;
        }
        return $result;
    }

    /**
     * @param Order $order
     * @param array $currentItems
     * @return array
     */
    protected function getBrokenItemsData(Order $order, array $currentItems)
    {
        $items = [];
        foreach ($order->getAllItems() as $item) {
            if (self::TYPE_PRODUCT_SIMPLE == $item->getProductType()) {
                continue;
            }
            if (!isset($currentItems[$item->getSku()]) && self::TYPE_PRODUCT_CONFIGURABLE == $item->getProductType()) {
                $items[$item->getSku()] = [
                    'Qty'=>$this->getItemQty($item),
                    'SN'=>$item->getSku()
                ];

                $message = "Broken Item ID {$item->getId()}, SKU {$item->getSku()} in order {$this->_currentOrder->getIncrementId()}";
                $this->commonLogic->getLogger()->error($message);
            }
        }

        return $items;
    }
}
