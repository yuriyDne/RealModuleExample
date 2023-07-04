<?php
namespace Fisha\OrderFlow\Model\ResourceModel;

class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('order_flow_queue', 'entity_id');
    }
}
