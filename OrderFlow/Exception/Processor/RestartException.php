<?php

namespace Fisha\OrderFlow\Exception\Processor;

use Throwable;

/**
 * Throw in case we need to restart processor next time
 *
 * Class RestartException
 *
 * @package Fisha\OrderFlow\Exception\Processor
 */
class RestartException extends \LogicException
{
    /**
     * @var int
     */
    protected int $nextRunInMinutes;

    public function __construct(
        $message = "",
        int $nextRunInMinutes = 0,
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->nextRunInMinutes = $nextRunInMinutes;
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
     */
    public function setNextRunInMinutes(int $nextRunInMinutes): void
    {
        $this->nextRunInMinutes = $nextRunInMinutes;
    }

}
