<?php

namespace Fisha\OrderFlow\Console\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Clean
 * @package Fisha\OrderFlow\Console\Queue
 */
class Clean extends Command
{
    /**
     * @var \Fisha\OrderFlow\Service\Queue\Clean
     */
    protected \Fisha\OrderFlow\Service\Queue\Clean $cleanService;

    public function __construct(
        \Fisha\OrderFlow\Service\Queue\Clean $cleanService,
        string $name = null
    ) {
        $this->cleanService = $cleanService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('orderflow:queue:clean');
        $this->setDescription('Clean Fisha OrderFlow Queue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $this->cleanService->execute();

        $output->write("Result query: {$query}" . PHP_EOL);
    }

}
