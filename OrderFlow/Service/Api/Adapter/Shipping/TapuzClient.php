<?php

namespace Fisha\OrderFlow\Service\Api\Adapter\Shipping;

use Fisha\BaldarShipping\Model\Api\Baldar;
use Fisha\OrderFlow\Exception\Processor\RestartException;
use Fisha\OrderFlow\Model\Api\Response\OrderApiStatus;
use Fisha\OrderFlow\Model\Api\Response\OrderApiStatusFactory;
use Fisha\OrderFlow\Model\Api\ResponseFactory;
use Fisha\OrderFlow\Model\Config\OrderStatus;
use Fisha\OrderFlow\Service\Api\Logger;
use Magento\Sales\Api\Data\OrderInterface;

class TapuzClient
{
    const API_TYPE = 'TapuzShippingApi';

    const STATUS_ERROR = '0';
    const STATUS_OPEN = '1';
    const STATUS_MOVE_TO_DELIVERY = '2';
    const STATUS_DONE = '3';
    const STATUS_COLLECTED = '4';
    const STATUS_RETURN_FROM_DOUBLE = '5';
    const STATUS_OK_EXECUTION = '6';
    const STATUS_OK_EXECUTION2 = '7';
    const STATUS_CANCEL = '8';
    const STATUS_SECOND_MESSENGER = '9';
    const STATUS_PENDING_DELIVERY = '10';
    const STATUS_GENERAL_ERROR = '-999';

    const OK_MESSAGE = 'OK';

    /**
     * @var array
     */
    const ERROR_STATUSES = [
        self::STATUS_CANCEL,
        self::STATUS_ERROR,
        self::STATUS_GENERAL_ERROR
    ];

    const API_TO_ORDER_STATUS_MAP = [
        self::STATUS_OPEN => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_COLLECTED => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_OK_EXECUTION => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_OK_EXECUTION2 => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_CANCEL => OrderStatus::STATUS_DELIVERY_FAILED,
        self::STATUS_RETURN_FROM_DOUBLE => OrderStatus::STATUS_DELIVERY_FAILED,
        self::STATUS_PENDING_DELIVERY => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_MOVE_TO_DELIVERY => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_SECOND_MESSENGER => OrderStatus::STATUS_DELIVERY_SHIPPED,
        self::STATUS_DONE => OrderStatus::STATUS_DELIVERY_RECEIVED,
    ];

    /**
     * @var OrderApiStatusFactory
     */
    protected $orderStatusFactory;
    /**
     * @var Baldar
     */
    protected $baldarApi;
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;
    /**
     * @var Logger
     */
    protected $apiLogger;

    /**
     * TapuzClient constructor.
     *
     * @param OrderApiStatusFactory $orderStatusFactory
     * @param Baldar $baldarApi
     */
    public function __construct(
        OrderApiStatusFactory $orderStatusFactory,
        ResponseFactory $responseFactory,
        Logger $apiLogger,
        Baldar $baldarApi
    ) {
        $this->orderStatusFactory = $orderStatusFactory;
        $this->baldarApi = $baldarApi;
        $this->responseFactory = $responseFactory;
        $this->apiLogger = $apiLogger;
    }

    /**
     * @param OrderInterface $order
     * @param string $orderTrackingNumber
     * @return OrderApiStatus
     */
    public function execute(OrderInterface $order, string $orderTrackingNumber): OrderApiStatus
    {
        $apiResponse = $this->sendApiRequest($orderTrackingNumber);
        $this->logApiResponse($order, $orderTrackingNumber, $apiResponse);
        $shippingStatusCode = $this->parseApiResponse($apiResponse);

        if (in_array($shippingStatusCode, self::ERROR_STATUSES)) {
            throw new RestartException('Home Delivery was failed , Tracking status is: ' . $shippingStatusCode);
        }

        $nextOrderStatus = $this->mapCodeToStatus($shippingStatusCode);

        /** @var OrderApiStatus $orderStatus */
        $orderApiStatus = $this->orderStatusFactory->create();
        $orderApiStatus->setStatus($nextOrderStatus);

        return $orderApiStatus;
    }

    /**
     * @param \stdClass $response
     * @return string
     */
    private function parseApiResponse(\stdClass $response)
    {
        $statusCode      = self::STATUS_ERROR;
        $responseMessage = '';
        $property        = 'ListDeliveryDetailsResult';

        $responseArray = [];

        if (property_exists($response, $property)) {
            $responseArray = (array)simplexml_load_string($response->ListDeliveryDetailsResult, "SimpleXMLElement", LIBXML_NOCDATA);
        }

        if (isset($responseArray['StatusCode'])) {
            $statusCode      = $responseArray['StatusCode'];
            $responseMessage = strtoupper($responseArray['StatusMessage']) ?? '';
        }

        if ($statusCode === self::STATUS_ERROR
            && $responseMessage == self::OK_MESSAGE
            && isset($responseArray['Records'])
        ) {
            $statusCode = $this->getStatusFromResultRecords($responseArray['Records']);
        }

        return $statusCode;
    }

    /**
     * @param array $records
     *
     * @return string
     */
    private function getStatusFromResultRecords($records)
    {
        $status = self::STATUS_ERROR;

        if (isset($records->Record->DeliveryStatus)) {
            $deliveryStatus = (array)$records->Record->DeliveryStatus;
            $status         = (int)$deliveryStatus[0];
        }

        return $status;
    }

    /**
     * @param string $shippingStatusCode
     * @return mixed
     */
    private function mapCodeToStatus(string $shippingStatusCode)
    {
        if (!array_key_exists($shippingStatusCode, self::API_TO_ORDER_STATUS_MAP)) {
            throw new RestartException('Unknown API status: '.$shippingStatusCode);
        }

        return self::API_TO_ORDER_STATUS_MAP[$shippingStatusCode];
    }

    /**
     * @param string $orderTrackingNumber
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function sendApiRequest(string $orderTrackingNumber)
    {
        return $this->baldarApi->gteCustomerRecords($orderTrackingNumber);
    }

    /**
     * @param OrderInterface $order
     * @param string $orderTrackingNumber
     * @param $apiResponse
     */
    private function logApiResponse(OrderInterface $order, string $orderTrackingNumber, $apiResponse)
    {
        $logResponse = $this->responseFactory->create();
        $requestData = [
            'trackingNumber' => $orderTrackingNumber
        ];
        $logResponse->setRequest(json_encode($requestData));
        $responseAsArray = json_decode(json_encode($apiResponse), true);
        $logResponse->setResponse($responseAsArray);
        $this->apiLogger->execute($order, $logResponse, self::API_TYPE);
    }
}
