<?php

namespace Fisha\OrderFlow\Model;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    public $_cacheTag = 'fisha_order_flow_queue';
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Fisha\OrderFlow\Model\ResourceModel\Queue::class);
    }

    public function increaseAttemptsCount()
    {
        $this->setData('attempts_count', $this->getData('attempts_count') + 1);
    }
}
