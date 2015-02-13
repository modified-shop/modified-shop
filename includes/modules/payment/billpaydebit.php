<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpaydebit.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2010 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php';

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
 											' ' . $order->billing['lastname'], 'style="width:250px"');
 		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaydebit_number', '', 'style="width:250px"');
 		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaydebit_code', '', 'style="width:250px"').'<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr></table>';

 		return $bankdata;
	}

    function _displaySepaBankData()
    {
        global $smarty, $order;

        $accountPreselect = isset($_SESSION['billpaydebit_owner'])
            ? $_SESSION['billpaydebit_owner']
            : $order->billing['firstname'] . ' ' . $order->billing['lastname'];
        $accountHolderInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_owner',
            $accountPreselect,
            'style="width:250px"'
        );

        $accountNumberInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_number',
            '',
            'style="width:250px"'
        );

        $bankCodeInput = xtc_draw_input_field(
            strtolower($this->_paymentIdentifier) . '_code',
            '',
            'style="width:250px"'
        );

        $smarty->assign(array(
                'headline'             => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BANKDATA,
                'account_holder_text'  => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ACCOUNT_HOLDER,
                'account_holder_input' => $accountHolderInput,
                'account_number_text'  => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_IBAN,
                'account_number_input' => $accountNumberInput,
                'bank_code_text'       => MODULE_PAYMENT_BILLPAYDEBIT_TEXT_BIC,
                'bank_code_input'      => $bankCodeInput,
            ));

        return $smarty->fetch('../includes/external/billpay/templates/bankdata_sepa_form.tpl');
    }

    function _getSepaEulaText()
    {
        $baseIdentifier = 'MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_EULA_CHECK_SEPA';
        $eulaIdentifier = $this->_getCountrySpecificIdentifier($baseIdentifier);

        // fallback
        if (defined($eulaIdentifier) === false) {
            return $this->_getEulaText();
        }

        $eulaText = constant($eulaIdentifier);
        // Es gelten die <a href='%1$s'>Datenschutzbestimmungen</a> von Billpay.
        $eulaText = sprintf($eulaText, $this->_buildTermsOfServiceUrl());

        return $this->_buildEulaHTML($eulaText);
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

    function _checkBankValues($data_arr=array())
    {
        $_SESSION['billpaydebit_owner'] = (isset($data_arr['billpaydebit_owner'])) ? $data_arr['billpaydebit_owner'] : null;

        //check direct debit specific values
        $error = false;
        $error_message = '';

        if (isset($data_arr[strtolower($this->_paymentIdentifier).'_number'])
            && $data_arr[strtolower($this->_paymentIdentifier).'_number'] == ''
        ) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NUMBER;

        } elseif ((defined('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT') === false
                || MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT != 'True')
            && isset($data_arr[strtolower($this->_paymentIdentifier).'_code'])
            && $data_arr[strtolower($this->_paymentIdentifier).'_code'] == ''
        ) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_CODE;

        } elseif (isset($data_arr[strtolower($this->_paymentIdentifier).'_owner'])
            && $data_arr[strtolower($this->_paymentIdentifier).'_owner'] == ''
        ) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYDEBIT_TEXT_ERROR_NAME;
        }

        if ($error == true) {
            //if($is_ajax == true)
            if($_SESSION['billpay_is_ajax'] == true) {
                $_SESSION['checkout_payment_error'] = 'payment_error=' . $this->code . '&error=' . urlencode($error_message);
            } else {
                xtc_redirect(xtc_href_link(
                        FILENAME_CHECKOUT_PAYMENT,
                        'error_message='.urlencode($error_message), 'SSL'
                    ));
            }
        }
    }

    function addJsBankValidation() {

        $js = ' if (document.getElementById("checkout_payment").elements["billpaydebit_owner"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYDEBIT_NAME . '");
                error = 1;
            }
            if (document.getElementById("checkout_payment").elements["billpaydebit_number"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYDEBIT_NUMBER . '");
                error = 1;
            }';

        if (defined('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT') === false
            || MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT != 'True'
        ) {
            $js .= '
            if (document.getElementById("checkout_payment").elements["billpaydebit_code"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYDEBIT_CODE . '");
                error = 1;
            }';
        }

        return $js;
    }
}
