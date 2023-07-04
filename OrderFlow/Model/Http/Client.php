<?php

namespace Fisha\OrderFlow\Model\Http;

use Fisha\OrderFlow\Model\Api\Response;
use Fisha\OrderFlow\Model\Api\ResponseFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Inspection\Exception;
use Psr\Log\LoggerInterface;

/**
 * Class Client
 * TODO use \Fisha\EdeaClient\Api\HttpClientInterface as  http client
 * @package Fisha\OrderFlow\Model\Http
 */
class Client
{
    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Client constructor.
     *
     * @param ClientInterface $client
     * @param Json $serializer
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ClientInterface $client,
        Json $serializer,
        LoggerInterface $logger,
        ResponseFactory $responseFactory
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * @param string $action
     * @param array $params
     * @return Response
     */
    public function sendGetRequest(string $action, array $params = []): Response
    {

        /*
         *
        $result= [
            'status' => 'cancelled',
            'order_ID' => 'LOCALM21000174451',
            'cancelledItems' => [
                [
                    'sn' => 15660617100237,
                'qty' => 1
            ]
            ]
        ];
         */
        /** @var Response $response */
        $response = $this->responseFactory->create();
        if (!empty($params)) {
            $action.='?'.http_build_query($params);
        }
        $response->setIsSuccess(true);
        $response->setRequest($action);

        $this->logger->debug('INV API request:' . $response->getRequest());

        $this->client->get($action);

        $this->logger->debug('INV API response:' . $this->client->getBody());

        if ($this->client->getStatus() != 200) {
            $response->setIsSuccess(false);
            $response->setErrorMessage('API request failed. Got http status: ' . $this->client->getStatus());

            $this->logger->debug('INV API error:' . $response->getErrorMessage());
        }

        /**
         * unserialize if it's json string
         */
        $result = [];
        try {
            if ((bool)$this->client->getBody()) {
                $result = $this->serializer->unserialize($this->client->getBody());
            }

        } catch (Exception $e) {
            $result = [];
        }

        $response->setResponse($result);

        return $response;
    }

    /**
     * @param string $action
     * @param array $params
     * @return Response
     */
    public function sendPostRequest(string $action, array $params = []): Response
    {
        /** @var Response $response */
        $response = $this->responseFactory->create();
        $response->setIsSuccess(true);
        $response->setRequest($this->serializer->serialize([
            'action' => $action,
            'params' => $params,
        ]));

        /*
                 $result= [
            'status' => 'cancelled',
            'order_ID' => 'LOCALM21000174425',
            'cancelledItems' => [
                'sn' => 51058066802936,
                'qty' => 1

            ]
        ];
         */

        $this->logger->debug('INV API request:' . $response->getRequest());

        $this->client->addHeader('content-type', 'application/json');
        $this->client->post($action, $this->serializer->serialize($params));
        $this->client->removeHeader('content-type');

        $this->logger->debug('INV API response:' . $this->client->getBody());

        if ($this->client->getStatus() != 200) {
            $response->setIsSuccess(false);
            $response->setErrorMessage('API request failed. Got status: ' . $this->client->getStatus());

            $this->logger->debug('INV API error:' . $response->getErrorMessage());
        }

        /**
         * unserialize if it's json string
         */
        try {
            $result = $this->serializer->unserialize($this->client->getBody());

        } catch (Exception $e) {
            $result = [];
        }

        $response->setResponse($result);

        return $response;
    }
}
