<?php

namespace Fisha\OrderFlow\Api\Data;

interface QueueItemInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return QueueItemInterface
     */
    public function setId(int $id): QueueItemInterface;

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return QueueItemInterface
     */
    public function setOrderId(int $orderId): QueueItemInterface;

    /**
     * @return string
     */
    public function getIncrementId();

    /**
     * @param string $incrementId
     * @return QueueItemInterface
     */
    public function setIncrementId(string $incrementId): QueueItemInterface;

    /**
     * @return string
     */
    public function getState();

    /**
     * @param string $state
     * @return QueueItemInterface
     */
    public function setState(string $state): QueueItemInterface;

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return QueueItemInterface
     */
    public function setStatus(string $status): QueueItemInterface;

    /**
     * @return string
     */
    public function getLastError();

    /**
     * @param string $lastError
     * @return QueueItemInterface
     */
    public function setLastError(string $lastError): QueueItemInterface;

    /**
     * @param int $reasonType
     * @return QueueItemInterface
     */
    public function setStopProcessingReason(int $reasonType): QueueItemInterface;

    /**
     * @return int
     */
    public function getAttemptsCount();

    /**
     * @param int $attemptsCount
     * @return QueueItemInterface
     */
    public function setAttemptsCount(int $attemptsCount): QueueItemInterface;

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return QueueItemInterface
     */
    public function setCreatedAt(string $createdAt): QueueItemInterface;

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return QueueItemInterface
     */
    public function setUpdatedAt(string $updatedAt): QueueItemInterface;

    public function increaseAttemptsCount();

    /**
     * @return string
     */
    public function getNextUpdate();

    /**
     * @param string $nextUpdateInterval
     * @return QueueItemInterface
     */
    public function setNextUpdate(string $nextUpdateInterval): QueueItemInterface;
}
