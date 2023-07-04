<?php

namespace Fisha\OrderFlow\Controller\Adminhtml\Queue;

use Fisha\OrderFlow\Api\Data\QueueItemInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
    const ADMIN_RESOURCE = 'Fisha_OrderFlow';

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $orderIncrementId = $this->getRequest()->getParam('increment_id');
        $resultPage->getConfig()->getTitle()->prepend(__("Order {$orderIncrementId} Api Logs"));
        return $resultPage;
    }
}
