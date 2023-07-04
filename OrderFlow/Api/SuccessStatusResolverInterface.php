<?php

namespace Fisha\OrderFlow\Api;

use Magento\Sales\Model\Order;

interface SuccessStatusResolverInterface
{
    public function execute(Order $order): string;
}
