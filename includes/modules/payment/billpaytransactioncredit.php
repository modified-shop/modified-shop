<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpaytransactioncredit.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

class billpaytransactioncredit extends billpayBase {
	var $_paymentIdentifier = 'BILLPAYTRANSACTIONCREDIT';

	function _getPaymentType() {
		return IPL_CORE_PAYMENT_TYPE_RATE_PAYMENT;
	}
	
	function _getStaticLimit($config) {
		return $config['static_limit_transactioncredit']; 
	}
	
	function _getMinValue($config) {
		return $config['min_value_transactioncredit']; 
	}
	
	/**
	 * display input fields for customers bank data. only for transaction credit
	 */
	function _displayBankData() {
		global $order;
		
		$bankdata = '<div style="margin-top:10px; margin-left:3px; margin-bottom:3px">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA . '</div>';
		$bankdata .= '<table style="margin-bottom:5px"><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER;
		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaytransactioncredit_owner', isset($_SESSION['billpaytransactioncredit_owner']) ? 
 											$_SESSION['billpaytransactioncredit_owner'] : $order->billing['firstname'] . 
 											' ' . $order->billing['lastname']);
  		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaytransactioncredit_number');
 		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaytransactioncredit_code').'<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr></table>';

 		return $bankdata;
	}

	//set bankdata if selected payment method is billpay transaction credit
	function _addBankData($req, $vars) {
		/** ajax one page checkout  */
		if (is_array($vars) && !empty($vars)) 
		{
	  		$data_arr = $vars;
	  		$is_ajax = true;
		}
		else
		{
	  		$data_arr = $_POST;
		}
		$req->set_bank_account(utf8_encode($data_arr['billpaytransactioncredit_owner']),
								utf8_encode($data_arr['billpaytransactioncredit_number']),
								utf8_encode($data_arr['billpaytransactioncredit_code']));
		return $req;
	}
	
	function _addPreauthTcDetails($req, $numberRates, $total) {
		$req->set_rate_request($numberRates, $total);
		return $req;
	}

	function _checkBankValues($data_arr) {
		$_SESSION['billpaytransactioncredit_owner'] = (isset($data_arr['billpaytransactioncredit_owner'])) ? $data_arr['billpaytransactioncredit_owner'] : NULL;
		//check transaction credit specific values
		$error = false;
		if(isset($data_arr[strtolower($this->_paymentIdentifier).'_number']) && 
				$data_arr[strtolower($this->_paymentIdentifier).'_number'] == '')
		{
			$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NUMBER;
		}
		else if(isset($data_arr[strtolower($this->_paymentIdentifier).'_code']) && 
				$data_arr[strtolower($this->_paymentIdentifier).'_code'] == '')
		{
						$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_CODE;
		}
		else if(isset($data_arr[strtolower($this->_paymentIdentifier).'_owner']) && 
				$data_arr[strtolower($this->_paymentIdentifier).'_owner'] == '')
		{
			$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NAME;
		} else if(!isset($_SESSION['bp_rate_result'])) {
			$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN;
		}
		if($error == true)
		{
			if($_SESSION['billpay_is_ajax'] == true)
			{
				$_SESSION['checkout_payment_error'] = 'payment_error=' . $this->code . '&error=' . urlencode($error_message);
			}
			else
			{
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 
					'error_message='.urlencode($error_message), 'SSL'));	
			}
		}
		//EOF check transaction credit specific values
	}

	function addJsBankValidation() {
	    // check the transaction credit specific input fields
        $js .= '	if (document.getElementById("checkout_payment").elements["billpaytransactioncredit_owner"].value == "") {' . "\n" .
    	  	   '	error_message = error_message + unescape("' . JS_BILLPAYTRANSACTIONCREDIT_NAME . '");' . "\n" .
	    	   '   error = 1;'."\n".'    }' . "\n" .  
      		   '	if (document.getElementById("checkout_payment").elements["billpaytransactioncredit_number"].value == "") {' . "\n" .
    		   '	error_message = error_message + unescape("' . JS_BILLPAYTRANSACTIONCREDIT_NUMBER . '");' . "\n" .
      		   '   error = 1;'."\n".'    }' . "\n" .  
     		   '	if (document.getElementById("checkout_payment").elements["billpaytransactioncredit_code"].value == "") {' . "\n" .
    		   '	error_message = error_message + unescape("' . JS_BILLPAYTRANSACTIONCREDIT_CODE . '");' . "\n" .
      		   '   error = 1;'."\n".'    }' . "\n";
		return $js;
	}
	
	function showFeeInTitle() {
		return true;
	}
	
}

?>