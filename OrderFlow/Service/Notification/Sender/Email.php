<?php

namespace Fisha\OrderFlow\Service\Notification\Sender;

use Fisha\OrderFlow\Api\Notification\SenderInterface;
use Fisha\OrderFlow\Model\Config;
use Fisha\OrderFlow\Model\Processor\Result;
use Fisha\OrderFlow\Mail\Template\TransportBuilderWithAttachments as TransportBuilder;
use Fisha\OrderFlow\Service\Notification\Sender\Email\AddOrderExtraData;
use Fisha\Pdf\Service\DesignParamsInitializer;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class Email implements SenderInterface
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var OrderIdentity
     */
    protected $orderIdentity;
    /**
     * @var StateInterface
     */
    protected $state;
    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;
    /**
     * @var AddOrderExtraData
     */
    protected AddOrderExtraData $addOrderExtraDataService;
    /**
     * @var DesignParamsInitializer
     */
    private DesignParamsInitializer $designParamsInitializer;
    /**
     * @var AreaList
     */
    private AreaList $areaList;
    /**
     * @var Emulation
     */
    private Emulation $appEmulation;

    /**
     * Email constructor.
     *
     * @param TransportBuilder $transportBuilder
     * @param OrderIdentity $orderIdentity
     * @param DirectoryList $directoryList
     * @param AddOrderExtraData $addOrderExtraDataService
     * @param StoreManagerInterface $storeManager
     * @param Emulation $appEmulation
     * @param DesignParamsInitializer $designParamsInitializer
     * @param AreaList $areaList
     * @param Config $config
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        OrderIdentity $orderIdentity,
        DirectoryList $directoryList,
        AddOrderExtraData $addOrderExtraDataService,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation,
        DesignParamsInitializer $designParamsInitializer,
        AreaList $areaList,
        Config $config
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->orderIdentity = $orderIdentity;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->addOrderExtraDataService = $addOrderExtraDataService;
        $this->designParamsInitializer = $designParamsInitializer;
        $this->areaList = $areaList;
        $this->appEmulation = $appEmulation;
    }

    /**
     * @param OrderInterface $order
     * @param Result $processorResult
     * @param string|null $templateId
     * @return bool|mixed
     */
    public function execute(OrderInterface $order, Result $processorResult, string $templateId = null)
    {
        $storeId = $order->getStoreId();
        $this->appEmulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $this->process($order, $processorResult, $templateId);
        $this->appEmulation->stopEnvironmentEmulation();
    }

    /**
     * @param OrderInterface $order
     * @param Result $processorResult
     * @param string|null $templateId
     * @return bool|mixed
     */
    public function process(OrderInterface $order, Result $processorResult, string $templateId = null)
    {
        $this->areaList->getArea(Area::AREA_FRONTEND)->load(Area::PART_TRANSLATE);
        $orderStatus = $order->getStatus();
        if ($this->needNotifyCustomer($orderStatus)) {
            $sendTo = [$order->getCustomerEmail()];
            $this->sendEmail($order, $processorResult, $templateId, $sendTo);
        }
        if ($this->needNotifyAdmin($orderStatus)) {
            $adminEmails = $this->config->getAdminEmailsForStatusUpdate();
            if (empty($adminEmails)) {
                return false;
            }
            $this->sendEmail($order, $processorResult, 'orderflow_failed', $adminEmails);
        }
    }

    /**
     * @param string $status
     * @return bool
     */
    public function needSendNotification(string $status): bool
    {
        return $this->needNotifyAdmin($status)
            || $this->needNotifyCustomer($status);
    }

    /**
     * @param string $orderStatus
     * @return bool
     */
    protected function needNotifyAdmin(string $orderStatus)
    {
        return in_array($orderStatus, $this->config->getSendToAdminOrderStatuses());
    }

    /**
     * @param string $orderStatus
     * @return bool
     */
    protected function needNotifyCustomer(string $orderStatus)
    {
        return in_array($orderStatus, $this->config->getSendToCustomerOrderStatuses());
    }

    /**
     * @param OrderInterface $order
     * @param Result $processorResult
     * @param string $templateId
     * @param array $sendTo
     * @throws LocalizedException
     * @throws MailException
     */
    protected function sendEmail(OrderInterface $order, Result $processorResult, string $templateId, array $sendTo)
    {
        if (empty($templateId)) {
            return false;
        }
        $this->designParamsInitializer->execute();
        $order = $this->addOrderExtraDataService->execute($order);
        $attachmentPath = $processorResult->getAttachment();
        $templateVars = [
            'order' => $order,
            'orderId' => $order->getEntityId(),
            'order_id' => $order->getEntityId(),
            'errorMessage' => $processorResult->getErrorMessage(),
            'store' => $this->orderIdentity->getStore()
        ];
        $templateVars = $this->addOrderTemplateVars($order, $templateVars);

        if ($attachmentPath) {
            $attachmentUrl = $this->convertPathToUrl($attachmentPath);
            $templateVars['attachmentUrl'] = $attachmentUrl;
            $this->transportBuilder->addAttachment($attachmentPath, $processorResult->getMimeType());
        }

        $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => $order->getStoreId(),
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope(
                $this->orderIdentity->getEmailIdentity(),
                $this->orderIdentity->getStore()->getId(),
                )
            ->addTo($sendTo);

        $this->storeManager->setCurrentStore($order->getStoreId());
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }

    /**
     * @param string $attachmentPath
     * @return string
     */
    private function convertPathToUrl(string $attachmentPath): string
    {
        $result = '';
        $urlParts = explode('pub/', $attachmentPath);
        if (!empty($urlParts[1])) {
            $result = $this->directoryList->getUrlPath(DirectoryList::PUB) . $urlParts[1];
            $result = $this->storeManager->getStore()->getBaseUrl(). $result;
        }

        return $result;
    }

    /**
     * @param OrderInterface $order
     * @param array $templateVars
     * @return array
     */
    private function addOrderTemplateVars(OrderInterface $order, array $templateVars)
    {
        $passToTemplatesOrderData = [
            'creditmemo_id',
            'shipment_id',
            'formattedBillingAddress',
            'formattedShippingAddress',
            'payment_html'
        ];

        foreach ($passToTemplatesOrderData as $key)
        if ($order->getData($key)) {
            $templateVars[$key] = $order->getData($key);
        }

        return $templateVars;
    }
}
