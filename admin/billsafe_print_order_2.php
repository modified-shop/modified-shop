<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_print_order_2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_print_order_2.php
* location = /admin
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 Bernd Blazynski
* @license GPLv2
*/

require ('includes/application_top.php');
require (DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/payment/billsafe_2.php');
require_once (DIR_FS_INC.'xtc_get_order_data.inc.php');
require_once (DIR_FS_INC.'xtc_not_null.inc.php');
require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');

$smarty = new Smarty;
$order_query_check = xtc_db_query('SELECT customers_id FROM '.TABLE_ORDERS.' WHERE orders_id = "'.xtc_db_input((int)$_GET['oID']).'"');
$order_check = xtc_db_fetch_array($order_query_check);

include (DIR_WS_CLASSES.'order.php');
$order = new order($_GET['oID']);
$order_id = $_GET['oID'];

$i = 0;
$tax_rates_query = xtc_db_query('SELECT tax_rate FROM '.TABLE_TAX_RATES.' WHERE tax_rate != "0"');
while ($rates = xtc_db_fetch_array($tax_rates_query)) {
  $taxRate[$i] = number_format($rates['tax_rate'], 2, '.', '');
  $i++;
}
$countTaxRates = $i + 1;
$taxRate[$i] = number_format(0, 2, '.', '');
$smarty->assign('address_label_customer', xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'));
$smarty->assign('address_label_shipping', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));
$smarty->assign('address_label_payment', xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'));
$smarty->assign('csID', $order->customer['csID']);

$order_query = xtc_db_query('SELECT *, COUNT(articlenumber) AS quantity FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "goods" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
$order_data = array();
while ($order_data_values = xtc_db_fetch_array($order_query)) {
  $order_data[] = array('PRODUCTS_MODEL' => $order_data_values['articlenumber'], 'PRODUCTS_NAME' => $order_data_values['articlename'], 'PRODUCTS_ATTRIBUTES' => '', 'PRODUCTS_ATTRIBUTES_MODEL' => '', 'PRODUCTS_PRICE' => xtc_format_price_order($order_data_values['articleprice'] * $order_data_values['quantity'], 1, $order->info['currency']), 'PRODUCTS_QTY' => $order_data_values['quantity']);
  $total = $total + $order_data_values['articleprice'] * $order_data_values['quantity'];
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == number_format($order_data_values['articletax'], 2, '.', '')) $tax[$i] = $tax[$i] + (($order_data_values['articleprice'] / (100 + $order_data_values['articletax'])) * $order_data_values['articletax'] * $order_data_values['quantity']);
  }
}
$order_total[] = array('TITLE' => MODULE_PAYMENT_BILLSAFE_2_VALUE.':', 'TEXT' => '<b>'.xtc_format_price_order($total, 1, $order->info['currency']).'</b>');
$orderShip = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "shipment" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
while ($ship = xtc_db_fetch_array($orderShip)) {
  $shipName = $ship['articlename'];
  $shipCost = $ship['articleprice'];
  $shipTax = $ship['articletax'];
}
if ($shipCost != 0) {
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == $shipTax) $tax[$i] = $tax[$i] + (($shipCost / (100 + $shipTax)) * $shipTax);
  }
  $order_total[] = array('TITLE' => $shipName.':', 'TEXT' => '<b>'.xtc_format_price_order($shipCost, 1, $order->info['currency']).'</b>');
  $total = $total + $shipCost;
}
$orderVoucher = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "voucher" AND articlenumber = "voucher" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
while ($voucher = xtc_db_fetch_array($orderVoucher)) {
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == number_format($voucher['articletax'], 2, '.', '')) $tax[$i] = $tax[$i] + (($voucher['articleprice'] / (100 + $voucher['articletax'])) * $voucher['articletax']);
  }
  $vAmount = $vAmount + $voucher['articleprice'];
  $vName = $voucher['articlename'];
}
if ($vAmount != 0) {
  $order_total[] = array('TITLE' => $vName, 'TEXT' => '<b><font color="#ff0000">'.xtc_format_price_order($vAmount, 1, $order->info['currency']).'</font></b>');
  $total = $total + $vAmount;
}
$orderCoupon = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "voucher" AND articlenumber = "coupon" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
while ($coupon = xtc_db_fetch_array($orderCoupon)) {
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == number_format($coupon['articletax'], 2, '.', '')) $tax[$i] = $tax[$i] + (($coupon['articleprice'] / (100 + $coupon['articletax'])) * $coupon['articletax']);
  }
  $cAmount = $cAmount + $coupon['articleprice'];
  $cName = $coupon['articlename'];
}
if ($cAmount != 0) {
  $order_total[] = array('TITLE' => $cName, 'TEXT' => '<b><font color="#ff0000">'.xtc_format_price_order($cAmount, 1, $order->info['currency']).'</font></b>');
  $total = $total + $cAmount;
}
$orderDiscount = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "voucher" AND articlenumber = "discount" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
while ($discount = xtc_db_fetch_array($orderDiscount)) {
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == number_format($discount['articletax'], 2, '.', '')) $tax[$i] = $tax[$i] + (($discount['articleprice'] / (100 + $discount['articletax'])) * $discount['articletax']);
  }
  $dAmount = $dAmount + $discount['articleprice'];
  $dName = $discount['articlename'];
}
if ($dAmount != 0) {
  $order_total[] = array('TITLE' => $dName, 'TEXT' => '<b><font color="#ff0000">'.xtc_format_price_order($dAmount, 1, $order->info['currency']).'</font></b>');
  $total = $total + $dAmount;
}
$orderSurcharge = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "handling" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
while ($surcharge = xtc_db_fetch_array($orderSurcharge)) {
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == number_format($surcharge['articletax'], 2, '.', '')) $tax[$i] = $tax[$i] + (($surcharge['articleprice'] / (100 + $surcharge['articletax'])) * $surcharge['articletax']);
  }
  $sAmount = $sAmount + $surcharge['articleprice'];
  $sName = $surcharge['articlename'];
}
if ($sAmount != 0) {
  $order_total[] = array('TITLE' => $sName, 'TEXT' => '<b>'.xtc_format_price_order($sAmount, 1, $order->info['currency']).'</b>');
  $total = $total + $sAmount;
}
$orderBVoucher = xtc_db_query('SELECT * FROM billsafe_orders_details_2 WHERE ordernumber = "'.xtc_db_input($order_id).'" AND articletype = "voucher" AND articlenumber LIKE "backend-voucher-%" AND storno = 0 AND retoure = 0 GROUP BY articlenumber, articlename, articleprice;');
while ($bvoucher = xtc_db_fetch_array($orderBVoucher)) {
  for ($i = 0; $i < $countTaxRates; $i++) {
    if ($taxRate[$i] == number_format($bvoucher['articletax'], 2, '.', '')) $tax[$i] = $tax[$i] + (($bvoucher['articleprice'] / (100 + $bvoucher['articletax'])) * $bvoucher['articletax']);
  }
  $bAmount = $bAmount + $bvoucher['articleprice'];
  $bName = $bvoucher['articlename'];
}
if ($bAmount != 0) {
  $order_total[] = array('TITLE' => $bName.':', 'TEXT' => '<b><font color="#ff0000">'.xtc_format_price_order($bAmount, 1, $order->info['currency']).'</font></b>');
  $total = $total + $bAmount;
}
for ($i = 0; $i < $countTaxRates; $i++) {
  $name = explode('.', (string)$taxRate[$i]);
  if ((int)$name[1] > 0) {
    $taxRateName[$i] = $name[0].','.$name[1];
  } else {
    $taxRateName[$i] = $name[0];
  }
  if (!empty($tax[$i])) $order_total[] = array('TITLE' => MODULE_PAYMENT_BILLSAFE_2_INC.$taxRateName[$i].MODULE_PAYMENT_BILLSAFE_2_VAT.':', 'TEXT' => '<b>'.xtc_format_price_order($tax[$i], 1, $order->info['currency']).'</b>');
}
$order_total[] = array('TITLE' => '<b>'.MODULE_PAYMENT_BILLSAFE_2_TOTAL.':</b>', 'TEXT' => '<b>'.xtc_format_price_order($total, 1, $order->info['currency']).'</b>');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
$smarty->assign('oID',$_GET['oID']);
if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
  include(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
  $payment_method = constant(strtoupper('MODULE_PAYMENT_'.$order->info['payment_method'].'_TEXT_TITLE'));
  $smarty->assign('PAYMENT_METHOD',$payment_method);
}
$smarty->assign('COMMENTS', $order->info['comments']);
$smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
$smarty->assign('order_data', $order_data);
$smarty->assign('order_total', $order_total);

