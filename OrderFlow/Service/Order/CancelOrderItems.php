<?php

namespace Fisha\OrderFlow\Service\Order;

use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Plugin\Order\ItemPlugin;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class CancelOrderItems
{
    /**
     * @var CommonLogic
     */
    protected $commonLogic;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CancelOrderItems constructor.
     *
     * @param CommonLogic $commonLogic
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommonLogic $commonLogic,
        LoggerInterface $logger
    ) {
        $this->commonLogic = $commonLogic;
        $this->logger = $logger;
    }

    /**
     * @param Order $order
     * @param array $cancelItems
     * @return int
     */
    public function execute(Order $order, array $cancelItems): int
    {
        $countCanceledItems = 0;
        $this->logger->debug('Start cancel items process ...');
        foreach ($order->getAllItems() as $item) {
            $itemSku = $item->getSku();
            if (!$item->isDummy()
                && isset($cancelItems[$itemSku])
                && (int)$cancelItems[$itemSku] > 0
            ) {
                $count = (int)$cancelItems[$itemSku];
                $this->cancelOrderItem($item, $count);
                $this->commonLogic->addCommentToOrder($order, 'Order Item sku' . $itemSku . ' was cancelled automatically');
                $this->logger->debug('Item SKU ' . $itemSku . ' was cancelled automatically.' );
                $countCanceledItems++;
            }
        }
        $this->logger->debug('Canceled ' . $countCanceledItems . ' items');
        return $countCanceledItems;
    }

    /**
     * @param Order\Item $item
     * @param int $qtyToCancel
     */
    protected function cancelOrderItem(Order\Item $item, int $qtyToCancel)
    {
        if ((int)$item->getQtyInvoiced() > 0) {
            // J4
            $item->setQtyCanceled($qtyToCancel);

        } else {
            // J5
            $item->setData(ItemPlugin::KEY_CANCELLED, $qtyToCancel);
            $item = $item->cancel();
        }

        $item->save();
    }
}
