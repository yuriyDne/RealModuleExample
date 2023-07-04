<?php

namespace Fisha\OrderFlow\Service\Queue;

use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Class Clean
 * @package Fisha\OrderFlow\Service\Queue
 */
class Clean implements \Fisha\OrderFlow\Api\QueueServiceInterface
{
    const MIN_RECORD_CREATED_AT = '-1 Month';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $dbConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * RemoveItems constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->dbConnection = $resourceConnection->getConnection();
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function execute() : string
    {

        try {

            $orderflowQueueTable = $this->dbConnection->getTableName('order_flow_queue');
            $minRecordCreatedAtDate = date('Y-m-d', strtotime(self::MIN_RECORD_CREATED_AT));

            $select = $this->dbConnection->select();
            $select->from(['q' => $orderflowQueueTable], 'entity_id')
                ->where('created_at < ?', $minRecordCreatedAtDate);

            $result =  $this->dbConnection->deleteFromSelect($select, $orderflowQueueTable);

            $this->logger->debug('Cleanup of queue is success. Query: ' . $result );

        } catch (Exeption $e) {
            $this->logger->error('Cleanup of queue failed: ' . $e->getMessage(), $e->getTrace());

            throw  $e;
        }

        return $result;
    }
}
