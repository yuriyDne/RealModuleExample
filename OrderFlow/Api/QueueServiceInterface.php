<?php


namespace Fisha\OrderFlow\Api;

/**
 * Interface QueueServiceInterface
 * @package Fisha\OrderFlow\Api
 */
interface QueueServiceInterface
{
    public function execute() : string;
}
