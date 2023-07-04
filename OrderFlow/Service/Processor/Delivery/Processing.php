<?php

namespace Fisha\OrderFlow\Service\Processor\Delivery;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Exception\Processor\ProcessFailedStatusException;
use Fisha\OrderFlow\Model\Config\OrderStatus;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Model\Queue\QueueDataResolver;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Fisha\OrderFlow\Service\Processor\StorePickup;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ShipOrder;

class Processing extends AbstractProcessor
{
    const RETRY_NEXT_UPDATE_AT = [
        1 => 10,
        2 => 60,
        3 => 360, // 6 hours
        4 => 60*24, // 1 day
        5 => 3*24*60, // 3 days
    ];

    /**
     * @var ShipOrder
     */
    private ShipOrder $shipOrderService;
    /**
     * @var QueueDataResolver
     */
    private QueueDataResolver $queueDataResolver;
    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $shipmentRepository;

    /**
     * Picked constructor.
     *
     * @param CommonLogic $commonLogic
     * @param ShipOrder $shipOrderService
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param StorePickup $storePickup
     */
    public function __construct(
        CommonLogic $commonLogic,
        ShipOrder $shipOrderService,
        QueueDataResolver $queueDataResolver,
        ShipmentRepositoryInterface $shipmentRepository
    ) {
        parent::__construct($commonLogic);
        $this->commonLogic = $commonLogic;
        $this->shipOrderService = $shipOrderService;
        $this->queueDataResolver = $queueDataResolver;
        $this->shipmentRepository = $shipmentRepository;
    }


    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $this->changeOrderStatus($order, OrderStatus::STATUS_DELIVERY_PROCESSING);
        $result = $this->createResult(); // Move to nex status by config
        if (!$this->isAlreadyShipped($order)) {
            $this->processShipmentCreation($order);
        }

        return $result;
    }
    /**
     * @param OrderInterface $order
     * @return bool
     */
    protected function isAlreadyShipped(OrderInterface $order): bool
    {
        $result = true;

        if (count($order->getShipmentsCollection()) === 0) {
            $result = false;
        }

        if ($result) {
            /** @var Shipment $shipment */
            $shipment = $order->getShipmentsCollection()->getFirstItem();
            $result = $this->validateTrackNumbers($order, $shipment);
        }

        return $result;
    }

    /**
     * @param OrderInterface $order
     * @param Shipment $shipment
     * @return bool
     */
    protected function validateTrackNumbers(OrderInterface $order, Shipment $shipment): bool
    {
        $result = count($shipment->getAllTracks()) > 0;

        if (!$result) {
            $this->throwFailedStatusException(
                $order,
                "Order {$order->getIncrementId()} already has shipment without track number. Need to check manually",
                true
            );
        };

        return $result;
    }

    /**
     * @param OrderInterface $order
     */
    protected function processShipmentCreation(OrderInterface $order)
    {
        try {
            $shipmentId = $this->shipOrderService->execute($order->getEntityId(), [], true);
            $shipment = $this->shipmentRepository->get($shipmentId);
            $this->validateTrackNumbers($order, $shipment);
        } catch (ProcessFailedStatusException $e) {
            throw $e;
        } catch (\Exception $e) {
            $errorMessage = "Cannot create shipment for order: {$order->getIncrementId()}: {$e->getMessage()}";
            $this->commonLogic->logException($errorMessage, $e);
            $this->processRetryLogic($order, $errorMessage);
        }
    }

    /**
     * @param OrderInterface $order
     * @param string $errorMessage
     */
    private function processRetryLogic(OrderInterface $order, string $errorMessage)
    {
        $queueItem = $this->queueDataResolver->getByOrder($order);
        $queueItem->setStatus(OrderStatus::STATUS_DELIVERY_PROCESSING);
        $queueItem->save();
        $retryStep = $queueItem->getAttemptsCount() + 1;
        $nextUpdateAt = 0;
        if (array_key_exists($retryStep, self::RETRY_NEXT_UPDATE_AT)) {
            $nextUpdateAt = self::RETRY_NEXT_UPDATE_AT[$retryStep];
        }

        $this->throwRestartException($errorMessage, $nextUpdateAt);
    }
}
