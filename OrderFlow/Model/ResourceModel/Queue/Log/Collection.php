<?php

namespace Fisha\OrderFlow\Model\ResourceModel\Queue\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'entity_id';


    protected function _construct()
    {
        $this->_init(
            \Fisha\OrderFlow\Model\Queue\Log::class,
            \Fisha\OrderFlow\Model\ResourceModel\Queue\Log::class
        );
    }
}
