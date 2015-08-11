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
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/modules/payment/billpay.php');
$paymentMethod = strtolower($order->info['payment_method']);
if (in_array($paymentMethod, billpayBase::GetPaymentMethods())) {
    /** @var billpayBase $pm */
    $pm = billpayBase::PaymentInstance($paymentMethod);
    $transaction_id = billpayBase::GetTransactionId();
    $order_id = $this->order_id;
    $pm->saveOrderId($transaction_id, $order_id);
    /**
     * $pm->saveOrderId updates BillPay BankData table to link transaction_id and order_id.
     * While this is not correct place to set it (email sending in one of the shop modifications?!),
     * it simplifies invoice text generation (which is same for email and pdf).
     * Plus saveOrderId is executed in after_process so it is fine in other shops.
     */

    $payment_info = $pm->getPaymentInfo($order_id);
    $t_payment_info_html = $payment_info['html'];
    $t_payment_info_text = $payment_info['text'];
}

