<?php

namespace Fisha\OrderFlow\Service\Order;

use Klarna\Core\Model\OrderRepository;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class ResetOrder
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private OrderRepositoryInterface $orderRepository;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
    }

    public function execute(Order $order)
    {
        /** @var Order $order */
        $incrementId = $order->getIncrementId();
        $order->reset();
        $order->loadByIncrementId($incrementId);
        $this->orderRepository->save($order);
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        if ($invoice->getId()) {
            // Reload invoice in repository registry
            $this->invoiceRepository->save($invoice);
        }


        return $order;
    }
}
