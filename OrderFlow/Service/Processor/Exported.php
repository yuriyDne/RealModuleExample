<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Api\Response\OrderApiStatus;
use Fisha\OrderFlow\Model\Config\OrderStatus;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Api\Adapter\Inventory\GetOrderApiStatus;
use Fisha\OrderFlow\Service\Order\CancelOrderItems;
use LogicException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 * Class Exported
 * @package Fisha\OrderFlow\Service\Processor
 */
class Exported extends AbstractProcessor
{
    const STATUS_INV_PICKED = 'picked';
    const STATUS_INV_CANCELLED = 'cancelled';
    const STATUS_PROCESSING = 'processing';

    /**
     * @var GetOrderApiStatus
     */
    protected GetOrderApiStatus $getOrderApiStatus;
    /**
     * @var CancelOrderItems
     */
    protected $cancelOrderItems;

    /**
     * Exported constructor.
     * @param CommonLogic $commonLogic
     * @param GetOrderApiStatus $getOrderApiStatus
     * @param CancelOrderItems $cancelOrderItems
     */
    public function __construct(
        CommonLogic $commonLogic,
        GetOrderApiStatus $getOrderApiStatus,
        CancelOrderItems $cancelOrderItems
    ) {
        parent::__construct($commonLogic);
        $this->getOrderApiStatus = $getOrderApiStatus;
        $this->cancelOrderItems = $cancelOrderItems;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $result = $this->createResult();
        $orderApiStatusResult = $this->getOrderApiStatus->execute($order);
        $apiStatus = $orderApiStatusResult->getStatus();

        if ($apiStatus) {
            if ($apiStatus !== self::STATUS_PROCESSING) {
                $comment = sprintf('Got <strong>" %s "</strong> status from INV API', $apiStatus);
                $this->addCommentToOrder($order, $comment);
            }
            $nextStatus = $this->processApiStatus($orderApiStatusResult, $order);
            $result->setStatus($nextStatus);

        } else {
            $errorMsg = 'Can\'t retrive order Status via INV API' . $order->getIncrementId();
            $this->throwRestartException($errorMsg);
        }

        return $result;
    }

    /**
     * @param OrderApiStatus $orderApiStatusResult
     * @param Order $order
     * @return Exported|string
     */
    protected function processApiStatus(OrderApiStatus $orderApiStatusResult, Order $order)
    {
        switch ($status = $orderApiStatusResult->getStatus()) {
            case self::STATUS_INV_CANCELLED:
                $result =  $this->processCanceledStatus($order);
                break;

            case self::STATUS_INV_PICKED:
                $result = $this->processCanceledItems($orderApiStatusResult, $order);
                break;

            case self::STATUS_PROCESSING:
                $this->throwRestartException(
                    "Order: {$order->getIncrementId()} InvApi order status is processing. Need re-send request"
                );

            default:
                throw new LogicException('Status ' . $status . ' is Unknown');
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function processCanceledStatus(Order $order)
    {
        $status = OrderStatus::STATUS_REFUND_TRANSACTION;
        $this->addCommentToOrder(
            $order,
            'Got ' . $status . ' status INV API. Order will be canceled automatically.'
        );
        return $status;
    }

    /**
     * @param OrderApiStatus $orderApiStatusResult
     * @param Order $order
     * @return string
     */
    protected function processCanceledItems(OrderApiStatus $orderApiStatusResult, Order $order)
    {
        return (count($orderApiStatusResult->getCanceledItems()) > 0)
            ? OrderStatus::STATUS_REFUND_TRANSACTION_PARTIALLY
            : self::STATUS_INV_PICKED;
    }
}
