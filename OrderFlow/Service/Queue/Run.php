<?php

namespace Fisha\OrderFlow\Service\Queue;

use Fisha\OrderFlow\Model\Order\OrderDataResolver;
use Fisha\OrderFlow\Model\Queue\QueueDataResolver;
use Fisha\OrderFlow\Service\RunProcessorService;
use Fisha\ProcessLocker\Api\LockerInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class Run
 * @package Fisha\OrderFlow\Service\Queue
 */
class Run implements \Fisha\OrderFlow\Api\QueueServiceInterface
{
    /**
     * @var AddItems
     */
    protected AddItems $addItemsService;
    /**
     * @var RemoveItems
     */
    protected RemoveItems $removeItemsService;
    /**
     * @var QueueDataResolver
     */
    protected QueueDataResolver $queueItemsResolver;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var RunProcessorService
     */
    protected RunProcessorService $runProcessorService;
    /**
     * @var LockerInterface
     */
    protected $lockUpdateItemsProcess;
    /**
     * @var OrderDataResolver
     */
    protected $orderDataResolver;

    /**
     * Run constructor.
     *
     * @param AddItems $addItemsService
     * @param QueueDataResolver $queueItemsResolver
     * @param RemoveItems $removeItemsService
     * @param LockerInterface $lockUpdateItemsProcess
     * @param OrderDataResolver $orderDataResolver
     * @param RunProcessorService $runProcessorService
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddItems            $addItemsService,
        QueueDataResolver   $queueItemsResolver,
        RemoveItems         $removeItemsService,
        LockerInterface     $lockUpdateItemsProcess,
        OrderDataResolver   $orderDataResolver,
        RunProcessorService $runProcessorService,
        LoggerInterface     $logger
    ) {
        $this->addItemsService = $addItemsService;
        $this->removeItemsService = $removeItemsService;
        $this->queueItemsResolver = $queueItemsResolver;
        $this->logger = $logger;
        $this->runProcessorService = $runProcessorService;
        $this->lockUpdateItemsProcess = $lockUpdateItemsProcess;
        $this->orderDataResolver = $orderDataResolver;
    }

    /**
     * @return string
     */
    public function execute() : string
    {
        if ($this->lockUpdateItemsProcess->lock()) {
            $this->logger->debug('Lock Run Process at '. date('Y-m-d H:i:s'));
        } else {
            $this->logger->debug('Run Process is locked. Skipping run');
            return (string)false;
        }

        try {
            $this->addItemsService->execute();
            $this->processQueueByStatuses();
            $this->removeItemsService->execute();
        } catch (\Exception $e) {
            $this->logger->critical('Run process Failed: ' . $e->getMessage());
        }

        $this->lockUpdateItemsProcess->unlock();

        return (string)true;
    }

    protected function processQueueByStatuses()
    {
        $statuses = $this->queueItemsResolver->getUniqueStatuses();
        foreach ($statuses as $status) {
            try {
                $this->processItemsByStatus($status);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param $status
     */
    protected function processItemsByStatus($status)
    {
        $this->logger->debug("--- Start process status " . $status);
        $orderCollection = $this->orderDataResolver->getOrdersToProcess($status);

        /** @var Order $order */
        foreach ($orderCollection as $order) {
            try {
                $this->logger->debug('--- Start processing Order ID ' . $order->getId() . ', #' . $order->getIncrementId());
                $this->runProcessorService->execute($order);
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
            $this->logger->debug('--- End processing Order ID ' . $order->getId() . ', #' . $order->getIncrementId());
        }
    }
}
