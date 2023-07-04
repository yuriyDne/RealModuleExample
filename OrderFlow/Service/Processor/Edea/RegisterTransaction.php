<?php

namespace Fisha\OrderFlow\Service\Processor\Edea;

use Fisha\EdeaClient\Exception\Timeout;
use Fisha\EdeaIntegration\Api\FinalTransactionManagementInterface;
use Fisha\EdeaIntegration\Model\FinalTransactionManagement;
use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class RegisterTransaction
 * @package Fisha\OrderFlow\Service\Processor\Edea
 */
class RegisterTransaction extends AbstractProcessor
{
    /**
     * @var FinalTransactionManagement
     */
    protected $transactionManagement;
    /**
     * @var string
     */
    protected $transactionType;

    /**
     * Processing constructor.
     *
     * @param CommonLogic $commonLogic
     * @param FinalTransactionManagementInterface $transactionManagement
     * @param string $transactionType
     */
    public function __construct(
        CommonLogic $commonLogic,
        FinalTransactionManagementInterface $transactionManagement,
        string $transactionType
    ) {
        parent::__construct($commonLogic);
        $this->transactionManagement = $transactionManagement;
        $this->transactionType = $transactionType;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $edeaOrderId = $this->registerTransaction($order);

        return $this->createResult();
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function registerTransaction(OrderInterface $order)
    {
        try {
            $externalOrderId = $this->transactionManagement->registerTransaction($order);
            return $externalOrderId;
        } catch (Timeout $e) {
            $this->throwRestartException('Send to Edea Timeout Error - need to re-send');
        } catch (\Exception $e) {
            $trace = json_encode(array_slice($e->getTrace(), 0, 10));
            $this->throwFailedStatusException(
                $order,
                "Send {$this->transactionType} transaction to Edea Error: {$e->getMessage()} ". $trace,
                true
            );
        }
    }

}
