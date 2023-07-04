<?php

namespace Fisha\OrderFlow\Model\Queue;

use Fisha\OrderFlow\Api\Data\QueueItemInterface;
use Fisha\OrderFlow\Model\Queue;
use Fisha\OrderFlow\Model\ResourceModel;
use Fisha\OrderFlow\Model\Source\StopProcessingReason;
use Magento\Sales\Model\Order;

/**
 * Class QueueDataResolver
 * @package Fisha\OrderFlow\Model\Queue
 */
class QueueDataResolver
{
    const KEY_ORDERFLOW = 'fisha_orderflow';
    /**
     * @var ResourceModel\Queue\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * QueueItemsResolver constructor.
     *
     * @param ResourceModel\Queue\CollectionFactory $collectionFactory
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Fisha\OrderFlow\Model\ResourceModel\Queue\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getUniqueStatuses(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(['status'])->getSelect()->group('status');
        $data = $collection->getData();
        return array_column($data, 'status');
    }

    /**
     * @param null $status
     * @return array
     */
    public function getOrderIdsByStatus($status = null)
    {
        $collection = $this->collectionFactory->create();
        if ($status) {
            $collection->addFieldToSelect(['order_id'])->addFieldToFilter('status', ['eq' => $status]);
        }
        $data = $collection->getData();

        return array_column($data, 'order_id');
    }

    /**
     * @param Order $order
     * @return QueueItemInterface
     */
    public function getByOrder(Order $order)
    {
        if (!empty($order->getData(self::KEY_ORDERFLOW))
            && $order->getData(self::KEY_ORDERFLOW) instanceof Queue
        ) {
            return $order->getData(self::KEY_ORDERFLOW);
        }
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('order_id', ['eq' => $order->getId()]);
        $collection->load();
        /** @var QueueItemInterface $queueItem */
        $queueItem = $collection->getFirstItem();
        if (!$queueItem->getOrderId()) {
            $queueItem->setOrderId($order->getId());
        }
        $order->setData(self::KEY_ORDERFLOW, $queueItem);
        return $order->getData(self::KEY_ORDERFLOW);
    }

    /**
     * @param string $status
     * @param $limit
     * @return Queue[]
     */
    public function getItemsToProcess(string $status, $limit)
    {
        $nowDate = date('Y-m-d H:i:s');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => $status])
            ->addFieldToFilter('next_update', ['lt' => $nowDate])
            ->addFieldToFilter('stop_processing_reason', StopProcessingReason::NO_REASON)
            ->setOrder('updated_at', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->setPageSize($limit);

        $result = [];
        /** @var Queue $item */
        foreach ($collection as $item) {
            $result[$item->getOrderId()] = $item;
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param Queue $queue
     */
    public function appendQueueToOrder(Order $order, Queue $queue)
    {
        $order->setData(self::KEY_ORDERFLOW, $queue);
    }
}
