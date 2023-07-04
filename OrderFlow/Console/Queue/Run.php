<?php

namespace Fisha\OrderFlow\Console\Queue;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Run
 * @package Fisha\OrderFlow\Console\Queue
 */
class Run extends Command
{
    /**
     * @var \Fisha\OrderFlow\Service\Queue\Run
     */
    protected $runService;
    /**
     * @var State
     */
    protected $appState;

    /**
     * Run constructor.
     *
     * @param \Fisha\OrderFlow\Service\Queue\Run $runService
     * @param State $appState
     * @param string|null $name
     */
    public function __construct(
        \Fisha\OrderFlow\Service\Queue\Run $runService,
        State $appState,
        string $name = null
    ) {
        $this->runService = $runService;
        parent::__construct($name);
        $this->appState = $appState;
    }

    protected function configure()
    {
        $this->setName('orderflow:queue:run');
        $this->setDescription('Run Fisha OrderFlow Queue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');
        $this->runService->execute();
    }

}
