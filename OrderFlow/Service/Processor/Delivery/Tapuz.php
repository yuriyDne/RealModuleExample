<?php

namespace Fisha\OrderFlow\Service\Processor\Delivery;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Api\Adapter\Shipping\TapuzClient;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Order\Track;

class Tapuz extends AbstractProcessor
{
    /**
     * @var TapuzClient
     */
    protected $tapuzApiClient;

    /**
     * Tapuz constructor.
     *
     * @param CommonLogic $commonLogic
     */
    public function __construct(
        CommonLogic $commonLogic,
        TapuzClient $tapuzApiClient
    ) {
        parent::__construct($commonLogic);
        $this->tapuzApiClient = $tapuzApiClient;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $trackNumber = $this->getOrdersTrackNumber($order);
        $orderNextStatus = $this->tapuzApiClient->execute($order, $trackNumber);

        if ($orderNextStatus->getStatus() === $order->getStatus()) {
            $this->throwRestartException('Shippind status wasn\'t changed');
        }

        $result = $this->createResult();
        $result->setStatus($orderNextStatus->getStatus());

        return $result;
    }

    /**
     * @param OrderInterface|Order $order
     * @return string
     */
    private function getOrdersTrackNumber(OrderInterface $order)
    {
        $tracksCollection = $order->getTracksCollection();
        if ($tracksCollection->count() > 1) {
            $this->throwRestartException('This order has more than 1 tracking number - need to be processed manually');
        }
        if ($tracksCollection->count() < 1) {
            $this->throwRestartException('This order has no tracking number - cannot be processed');
        }

        /** @var Track $trackModel */
        $trackModel  = $tracksCollection->getFirstItem();

        return $trackModel->getTrackNumber();
    }

}
