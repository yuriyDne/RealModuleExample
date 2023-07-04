<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\ProcessorInterface;
use Fisha\OrderFlow\Exception\Processor\ProcessFailedStatusException;
use Fisha\OrderFlow\Exception\Processor\RestartException;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Model\Processor\Result;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

abstract class AbstractProcessor implements ProcessorInterface
{
    /**
     * @var CommonLogic
     */
    protected CommonLogic $commonLogic;

    /**
     * AbstractProcessor constructor.
     *
     * @param CommonLogic $commonLogic
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommonLogic $commonLogic
    ) {
        $this->commonLogic = $commonLogic;
    }

    /**
     * @param Order $order
     * @param string $comment
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function addCommentToOrder(Order $order, string $comment)
    {
        $this->commonLogic->addCommentToOrder($order, $comment);
    }

    /**
     * @return Result
     */
    protected function createResult(): Result
    {
        return $this->commonLogic->createResult();
    }

    /**
     * @param string $message
     * @param int $nextRunInMinutes
     */
    protected function throwRestartException(string $message, int $nextRunInMinutes = 0)
    {
        throw new RestartException($message, $nextRunInMinutes);
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->commonLogic->getLogger();
    }

    /**
     * @param Order $order
     * @param string $state
     */
    protected function changeOrderState(Order $order, string $state)
    {
        $order->setState(Order::STATE_PROCESSING);
        $order->getResource()->saveAttribute($order, 'state');
    }

    protected function changeOrderStatus(Order $order, string $status)
    {
        $order->setStatus($status);
        $order->getResource()->saveAttribute($order, 'status');
    }

    /**
     * @param Order $order
     * @param $message
     * @param bool $addToOrderComment
     * @param bool $addToLogs
     */
    protected function throwFailedStatusException(
        Order $order,
        $message,
        bool $addToOrderComment = false,
        bool $addToLogs = true
    ) {
        if ($addToOrderComment) {
            $this->addCommentToOrder($order, $message);
        }

        if ($addToLogs) {
            $this->getLogger()->error($message);
        }

        throw new ProcessFailedStatusException($message);
    }
}
