<?php

namespace Fisha\OrderFlow\Service\Processor\Presale;

use Fisha\OrderFlow\Api\Data\Processor\ResultInterface;
use Fisha\OrderFlow\Model\Processor\CommonLogic;
use Fisha\OrderFlow\Service\Processor\AbstractProcessor;
use Magento\Sales\Api\Data\OrderInterface;

class Processing extends AbstractProcessor
{
    public function __construct(CommonLogic $commonLogic)
    {
        parent::__construct($commonLogic);
    }

    /**
     * @param OrderInterface $order
     * @return ResultInterface
     */
    public function execute(OrderInterface $order): ResultInterface
    {

    }
}
