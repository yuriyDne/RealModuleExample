<?php

namespace Fisha\OrderFlow\Model\Api;

class Response
{
    /**
     * @var bool
     */
    protected bool $isSuccess = false;
    /**
     * @var string
     */
    protected string $errorMessage = '';
    /**
     * @var string
     */
    protected string $request = '';
    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     * @return Response
     */
    public function setErrorMessage(string $errorMessage): Response
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $request
     * @return Response
     */
    public function setRequest(string $request): Response
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $response
     * @return Response
     */
    public function setResponse(array $response): Response
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     * @return Response
     */
    public function setIsSuccess(bool $isSuccess): Response
    {
        $this->isSuccess = $isSuccess;
        return $this;
    }
}
