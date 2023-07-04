<?php

namespace Fisha\OrderFlow\Service\Queue\Item;

use Fisha\OrderFlow\Api\Data\QueueItemInterface;
use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Fisha\OrderFlow\Model\Queue;
use Fisha\OrderFlow\Model\Queue\QueueDataResolver;
use Fisha\OrderFlow\Model\Source\StopProcessingReason;
use Magento\Sales\Model\Order;

class ScheduleNextRun
{
    const FAILED_NEXT_RUN_IN_MINUTES = 1;
    const FAILED_RUN_MAX_ATTEMPTS = 20;

    const MAX_UPDATE_INTERVAL = 60*24; // 1 day

    const RETRY_NEXT_UPDATE_AT = [
        1 => 10,
        2 => 60,
        3 => 120, // 2 hours
        4 => 240, // 4 hours
        5 => 240, // 4 hours
        6 => 240, // 4 hours
        7 => 360, // 6 hours
        8 => 360, // 6 hours
        9 => 360, // 6 hours
        10 => 360, // 6 hours
        11 => 720, // 12 hours
        12 => 720, // 12 hours
        13 => self::MAX_UPDATE_INTERVAL,
    ];
    /**
     * @var QueueDataResolver
     */
    protected QueueDataResolver $queueDataResolver;

    /**
     * ScheduleItem constructor.
     *
     * @param QueueDataResolver $queueDataResolver
     */
    public function __construct(
        QueueDataResolver $queueDataResolver
    ) {
        $this->queueDataResolver = $queueDataResolver;
    }

    public function execute(
        ProcessorConfig $processorConfig, Order $order, string $message)
    {
        /** @var QueueItemInterface $queueItem */
        $queueItem = $this->queueDataResolver->getByOrder($order);
        $queueItem->setLastError($message)
            ->increaseAttemptsCount();

        $nextRunInMinutes = $processorConfig->getNextRunInMinutes();
        if ($nextRunInMinutes > 0) {
            $queueItem->setNextUpdate($processorConfig->getNextRunAt($nextRunInMinutes));
        } else {
            $attemptsCount = $queueItem->getAttemptsCount();
            $nextRunInMinutes = self::RETRY_NEXT_UPDATE_AT[$attemptsCount] ?? self::MAX_UPDATE_INTERVAL;
            $queueItem->setNextUpdate($processorConfig->getNextRunAt($nextRunInMinutes));
        }

        $queueItem->save();
        if ($queueItem->getAttemptsCount() > $processorConfig->getAttemptsCount()) {
            $this->stopProcessing(
                $order,
                StopProcessingReason::MAX_ATTEMPTS_COUNT_REACHED,
                'Max Attempts Count Reached'
            );
        }
    }

    /**
     * @param Order $order
     * @param string $errorMessage
     * @param string $stopProcessingReason
     */
    public function markProcessAsFailed(
        Order $order,
        string $errorMessage = '',
        string $stopProcessingReason = StopProcessingReason::FAILED_PROCESSOR_STATUS
    )
    {
        /** @var QueueItemInterface $queueItem */
        $queueItem = $this->queueDataResolver->getByOrder($order);

        $queueItem->setStatus($order->getStatus());
        $queueItem->setStopProcessingReason($stopProcessingReason);

        if (strlen($errorMessage)) {
            $queueItem->setLastError($errorMessage);
        }

        $queueItem->save();
    }

    /**
     * @param Order $order
     * @param int $reason
     * @param string|null $errorMessage
     * @return bool
     */
    public function stopProcessing(Order $order, int $reason, ?string $errorMessage)
    {
        if ($reason < 1) {
            return false;
        }

        /** @var QueueItemInterface $queueItem */
        $queueItem = $this->queueDataResolver->getByOrder($order);

        /** @var QueueItemInterface $queueItem */
        $queueItem->setStopProcessingReason($reason)
            ->setLastError($errorMessage)
            ->save();

        return true;
    }

    /**
     * @param Order $order
     * @param ProcessorConfig $processorConfig
     * @param string $errorMessage
     */
    public function retryFailedRunByUnknownReason(Order $order, ProcessorConfig $processorConfig, string $errorMessage)
    {
        /** @var QueueItemInterface|Queue $queueItem */
        $queueItem = $this->queueDataResolver->getByOrder($order);
        if (!$queueItem->getId()) {
            return false;
        }
        $queueItem->setAttemptsCount($queueItem->getAttemptsCount() + 1);
        if ($queueItem->getAttemptsCount() > self::FAILED_RUN_MAX_ATTEMPTS) {
            $this->markProcessAsFailed($order, $errorMessage, StopProcessingReason::UNKNOWN_REASON);
        }

        $nextRunInMinutes = max($processorConfig->getNextRunInMinutes(), self::FAILED_NEXT_RUN_IN_MINUTES);
        $nextRunAt = $processorConfig->getNextRunAt($nextRunInMinutes * $queueItem->getAttemptsCount());
        $queueItem->setNextUpdate($nextRunAt);
        $queueItem->save();
    }
}
