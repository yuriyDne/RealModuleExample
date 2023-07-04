<?php

namespace Fisha\OrderFlow\Model\Queue;

class Log extends \Magento\Framework\Model\AbstractModel
{
    public $_cacheTag = 'fisha_order_flow_queue_log';
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Fisha\OrderFlow\Model\ResourceModel\Queue\Log::class);
    }

}
