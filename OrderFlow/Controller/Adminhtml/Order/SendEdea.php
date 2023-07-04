<?php

namespace Fisha\OrderFlow\Controller\Adminhtml\Order;

use Fisha\ClerkRefund\Model\OrderFlow\Sender;
use Fisha\OrderFlow\Model\Config\OrderStatus;
use Fisha\OrderFlow\Model\Queue;
use Fisha\OrderFlow\Model\Queue\QueueDataResolver;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class SendEdea  extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Fisha_OrderFlow';

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;
    /**
     * @var Sender
     */
    private Sender $sender;
    private QueueDataResolver $queueDataResolver;

    /**
     * @param Context $context
     * @param Sender $sender
     * @param QueueDataResolver $queueDataResolver
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Sender $sender,
        QueueDataResolver $queueDataResolver,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->sender = $sender;
        $this->queueDataResolver = $queueDataResolver;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $orderId = (int) $request->getParam('orderId');

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $this->sender->sendToEdea($order);

        $this->messageManager->addSuccessMessage('Order was sent to EDEA');
        $order->setStatus(OrderStatus::STATUS_PICKED);
        $order->save();

        /** @var Queue $queueItem */
        $queueItem = $this->queueDataResolver->getByOrder($order);
        if ($queueItem->getId()) {
            $queueItem->delete();
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
