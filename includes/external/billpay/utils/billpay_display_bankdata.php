<?php
/**
 * Function is called, when admin tries to print the invoice.
 * @return string
 */
function display_billpay_bankdata() {
    global $order;
    $orderId = (int)$_GET['oID'];

    $paymentMethod = $order->info['payment_method'];

    /** @noinspection PhpIncludeInspection */
    require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/billpayBase.php');
    if (!in_array(strtolower($paymentMethod), billpayBase::GetPaymentMethods())) {
        return '';
    }
    /** @var BillpayBase $billpay */
    $billpay = billpayBase::PaymentInstance($paymentMethod);
    $invoiceData = $billpay->getPaymentInfo($orderId);
    return '<br>'.$invoiceData['html'];
}

