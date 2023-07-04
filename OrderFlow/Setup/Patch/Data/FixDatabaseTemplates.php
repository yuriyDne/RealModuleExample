<?php

namespace Fisha\OrderFlow\Setup\Patch\Data;

class FixDatabaseTemplates extends FixPrioEmailTemplates
{
    const TEMPLATE_ID_TO_FILE = [
        18 => 'order_updated.html',
        19 => 'order_canceled.html',
        20 => 'order_received.html',
        21 => 'order_in_store.html',
        22 => 'order_shipped_to_store.html',
        23 => 'order_invoice.html',
        24 => 'order_refund.html',
        25 => 'refund_transactin_failed.html',
    ];
}
