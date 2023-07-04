<?php

namespace Fisha\OrderFlow\Model\Processor;


use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;

class Result implements ResultInterface
{
    /**
     * @var string|null
     */
    protected ?string $status = null;

    /**
     * @var string|null
     */
    protected ?string $attachment = null;

    /**
     * @var string|null
     */
    protected ?string $mimeType = null;

    /**
     * @var string|null
     */
    protected ?string $errorMessage = null;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ResultInterface
     */
    public function setStatus(string $status): ResultInterface
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param string|null $attachment
     * @return ResultInterface
     */
    public function setAttachment(string $attachment): ResultInterface
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $mimeType
     * @return Result
     */
    public function setMimeType(?string $mimeType): ResultInterface
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string|null $errorMessage
     * @return ResultInterface
     */
    public function setErrorMessage(?string $errorMessage): ResultInterface
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }


}
