<?php

namespace Fisha\OrderFlow\Service\Processor\Inv\Cancel;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Order\Refund;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Fisha\OrderFlow\Service\Processor\Edea\RegisterTransaction;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;

class Transaction extends AbstractProcessor
{
    /**
     * @var OrderService
     */
    protected $orderService;
    /**
     * @var RegisterTransaction
     */
    protected $registerEdeaTransaction;
    private Refund $refundOrderEmailService;

    /**
     * Transaction constructor.
     *
     * @param CommonLogic $commonLogic
     * @param RegisterTransaction $registerEdeaTransaction
     * @param Refund $refundOrderEmailService
     * @param OrderService $orderService
     */
    public function __construct(
        CommonLogic $commonLogic,
        RegisterTransaction $registerEdeaTransaction,
        Refund $refundOrderEmailService,
        OrderService $orderService
    ) {
        parent::__construct($commonLogic);
        $this->orderService = $orderService;
        $this->registerEdeaTransaction = $registerEdeaTransaction;
        $this->refundOrderEmailService = $refundOrderEmailService;
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {
        $result = $this->registerEdeaTransaction->execute($order);
        /** @var Order $order */
        if ($order->canCancel()) {
            $this->orderService->cancel($order->getId());
        }
        $creditMemo = $order->getCreditmemosCollection()->getLastItem();
        /** @var Order\Creditmemo $creditMemo */
        if ($creditMemo->getId()) {
            $creditMemoPath = $this->refundOrderEmailService->getCreditMemoPath($order, $creditMemo);
            $result->setAttachment($creditMemoPath);
            $result->setMimeType(ResultInterface::MIME_TYPE_PDF);
        }

        return $result;
    }
}
