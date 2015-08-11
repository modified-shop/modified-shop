<?php
/**
 * @var $order order
 */
// TODO: rework the same way as billpay_mail_gambio21.php
require_once(DIR_FS_CATALOG . 'includes/modules/payment/billpay.php');
$paymentMethod = strtoupper($order->info['payment_method']);
if ($paymentMethod == billpayBase::PAYMENT_METHOD_INVOICE) {
    $billpay = new billpay(strtoupper($order->info['payment_method']));
    if (isset($insert_id)) {
        $oID = $insert_id; // Gambio 2.0 does not have $oID, instead have $insert_id
    }

    $transaction_id = BillPayBase::GetTransactionId();
    if ($transaction_id) {
        $billpay_bank_data_query = "SELECT account_holder, account_number, bank_code, bank_name, invoice_reference ".
                                  "FROM billpay_bankdata ".
                                  "WHERE tx_id = '".$transaction_id."'";
    }else {
        $billpay_bank_data_query = "SELECT account_holder, account_number, bank_code, bank_name, invoice_reference ".
                                  "FROM billpay_bankdata ".
                                  "WHERE api_reference_id = '".(int)$oID."'";
    }

    $billpay_bank_data_result = xtc_db_query($billpay_bank_data_query);
    $billpay_bank_data = xtc_db_fetch_array($billpay_bank_data_result);
    //$billpay_info_text = MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL . '<br /><br />';

    if(!$billpay_bank_data['api_reference']){
        $invoiceReference = $billpay->generateInvoiceReference($insert_id);
    } else {
        $invoiceReference = $billpay_bank_data['invoice_reference'];
    }

    //$invoiceReference = $billpay->generateInvoiceReference($insert_id);

    $billpay_info_text = sprintf(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL, $invoiceReference) . '<br /><br />';
    $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER .': '. $billpay_bank_data['account_holder'].'<br />';
    $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_IBAN .': '. $billpay_bank_data['account_number'].'<br />';
    $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_BIC .': '. $billpay_bank_data['bank_code'].'<br />';
    $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME .': '. $billpay_bank_data['bank_name'].'<br />';
    $billpay_info_text .= MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE .': ' . $invoiceReference . '<br />';
    if(defined('EMAIL_USE_HTML') && EMAIL_USE_HTML == 'false') {
        $billpay_info_text = utf8_decode(html_entity_decode($billpay_info_text, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
    }
    if(defined('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') &&
        constant('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') == 'True') {
        $billpay_info_text = utf8_encode($billpay_info_text);
    }
    $smarty->assign('PAYMENT_INFO_HTML', $billpay_info_text);
    $smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", $billpay_info_text));
//	}
}
else if ($paymentMethod == billpayBase::PAYMENT_METHOD_DEBIT) {
    $billpay_info_text = '<br /><br />' . MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1;
    if(defined('EMAIL_USE_HTML') && EMAIL_USE_HTML == 'false') {
        $billpay_info_text = utf8_decode(html_entity_decode($billpay_info_text, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
    }
    if(defined('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE') &&
    constant('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE') == 'True') {
        $billpay_info_text = utf8_encode($billpay_info_text);
    }
    $smarty->assign('PAYMENT_INFO_HTML', $billpay_info_text);
    $smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", $billpay_info_text));
}
else if ($paymentMethod == billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT) {
    /** @var billpayBase $pm */
    $pm = billpayBase::PaymentInstance($paymentMethod);
    $payment_info = $pm->getPaymentInfo($insert_id);

    $smarty->assign('PAYMENT_INFO_HTML', $payment_info['html']);
    $smarty->assign('PAYMENT_INFO_TXT', $payment_info['text']);
}
else if ($paymentMethod == billpayBase::PAYMENT_METHOD_PAY_LATER) {
    $infoText = constant('MODULE_PAYMENT_BILLPAYPAYLATER_TEXT_INVOICE_INFO1');
    $smarty->assign('PAYMENT_INFO_HTML', $infoText);
    $smarty->assign('PAYMENT_INFO_TXT', $infoText);
}
