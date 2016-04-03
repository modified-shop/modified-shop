<?php
chdir('../../');
/** @noinspection PhpIncludeInspection */
include('includes/application_top.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_WS_INCLUDES . 'external/billpay/base/billpayBase.php');
/** @noinspection PhpIncludeInspection */
require_once(DIR_WS_INCLUDES . 'external/billpay/base/BillpayDB.php');


$callbackData = billpayBase::ParseCallback();

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
$paymentMethod = BillpayDB::DBFetchValue("SELECT payment_method FROM $table WHERE orders_id = '$orderId'");

/** @var billpayBase $billpay */
$billpay = billpayBase::PaymentInstance($paymentMethod);

if (!$billpay) {
    $billpay->_logDebug('Received callback for non-existing order: ' . $orderId);
    header("HTTP/1.0 400 Bad Request");
    exit();
}
$billpay->_logDebug('Received valid callback.');

$success = $billpay->onBillpayCallback($callbackData);

if ($success) {
    header("HTTP/1.0 200 OK");
} else {
    // shouldn't it be HTTP 400?
    $billpay->_logDebug('Callback returned false.');
    header("HTTP/1.0 400 Bad Request");
}
