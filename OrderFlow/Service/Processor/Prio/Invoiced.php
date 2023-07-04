<?php

namespace Fisha\OrderFlow\Service\Processor\Prio;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Exception\Processor\ProcessFailedStatusException;
use Fisha\OrderFlow\Model\Api\Response;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Api\Adapter\Inventory\SendOrderToApi;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class Invoiced
 * @package Fisha\OrderFlow\Service\Processor\Prio
 */
class Invoiced extends AbstractProcessor
{
    /**
     * @var SendOrderToApi
     */
    protected SendOrderToApi $sendOrderToApi;

    /**
     * Invoiced constructor.
     *
     * @param CommonLogic $commonLogic
     * @param SendOrderToApi $sendOrderToApi
     */
    public function __construct(
        CommonLogic $commonLogic,
        SendOrderToApi $sendOrderToApi
    ) {
        parent::__construct($commonLogic);
        $this->sendOrderToApi = $sendOrderToApi;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        try {
            $apiResult = $this->sendOrderToApi->execute($order);
            $this->logResult($order, $apiResult);
            if (!$apiResult->isSuccess()) {
                $message = "Cannot Send Order {$order->getIncrementId()} to InvApi: " . json_encode($apiResult->getResponse());
                $this->throwFailedStatusException($order, $message);
            }
            $this->addCommentToOrder($order, 'InvApiOrderExport Result: ' . json_encode($apiResult->getResponse()));
            return $this->createResult();
        } catch (ProcessFailedStatusException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->getLogger()->critical($e->getMessage(), $e->getTrace());
            $message = "Cannot Send Order {$order->getIncrementId()} to InvApi: {$e->getMessage()}";
            $this->throwFailedStatusException($order, $message);
        }
    }

    /**
     * @param OrderInterface $order
     * @param Response $apiResult
     */
    protected function logResult(OrderInterface $order, Response $apiResult)
    {
        $logData = [
            'orderId' => $order->getId(),
            'incrementId' => $order->getIncrementId(),
            'success' => $apiResult->isSuccess(),
            'request' => $apiResult->getRequest(),
            'response' => $apiResult->getResponse(),
        ];

        $this->getLogger()->debug('InvApi Order Export Data: ' . json_encode($logData));
    }
}
