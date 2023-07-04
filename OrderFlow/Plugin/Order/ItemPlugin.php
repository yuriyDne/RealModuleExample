<?php

namespace Fisha\OrderFlow\Plugin\Order;

use Magento\Sales\Model\Order\Item;

class ItemPlugin
{
    const KEY_CANCELLED = 'orderflow_cancelled_items';

    /**
     * Retrieve item qty available for cancel
     *
     * @param Item $item
     * @param float|integer $result
     * @return float|integer
     */
    public function afterGetQtyToCancel(Item $item, $result)
    {
        if (!$item->hasData(self::KEY_CANCELLED)) {
            return $result;
        }
        $qtyToCancel = min($item->getQtyToInvoice(), $item->getQtyToShip(), $item->getData(self::KEY_CANCELLED));
        $item->unsetData(self::KEY_CANCELLED);
        return max($qtyToCancel, 0);
    }

}
