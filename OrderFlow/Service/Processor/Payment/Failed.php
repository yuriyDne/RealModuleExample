<?php

namespace Fisha\OrderFlow\Service\Processor\Payment;

use Fisha\OrderFlow\Service\Processor\ProcessFailedStatus;
use Magento\Sales\Api\Data\OrderInterface;

class Failed extends ProcessFailedStatus
{
    protected function getErrorMessage(OrderInterface $order)
    {
        return "Payment ".$order->getPayment()->getMethod()." was failed , need to change this order manually";
    }

}
