<?php

namespace Fisha\OrderFlow\Model\Config;

use Fisha\OrderFlow\Api\SuccessStatusResolverInterface;
use Fisha\OrderFlow\Service\Notification\Sender;

class ProcessorConfig
{
    /**
     * @var string
     */
    protected string $processStatus;
    /**
     * @var string|null
     */
    protected ?string $nextStatus;
    /**
     * @var int
     */
    protected int $attemptsCount;
    /**
     * @var int
     */
    protected int $nextRunInMinutes;
    /**
     * @var array
     */
    protected $possibleStatuses;
    /**
     * @var string
     */
    protected $sendNotificationClass;
    /**
     * @var string
     */
    protected $processor;
    /**
     * @var string|null
     */
    protected $failedStatus;

    /**
     * Processor constructor.
     *
     * @param string $processStatus
     * @param string|null $nextStatus
     * @param string|null $failedStatus
     * @param int $attemptsCount
     * @param string $processor
     * @param int $nextRunInMinutes
     * @param array $possibleStatuses
     * @param string $sendNotificationClass
     */
    public function __construct(
        string $processStatus,
        string $processor,
        string $nextStatus = null,
        string $failedStatus = null,
        int $attemptsCount = 100,
        int $nextRunInMinutes = 0,
        array $possibleStatuses = [],
        string $sendNotificationClass = Sender::class
    ) {
        $this->processStatus         = $processStatus;
        $this->nextStatus            = $nextStatus;
        $this->attemptsCount         = $attemptsCount;
        $this->nextRunInMinutes      = $nextRunInMinutes;
        $this->possibleStatuses      = $possibleStatuses;
        $this->sendNotificationClass = $sendNotificationClass;
        $this->processor             = $processor;
        $this->failedStatus          = $failedStatus;
    }

    /**
     * @return string
     */
    public function getProcessStatus()
    {
        return $this->processStatus;
    }

    /**
     * @return string|null
     */
    public function getNextStatus()
    {
        return $this->nextStatus;
    }

    /**
     * @return int
     */
    public function getAttemptsCount()
    {
        return $this->attemptsCount;
    }

    /**
     * @return int
     */
    public function getNextRunInMinutes()
    {
        return $this->nextRunInMinutes;
    }

    /**
     * @param int $nextRunInMinutes
     * @return false|string
     */
    public function getNextRunAt(int $nextRunInMinutes)
    {
        return date('Y-m-d H:i:s', strtotime("+{$nextRunInMinutes} minutes"));
    }

    /**
     * @return array
     */
    public function getPossibleStatuses()
    {
        return $this->possibleStatuses;
    }

    /**
     * @param array $possibleStatuses
     * @return ProcessorConfig
     */
    public function setPossibleStatuses(array $possibleStatuses): ProcessorConfig
    {
        $this->possibleStatuses = $possibleStatuses;
        return $this;
    }

    /**
     * @return string
     */
    public function getSendNotificationClass()
    {
        return $this->sendNotificationClass;
    }

    /**
     * @param string $sendNotificationClass
     * @return ProcessorConfig
     */
    public function setSendNotificationClass(string $sendNotificationClass): ProcessorConfig
    {
        $this->sendNotificationClass = $sendNotificationClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param string $processor
     * @return ProcessorConfig
     */
    public function setProcessor(string $processor): ProcessorConfig
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFailedStatus()
    {
        return $this->failedStatus;
    }

    /**
     * @param string|null $failedStatus
     * @return ProcessorConfig
     */
    public function setFailedStatus(?string $failedStatus): ProcessorConfig
    {
        $this->failedStatus = $failedStatus;
        return $this;
    }

    /**
     * @param int $nextRunInMinutes
     * @return ProcessorConfig
     */
    public function setNextRunInMinutes(int $nextRunInMinutes): ProcessorConfig
    {
        $this->nextRunInMinutes = $nextRunInMinutes;
        return $this;
    }
}
