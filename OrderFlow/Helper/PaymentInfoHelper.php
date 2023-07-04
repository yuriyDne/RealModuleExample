<?php
declare(strict_types=1);

namespace Fisha\OrderFlow\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;


class PaymentInfoHelper extends AbstractHelper
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected \Magento\Payment\Helper\Data $paymentHelper;
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    protected StoreManagerInterface $storeManager;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->paymentHelper = $paymentHelper;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $orderId
     * @return string
     * @throws \Exception
     */
    public function getPaymentInfoHtml(int $orderId): string
    {
        $order = $this->orderRepository->get($orderId);
        $storeId = $this->storeManager->getStore()->getId();
        return $this->paymentHelper->getInfoBlockHtml($order->getPayment(), $storeId);
    }

}
