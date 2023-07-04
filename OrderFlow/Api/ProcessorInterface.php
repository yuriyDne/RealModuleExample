<?php

namespace Fisha\OrderFlow\Api;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Config\ProcessorConfig;
use Magento\Sales\Api\Data\OrderInterface;

interface ProcessorInterface
{
    public function execute(OrderInterface $order): ResultInterface;
}
