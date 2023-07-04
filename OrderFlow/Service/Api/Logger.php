<?php

namespace Fisha\OrderFlow\Service\Api;

use Fisha\OrderFlow\Model\Api\Response;
use Fisha\OrderFlow\Model\Queue\Log;
use Fisha\OrderFlow\Model\Queue\LogFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var LogFactory
     */
    protected $logFactory;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logger constructor.
     *
     * @param LogFactory $logFactory
     * @param LoggerInterface $logger
     * @param Json $serializer
     */
    public function __construct(
        LogFactory $logFactory,
        LoggerInterface $logger,
        Json $serializer
    ) {
        $this->logFactory = $logFactory;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function execute(OrderInterface $order, Response $response, string $apiType)
    {
        try {
            /** @var Log $logModel */
            $logModel = $this->logFactory->create();
            $dataToInsert = [
                'order_id' => $order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'state' => $order->getState(),
                'status' => $order->getStatus(),
                'created_at' => date('Y-m-d H:i:s'),
                'message' => $response->getErrorMessage(),
                'api_type' => $apiType,
                'api_request' => $response->getRequest(),
                'api_response' => $this->serializer->serialize($response->getResponse()),
                'is_success' => (int) $response->isSuccess()
            ];
            $logModel->setData($dataToInsert);
            $logModel->save();
        } catch (\Exception $e) {
            $this->logger->error('Cannot save API log: '. $e->getMessage());
        }
    }
}
