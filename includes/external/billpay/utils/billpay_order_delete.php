<?php
/**
 * This code is executed when admin deletes an order
 */
/**
 */

require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/billpayBase.php');

$orderId   = (int) $_GET['oID'];
$paymentMethod = BillpayDB::DBFetchValue("select payment_class from ".TABLE_ORDERS." where orders_id = '".xtc_db_input($orderId)."'");

$billpayMethods = billpayBase::GetPaymentMethods();

if (in_array($paymentMethod, $billpayMethods) ) {
    $billpay = billpayBase::PaymentInstance($paymentMethod);
    $success = $billpay->onOrderDelete($orderId);
}
