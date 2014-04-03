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

class billpaytransactioncredit extends billpayBase
{
    var $_paymentIdentifier = self::PAYMENT_METHOD_TRANSACTION_CREDIT;

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
		
		$bankdata = '<div style="border:1px solid silver; padding:5px; width:' . ($this->_getPaymentBlockWidth() - 12) . 'px; margin-bottom:10px">';
		$bankdata .= '<div style="margin-top:10px; margin-left:3px; margin-bottom:3px; border">' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA . '</div>';
		$bankdata .= '<table style="margin-bottom:5px"><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER;
		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaytransactioncredit_owner', isset($_SESSION['billpaytransactioncredit_owner']) ? 
 											$_SESSION['billpaytransactioncredit_owner'] : $order->billing['firstname'] . 
 											' ' . $order->billing['lastname'], 'style="width:250px"');
  		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_NUMBER;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaytransactioncredit_number', '', 'style="width:250px"');
 		$bankdata .= '<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr><tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_BANK_CODE;
 		$bankdata .= '</td><td>' . xtc_draw_input_field('billpaytransactioncredit_code', '', 'style="width:250px"').'<span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr></table>';
		$bankdata .= '</div>';
		
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
            'headline'             => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BANKDATA,
            'account_holder_text'  => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ACCOUNT_HOLDER,
            'account_holder_input' => $accountHolderInput,
            'account_number_text'  => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_IBAN,
            'account_number_input' => $accountNumberInput,
            'bank_code_text'       => MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_BIC,
            'bank_code_input'      => $bankCodeInput,
        ));

        return $smarty->fetch('../includes/external/billpay/templates/bankdata_sepa_form.tpl');
    }
	
	function _wrapB2CInputFieldsHTML($margin, $genderSelectHTML, $birthdaySelectHTML) {
		if ($margin > 0) {
			return '<div style="border:1px solid silver;padding:5px;margin-bottom:10px; width:488px"><table>'.$genderSelectHTML.$birthdaySelectHTML.'</table></div>';
		}
		else {
			return '<table>'.$genderSelectHTML.$birthdaySelectHTML.'</table>';
		}
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
        // Es gelten die <a href='%1$s'>AGB</a>, <a href='%2$s'>Zahlungsbedingungen</a> und <a href='%3$s'>Datenschutzbestimmungen</a>"
        $eulaText = sprintf($eulaText, $this->_buildTcTermsUrl(), $this->_buildPaymentConditionUrl(), $this->_buildTcPrivacyUrl());

        return $this->_buildEulaHTML($eulaText);
    }

    function _getEulaText()
    {
        $eulaText = constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_EULA_CHECK');
        $eulaText = sprintf($eulaText, $this->_buildTcTermsUrl(),$this->_buildPaymentConditionUrl(), $this->_buildTcPrivacyUrl());

        return $this->_buildEulaHTML($eulaText);
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

    function _checkBankValues($data_arr)
    {
        $_SESSION['billpaytransactioncredit_owner'] = (isset($data_arr['billpaytransactioncredit_owner'])) ? $data_arr['billpaytransactioncredit_owner'] : NULL;

        //check transaction credit specific values
        $error = false;
        $error_message = '';

        if (isset($data_arr[strtolower($this->_paymentIdentifier).'_number'])
            && $data_arr[strtolower($this->_paymentIdentifier).'_number'] == ''
        ) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NUMBER;

        } elseif ((defined('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT') === false
                || MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT != 'True')
            && isset($data_arr[strtolower($this->_paymentIdentifier).'_code'])
            && $data_arr[strtolower($this->_paymentIdentifier).'_code'] == ''
        ) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_CODE;

        } elseif (isset($data_arr[strtolower($this->_paymentIdentifier).'_owner'])
            && $data_arr[strtolower($this->_paymentIdentifier).'_owner'] == ''
        ) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NAME;

        } elseif(isset($_SESSION['bp_rate_result']) === false) {
            $error = true;
            $error_message = MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_NO_RATEPLAN;
        }

        if($error == true) {
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

        $js = ' if (document.getElementById("checkout_payment").elements["billpaytransactioncredit_owner"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYTRANSACTIONCREDIT_NAME . '");
                error = 1;
            }
            if (document.getElementById("checkout_payment").elements["billpaytransactioncredit_number"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYTRANSACTIONCREDIT_NUMBER . '");
                error = 1;
            }';

        if (defined('MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT') === false
            || MODULE_PAYMENT_BILLPAY_GS_SEPA_SUPPORT != 'True'
        ) {
            $js .= '
            if (document.getElementById("checkout_payment").elements["billpaytransactioncredit_code"].value == "") {
                error_message = error_message + unescape("' . JS_BILLPAYTRANSACTIONCREDIT_CODE . '");
                error = 1;
            }';
        }

        return $js;
    }
	
	function showFeeInTitle() {
		return true;
	}

    /**
     * step for temporary order to execute the redirect to our giropay landing page
     * @return void
     */
    function payment_action()
    {
        if(isset($_SESSION['billpay_state']) && $_SESSION['billpay_state'] == "YELOW") {
            // the after process is not called for temporary orders but we need it for the order update request
            $this->after_process();
            xtc_redirect(xtc_href_link("checkout_billpay_giropay.php", null, 'SSL'));
        }
    }

    /**
     * installs the transaction credit payment module
     *
     * @return void
     */
    function install()
    {
        parent::install();

        // install totals for transaction credit
        $otBillpayTcSurcharge = new ot_billpaytc_surcharge();
        $otBillpayTcSurcharge->install();
    }

    function remove($state = null)
    {
        parent::remove($state);

        $otBillpayTcSurcharge = new ot_billpaytc_surcharge();
        $otBillpayTcSurcharge->remove();
    }
}
