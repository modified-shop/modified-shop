<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpay_status_requests.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
 	$status = $_POST['status'];
 	 
	// fetch status from db
	$billpayCheckStatusQuery = xtc_db_query("select orders_status from ".TABLE_ORDERS." where orders_id = '".xtc_db_input($oID)."'");
	$billpayCheckStatus = xtc_db_fetch_array($billpayCheckStatusQuery);

	if ($billpayCheckStatus['orders_status'] != $status && in_array($order->info['payment_method'], array('billpay', 'billpaydebit', 'billpaytransactioncredit'))) {
		$paymentMethod 	= $order->info['payment_method'];
		$orderId 		= $_GET["oID"];
		
		require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaydebit.php');
		require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/billpaytransactioncredit.php');
		
		// find correct language constants
		$_INVOICE_CREATED_STATUS_INFO = constant('MODULE_PAYMENT_'.strtoupper($paymentMethod).'_TEXT_INVOICE_CREATED_COMMENT');		
		
		if($status == MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED || $status == MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED) {
			/** prepare default params for cancel request and invoice created request */
			require_once(DIR_FS_CATALOG . 'includes/modules/payment/billpay.php');
			
			$billpay = new billpay(strtoupper($order->info['payment_class']));
			$total_query 		= xtc_db_query('SELECT class, value FROM '. TABLE_ORDERS_TOTAL .' WHERE class = "ot_total" AND orders_id = '.$orderId);
			$total_array 		= xtc_db_fetch_array($total_query);
			
			$total 				= $billpay->_currencyToSmallerUnit($total_array['value']);
			$currency 			= $order->info['currency'];
			$sucess				= false;
			/** EOF prepare default params for cancel request and invoice created request */
			
			/** BEGIN invoice created request */
			if($status == MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED) {
				require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
				require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_invoice_created_request.php');
				
				$query = 'SELECT api_reference_id FROM billpay_bankdata WHERE orders_id = ' . $orderId;
				
				$res = xtc_db_query($query);
				$a = xtc_db_fetch_array($res);
				
				$apiReferenceId = $a['api_reference_id'];
				
				if (!$apiReferenceId) {
					$infoText = 'No api reference found for orders_id ' . $orderId;
					$billpay->_logError($infoText, 'ERROR trying to submit invoiceCreated');
				}
				else {
			  		$req = new ipl_invoice_created_request($billpay->api_url);
		
					$req->set_default_params($billpay->bp_merchant, $billpay->bp_portal, $billpay->bp_secure);
					$req->set_invoice_params($total, $currency, $apiReferenceId);	
					
					$internalError = $req->send();
					
					// log xml
					$_xmlreq 	= (string)utf8_decode($req->get_request_xml());
					$_xmlresp 	= (string)utf8_decode($req->get_response_xml());
					$billpay->_logError($_xmlreq, 'XML request (invoiceCreated)');
					$billpay->_logError($_xmlresp, 'XML response (invoiceCreated)');
					
					if ($internalError) {
						$infoText = $internalError['error_message'];
						$billpay->_logError($infoText, 'Internal error occured (invoiceCreated)');
					}
					else if ($req->has_error()) {
						$infoText = utf8_decode($req->get_merchant_error_message())." (Error Code: ".$req->get_error_code().")";
						$billpay->_logError($infoText, 'Error occured (invoiceCreated)');
					}
					else {
						if($req->get_invoice_duedate() == '') {
							/** due date is empty. */
							$infoText = 'Fehler: Das Zahlungsziel ist leer';
							$billpay->_logError($infoText, 'invoice created error');
						}
						else {
							$upd_success = xtc_db_query('UPDATE billpay_bankdata SET invoice_due_date = "'.
								$req->get_invoice_duedate().'" '.'WHERE orders_id = '.$orderId);
							
							if ($paymentMethod == 'billpay') {	/* INVOICE */
								$billpay->addHistoryEntry($orderId, $_INVOICE_CREATED_STATUS_INFO, MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED);
								
								$dueDate 			= $req->get_invoice_duedate();
								$dueDateFormatted 	= substr($dueDate,6,2).".".substr($dueDate,4,-2).".".substr($dueDate,0,-4);
								
								//$infoText .= "\n\n";
								$infoText = MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER . ": " . $req->get_account_holder() . "\n";
								$infoText .= MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER . ": " . $req->get_account_number() . "\n";
								$infoText .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE . ": " . $req->get_bank_code() . "\n";
								$infoText .= MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME . ": " . $req->get_bank_name() . "\n";
								$infoText .= MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE . ": " . $billpay->generateInvoiceReference($orderId) . "\n";
								$infoText .= MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE . ": " . $dueDateFormatted;
							}
							else if ($paymentMethod == 'billpaydebit') {			/* DIRECT DEBIT */
								$infoText = $_INVOICE_CREATED_STATUS_INFO;
							}
							else if ($paymentMethod == 'billpaytransactioncredit') {	 /* TRANSACTION CREDIT */
								
								// Get due dates and amounts from response
								$dueDateList = $req->get_dues();
								$serializedDueDateList = $billpay->serializeDueDateArray($dueDateList);
								
								// Store serialzed data in db
								$upd_success = xtc_db_query('UPDATE billpay_bankdata SET rate_dues = "'.
									$serializedDueDateList.'" '.'WHERE orders_id = '.$orderId);
								$infoText = $_INVOICE_CREATED_STATUS_INFO;
							}
							
							$success = true;
						}
					}
				}
			}
			/** EOF invoice created request */

			/** BEGIN cancel request */
			if($status == MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED) {
				require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
				require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_cancel_request.php');
				
				$query = 'SELECT api_reference_id FROM billpay_bankdata WHERE orders_id = ' . $orderId;
				
				$res = xtc_db_query($query);
				$a = xtc_db_fetch_array($res);
				
				$apiReferenceId = $a['api_reference_id'];
				$newSuccessStatus = MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED;
				
				if (!$apiReferenceId) {
					$infoText = 'No api reference found for orders_id ' . $orderId;
					$billpay->_logError($infoText, 'ERROR trying to submit cancel');
				}
				else {
			  		$req = new ipl_cancel_request($billpay->api_url);
		
					$req->set_default_params($billpay->bp_merchant, $billpay->bp_portal, $billpay->bp_secure);
					$req->set_cancel_params($apiReferenceId, $total, $currency);
			
					$internalError = $req->send();
	
					// log xml
					$_xmlreq 	= (string)utf8_decode($req->get_request_xml());
					$_xmlresp 	= (string)utf8_decode($req->get_response_xml());
					$billpay->_logError($_xmlreq, 'XML request (cancel)');
					$billpay->_logError($_xmlresp, 'XML response (cancel)');
						
					if ($internalError) {
						$infoText = $internalError['error_message'];
						$billpay->_logError($infoText, 'Internal error occured (cancel)');
					}
					else if ($req->has_error()) {
						$infoText = utf8_decode($req->get_merchant_error_message())." (Error Code: ".$req->get_error_code().")";
						$billpay->_logError($infoText, 'Error occured (cancel)');
					}
					else {
						$infoText = MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT;
						$success = true;
					}
				}
			}
			/** EOF cancel request */
			
		
			if ($success) {
				if (!isset($comments) || $comments != '') {
					$billpay->addHistoryEntry($orderId, $infoText, $newSuccessStatus);
				}
				else {
					$comments = $infoText;
				}
			}
			else {
				$billpay->addHistoryEntry($orderId, $infoText, 0);
				xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&oID='.$orderId));
				return;
			}
		}
	}
?>