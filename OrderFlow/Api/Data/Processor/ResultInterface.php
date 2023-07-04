<?php

namespace Fisha\OrderFlow\Api\Data\Processor;

use Fisha\OrderFlow\Model\Processor\Result;

/**
 * Interface ResultInterface
 * @package Fisha\OrderFlow\Api\Data\Processor
 */
interface ResultInterface
{
    const MIME_TYPE_PDF = 'application/pdf';

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return ResultInterface
     */
    public function setStatus(string $status): ResultInterface;

    /**
     * @return string|null
     */
    public function getAttachment();

    /**
     * @param string|null $attachment
     * @return ResultInterface
     */
    public function setAttachment(string $attachment): ResultInterface;

    /**
     * @return string|null
     */
    public function getMimeType();

    /**
     * @param string|null $mimeType
     * @return Result
     */
    public function setMimeType(?string $mimeType): ResultInterface;

    /**
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * @param string|null $errorMessage
     * @return ResultInterface
     */
    public function setErrorMessage(?string $errorMessage): ResultInterface;

}
