<?php

namespace Fisha\OrderFlow\Model\Config;

use Magento\Sales\Model\Order;

class OrderStatus
{
    /**
     * Order states
     * Default Statuses
     */
    const STATUS_NEW             = Order::STATE_NEW;
    const STATUS_PENDING_PAYMENT = Order::STATE_PENDING_PAYMENT;
    const STATUS_PROCESSING      = Order::STATE_PROCESSING;
    const STATUS_PRESALE_PROCESSING      = 'presale_processing';
    const STATUS_PRESALE_FAIL      = 'presale_fail';
    const STATUS_PRESALE_COMPLETE      = 'presale_complete';
    const STATUS_COMPLETE        = Order::STATE_COMPLETE;
    const STATUS_CLOSED          = Order::STATE_CLOSED;
    const STATUS_CANCELED        = Order::STATE_CANCELED;
    const STATUS_HOLDED          = Order::STATE_HOLDED;
    const STATUS_PAYMENT_REVIEW  = Order::STATE_PAYMENT_REVIEW;

    /**
     * Prio Statuses
     */
    const STATUS_PRIO_EXPORTED = 'prio_exported';
    const STATUS_PRIO_EXPORT_FAILED = 'prio_export_failed';
    const STATUS_PRIO_INVOICED = 'prio_invoiced';
    const STATUS_PRIO_INVOICED_EXISTS = 'prio_invoiced_exists';

    /**
     * Export TO EDEA
     */
    const STATUS_EXPORTED = 'exported';  /* exists */
    const STATUS_PICKED = 'picked'; /* exists */
    const STATUS_INV_CANCEL_PARTIALLY = 'inv_cancel_partially';
    const STATUS_INV_CANCEL_TRANSACTION = 'inv_cancel_transaction';
    const STATUS_INV_CANCEL_CREDIT = 'inv_cancel_credit';
    const STATUS_INV_CANCEL_TRANSACTION_FAILED = 'inv_cancel_transaction_failed';
    const STATUS_INV_EXPORT_FAILED = 'inv_export_failed';
    const STATUS_EDEA_EXPORT_FAILED = 'edea_export_failed';
    const STATUS_INV_CANCEL_TRANSACTION_EMAIL = 'inv_cancel_transaction_email';
    const STATUS_INV_CANCEL_CREDIT_EMAIL = 'inv_cancel_credit_email';

    /**
     * Payment statuses
     */

    const STATUS_PAYMENT_FAILED = 'payment_failed';
    const STATUS_PAYMENT_RECEIVED = 'payment_received'; /* exists */
    const STATUS_REFUND_TRANSACTION = 'refund_transaction';
    const STATUS_REFUNDED_CANCELED = 'refunded_canceled';
    const STATUS_REFUND_TRANSACTION_PARTIALLY = 'refund_transaction_partially';
    const STATUS_REFUND_TRANSACTION_FAILED = 'refund_transaction_failed';


    /**
     * Home Delivery Statuses
     */
    const STATUS_DELIVERY_EXPORTED = 'delivery_exported';
    const STATUS_DELIVERY_FAILED = 'delivery_failed';
    const STATUS_DELIVERY_RECEIVED = 'delivery_received';
    const STATUS_DELIVERY_SHIPPED = 'delivery_shipped';
    const STATUS_DELIVERY_PROCESSING = 'delivery_processing';
    /**
     * Store Pickup Statuses
     */
    const STATUS_PICKUP_CUSTOMER_RECEIVED = 'pickup_customer_received';
    const STATUS_PICKUP_RECEIVED = 'pickup_received';
    const STATUS_PICKUP_SHIPPED = 'pickup_shipped';

    public function getRemoveFromQueueStatuses()
    {
        return [];
    }
}
