<?php

namespace Fisha\OrderFlow\Console\Order;

use Fisha\OrderFlow\Service\RunProcessorService;
use Magento\Framework\App\State;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestRun
 * @package Fisha\OrderFlow\Console\Order
 */
class TestRun extends Command
{
    const ORDER_ID = 'order_id';
    const STATUS = 'status';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var RunProcessorService
     */
    protected $runProcessorService;
    /**
     * @var State
     */
    protected $appState;

    /**
     * Test constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param RunProcessorService $runProcessorService
     * @param State $appState
     * @param string|null $name
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RunProcessorService $runProcessorService,
        State $appState,
        string $name = null
    ) {
        $this->orderRepository = $orderRepository;

        parent::__construct($name);
        $this->runProcessorService = $runProcessorService;
        $this->appState = $appState;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return false
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');
        $orderId = $input->getArgument(self::ORDER_ID);
        /** @var Order $order */
        $order  = $this->orderRepository->get($orderId);
        $status = $input->getArgument(self::STATUS);
        if ($status) {
            $currentStatus = $order->getStatus();
            $orderFlowStatuses = $this->getOrderFlowStatuses();
            if (!in_array($status, $orderFlowStatuses)) {
                $output->write('Order status :'.$status.' not related to orderflow');
                return false;
            }
            $order->setStatus($status);
            $order->getResource()->saveAttribute($order, 'status');
            $output->write("Order status was changed from {$currentStatus} to {$status}");
        } else {
            $this->runProcessorService->execute($order);

            $output->write("Current order status is {$order->getStatus()}, state {$order->getState()}" . PHP_EOL);
        }

    }

    /**
     * @return array
     */
    protected function getOrderFlowStatuses()
    {
        $reflectionClass = new \ReflectionClass(\Fisha\OrderFlow\Model\Config\OrderStatus::class);
        $constants = $reflectionClass->getConstants();
        return array_values($constants);
    }

    protected function configure()
    {
        $this->setName('orderflow:order:testRun')
            ->setDescription('Test OrderFlow Order Processing')
            ->addArgument(
                self::ORDER_ID,
                InputArgument::REQUIRED,
                'Order Id.'
            )->addArgument(
                self::STATUS,
                InputArgument::OPTIONAL,
                'Status'
            );
    }
}
