<?php

/**
 * Not used anymore. Check /includes/external/billpay/base/BillpayOrderEdit.php
 *
 * If your shop tries to execute code here, you have to update /admin/orders_edit.php file according to
 * installation documentation. You have to switch:
 *      require_once(DIR_FS_CATALOG .'includes/external/billpay/utils/billpay_edit_orders.php');
 * for:
 *      require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/BillpayOrderEdit.php');
 *      $billpayOrderEdit = new BillpayOrderEdit();
 *      $billpayOrderEdit->onBeforeUpdate();
 * and add new hook in line 644 (inside "save_order" action, before redirect)
 *      $billpayOrderEdit->onAfterUpdate();
 */

require_once(DIR_FS_CATALOG. 'includes/external/billpay/base/billpayBase.php');

$billpayMethods = billpayBase::GetPaymentMethods();
/** @var order $order */
$paymentMethod = $order->info['payment_method'];
if (in_array($paymentMethod, $billpayMethods)) {
    // $errorMessage = 'Error: File /admin/orders_edit.php requires manual update. Please check the installation documentation. If you have a question, please contact Billpay\'s support.';
    $errorMessage = 'Die BillPay Plugin Integration in Ihren Shop ist veraltet und muss angepasst werden. Bitte pr&uuml;fen Sie in der neuen Installationsanleitung, welche &Auml;nderungen Sie an'
                    .' /'.(defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'orders_edit.php '
                    .'vornehmen m&uuml;ssen. Bei Fragen steht Ihnen unser <a href="mailto:support@billpay.de">H&auml;ndler Support</a> zur Verf&uuml;gung.';
    billpayBase::DisplayErrorAndExit($errorMessage);
}
