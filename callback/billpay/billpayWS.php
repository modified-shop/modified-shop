<?php
chdir('../../');
// Async request
/** @noinspection PhpIncludeInspection */
include('includes/application_top.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_WS_INCLUDES . 'external/billpay/base/billpayBase.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_WS_INCLUDES . 'external/billpay/base/BillpayDB.php');

/** @var billpayBase $billpay */
$billpay = new billpayBase();

$callbackData = billpayBase::ParseCallback();
$billpay->_logDebug('Giropay async: Start');
// example response
/*$callbackData = array(
    'xmlStatus' =>  200,    # in correct response it should exist
    'mid'       =>  123,    # merchantId
    'pid'       =>  456,    # portalId
    'bpsecure'  =>  'sadasdasdasd', # md5 hashed securityKey

    'reference'     =>  '26',    # orderId
    'status'        =>  'APPROVED',     # new status of the order
    'customer_id'   =>  '123',          # if set, will send an email

    'calculation'   =>  array(      # if set, we have new installment data
        # check below to get fields
    ),

    'postdata'  =>  'Everything ok!',   # additional info text logged in the file
);*/
$orderId = (int)$callbackData['reference'];
$table = TABLE_ORDERS;

$billpay->_logDebug('Giropay async: Try to fetch the order from the database');
$paymentMethod = BillpayDB::DBFetchValue("SELECT payment_method FROM $table WHERE orders_id = '$orderId'");

/** @var billpayBase $billpay */
$billpay = billpayBase::PaymentInstance($paymentMethod);

if (!$billpay) {
    $billpay->_logDebug('Received callback for non-existing order: ' . $orderId);
    header("HTTP/1.0 400 Bad Request");
    exit();
}
$billpay->_logDebug('Giropay async: Received valid callback.');

$success = $billpay->onBillpayCallback($callbackData);

if ($success) {
    $billpay->_logDebug('Giropay async: Successful');
    header("HTTP/1.0 200 OK");
} else {
    // shouldn't it be HTTP 400?
    $billpay->_logDebug('Giropay async: Failed.');
    header("HTTP/1.0 400 Bad Request");
}
