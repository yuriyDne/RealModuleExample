<?php

namespace Fisha\OrderFlow\Model\InventoryApi;

use \Fisha\OrderFlow\Model\Api\Response;
use \Fisha\OrderFlow\Model\Api\ResponseFactory;
use Fisha\OrderFlow\Model\Config;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Client
{
    const ACTION_GET_ORDER_STATUS = 'GetOrderStatus/';
    const ACTION_GET_SHIPMENT_STATUS = 'GetShipmentStatus/';

    const ACTION_SEND_ORDER = 'SendOrders';


    /**
     * @var Config
     */
    protected Config $config;
    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;
    /**
     * @var ResponseFactory
     */
    protected ResponseFactory $responseFactory;
    /**
     * @var Json
     */
    protected Json $serializer;
    /**
     * @var \Fisha\OrderFlow\Model\Http\Client
     */
    protected $httpClient;

    /**
     * Client constructor.
     *
     * @param Config $config
     * @param \Fisha\OrderFlow\Model\Http\Client $httpClient
     */
    public function __construct(
        Config $config,
        \Fisha\OrderFlow\Model\Http\Client $httpClient
    ) {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $orderIncrementId
     * @return Response
     */
    public function getOrderStatusResponse(string $orderIncrementId)
    {
        return $this->sendGetRequest($this->getURI(self::ACTION_GET_ORDER_STATUS . $orderIncrementId));
    }

    /**
     * @param string $orderIncrementId
     * @return Response
     */
    public function getShipmentStatusResponse(string $orderIncrementId)
    {
        return $this->sendGetRequest($this->getURI(self::ACTION_GET_SHIPMENT_STATUS . $orderIncrementId));
    }


    /**
     * @param array $orderData
     * @return Response
     */
    public function getSendOrderResponse(array $orderData)
    {
        return $this->sendPostRequest(
            $this->getURI(self::ACTION_SEND_ORDER),
            [$orderData]
        );
    }

    /**
     * @param string $string
     * @return string
     */
    protected function getURI(string $string)
    {
        return $this->config->getInventoryApiURL() . $string;
    }

    /**
     * @param string $action
     * @param array $params
     * @return Response
     */
    protected function sendGetRequest(string $action, array $params = []): Response
    {
        return $this->httpClient->sendGetRequest($action, $params);
    }

    /**
     * @param string $action
     * @param array $params
     * @return Response
     */
    protected function sendPostRequest(string $action, array $params = []): Response
    {
        return $this->httpClient->sendPostRequest($action, $params);
    }
}
