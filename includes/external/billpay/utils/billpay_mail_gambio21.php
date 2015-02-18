<?php
/**
 * Input:
 *      @var $order order
 *
 * Output:
 *      @var $t_payment_info_html string
 *      @var $t_payment_info_text string
 */
if (empty($order)) {
    $order = $GLOBALS['order'];
}
require_once(DIR_FS_CATALOG . 'includes/modules/payment/billpay.php');
$paymentMethod = strtolower($order->info['payment_method']);
if (in_array($paymentMethod, billpayBase::GetPaymentMethods())) {
    /** @var billpayBase $pm */
    $pm = billpayBase::PaymentInstance($paymentMethod);
    $payment_info = $pm->getPaymentInfo($this->order_id);
    $t_payment_info_html = $payment_info['html'];
    $t_payment_info_text = $payment_info['text'];
}

