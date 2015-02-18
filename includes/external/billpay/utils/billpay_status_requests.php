<?php
    /**
     * This code is executed when admin manually changes order's status
     */

    require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/billpayBase.php');

    $newStatus = (int) $_POST['status'];
    $orderId   = (int) $_GET['oID'];

    $paymentMethod = BillpayDB::DBFetchValue("select payment_class from ".TABLE_ORDERS." where orders_id = '".xtc_db_input($orderId)."'");
    $billpayMethods = billpayBase::GetPaymentMethods();

    if (in_array($paymentMethod, $billpayMethods) ) {
        $billpay = billpayBase::PaymentInstance($paymentMethod);
        $success = $billpay->onOrderStatusChange($orderId, $newStatus, $oldStatus);
    }
