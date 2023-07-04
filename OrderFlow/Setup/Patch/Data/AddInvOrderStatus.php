<?php

namespace Fisha\OrderFlow\Setup\Patch\Data;

use Fisha\OrderFlow\Model\Config\OrderStatus;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

/**
 * Class AddEdeaOrderStatus
 * @package Fisha\OrderFlow\Setup\Patch\Data
 */
class AddInvOrderStatus implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    private $statusResourceFactory;

    /**
     * InstallOrderStatusesAndInitialSalesConfig constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => OrderStatus::STATUS_INV_CANCEL_CREDIT_EMAIL,
            'label' => 'Invoice Cancel Credit Email',
        ]);
        $statusResource->save($status);
        $status->assignState(Order::STATE_PROCESSING, false, true);

        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => OrderStatus::STATUS_INV_CANCEL_TRANSACTION_EMAIL,
            'label' => 'Invoice Cancel Transaction Email',
        ]);
        $statusResource->save($status);
        $status->assignState(Order::STATE_PROCESSING, false, true);

    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

}
