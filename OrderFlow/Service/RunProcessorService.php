<?php

namespace Fisha\OrderFlow\Service;

use Fisha\OrderFlow\Exception\Processor\ProcessFailedStatusException;
use Fisha\OrderFlow\Exception\Processor\RestartException;
use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Fisha\OrderFlow\Model\Factory\NotificationSenderFactory;
use Fisha\OrderFlow\Model\Factory\ProcessorConfigFactory;
use Fisha\OrderFlow\Model\Factory\ProcessorFactory;
use Fisha\OrderFlow\Model\Notification\Sender;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Queue\Item\ScheduleNextRun;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\OrderFactory as OrderResourceModelFactory;
use Psr\Log\LoggerInterface;

/**
 * Class RunProcessorService
 * @package Fisha\OrderFlow\Service
 */
class RunProcessorService
{
    /**
     * @var Sender
     */
    protected Sender $notificationSender;
    /**
     * @var NotificationSenderFactory
     */
    protected NotificationSenderFactory $notificationSenderFactory;
    /**
     * @var ProcessorConfigFactory
     */
    protected ProcessorConfigFactory $processorConfigFactory;
    /**
     * @var ProcessorFactory
     */
    protected ProcessorFactory $processorFactory;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var ScheduleNextRun
     */
    protected ScheduleNextRun $scheduleNextRun;
    /**
     * @var CommonLogic
     */
    protected CommonLogic $commonLogic;
    /**
     * @var bool
     */
    protected bool $isTestMode = false;

    /**
     * @var OrderResourceModelFactory
     */
    protected OrderResourceModelFactory $orderResourceModelFactory;

    /**
     * RunProcessorService constructor.
     *
     * @param Sender $notificationSender
     * @param NotificationSenderFactory $notificationSenderFactory
     * @param ProcessorConfigFactory $processorConfigFactory
     * @param Queue\Item\ScheduleNextRun $scheduleNextRun
     * @param CommonLogic $commonLogic
     * @param ProcessorFactory $processorFactory
     * @param bool $isTestMode
     * @param LoggerInterface $logger
     */
    public function __construct(
        Sender $notificationSender,
        NotificationSenderFactory $notificationSenderFactory,
        ProcessorConfigFactory $processorConfigFactory,
        ScheduleNextRun $scheduleNextRun,
        CommonLogic $commonLogic,
        ProcessorFactory $processorFactory,
        OrderResourceModelFactory $orderResourceModelFactory,
        bool $isTestMode = false,
        LoggerInterface $logger
    ) {
        $this->notificationSender = $notificationSender;
        $this->notificationSenderFactory = $notificationSenderFactory;
        $this->processorConfigFactory = $processorConfigFactory;
        $this->processorFactory = $processorFactory;
        $this->logger = $logger;
        $this->scheduleNextRun = $scheduleNextRun;
        $this->commonLogic = $commonLogic;
        $this->isTestMode = $isTestMode;
        $this->orderResourceModelFactory = $orderResourceModelFactory;
    }

    /**
     * @param Order $order
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Order $order)
    {
        $orderStatus = $order->getStatus();
        $processorConfig = $this->processorConfigFactory->create($orderStatus);
        $processor = $this->processorFactory->create($processorConfig);
        $notificationSender = $this->notificationSenderFactory->create($processorConfig); // ?

        try {
            $result = $processor->execute($order);
            $nextStatus = $result->getStatus();
            if ($nextStatus === null) {
                $nextStatus = $processorConfig->getNextStatus();
            }
            $this->validateNexStatus($nextStatus, $processorConfig);
            if ($nextStatus !== $orderStatus) {
                $this->updateOrderStatus($order, $nextStatus);
                $notificationSender->execute($order, $result);
            }
        } catch (RestartException $e) {
            $this->scheduleNextRun($processorConfig, $order, $e);
        } catch (ProcessFailedStatusException $e) {
            $result = $this->processFailedStatus($order, $processorConfig, $e->getMessage());
            $notificationSender->execute($order, $result);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $this->retryFailedRunByUnknownReason($order, $processorConfig, $e->getMessage());
        }
    }

    /**
     * @param Order $order
     * @param string $nextStatus
     */
    protected function updateOrderStatus(Order $order, string $nextStatus)
    {
        $order->setStatus($nextStatus);
        $orderResourceModel = $this->orderResourceModelFactory->create();
        $orderResourceModel->saveAttribute($order, 'status');
    }

    /**
     * @param string $nextStatus
     * @param ProcessorConfig $processorConfig
     */
    protected function validateNexStatus(string $nextStatus, ProcessorConfig $processorConfig)
    {
        if (!in_array($nextStatus, $processorConfig->getPossibleStatuses())) {
            $this->logger->warning("{$nextStatus} is not specified as next status for {$processorConfig->getProcessStatus()}. Please add it to orderflow_status.xml");
        }
    }

    /**
     * @param Order $order
     * @param ProcessorConfig $processorConfig
     * @param string $errorMessage
     * @return \Fisha\OrderFlow\Model\Processor\Result
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function processFailedStatus(Order $order, ProcessorConfig $processorConfig, $errorMessage = '')
    {
        $failedStatus = $processorConfig->getFailedStatus();
        if (!empty($failedStatus)) {
            $this->commonLogic->addCommentToOrder($order, "Changes status to  {$failedStatus} automatically via orderflow. Need to check order manually");
            $this->updateOrderStatus($order, $failedStatus);

            $this->scheduleNextRun->markProcessAsFailed($order, $errorMessage);
        } else {
            throw new \LogicException("Failed status is not specified for status: {$processorConfig->getProcessStatus()}");
        }

        $result = $this->commonLogic->createResult();
        $result->setStatus($order->getStatus());
        $result->setErrorMessage($errorMessage);

        return $result;
    }

    /**
     * @param ProcessorConfig $processorConfig
     * @param Order $order
     * @param RestartException $restartException
     */
    protected function scheduleNextRun(
        ProcessorConfig $processorConfig,
        Order $order,
        RestartException $restartException
    ) {
        if (!$this->isTestMode) {
            $message = $restartException->getMessage();
            if ($restartException->getNextRunInMinutes() > 0) {
                $processorConfig->setNextRunInMinutes($restartException->getNextRunInMinutes());
            }
            $this->scheduleNextRun->execute($processorConfig, $order, $message);
        }
    }

    /**
     * @param Order $order
     * @param ProcessorConfig $processorConfig
     * @param string $errorMessage
     */
    protected function retryFailedRunByUnknownReason(Order $order, ProcessorConfig $processorConfig, string $errorMessage)
    {
        if (!$this->isTestMode) {
            $this->scheduleNextRun->retryFailedRunByUnknownReason(
                $order,
                $processorConfig,
                $errorMessage
            );
        }
    }
}
