<?php

namespace Fisha\OrderFlow\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ApplyEmailConfigurationsV2 implements DataPatchInterface
{
    private WriterInterface $configWriter;

    /**
     * @param WriterInterface $configWriter
     */
    public function __construct(WriterInterface $configWriter)
    {
        $this->configWriter = $configWriter;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->configWriter->save('sales_email/invoice/enabled', 0);
        $this->configWriter->save('sales_email/shipment/enabled', 0);

        $this->configWriter->save('fisha_orderflow/email_templates/orderflow_active_templates', 'delivery_shipped,prio_invoiced,refund_transaction_failed,pickup_received,pickup_shipped,complete,inv_cancel_credit_email,inv_cancel_transaction_email');
        $this->configWriter->save('fisha_orderflow/email_templates/inv_cancel_transaction_email', 'fisha_orderflow_email_templates_refund_transaction');
        $this->configWriter->save('fisha_orderflow/email_templates/inv_cancel_credit_email', 'fisha_orderflow_email_templates_refund_transaction_partially');
    }

}