require_once (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/billsafe_2.php'); //DokuMan - 2012-06-19 - move billsafe to external directory
require (DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php'); //DokuMan - 2012-06-19 - move billsafe to external directory
$bs = new Billsafe_Sdk(DIR_FS_CATALOG.'includes/external/billsafe/classes/billsafe_2/ini.php'); //DokuMan - 2012-06-19 - move billsafe to external directory
if ($_SESSION['language_charset'] == 'iso-8859-1' || $_SESSION['language_charset'] == 'iso-8859-15') {
  $bs->setUtf8Mode(false);
} else {
  $bs->setUtf8Mode(true);
}
if (MODULE_PAYMENT_BILLSAFE_2_SERVER == 'Live') {
  $bs->setMode('LIVE');
} else {
  $bs->setMode('SANDBOX');
}
$bs->setCredentials(array('merchantId' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID, 'merchantLicenseSandbox' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'merchantLicenseLive' => MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE, 'applicationSignature' => $ini['applicationSignature'], 'applicationVersion' => $ini['applicationVersion']));

$orders_query = xtc_db_query('SELECT id, transactionid, billsafeStatus, type FROM billsafe_orders_2 WHERE orderid = "'.xtc_db_input($order_id).'"');
$billsafe_orders = xtc_db_fetch_array($orders_query);
$paramsIns = array('transactionId' => $billsafe_orders['transactionid']);
$responseInstruction = $bs->callMethod('getPaymentInstruction', $paramsIns);
$smarty->assign('BillsafeType', $billsafe_orders['type']);
if ($billsafe_orders['type'] == 'invoice') {
  if ($responseInstruction->ack == 'OK') {
    $url = substr(HTTP_SERVER, 7).DIR_WS_CATALOG;
    $ins = $responseInstruction->instruction;
    $smarty->assign('BillsafeText', $ins->legalNote);
    $smarty->assign('BillsafeText2', '<b>'.$ins->note.'</b>');
    $smarty->assign('recipient', MODULE_PAYMENT_BILLSAFE_2_RECIPIENT.':');
    $smarty->assign('recipientValue', $ins->recipient);
    $smarty->assign('account', MODULE_PAYMENT_BILLSAFE_2_ACCOUNT_NUMBER.':');
    $smarty->assign('accountValue', $ins->accountNumber);
    $smarty->assign('bankcode', MODULE_PAYMENT_BILLSAFE_2_BANK_CODE.':');
    $smarty->assign('bankcodeValue', $ins->bankCode);
    $smarty->assign('bank', MODULE_PAYMENT_BILLSAFE_2_BANK_NAME.':');
    $smarty->assign('bankValue', $ins->bankName);
    $smarty->assign('bic', MODULE_PAYMENT_BILLSAFE_2_BIC.':');
    $smarty->assign('bicValue', $ins->bic);
    $smarty->assign('iban', MODULE_PAYMENT_BILLSAFE_2_IBAN.':');
    $smarty->assign('ibanValue', $ins->iban);
    $smarty->assign('amount', MODULE_PAYMENT_BILLSAFE_2_AMOUNT.':');
    $smarty->assign('amountValue', xtc_format_price_order($ins->amount, 1, $order->info['currency']));
    $smarty->assign('reference', MODULE_PAYMENT_BILLSAFE_2_REFERENCE.':');
    $smarty->assign('referenceValue', $ins->reference);
    $smarty->assign('referenceUrl', MODULE_PAYMENT_BILLSAFE_2_REFERENCE.':');
    $smarty->assign('referenceUrlValue', $url);
  } else {
    $smarty->assign('BillsafeText', '');
    $smarty->assign('BillsafeText2', '');
    $smarty->assign('recipient', '');
    $smarty->assign('recipientValue', '');
    $smarty->assign('account', '');
    $smarty->assign('accountValue', '');
    $smarty->assign('bankcode', '');
    $smarty->assign('bankcodeValue', '');
    $smarty->assign('bank', '');
    $smarty->assign('bankValue', '');
    $smarty->assign('bic', '');
    $smarty->assign('bicValue', '');
    $smarty->assign('iban', '');
    $smarty->assign('ibanValue', '');
    $smarty->assign('amount', '');
    $smarty->assign('amountValue', '');
    $smarty->assign('reference', '');
    $smarty->assign('referenceValue', '');
    $smarty->assign('referenceUrl', '');
    $smarty->assign('referenceUrlValue', '');
  }
} elseif ($billsafe_orders['type'] == 'installment') {
  if ($responseInstruction->ack == 'OK') {
    $url = substr(HTTP_SERVER, 7).DIR_WS_CATALOG;
    $ins = $responseInstruction->instruction;
    $smarty->assign('BillsafeText2', '<b>'.$ins->note.'</b>');
  } else {
    $smarty->assign('BillsafeText2', '');
  }
}
$smarty->caching = false;
$smarty->template_dir = DIR_FS_CATALOG.'templates';
$smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
$smarty->config_dir = DIR_FS_CATALOG.'lang';
$smarty->display(CURRENT_TEMPLATE.'/admin/billsafe_print_order.html');

?>
