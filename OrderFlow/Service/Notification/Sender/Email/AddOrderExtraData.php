<?php
declare(strict_types=1);

namespace Fisha\OrderFlow\Service\Notification\Sender\Email;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;

class AddOrderExtraData
{
    /**
     * @var Renderer
     */
    protected Renderer $addressRenderer;

    /**
     * @var Data
     */
    protected Data $paymentHelper;

    /**
     * @param Renderer $addressRenderer
     * @param Data $paymentHelper
     */
    public function __construct(
        Renderer $addressRenderer,
        Data $paymentHelper
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
    }


    /**
     * @param OrderInterface|Order $order
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function execute(OrderInterface $order): OrderInterface
    {
        $statusLabel = $order->getStatusLabel();
        $order->setData('customer_name', $order->getCustomerFirstname().' '.$order->getCustomerLastname());
        $order->setData('status_label', $statusLabel);
        $formattedBillingAddress = $this->addressRenderer->format($order->getBillingAddress(), 'html');
        $order->setData('formattedBillingAddress', $formattedBillingAddress);
        $formattedShippingAddress = $this->addressRenderer->format($order->getShippingAddress(), 'html');
        $order->setData('formattedShippingAddress', $formattedShippingAddress);

        $paymentHtml = $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStoreId()
        );

        $order->setData('payment_html', $paymentHtml);

        $creditMemo = $order->getCreditmemosCollection()->getLastItem();
        if ($creditMemo && $creditMemo->getId()) {
            $order->setData('creditmemo_id', $creditMemo->getId());
        }

        $shipment = $order->getShipmentsCollection()->getLastItem();
        if ($shipment && $shipment->getId()) {
            $order->setData('shipment_id', $shipment->getId());
        }

        return $order;
    }
}
