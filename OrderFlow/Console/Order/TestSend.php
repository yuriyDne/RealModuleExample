<?php

namespace Fisha\OrderFlow\Console\Order;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Notification\Sender;
use Magento\Framework\App\State;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestSend extends Command
{
    const ORDER_ID = 'order_id';
    const ATTACHMENT_PATH = 'attachment_path';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var State
     */
    protected $appState;
    /**
     * @var Sender
     */
    protected $sender;
    /**
     * @var CommonLogic
     */
    protected $commonLogic;

    /**
     * Test constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param Sender $sender
     * @param CommonLogic $commonLogic
     * @param State $appState
     * @param string|null $name
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Sender $sender,
        CommonLogic $commonLogic,
        State $appState,
        string $name = null
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($name);
        $this->appState = $appState;
        $this->sender = $sender;
        $this->commonLogic = $commonLogic;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');
        $orderId = $input->getArgument(self::ORDER_ID);
        /** @var Order $order */
        $order  = $this->orderRepository->get($orderId);

        $emptyProcessorResult = $this->commonLogic->createResult();

        $attachmentPath = $input->getArgument(self::ATTACHMENT_PATH);
        if ($attachmentPath) {
            $emptyProcessorResult->setAttachment($attachmentPath); ///var/creditmemo/sales_order_creditmemo_1000174489_1640683044.pdf'
            $emptyProcessorResult->setMimeType(ResultInterface::MIME_TYPE_PDF);
        }

        $this->sender->execute($order, $emptyProcessorResult);
    }

    protected function configure()
    {
        $this->setName('orderflow:order:testSend')
            ->setDescription('Test OrderFlow Order Processing')
            ->addArgument(
                self::ORDER_ID,
                InputArgument::REQUIRED,
                'Order Id.'
            )->addArgument(
                self::ATTACHMENT_PATH,
                InputArgument::OPTIONAL,
                'Attachmant Path'
            );


    }
}
