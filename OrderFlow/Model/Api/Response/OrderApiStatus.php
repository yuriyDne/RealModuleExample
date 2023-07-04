<?php

namespace Fisha\OrderFlow\Model\Api\Response;

/**
 * Class OrderApiStatus
 * @package Fisha\OrderFlow\Model\Api\Response
 */
class OrderApiStatus
{
    /**
     * @var string
     */
    protected string $status = '';
    /**
     * @var string
     */
    protected string $orderIncrementId;
    /**
     * @var array
     */
    protected array $canceledItems = [];

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return OrderApiStatus
     */
    public function setStatus(string $status): OrderApiStatus
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderIncrementId(): string
    {
        return $this->orderIncrementId;
    }

    /**
     * @param string $orderIncrementId
     * @return OrderApiStatus
     */
    public function setOrderIncrementId(string $orderIncrementId): OrderApiStatus
    {
        $this->orderIncrementId = $orderIncrementId;
        return $this;
    }

    /**
     * @return array
     */
    public function getCanceledItems()
    {
        return $this->canceledItems;
    }

    /**
     * @return bool
     */
    public function hasCanceledItems()
    {
        return count($this->canceledItems) > 0;
    }

    /**
     * @param array $canceledItems
     * @return OrderApiStatus
     */
    public function setCanceledItems(array $canceledItems): OrderApiStatus
    {
        $this->canceledItems = $canceledItems;
        return $this;
    }
}
