<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Inventory\Request\Order;

use Fisha\ProductImport\Model\ResourceModel\Archive\CollectionFactory;
use Fisha\ProductImport\Model\ResourceModel\Archive\Collection;
use Magento\Sales\Model\Order\Item;

class GetVinSn
{
    /**
     * @var string[]
     */
    private array $skuToVinSn = [];
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param Item $item
     * @return string
     */
    public function execute(Item $item): string
    {
        $product = $item->getProduct();
        $vinSn = $product ? $product->getData('vinv_sn') : '';
        if (empty($vinSn)) {
            $vinSn = $this->getFromArchiveData($item->getSku());
        }

        return (int)$vinSn;
    }

    /**
     * @param string $sku
     * @return string
     */
    private function getFromArchiveData(string $sku): string
    {
        if (!array_key_exists($sku, $this->skuToVinSn)) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('sku', $sku);
            $this->skuToVinSn[$sku] = $collection->getFirstItem()->getData('vinv_sn') ?: '';
        }

        return $this->skuToVinSn[$sku];
    }
}
