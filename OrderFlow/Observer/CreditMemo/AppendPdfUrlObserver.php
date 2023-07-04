<?php

namespace Fisha\OrderFlow\Observer\CreditMemo;

use Fisha\OrderFlow\Service\Order\Refund;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

class AppendPdfUrlObserver implements ObserverInterface
{
    /**
     * @var Refund
     */
    private Refund $refundOrderService;

    /**
     * @param Refund $refundOrderService
     */
    public function __construct(
        Refund $refundOrderService
    ) {
        $this->refundOrderService = $refundOrderService;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var DataObject $transportObject */
        $transportObject = $observer->getData('transportObject');
        /** @var CreditmemoInterface $creditMemo */
        $creditMemo = $transportObject->getData('creditmemo');
        $order = $transportObject->getData('order');
        $creditMemoUrl = $this->refundOrderService->getCreditMemoPath($order, $creditMemo);
        $transportObject->setData('attachmentUrl', $creditMemoUrl);
    }
}
