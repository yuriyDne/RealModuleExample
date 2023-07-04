<?php

namespace Fisha\OrderFlow\Service\Order;

use Fisha\OrderFlow\Exception\Processor\ProcessFailedStatusException;
use Fisha\OrderFlow\Exception\Processor\RestartException;
use Magento\Sales\Model\Order;

class MatchRefundedItemsService
{
    /**
     * @param Order $order
     * @param array $skuToQty
     * @return mixed
     */
    public function execute(Order $order, array $skuToQty)
    {
        $items = [];

        foreach ($order->getAllItems() as $item) {
            if (isset($skuToQty[$item->getSku()])
                && !$item->isDummy()
            ) {
                $sku = $item->getSku();
                $qty = $skuToQty[$item->getSku()];

                if ($qty <= 0) {
                    throw new RestartException("refund qty {$qty} for product {$sku} should be greater than 0");
                }

                $availableCountForRefund = $this->getAvailableForRefundCount($item);
                if ($qty > $availableCountForRefund) {
                    throw new ProcessFailedStatusException("Not enough items count for refund {$qty} products {$sku} - only {$availableCountForRefund} available");
                }
                $items[$item->getId()] = $qty;
            }
        }

        return $items;
    }

    /**
     * @param Order\Item $item
     * @return float|null
     */
    protected function getAvailableForRefundCount(Order\Item $item)
    {
        // @todo check difference between cancel item and refund item
        return $item->getQtyInvoiced() - $item->getQtyRefunded();
    }

}
