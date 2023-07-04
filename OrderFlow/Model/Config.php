<?php

namespace Fisha\OrderFlow\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 * @package Fisha\OrderFlow\Model
 */
class Config
{
    const SMS_TEMPLATES = 'sms_templates';
    const EMAIL_TEMPLATES = 'email_templates';

    const GENERAL_XPATH = 'fisha_orderflow/general/';
    const API_XPATH = 'fisha_orderflow/api/';
    const EMAIL_XPATH = 'fisha_orderflow/email_templates/';
    const XPATH = 'fisha_orderflow/';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array|false|string[]
     */
    public function getRemoveFromQueueStatuses()
    {
        $statusesStr = $this->getGeneralConfig('remove_order_statuses');
        return !empty($statusesStr)
            ? explode(',', $statusesStr)
            : [];
    }

    /**
     * @return mixed
     */
    public function getStartOrderId()
    {
        return $this->getGeneralConfig('start_order_id');
    }

    /**
     * @return array
     */
    public function getLeaveInQueueFailedOrderStatuses()
    {
        $statusesStr = $this->getGeneralConfig('leave_in_queue_order_statuses');

        return !empty($statusesStr)
            ? explode(',', $statusesStr)
            : [];
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::GENERAL_XPATH . 'enable');
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getGeneralConfig(string $key)
    {
        return $this->scopeConfig->getValue(self::GENERAL_XPATH . $key);
    }

    /**
     * @return string
     */
    public function getInventoryApiURL()
    {
        return $this->getAPIConfig('inv_api_url');
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getAPIConfig(string $key)
    {
        return $this->scopeConfig->getValue(self::API_XPATH . $key);
    }

    /**
     * @param string $orderStatus
     * @param string $type
     * @return mixed
     */
    public function getTemplateId(string $orderStatus, string $type)
    {
        return $this->scopeConfig->getValue(self::XPATH . "{$type}/" . $orderStatus);
    }

    /**
     * @param string $type
     * @return int
     */
    public function isSenderEnabled(string $type)
    {
        return (int)$this->scopeConfig->getValue(self::XPATH . "{$type}/enable");
    }

    /**
     * @return array|false|string[]
     *
     */
    public function getSendToCustomerOrderStatuses()
    {
        $statusesStr = $this->getEmailConfig('orderflow_active_templates');
        return !empty($statusesStr)
            ? explode(',', $statusesStr)
            : [];
    }

    /**
     * @return array
     */
    public function getSendToAdminOrderStatuses()
    {
        $statusesStr = $this->getEmailConfig('orderflow_send_to_admin_templates');
        return !empty($statusesStr)
            ? explode(',', $statusesStr)
            : [];
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getEmailConfig(string $key)
    {
        return $this->scopeConfig->getValue(self::EMAIL_XPATH . $key);
    }

    /**
     * @return array
     */
    public function getAdminEmailsForStatusUpdate()
    {
        $recipients = array_map('trim', explode(',', $this->getEmailConfig('orderflow_bcc_recipients')));
        return $recipients;
    }
}
