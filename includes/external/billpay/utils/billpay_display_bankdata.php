<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

 	function display_billpay_bankdata() {
 		global $order;
		$orderId = $_GET['oID'];
 		
 		$paymentMethod = $order->info['payment_method'];
 		
		if ($paymentMethod == 'billpay') {		/* INVOICE */
	 		$bank_data_query = xtc_db_query(' SELECT account_holder, account_number, bank_code, bank_name, invoice_reference, invoice_due_date '.
											  ' FROM billpay_bankdata WHERE orders_id = '.$orderId);
			if (!xtc_db_num_rows($bank_data_query)) { 
				return '';
			}
			else {
				$bank_data = xtc_db_fetch_array($bank_data_query);
				$dueDate 			= $bank_data['invoice_due_date'];
				$dueDateFormatted 	= substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);

				$bank_data_string = sprintf(html_entity_decode(MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO), $bank_data['invoice_reference'], substr($dueDate,6,2), substr($dueDate,4,-2), substr($dueDate,0,-4));
				$bank_data_string = '<br/><br/>'.$bank_data_string.'<br/>';
				
				$bank_data_string .= '<br/>';
				$bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER .':</strong>&nbsp;' . $bank_data['account_holder'].'<br/>';
				$bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER .':</strong>&nbsp;' . $bank_data['account_number'].'<br/>';
				$bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE .':</strong>&nbsp;' . $bank_data['bank_code'].'<br/>';
				$bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME .':</strong>&nbsp;' . $bank_data['bank_name'].'<br/>';
				$bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE .':</strong>&nbsp;' . $bank_data['invoice_reference'].'<br/>';
				
				if ($dueDate) {
					$bank_data_string .= '<strong>'.MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE .':</strong>&nbsp;' . $dueDateFormatted . '<br/>';
				}
				else {
					$bank_data_string .= MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING;
				}
					
				return $bank_data_string;
			}
		}
 		else if ($paymentMethod == 'billpaydebit') {		/* DIRECT DEBIT */
 			$bank_data_query = xtc_db_query('SELECT invoice_due_date FROM billpay_bankdata WHERE orders_id = '.$orderId);
 			$bank_data = xtc_db_fetch_array($bank_data_query);
 			
 			$infoText = '<br/><br/>'.MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO1 . '<br/>'. MODULE_PAYMENT_BILLPAYDEBIT_TEXT_INVOICE_INFO2 . '<br/><br/>';
 			
 			if (!$bank_data['invoice_due_date']) {
 				$infoText .= '<br/>'.MODULE_PAYMENT_BILLPAYDEBIT_ACTIVATE_ORDER_WARNING;
 			}
 			
 			return $infoText;
		}
		else if ($paymentMethod == 'billpaytransactioncredit') { /* TRANSACTION CREDIT */
			require_once(DIR_FS_DOCUMENT_ROOT . DIR_WS_INCLUDES . 'modules/payment/billpaytransactioncredit.php');
			$billpay = new billpaytransactioncredit();
			$rateDetails = $billpay->buildTCPaymentInfo($orderId, $order, true);
			
			// Validate if order is activated. Otherwise show warning on invoice
			$activated_query = xtc_db_query('SELECT invoice_due_date FROM billpay_bankdata WHERE orders_id = '.$orderId);
			if (xtc_db_num_rows($activated_query)) {
				$data = xtc_db_fetch_array($activated_query);
				
			 	if (!trim($data['invoice_due_date'])) {
			 		$rateDetails .= '<br/><br/>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ACTIVATE_ORDER_WARNING;
			 	}
			}
			
			$infoText = '<br/><br/>'.$rateDetails;
			return $infoText;
		}
		else {	/* OTHER PAYMENT METHODS */
			return '';
		}
 	}
?>	