<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_EXTERNAL . 'billpay/base/billpayBase.php'); // DokuMan -2011-09-08 - BILLPAY payment module (in external directory)

class billpaydebit extends billpayBase {
	var $_paymentIdentifier = 'BILLPAYDEBIT';

	function _getPaymentType() {
		return IPL_CORE_PAYMENT_TYPE_DIRECT_DEBIT;
	}
	
	function _getStaticLimit($config) {
		return $config['static_limit_directdebit'];
	}
	
	/**
	 * display input fields for customers bank data. only for direct debit
	 */
	function _displayBankData() {
		global $order;
		
		$bankdata = '<div style="margin-top:10px; margin-left:3px; margin-bottom:3px">' . MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANKDATA . '</div>';
		$bankdata .= '<table style="margin-bottom:5px"><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER;
		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaydebit_owner', isset($_SESSION['billpaydebit_owner']) ? 
 											$_SESSION['billpaydebit_owner'] : $order->billing['firstname'] . 
 											' ' . $order->billing['lastname']);
 		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaydebit_number');
 		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaydebit_code').'<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr></table>';

 		return $bankdata;
	}

	//set bankdata if selected payment method is billpay debit
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
		$req->set_bank_account(utf8_encode($data_arr['billpaydebit_owner']),
								utf8_encode($data_arr['billpaydebit_number']),
								utf8_encode($data_arr['billpaydebit_code']));						
		return $req;
	}
	
	function _checkBankValues($data_arr) {
		$_SESSION['billpaydebit_owner'] = (isset($data_arr['billpaydebit_owner'])) ? $data_arr['billpaydebit_owner'] : NULL;
		//check direct debit specific values
		$error = false;
		if(isset($data_arr[strtolower($this->_paymentIdentifier).'_number']) && 
				$data_arr[strtolower($this->_paymentIdentifier).'_number'] == '')
		{
			$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NUMBER;
		}
		else if(isset($data_arr[strtolower($this->_paymentIdentifier).'_code']) && 
				$data_arr[strtolower($this->_paymentIdentifier).'_code'] == '')
		{
						$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_CODE;
		}
		else if(isset($data_arr[strtolower($this->_paymentIdentifier).'_owner']) && 
				$data_arr[strtolower($this->_paymentIdentifier).'_owner'] == '')
		{
			$error = true;
			$error_message = MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NAME;
		}
		if($error == true)
		{
			//if($is_ajax == true)
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
		//EOF check direct debit specific values
	}

	function addJsBankValidation() {
	    // check the debit specific input fields
        $js .= '	if (document.getElementById("checkout_payment").elements["billpaydebit_owner"].value == "") {' . "\n" .
    	  	   '	error_message = error_message + unescape("' . JS_BILLPAYDEBIT_NAME . '");' . "\n" .
	    	   '   error = 1;'."\n".'    }' . "\n" .  
      		   '	if (document.getElementById("checkout_payment").elements["billpaydebit_number"].value == "") {' . "\n" .
    		   '	error_message = error_message + unescape("' . JS_BILLPAYDEBIT_NUMBER . '");' . "\n" .
      		   '   error = 1;'."\n".'    }' . "\n" .  
     		   '	if (document.getElementById("checkout_payment").elements["billpaydebit_code"].value == "") {' . "\n" .
    		   '	error_message = error_message + unescape("' . JS_BILLPAYDEBIT_CODE . '");' . "\n" .
      		   '   error = 1;'."\n".'    }' . "\n";
		return $js;
	}
	
}

?>