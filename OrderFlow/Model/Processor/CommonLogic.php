<?php

namespace Fisha\OrderFlow\Model\Processor;

use Fisha\OrderFlow\Api\Data\QueueItemInterface;
use Fisha\OrderFlow\Model\Queue\QueueDataResolver;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class CommonLogic
{
    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    protected OrderStatusHistoryRepositoryInterface $historyRepository;
    /**
     * @var ResultFactory
     */
    protected $resultFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var QueueDataResolver
     */
    protected $queueDataResolver;

    /**
     * CommonLogic constructor.
     *
     * @param OrderStatusHistoryRepositoryInterface $historyRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param QueueDataResolver $queueDataResolver
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        OrderStatusHistoryRepositoryInterface $historyRepository,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        QueueDataResolver $queueDataResolver,
        ResultFactory $resultFactory
    ) {
        $this->historyRepository = $historyRepository;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->queueDataResolver = $queueDataResolver;
    }

    /**
     * @param Order $order
     * @param string $comment
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function addCommentToOrder(Order $order, string $comment)
    {
        $comment = $order->addCommentToStatusHistory($comment);
        $this->historyRepository->save($comment);
    }

    /**
     * @param Order $order
     * @param string $errorMessage
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveOrderQueueErrorMessage(Order $order, string $errorMessage)
    {
        $queueItem = $this->queueDataResolver->getByOrder($order);
        /** @var QueueItemInterface $queueItem */
        $queueItem->setLastError($errorMessage);
        $queueItem->save();
    }

    /**
     * @return Result
     */
    public function createResult(): Result
    {
        return $this->resultFactory->create();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Order $order
     * @return OrderInterface|Order
     */
    public function resetOrderToDefaults(Order $order)
    {
        return $this->orderRepository->get($order->getId());
    }

    /**
     * @param string $message
     * @param \Exception $e
     */
    public function logException(string $message, \Exception $e)
    {
        $this->getLogger()->critical($message, ['exception' => $e]);
    }
}
