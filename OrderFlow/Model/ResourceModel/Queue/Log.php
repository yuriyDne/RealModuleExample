<?php

namespace Fisha\OrderFlow\Model\ResourceModel\Queue;

class Log  extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('order_flow_log', 'entity_id');
    }
}
