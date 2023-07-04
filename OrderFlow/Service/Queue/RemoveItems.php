<?php

namespace Fisha\OrderFlow\Service\Queue;

use Fisha\OrderFlow\Model\Config;
use Magento\Framework\App\ResourceConnection;

/**
 * Class RemoveItems
 * @package Fisha\OrderFlow\Service\Queue
 */
class RemoveItems
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected \Magento\Framework\DB\Adapter\AdapterInterface $dbConnection;
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * RemoveItems constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param Config $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Config $config
    ) {
        $this->dbConnection = $resourceConnection->getConnection();
        $this->config = $config;
    }

    public function execute()
    {
        $orderTable = $this->dbConnection->getTableName('sales_order');
        $orderflowQueueTable = $this->dbConnection->getTableName('order_flow_queue');

        $leaveInQueueFailedOrderStatuses = $this->config->getLeaveInQueueFailedOrderStatuses();
        $select = $this->dbConnection->select();
        $select->from(['q' => $orderflowQueueTable], 'entity_id')
            ->join(['o' => $orderTable], 'o.entity_id = q.order_id and o.status <> q.status', []);

        if (!empty($leaveInQueueFailedOrderStatuses)) {
            $select->where('q.status not in (?)', $leaveInQueueFailedOrderStatuses);
        }

        $entityIds = $this->dbConnection->fetchCol($select);
        if (!empty($entityIds)) {
            $entityIdsStr = implode(',', $entityIds);
            $this->dbConnection->delete($orderflowQueueTable, "entity_id in ({$entityIdsStr})");
        }

        $this->removeByStatuses();
    }

    /**
     * @return int
     */
    public function removeByStatuses()
    {
        $deletedRows = 0;

        $removeFromQueueStatuses = $this->config->getRemoveFromQueueStatuses();

        $orderflowQueueTable = $this->dbConnection->getTableName('order_flow_queue');

        if (count($removeFromQueueStatuses) > 0) {
            $deletedRows = $this->dbConnection->delete(
                $orderflowQueueTable,
                "status in (" . $this->dbConnection->quote($removeFromQueueStatuses) . ")"
            );
        }

        return $deletedRows;
    }
}
