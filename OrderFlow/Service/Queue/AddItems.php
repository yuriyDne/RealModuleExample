<?php

namespace Fisha\OrderFlow\Service\Queue;

use Fisha\OrderFlow\Model\Order\OrderDataResolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddItems
 * @package Fisha\OrderFlow\Service\Queue
 */
class AddItems
{
    /**
     * @var OrderDataResolver
     */
    protected OrderDataResolver $orderDataResolver;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $dbConnection;

    /**
     * AddItems constructor.
     *
     * @param OrderDataResolver $orderDataResolver
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderDataResolver $orderDataResolver,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->orderDataResolver = $orderDataResolver;
        $this->logger = $logger;
        $this->dbConnection = $resourceConnection->getConnection();
    }

    public function execute()
    {
        $collection = $this->orderDataResolver->getOrdersToInsert();
        $now = date('Y-m-d H:i:s');
        $dataToInsert = [];
        foreach ($collection->getData() as $orderData) {
            $dataToInsert[] = [
                    'order_id' => $orderData['entity_id'],
                    'increment_id' => $orderData['increment_id'],
                    'state' => $orderData['state'],
                    'status' => $orderData['status'],
                    'created_at' => $now,
                ];
        }

        if (!empty($dataToInsert)) {
            $this->insertOnDuplicate($dataToInsert);
        }
    }

    /**
     * @param array $dataToInsert
     * @return int
     */
    protected function insertOnDuplicate(array $dataToInsert)
    {
        $newRows = 0;

        try {
            $tableName = $this->dbConnection->getTableName('order_flow_queue');

            $newRows = $this->dbConnection->insertOnDuplicate(
                $tableName,
                $dataToInsert,
                ['status', 'state', 'created_at']
            );
            $this->logger->debug('Added  ' . $newRows . ' orders to queue');

        } catch (\Exception $e) {
            $this->logger->critical('Cannot add orderflow Queue items: ' . $e->getMessage(), $e->getTrace());
        }

        return $newRows;
    }
}
