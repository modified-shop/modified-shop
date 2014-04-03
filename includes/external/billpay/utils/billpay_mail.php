<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

	require_once(DIR_FS_CATALOG . 'includes/modules/payment/billpay.php');
	$paymentMethod = $order->info['payment_method'];
	if ($paymentMethod == 'billpay') {
		//if(isset($_SESSION['billpay_transaction_id'])) {
			$billpay = new billpay(strtoupper($order->info['payment_method']));
			
			if(isset($_SESSION['billpay_transaction_id'])) {
				$billpay_bankdata_query = "SELECT account_holder, account_number, bank_code, bank_name, invoice_reference ".
			    						  "FROM billpay_bankdata ".
				                          "WHERE tx_id = '".$_SESSION['billpay_transaction_id']."'";
			}else {
				$billpay_bankdata_query = "SELECT account_holder, account_number, bank_code, bank_name, invoice_reference ".
			    						  "FROM billpay_bankdata ".
				                          "WHERE api_reference_id = '".$oID."'";
			}

			$billpay_bankdata_result = xtc_db_query($billpay_bankdata_query);
			$billpay_bankdata = xtc_db_fetch_array($billpay_bankdata_result);
			//$billpay_infotext = MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL . '<br /><br />';

			if(!$billpay_bankdata['api_reference']){
				$invoiceReference = $billpay->generateInvoiceReference($insert_id);
			} else {
				$invoiceReference = $billpay_bankdata['invoice_reference'];
			}
			
			//$invoiceReference = $billpay->generateInvoiceReference($insert_id);
			
			$billpay_infotext = sprintf(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL, $invoiceReference) . '<br /><br />';
			$billpay_infotext .= MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER .': '. $billpay_bankdata['account_holder'].'<br />';
			$billpay_infotext .= MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER .': '. $billpay_bankdata['account_number'].'<br />';
			$billpay_infotext .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE .': '. $billpay_bankdata['bank_code'].'<br />';
			$billpay_infotext .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME .': '. $billpay_bankdata['bank_name'].'<br />';
			$billpay_infotext .= MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE .': ' . $invoiceReference . '<br />';
			if(defined('EMAIL_USE_HTML') && EMAIL_USE_HTML == 'false') {
				$billpay_infotext = utf8_encode(html_entity_decode($billpay_infotext));
			}
			if(defined('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') &&
				constant('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') == 'True') {
				$billpay_infotext = utf8_encode($billpay_infotext);
			}
			$smarty->assign('PAYMENT_INFO_HTML', $billpay_infotext);
			$smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", $billpay_infotext));
	//	}
	}
	else if ($paymentMethod == 'billpaydebit') {
		$billpay_infotext = '<br /><br />' . MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1;
		if(defined('EMAIL_USE_HTML') && EMAIL_USE_HTML == 'false') {
			$billpay_infotext = utf8_decode(html_entity_decode($billpay_infotext));
		}
		if(defined('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE') &&
		constant('MODULE_PAYMENT_BILLPAYDEBIT_UTF8_ENCODE') == 'True') {
			$billpay_infotext = utf8_encode($billpay_infotext);
		}
		$smarty->assign('PAYMENT_INFO_HTML', $billpay_infotext);
		$smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", $billpay_infotext));
	}
	else if ($paymentMethod == 'billpaytransactioncredit') {
        require_once DIR_FS_CATALOG . 'includes/modules/payment/billpaytransactioncredit.php';
		$x = new billpaytransactioncredit();
		$rateDetailsHTML = $x->buildTCPaymentInfo($x->_getTransactionId(), $order, true, true);
		$rateDetailsText = $x->buildTCPaymentInfo($x->_getTransactionId(), $order, false, true);

		$smarty->assign('PAYMENT_INFO_HTML', $rateDetailsHTML);
		$smarty->assign('PAYMENT_INFO_TXT', $rateDetailsText);
	}
?>