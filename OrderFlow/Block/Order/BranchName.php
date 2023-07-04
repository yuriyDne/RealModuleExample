<?php

namespace Fisha\OrderFlow\Block\Order;

use Fisha\Branches\Api\BranchRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class BranchName extends Template
{
    protected $_template = 'Fisha_OrderFlow::order/branch_name.phtml';

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;
    /**
     * @var BranchRepositoryInterface
     */
    private BranchRepositoryInterface $branchRepository;

    /**
     * @param Template\Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param BranchRepositoryInterface $branchRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        OrderRepositoryInterface $orderRepository,
        BranchRepositoryInterface $branchRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->branchRepository = $branchRepository;
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBranchName(): ?string
    {
        $branchName = '';
        $orderId = $this->getData('order_id');
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);
        $shippingAddress = $order->getShippingAddress();
        $branchId = $shippingAddress->getData('storepickup_branch_id');

        if ($branchId) {
            $branch = $this->branchRepository->getById($branchId);
            $branchName = $branch->getName();
        }

        return $branchName;
    }
}
