<?php

namespace Fisha\OrderFlow\Service\Processor;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Model\Source\StopProcessingReason;
use Fisha\OrderFlow\Service\Queue\Item\ScheduleNextRun;
use Magento\Sales\Api\Data\OrderInterface;

class ProcessFailedStatus extends AbstractProcessor
{
    /**
     * @var ScheduleNextRun
     */
    protected ScheduleNextRun $scheduleNextRun;

    /**
     * ProcessFailedStatus constructor.
     *
     * @param CommonLogic $commonLogic
     * @param ScheduleNextRun $scheduleNextRun
     */
    public function __construct(
        CommonLogic $commonLogic,
        ScheduleNextRun $scheduleNextRun
    ) {
        parent::__construct($commonLogic);
        $this->scheduleNextRun = $scheduleNextRun;
    }

    public function execute(OrderInterface $order): ResultInterface
    {
        $message = $this->getErrorMessage($order);
        $this->getLogger()->debug($message);

        // Stop order processing but leave it in queue
        $this->scheduleNextRun->stopProcessing($order, StopProcessingReason::FAILED_PROCESSOR_STATUS, $message);

        $result = $this->createResult();
        $result->setStatus($order->getStatus());
        return $result;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    protected function getErrorMessage(OrderInterface $order)
    {
        return "Order {$order->getIncrementId()} status is {$order->getStatus()}. MANUAL_CHECK need";
    }
}
