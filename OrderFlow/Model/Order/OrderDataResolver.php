<?php

namespace Fisha\OrderFlow\Model\Order;

use Fisha\OrderFlow\Model\Config;
use Fisha\OrderFlow\Model\Queue\QueueDataResolver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * Class OrderDataResolver
 * @package Fisha\OrderFlow\Model\Order
 */
class OrderDataResolver
{
    const UPDATE_LIMIT = 1000;

    const MIN_CREATED_ORDER_DATE = "-1 Month";

    /**
     * @var QueueDataResolver
     */
    protected $queueDataResolver;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Config
     */
    protected $config;

    /**
     * OrderDataResolver constructor.
     *
     * @param QueueDataResolver $queueDataResolver
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        QueueDataResolver $queueDataResolver,
        Config $config,
        CollectionFactory $collectionFactory
    ) {
        $this->queueDataResolver = $queueDataResolver;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    /**
     * @param string $status
     * @return Collection|null
     */
    public function getOrdersToProcess(string $status): ?Collection
    {
        $queueItems = $this->queueDataResolver->getItemsToProcess($status, self::UPDATE_LIMIT);
        $orderIds = array_keys($queueItems);
        if (empty($orderIds)) {
            throw new \LogicException('No orders to process');
        }
        $ordersCollection = $this->collectionFactory->create();
        $ordersCollection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        /** @var Order $order */
        foreach ($ordersCollection as $order) {
            $this->queueDataResolver->appendQueueToOrder($order, $queueItems[$order->getId()]);
        }

        return $ordersCollection;
    }

    /**
     * @return Collection
     */
    public function getOrdersToInsert(): Collection
    {
        $minCreatedDate = $this->getMinCreateDate();
        $removeStatuses = $this->config->getRemoveFromQueueStatuses();
        $startOrderId = $this->config->getStartOrderId();

        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(['entity_id','state','status','increment_id'])
            ->addFieldToFilter('entity_id', ['gt' => $startOrderId])
            ->addFieldToFilter('main_table.created_at', ['gt' => $minCreatedDate])
            ->addFieldToFilter('main_table.status', ['nin' => $removeStatuses])
            ->addFieldToFilter('q.order_id', ['null' => true]);

        $queueTable = $collection->getResource()->getTable('order_flow_queue');

        $collection->getSelect()
            ->joinLeft(['q' => $queueTable], 'q.order_id = main_table.entity_id', [])
            ->order('main_table.created_at desc')
            ->limit(self::UPDATE_LIMIT);

        return $collection;
    }

    /**
     * @return string
     */
    protected function getMinCreateDate()
    {
        return date('Y-m-d', strtotime(self::MIN_CREATED_ORDER_DATE));
    }
}
