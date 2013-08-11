<?php
/* -----------------------------------------------------------------------------------------
   $Id: billpayBase.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2012 Billpay GmbH

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require (DIR_FS_CATALOG.'callback/billpay/billpayCallback.php');
require_once(DIR_FS_CATALOG . 'includes/modules/order_total/ot_billpaytc_surcharge.php');

if(!class_exists('billpayBase')) {

	class billpayBase {
		var $code, $title, $description, $enabled, $order;
		var $eula_url, $testmode, $api_url, $_formDob, $_formGender, $_log;
		var $_logPath, $enableLog, $debugLog, $_mode;
		var $bp_merchant, $bp_portal, $bp_secure;
		var $billpayCallback;

		var $requiredModules 		= array('ot_total', 'ot_subtotal');
		var $billpayShippingModules = array('ot_billpay_fee', 'ot_billpaydebit_fee', 'ot_billpaybusiness_fee', 'ot_cod_fee', 'ot_loworderfee', 'ot_ps_fee', 'ot_shipping');
		var $billpayExcludeModules  = array('ot_subtotal', 'ot_subtotal_no_tax', 'ot_tax', 'ot_total');

		/*
		 * check if bank data values are not empty. only for direct debit and transaction credit
		 */
		function _checkBankValues($vars='') {}

		function billpayBase($identifier = null) {
			global $order;

			if(isset($identifier) && !empty($identifier)) {
				$this->_paymentIdentifier = $identifier;
			}

			$this->code = strtolower($this->_paymentIdentifier);
			$this->title = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_TITLE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_TITLE') : '';
			$this->description = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_DESCRIPTION') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TEXT_DESCRIPTION') : '';
			$this->sort_order = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SORT_ORDER') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SORT_ORDER') : '';
			$this->min_order = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_MIN_AMOUNT') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_MIN_AMOUNT') : '';
			$this->_logPath = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING') : '';
			$this->order_status = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ORDER_STATUS') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ORDER_STATUS') : '';

			$this->error_status = defined('MODULE_PAYMENT_BILLPAY_STATUS_ERROR') ? MODULE_PAYMENT_BILLPAY_STATUS_ERROR : '';

			$this->b2b_active = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_B2BCONFIG') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_B2BCONFIG') : '';

			//$this->_testapi_url = 'https://test-api.billpay.de/xml/offline';
			$this->_testapi_url = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTAPI_URL_BASE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTAPI_URL_BASE') : '';
			$this->_merchant_info = 'http://www.billpay.de/haendler/integration-plugin';

			if (empty($this->_logPath)) {
				$this->_logPath = DIR_FS_CATALOG . 'includes/external/billpay/log/billpay.log';
			}
			else {
				$this->_logPath .= '/billpay.log';
			}
			$this->enableLog = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING_ENABLE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING_ENABLE') : false;

			// temporary variables for check below
			$_bpMerchant	= defined('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_MERCHANT_ID') ? constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_MERCHANT_ID') : null;
			$_bpPortal	= defined('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_PORTAL_ID') ? constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_PORTAL_ID') : null;
			$_bpSecure	= defined('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_SECURE') ? constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_SECURE') : null;

			$this->testmode 	= defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTMODE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTMODE') : false;

			if ($this->testmode == 'Testmodus') {
				$this->api_url = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTAPI_URL_BASE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTAPI_URL_BASE') : '';
			}
			else {
				$this->api_url = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_API_URL_BASE') ? constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_API_URL_BASE') : '';
			}
			// deactivate module on missing but needed settings
			if ((empty($_bpMerchant)) || (empty($_bpPortal)) || (empty($_bpSecure))) {
				$this->_mode = 'sandbox';
			}
			else {
				if($this->api_url == $this->_testapi_url)
				{
					$this->_mode = 'check';
				}
				$_SESSION['billpay_deactivated'] = $this->enabled;
				$this->bp_merchant = (int)$_bpMerchant;
				$this->bp_portal = (int)$_bpPortal;
				$this->bp_secure = md5($_bpSecure);
			}
			$this->enabled = defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_STATUS') && constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_STATUS') == 'True' ? true : false;

			// free ressources
			unset($_bpMerchant, $_bpPortal, $_bpSecure);
			$this->sessionID	= xtc_session_id();

			if (isset($order) && is_object($order)) {
				$this->update_status();
			}

			$this->billpayCallback = new billpayCallback();
		}

		function update_status() {
			global $order;

			if ( ($this->enabled == true) && ((int)constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ZONE') > 0) ) {
				$check_flag = false;
				$check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ZONE') . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");

				while ($check = xtc_db_fetch_array($check_query)) {
					if ($check['zone_id'] < 1) {
						$check_flag = true;
						break;
					}
					else if ($check['zone_id'] == $order->billing['zone_id']) {
						$check_flag = true;
						break;
					}
				}
				if ($check_flag == false) {
					$this->enabled = false;
				}
			}
		}

		function getMode() {
			return $this->testmode;
		}

		function javascript_validation() {
			// check values
			$js = '   if (payment_value == "' . $this->code . '") {' . "\n" .
		       		'   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'[dob][day]"].value == "00") {' . "\n" .
		      		'   error_message = error_message + unescape("' . JS_BILLPAY_DOBDAY . '");' . "\n" .
		      		'   error = 1;'."\n".'    }' . "\n" .
		      		'   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'[dob][month]"].value == "00") {' . "\n" .
		      		'   error_message = error_message + unescape("' . JS_BILLPAY_DOBMONTH . '");' . "\n" .
		      		'   error = 1;'."\n".'    }' . "\n" .
		            '   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'[dob][year]"].value == "00") {' . "\n" .
		      		'   error_message = error_message + unescape("' . JS_BILLPAY_DOBYEAR . '");' . "\n" .
		      		'   error = 1;'."\n".'    }' . "\n" ;
		            '   if (document.getElementById("checkout_payment").elements["'.strtolower($this->_paymentIdentifier).'_gender"].value == "") {' . "\n" .
		      		'   error_message = error_message + unescape("' . JS_BILLPAY_GENDER . '");' . "\n" .
		      		'   error = 1;'."\n".'    }' . "\n";

			$js .= 	 '	if (!document.getElementById("checkout_payment").'.strtolower($this->_paymentIdentifier).'_eula.checked) {' . "\n" .
		    			 '	error_message = error_message + unescape("' . JS_BILLPAY_EULA . '");' . "\n" .
		    			 '	error = 1;' . "\n" .
		 				 '	}'  . "\n" .
		      			 '}' . "\n";
			return $js;
		}

		function _getJSvalidation() {}

		/* returns javascript code for enable billpay input fields
		 * after activating eula*/
		function _displayJsSlider() {
			$billpay_js = "<script type='text/javascript'>".
 							"function show_billpay_details(method){".
 							"if(document.getElementById(method).style.display=='none') {".
							"if (method == 'BILLPAYTRANSACTIONCREDIT') {document.getElementById('ratePlanFrame').src='".
			(ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . "billpay_rate_requests.php?preload=1'}".
 							"document.getElementById(method).style.display='block';".
 							"} else { document.getElementById(method).style.display='none';}}".
							"</script>";
			return $billpay_js;
		}

		function _getStaticLimit($config) {
			return 0;
		}

		function _getMinValue($config) {
			return 0;
		}

		/*gambio specific error messages*/
		function _displayGMerror($error) {}

		/*clear gambio error messages from session. only for gambio*/
		function _clearGMerror() {}

		function selection() {
			global $order;

			$deactivateBillpay = FALSE;

			// STEP 1: Check if customer has been denied previously
			if (isset($_SESSION['billpay_hide_payment_method']) && $_SESSION['billpay_hide_payment_method'] === TRUE) {
				$deactivateBillpay = TRUE;
			}
			// STEP 2: Check if minimum order value is deceeded
			else if ($order->info['total'] < (float)$this->min_order) {
				$deactivateBillpay = TRUE;
			}
			else {
				// STEP 3: Check if all required default modules are installed (need not be activated)
				foreach ($this->requiredModules as $moduleName) {
					if ($this->isModuleInstalled($moduleName) === FALSE) {
						$this->_logError("Required module $moduleName is not installed. Hide billpay payment method.", "FATAL ERROR");
						$deactivateBillpay = TRUE;
						break;
					}
				}

				if ($deactivateBillpay === FALSE) {
					$config = $this->getModuleConfig();

					// STEP 4: Check, if static limit is exceeded
					if ($config != FALSE) {
						$staticLimit 	= $this->_getStaticLimit($config);
						$minValue 		= $this->_getMinValue($config);
						$orderTotal 	= $this->_currencyToSmallerUnit($order->info['total']);

						if ($orderTotal > $staticLimit) {
							$deactivateBillpay = TRUE;
							$this->_logError($this->_paymentIdentifier.' static limit exceeded (' . $orderTotal . ' > ' . $staticLimit . ')');
						}

						if ($orderTotal < $minValue) {
							$deactivateBillpay = TRUE;
							$this->_logError($this->_paymentIdentifier.' min value deceeded (' . $orderTotal . ' < ' . $minValue . ')');
						}
					}
					else {
						$deactivateBillpay = TRUE;
					}

					if ($deactivateBillpay === FALSE) {
						// STEP 5: Check, if all customer groups are denied
						if ($this->_is_b2b_allowed($config) == false && $this->_is_b2c_allowed($config) == false) {
							$this->_logError('No customer groups allowed for ' . $this->_paymentIdentifier);
							$deactivateBillpay = TRUE;
						}
					}
				}
			}

			/** gambio specific. remove last billpay error message from session */
			$this->_clearGMerror();

			if ($deactivateBillpay === TRUE) {
				$_SESSION['billpay_deactivated'] = TRUE;
			}
			else {
				return $this->_buildPaymentHtml();
			}
		}

		function addHistoryEntry($oID, $infoText, $status = null) {
			if (is_null($status)) {
				$handle = xtc_db_query("SELECT orders_status FROM ".TABLE_ORDERS." WHERE orders_id='".$oID."'");
				$data = xtc_db_fetch_array($handle);
				$status = $data['orders_status'];
			}

			xtc_db_query("INSERT INTO ".TABLE_ORDERS_STATUS_HISTORY." (orders_id, orders_status_id, date_added, comments) VALUES (".$oID.", ".$status.", now(), '".$infoText."')");
		}
			

		function _addB2BInputFields() {
			global $order;

			$companyName 	= $this->_getDataValue('company_name') ? $this->_getDataValue('company_name') : $order->customer['company'];
			$legalForm		= $this->_getDataValue('legal_form');
			$registerNumber	= $this->_getDataValue('register_number');
			$taxNumber		= $this->_getDataValue('tax_number') ? $this->_getDataValue('tax_number') : $_SESSION['customer_vat_id'];
			$holderName		= $this->_getDataValue('holder_name');
			$contactPerson	= $order->billing['firstname'] . ' ' . $order->billing['lastname'];

			$salutation		= $this->_getCustomerSalutation($this->_getDataValue('gender_b2b'));
			if ($salutation) {
				$contactPerson = $salutation . ' ' . $contactPerson;
			}
			$b2bdata = '<table style="margin-bottom:15px"><tr><td>' . MODULE_PAYMENT_BILLPAY_B2B_COMPANY_NAME_TEXT . '</td><td><input type="text" id="'.$this->_getDataIdentifier('company_name').'" name="'.$this->_getDataIdentifier('company_name').'" value="' . $companyName . '" style="width:170px" /><span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr>';
			$b2bdata .= '<tr><td>' . MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_TEXT . '</td><td>';
			$b2bdata .= '<select id="'.$this->_getDataIdentifier('legal_form').'" name="'.$this->_getDataIdentifier('legal_form').'" style="width:250px"><option value="">---</option>';

			$legalFormList = explode("|", MODULE_PAYMENT_BILLPAY_B2B_LEGALFORM_VALUES);
			foreach ($legalFormList as $l) {
				$parts = explode(":", $l);
				$key = trim($parts[0]);
				$value = trim($parts[1]);

				$b2bdata .= "<option value='$key'";
				$b2bdata .= ($key == $legalForm) ? " selected" : "";
				$b2bdata .= ">$value</option>";
			}

			$b2bdata .= '</select><span class="inputRequirement">&nbsp;*&nbsp;</span></td></tr>';
			$b2bdata .= $this->_add_gender_input_field('b2b');
			$b2bdata .= '<tr><td>' . MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_TEXT . '</td><td><input type="text" id="'.$this->_getDataIdentifier('register_number').'" name="'.$this->_getDataIdentifier('register_number').'" value="' . $registerNumber . '" style="width:110px" /></td></tr>';
			$b2bdata .= '<tr><td>' . MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_TEXT . '</td><td><input type="text" id="'.$this->_getDataIdentifier('tax_number').'" name="'.$this->_getDataIdentifier('tax_number').'" value="' . $taxNumber . '" style="width:110px" /></td></tr>';
			$b2bdata .= '<tr><td>' . MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_TEXT . '</td><td><input type="text" id="'.$this->_getDataIdentifier('holder_name').'" name="'.$this->_getDataIdentifier('holder_name').'" value="' . $holderName . '" style="width:170px" /></td></tr>';

			$b2bdata .= '<tr><td colspan="2" style="padding-top:10px; font-style:italic">'.MODULE_PAYMENT_BILLPAY_B2B_CONTACT_PERSON_TEXT.':&nbsp;'.$contactPerson.'</td></tr>';

			$b2bdata .= '</td></tr></table>';
			return $b2bdata;
		}

		function _addB2CInputFields() {
			$_customerDob = $this->_getCustomerDob();
			$_customerGender = $this->_getCustomerGender();

			$guiVisible = false;
			if (empty($_customerGender)) {
				$guiVisible = true;
			}

			$genderSelectHTML .= $this->_add_gender_input_field('b2c');

			if (!empty($_customerDob)) {
				$birthdaySelectHTML = '<input type="hidden" maxlength="10" size="10" name="'.$this->_getDataIdentifier('dob').'" value="'.$_customerDob.'"/>';
			}
			else {
				$birthdaySelectHTML = '<tr><td>' . MODULE_PAYMENT_BILLPAY_TEXT_BIRTHDATE .'</td><td>'.$this->_getSelectDobDay() .'&nbsp;'.$this->_getSelectDobMonth() .'&nbsp;'.$this->_getSelectDobYear() . '<span class="inputRequirement">&nbsp;*&nbsp;</span></td>';
				$guiVisible = true;
			}

			$margin = $guiVisible ? '10' : '0';
			return '<table style="margin-bottom:'.$margin.'px">'.$genderSelectHTML.$birthdaySelectHTML.'</table>';
		}

		function _addTcInputFields() {
			global $order, $order_total_modules;

			$tcInputFields = '<iframe id="ratePlanFrame" style="width:500px;height:295px;" src="' . (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . 'billpay_rate_requests.php" frameBorder="0"></iframe>';
			$customerPhone = $this->_getCustomerPhone();
			if (!empty($customerPhone)) {
				$tcInputFields .= '<input type="hidden" maxlength="10" size="10" name="'.$this->_getDataIdentifier('phone').'" value="' . $customerPhone . '"/>';
			} else {
				$tcInputFields .= '<div style="margin-top:10px; margin-left:3px; margin-bottom:3px">'
				. MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ENTER_PHONE . '</div>';
				$tcInputFields .= '<table style="margin-bottom:5px"><tr><td>';
				$tcInputFields .= '<tr><td>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_PHONE .'</td>
									   <td>' . xtc_draw_input_field('billpaytransactioncredit_phone') . '</td></tr></table>';
				$guiVisible = true;
			}
			return $tcInputFields;
		}

		function _addB2BSelection() {
			global $order;

			$config = $this->getModuleConfig();
			if ($this->_is_b2b_allowed($config)) {
				if($_SESSION['billpay_preselect'] == 'b2c') {
					$b2b_checked = '';
					$b2c_checked = 'checked';
				}
				else if(isset($_SESSION['customer_vat_id']) && $_SESSION['customer_vat_id']!='' ||
				$_SESSION['customers_status']['customers_status_id'] == 3 ||
				$_SESSION['billpay_preselect'] == 'b2b'	|| (isset($order->customer['company']) && $order->customer['company']!='')) {
					$b2b_checked = 'checked';
					$b2c_checked = '';
				}
				else {
					$b2b_checked = '';
					$b2c_checked = 'checked';
				}

				$titlePrivate  = MODULE_PAYMENT_BILLPAY_B2B_PRIVATE_CLIENT_TEXT;
				$titleBusiness = MODULE_PAYMENT_BILLPAY_B2B_BUSINESS_CLIENT_TEXT;

				$ext = $this->_buildFeeTitleExtension("BILLPAY");
				if ($ext) {
					$titlePrivate .= ' (' . $ext . ')';
				}

				$ext = $this->_buildFeeTitleExtension("BILLPAYBUSINESS");
				if ($ext) {
					$titleBusiness .= ' (' . $ext . ')';
				}
				
				return '<div style="margin-bottom:10px;margin-top:10px"><input type="radio" name="b2bflag" '.$b2c_checked.' id="billpay_radio_private" value="0" onClick="document.getElementById(\'b2c\').style.display=\'block\';document.getElementById(\'b2b\').style.display=\'none\';" >&nbsp;<label for="billpay_radio_private">' . $titlePrivate . '</label><br />'.
					   '<input type="radio" name="b2bflag" '.$b2b_checked.' id="billpay_radio_business" value="1" onClick="document.getElementById(\'b2b\').style.display=\'block\';document.getElementById(\'b2c\').style.display=\'none\';" >&nbsp;<label for="billpay_radio_business">' . $titleBusiness . '</label></div>';
			}
		}

		function _is_b2c_allowed($config) {
			return true;
		}

		function _is_b2b_allowed($config) {
			return false;
		}

		function _add_gender_input_field($id = 'b2c') {
			$_customerGender = $this->_getCustomerGender();
			if (!empty($_customerGender)) {
				$input_fields .= '<tr><td colspan="2"><input type="hidden" maxlength="10" size="10" name="'.$this->_getDataIdentifier('gender').'" value="'.$_customerGender.'" /></td><td>';
			}
			else {
				if ($id == 'b2c') {
					$input_fields .= '<tr><td>'.MODULE_PAYMENT_BILLPAY_TEXT_GENDER .'</td><td>' . $this->_genSelectGender($id) . '</td></tr>';
				}
				else {
					$input_fields .= '<tr><td>'.MODULE_PAYMENT_BILLPAY_TEXT_SALUTATION .'</td><td>' . $this->_genSelectGender($id) . '</td></tr>';
				}
			}
			return $input_fields;
		}
		
		function _extendSeoLayout($selection, $input) {
			$selection['fields'][] = array('title' => $input);
			return $selection;
		}
		
		function _extendSeoEula($selection, $eulaText, $onClickAction) {
			
			$selection['fields'][] = array('title' => '<input type="checkbox" name="'.$this->_getDataIdentifier('eula').'" '.$onClickAction.'>&nbsp;' . $eulaText);
			return $selection;
		}
		
		function _buildPaymentHtml() {
			global $order;
			
			// use span for one page checkout in order to avoid gui being displayed initially after payment selection
			$holder_element = class_exists("xajax") ? 'span' : 'div'; 
			$holder_element_height = class_exists("xajax") ? 'height:200px;' : '';

			$config = $this->getModuleConfig();
			
			// Add transaction credit-specific input fields
			if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
				$input_fields .= '<div id="b2c" style="display:block">' . $this->_addTcInputFields() . '</div>';
			}

			if ($this->_is_b2b_allowed($config)) {
				$b2bselection = '';

				if(isset($_SESSION['billpay_preselect']) && $_SESSION['billpay_preselect'] == 'b2c') {
					$preselect_b2b = 'none';
					$preselect_b2c = 'block';
				}
				else if(isset($_SESSION['billpay_preselect']) && $_SESSION['billpay_preselect'] == 'b2b' ||
				(isset($_SESSION['customer_vat_id']) && $_SESSION['customer_vat_id']!='') ||
				(isset($order->customer['company']) && $order->customer['company']!='')) {
					$preselect_b2b = 'block';
					$preselect_b2c = 'none';
				}
				else {
					$preselect_b2b = 'none';
					$preselect_b2c = 'block';
				}
				if ($this->b2b_active == 'BOTH' && $this->_is_b2b_allowed($config) && $this->_is_b2c_allowed($config)) {
					$b2bselection = $this->_addB2BSelection();
					$input_fields = '<'.$holder_element.' id="b2b" style="'.$holder_element_height.'display:'.$preselect_b2b.'" >' . $this->_addB2BInputFields() . '</'.$holder_element.'>';
					$input_fields .= '<'.$holder_element.' id="b2c" style="'.$holder_element_height.'display:'.$preselect_b2c.'">' . $this->_addB2CInputFields() . '</'.$holder_element.'>';
				}
				else if(in_array($this->b2b_active, array('B2B', 'BOTH')) && $this->_is_b2b_allowed($config)) {
					$input_fields = '<div id="b2b" style="display:block" >' . $this->_addB2BInputFields();
					$input_fields .= '<input type="hidden" name="b2bflag" value="1" /></div>';
				}
				else if(in_array($this->b2b_active, array('B2C', 'BOTH')) && $this->_is_b2c_allowed($config)) {
					$input_fields .= '<div id="b2c" style="display:block">' . $this->_addB2CInputFields();
					$input_fields .= '<input type="hidden" name="b2bflag" value="0" /></div>';
				}
			}
			else {
				$input_fields .= '<div id="b2c" style="display:block">' . $this->_addB2CInputFields() . '</div>';
			}

			$billpay_js = $this->_displayJsSlider();

			if(isset($_GET['error_message']) && $_SESSION['payment'] == strtolower($this->_paymentIdentifier)) {
				$slide_flag = 'block';
				$onClickAction = '';
			}
			// Check if OneStepCheckout is installed and activated
			else if(strstr($_SERVER['PHP_SELF'], FILENAME_CHECKOUT)) {
				$slide_flag = 'block';
				$onClickAction = '';
			}
			else {
				$slide_flag = 'none';
				$onClickAction = 'onclick="show_billpay_details(\''.$this->_paymentIdentifier.'\');"';
			}

			$billpay_input = "";
			if($this->_mode == 'sandbox') {
				$billpay_input .= '<div style="margin-top:3px; margin-bottom:10px; border-style: solid;border-color:red; text-align:center; background-color:#ffd9b3;"><font color="red"><strong>'. MODULE_PAYMENT_BILLPAY_TEXT_SANDBOX .'</font> <br /> <a href="'. $this->_merchant_info .'" target="_blank">'.MODULE_PAYMENT_BILLPAY_UNLOCK_INFO.'</a></strong> </div>';
			}
			else if($this->_mode == 'check') {
				$billpay_input .= '<div style="margin-top:3px; margin-bottom:10px; border-style: solid;border-color:red; text-align:center; background-color:#ffd9b3;"><font color="red"><strong>'. MODULE_PAYMENT_BILLPAY_TEXT_CHECK .'</font> <br /> <a href="'. $this->_merchant_info .'" target="_blank">'.MODULE_PAYMENT_BILLPAY_UNLOCK_INFO.'</a></strong> </div>';
			}

			$billpay_input .= $b2bselection . '<div id="' . $this->_paymentIdentifier . '" style="display: ' . $slide_flag . '">';
			$billpay_input .= $input_fields;

			$title_ext = $this->_checkBuildFeeTitleExtension($this->_paymentIdentifier);

			$selection = array('id' => $this->code,
						        'module' => $this->title . ($title_ext ? (' ' . $title_ext): ''));
			
			if(isset($GLOBALS['ot_payment']) && method_exists($GLOBALS['ot_payment'], 'get_percent')) {
				$selection['module_cost'] = $GLOBALS['ot_payment']->get_percent(strtolower($this->_paymentIdentifier));
			}

			if(isset($fee) && $fee > 0) {
				$billpay_input .= '<br /><br />'.constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_FEE_INFO1') . $billpay_fee->display_formated() .
				constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_FEE_INFO2');
			}
			$billpay_input .= $this->_displayBankData();
			$billpay_input .= '</div>'; //span
			//$selection['fields'][] = array('title' => $billpay_input.$billpay_js);
			$selection = $this->_extendSeoLayout($selection, $billpay_input.$billpay_js);
			
			$billingCountry = $order->billing['country']['iso_code_3'];

			$eulaText = constant('MODULE_PAYMENT_' . $this->_paymentIdentifier . '_TEXT_EULA_CHECK');
			if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
				$eulaText = sprintf($eulaText, $this->_buildTcTermsUrl(),$this->_buildTcPrivacyUrl());
			}
			else {
				$eulaText = sprintf($this->getTermsOfServiceText(), $this->_buildTermsOfServiceUrl());
			}
			
			if (!isset($_SESSION['bp_fraud_tags_rendered'])) {
				$hash = $this->getEncryptedSessionId();
				$eulaText .= '<p style="margin: 0px; background:url(https://cdntm.billpay.de/fp/clear.png?org_id=ulk99l7b&session_id='.$hash.'&m=1)"></p><img src="https://cdntm.billpay.de/fp/clear.png?org_id=ulk99l7b&session_id='.$hash.'&m=2" alt="" ><script src="https://cdntm.billpay.de/fp/check.js?org_id=ulk99l7b&session_id='.$hash.'" type="text/javascript"></script><object type="application/x-shockwave-flash" data="https://cdntm.billpay.de/fp/fp.swf?org_id=ulk99l7b&session_id='.$hash.'" width="1" height="1" id="obj_id"><param name="movie" value="https://cdntm.billpay.de/fp/fp.swf?org_id=ulk99l7b&session_id='.$hash.'" /><div></div></object>';
				$_SESSION['bp_fraud_tags_rendered'] = true;
			}

			// Attach html for Billpay logo
			if ($this->_paymentIdentifier != 'BILLPAYTRANSACTIONCREDIT') {
				$eulaText .= MODULE_PAYMENT_BILLPAY_TEXT_INFO;
			}

			$selection = $this->_extendSeoEula($selection, $eulaText, $onClickAction);
			return $selection;
		}

		function _checkBuildFeeTitleExtension($paymentIdentifier) {
			return $this->_buildFeeTitleExtension($paymentIdentifier);
		}

		function _buildFeeTitleExtension($paymentIdentifier) {
			
			if(class_exists('ot_'.$this->_getDataIdentifier('fee'))
			&& defined('MODULE_ORDER_TOTAL_'.$paymentIdentifier.'_FEE_STATUS')
			&& constant('MODULE_ORDER_TOTAL_'.$paymentIdentifier.'_FEE_STATUS') == 'true') {
				//$class_name = 'ot_'.$this->_getDataIdentifier('fee');
				$class_name = 'ot_'.strtolower($paymentIdentifier).'_fee';
				$billpay_fee = new $class_name;
				$fee = $billpay_fee->display();
				if(isset($fee) && $fee > 0) {
					return MODULE_PAYMENT_BILLPAY_TEXT_ADD. $billpay_fee->display_formated();
				}
			}
			return false;
		}

		/**
		 * add customers bank data to the preautho request. only for direct debit
		 */
		function _addBankData($req, $vars) {
			return $req;
		}

		/**
		 * add tc-specific details to preauth request
		 */
		
		function _addPreauthTcDetails($req, $numberRates, $ratePlanTotal) {
			return $req;
		}
		/**
		 * build payment terms url
		 */
		function _buildTermsOfServiceUrl() {
	    	global $order;
	    	
			$country = strtolower($order->billing['country']['iso_code_2']);
			$termsUrl = 'https://www.billpay.de/kunden/agb';
			if ($country != 'de') 
				$termsUrl .= '-' . $country;
			return $termsUrl . '#datenschutz';
        }
        
		/**
		 * build tc specific terms url
		 */		
		function _buildTcTermsUrl() {
			global $order;
			
			
			
			$fileName = md5(substr((string)$this->bp_secure, 0, 10)) . '.html';
			$termsUrl = '';
			
			$country = $this->_getCountry('3');
			
			if ($this->testmode == 'Testmodus') {
					$termsUrl = 'https://www.billpay.de/s/agb-beta/';
				}
				else {
					$termsUrl = 'https://www.billpay.de/s/agb/';
				}	
						
			if($country != 'deu') {
				$termsUrl.= $country.'/'.$fileName;
			} else {
				$termsUrl.= $fileName;
			}
			return $termsUrl;
		}
		
		/**
		 * build tc specific privacy url
		 */		
		function _buildTcPrivacyUrl() {
			global $order;
			$privacyUrl = '';
			
			$country = $this->_getCountry('2');

			$privacyUrl = 'https://www.billpay.de/'.$country.'/api-'.$country.'/ratenkauf-'.$country.'/datenschutz/';

			return $privacyUrl;
		}
		
		/**
		 * build tc specific conditions url
		 */		
		function _buildTcConditionsUrl(){
			global $order;
			$conditionsUrl = '';
			
			$country = $this->_getCountry('2');
	
			$conditionsUrl = 'https://www.billpay.de/'.$country.'/api-'.$country.'/ratenkauf-'.$country.'/zahlungsbedingungen/';
			
			return $conditionsUrl;
		}

		/**
		 * display input fields for customers bank data. only for direct debit
		 */
		function _displayBankData() {}

		function check() {
			if (!isset($this->_check)) {
				$check_query = xtc_db_query('SELECT configuration_value FROM ' . TABLE_CONFIGURATION . ' WHERE configuration_key =' . "'MODULE_PAYMENT_".$this->_paymentIdentifier."_STATUS'");
				$this->_check = xtc_db_num_rows($check_query);
			}
			return $this->_check;
		}

		/* set customer details */
		function _set_customer_details($req, $group='p') {
			global $order;
            
			//added get customer phone for tc
			if(empty($order->customer['telephone'])) {
                        $order->customer['telephone'] = $_POST[strtolower($this->_paymentIdentifier) . "_phone"];
			}        
			$req->set_customer_details(
			$this->_encodeValue($this->_getCustomerId()),
			$this->_encodeValue($this->_getCustomerGroup()),
			$this->_encodeValue($this->_getCustomerSalutation($this->_formGender)),
				'', // title
			$this->_encodeValue($order->billing['firstname']),
			$this->_encodeValue($order->billing['lastname']),
			$this->_encodeValue($order->billing['street_address'] . (isset($order->billing['suburb']) ? ' '.$order->billing['suburb'] : '')),
				'', // streetno
				'', // address extra
			$this->_encodeValue($order->billing['postcode']),
			$this->_encodeValue($order->billing['city']),
			$this->_encodeValue($order->billing['country']['iso_code_3']),
			$this->_encodeValue($order->customer['email_address']),
			$this->_encodeValue($order->customer['telephone']),
				'', // cellphone
			$this->_encodeValue($this->_formDob),
			$this->_encodeValue($this->_getLanguage()),
			$this->_encodeValue($this->_getCustomerIp()),
			$this->_encodeValue($group)
			);
				
			return $req;
		}

		function _set_shipping_details($req) {
			global $order;

			$req->set_shipping_details(FALSE,
			$this->_encodeValue($this->_getCustomerSalutation($this->_getDataIdentifier('gender', $_POST))),
				'', // title
			$this->_encodeValue($order->delivery['firstname']),
			$this->_encodeValue($order->delivery['lastname']),
			$this->_encodeValue($order->delivery['street_address'] . (isset($order->delivery['suburb']) ? ' '.$order->delivery['suburb'] : '')),
				'', // streetno
				'', // address extra
			$this->_encodeValue($order->delivery['postcode']),
			$this->_encodeValue($order->delivery['city']),
			$this->_encodeValue($order->delivery['country']['iso_code_3']),
			$this->_encodeValue($order->customer['telephone']),
				'' // cellphone
			);
			return $req;
		}

		function _add_articles($req) {
			global $order;

			foreach ($order->products as $product) {
				$req->add_article(
				$this->_encodeValue($product['id']),
				$this->_encodeValue($product['qty']),
				$this->_encodeValue($product['name']),
					'',
				$this->_encodeValue($this->_getPrice($product['price'], $product['tax'], $_SESSION['customers_status']['customers_status_show_price_tax'])),
				$this->_encodeValue($this->_currencyToSmallerUnit($product['price']))
				);
			}

			return $req;
		}

		function _add_order_totals($req, $order_total_modules, $order) {
			$billpayTotals = $this->_calculate_billpay_totals($order_total_modules, $order, false);

			$req->set_total(
			$this->_encodeValue($this->_currencyToSmallerUnit($billpayTotals['billpayRebateNet'])),	// rebate
			$this->_encodeValue($this->_currencyToSmallerUnit($billpayTotals['billpayRebateGross'])),	// rebategross
			$this->_encodeValue(isset($order->info['shipping_method']) ? $order->info['shipping_method'] : 'n/a'),
			$this->_encodeValue($this->_currencyToSmallerUnit($billpayTotals['billpayShippingNet'])),
			$this->_encodeValue($this->_currencyToSmallerUnit($billpayTotals['billpayShippingGross'])),
			$this->_encodeValue($this->_currencyToSmallerUnit($billpayTotals['orderTotalNet'])),
			$this->_encodeValue($this->_currencyToSmallerUnit($billpayTotals['orderTotalGross'])),
			$this->_encodeValue($this->_getCurrency()), // currency
			$this->_encodeValue(''));	// reference

			return array('totals' => $billpayTotals, 'req' => $req);
		}

		function _calculate_billpay_totals($order_total_modules, $order, $isNetShippingPrice) {
			// Calculate and add totals
			$order_totals = $order_total_modules->modules;
				
			$orderTotalGross = 0;
			$orderTotalNet = 0;
			$orderSubTotalGross = 0;
			$orderTax = 0;
			$billpayShippingNet = 0;
			$billpayShippingGross = 0;
			$billpayRebateNet = 0;
			$billpayRebateGross = 0;
				
			if (is_array($order_totals)) {
				reset($order_totals);
				
				while(list(, $value) = each($order_totals)) {
					$classname = substr($value, 0, strrpos($value, '.'));
				
					if (!class_exists($classname) || ! $GLOBALS[$classname]->enabled) {
						continue;
					}

					for($i = 0; $i < sizeof($GLOBALS[$classname]->output); $i ++) {
						// Handling shipping module differently
						if ($classname == 'ot_shipping') {
							$totalValue = $GLOBALS[$classname]->output [$i]['value'];
							$shippingId = $_SESSION['shipping']['id'];
							$parts = explode('_', $shippingId);
							$shippingCode = strtoupper($parts[0]);
								
							if (defined('MODULE_SHIPPING_'.$shippingCode.'_TAX_CLASS')) {
								$taxClass = constant('MODULE_SHIPPING_'.$shippingCode.'_TAX_CLASS');
								$taxRate = xtc_get_tax_rate($taxClass, $order->delivery['country']['id'], $order->delivery['zone_id']);
								$totalNetAmount = 0;
								$totalGrossAmount = 0;
								if($taxRate > 0) {
									if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
										&& $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) { /* Tax not calculated for customer group */
										$totalNetAmount		= $totalValue;
										$totalGrossValue 	= $totalValue;
									}
									else if ($isNetShippingPrice) { /* Shipping prices are excl. tax */
										$taxAmount = round(($totalValue / 100 * $taxRate), 2);
										$totalNetAmount		= $totalValue;
										
										// We want to be consistent with the shop and send net shipping amount
										if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
											&& $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
												$totalGrossValue 	= $totalValue;
										}
										else {
											$totalGrossValue 	= $totalValue + $taxAmount;
										}

										// Increase order total gross amount by tax amount
										$orderTotalGross += $taxAmount;
									}
									else if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
										&& $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
											$taxAmount = round(($totalValue / 100 * $taxRate), 2);
											
											$totalNetAmount		= $totalValue;
											$totalGrossValue 	= $totalValue;
											
											// Subtract shipping tax from rebate because we send net shipping amount
											$billpayRebateGross -= $taxAmount;
									}
									else {	/* Shipping prices are incl. tax */
										$taxAmount = round($totalValue / (100 + $taxRate) * $taxRate, 2);

										$totalNetAmount		= $totalValue - $taxAmount;
										$totalGrossValue 	= $totalValue;
									}			
								}
								else {
									$totalNetAmount 	= $totalValue;
									$totalGrossValue 	= $totalValue;
								}
								$billpayShippingNet 	+= $totalNetAmount;
								$billpayShippingGross	+= $totalGrossValue;
							}
						}
						else {
							$totalGrossValue = $GLOBALS[$classname]->output [$i]['value'];
							$codename = strtoupper(str_replace('ot_', '', $classname));
								
							unset($status);
							if(defined('MODULE_ORDER_TOTAL_' . $codename . '_STATUS')) {
								$status = constant('MODULE_ORDER_TOTAL_' . $codename . '_STATUS');
							}

							if($status == 'true') {
								if (in_array($classname, $this->billpayShippingModules)) {
									if(defined('MODULE_ORDER_TOTAL_' . $codename . '_TAX_CLASS') && $this->currentCustomerGroupUsesTax()) {
										$tax_class = constant('MODULE_ORDER_TOTAL_' . $codename . '_TAX_CLASS');
										$tax_rate = xtc_get_tax_rate($tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);

										if($tax_rate > 0) {
											$tax_amount = round($totalGrossValue / (100 + $tax_rate) * $tax_rate, 2);
										}
									}
										
									$billpayShippingNet += ($totalGrossValue - $tax_amount);
									$billpayShippingGross += $totalGrossValue;
								}
								else {
									switch ($classname) {
										case 'ot_total':
											$orderTotalGross += $totalGrossValue;
											break;
										case 'ot_subtotal':
											if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0
												&& $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
													$orderSubTotalGross += $totalGrossValue;
											}
											else {
												$orderSubTotalGross = $_SESSION['cart']->total;
											}
											break;
										case 'ot_tax':
											$orderTax += $totalGrossValue;
											break;
									}
								}
							}
						}
					}
				}
			}

			$billpayRebateGross = -($orderTotalGross - $orderSubTotalGross - $billpayShippingGross);
			$billpayRebateNet = $billpayRebateGross;
			$orderTotalNet = $orderTotalGross - $orderTax;
				
			return array(
				'billpayRebateNet' => $billpayRebateNet,
				'billpayRebateGross' => $billpayRebateGross,
				'billpayShippingNet' => $billpayShippingNet,
				'billpayShippingGross' => $billpayShippingGross,
				'orderTotalNet' => $orderTotalNet,
				'orderTotalGross' => $orderTotalGross  
			);
		}

		function currentCustomerGroupUsesTax() {
			return $_SESSION['customers_status']['customers_status_show_price_tax'] == 1
				|| $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1;
		}

		function _add_order_history($req) {
			$_OrderHistory = $this->_getOrderHistory($this->_getCustomerId());

			if (!empty($_OrderHistory)) {
				foreach ($_OrderHistory as $historyPart) {
					$history_amount = 0;
					if(isset($historyPart['hamount']) && $historyPart['hamount'] >= 0) {
						$history_amount = $historyPart['hamount'];
					}

					$req->add_order_history($historyPart['hid'],
					$historyPart['hdate'],
					$history_amount,
					isset($historyPart['hcurrency']) ? $historyPart['hcurrency'] : 'EUR' ,
					$historyPart['hpaymenttype'],
					$historyPart['hstatus']   // todo!
					);
				}
			}

			return $req;
		}

		function _encodeValue($value) {
			$trimmedValue = trim($value);
			if(defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE') &&
			constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE') == 'True') {
				return utf8_encode($trimmedValue);
			}
			else {
				return $trimmedValue;
			}
		}

		function _getDataValue($key, $data = null) {
			if (is_null($data)) {
				$data =& $_SESSION;
			}
				
			$prefixedKey = $this->_getDataIdentifier($key);
			if (array_key_exists($prefixedKey, $data)) {
				return $data[$prefixedKey];
			}
				
			if (array_key_exists($key, $data)) {
				return $data[$key];
			}
			return null;
		}

		function _setDataValue($key, $value, $data = null) {
			if (is_null($data)) {
				$data =& $_SESSION;
			}
			$dataIdentifier = $this->_getDataIdentifier($key);
			$data[$dataIdentifier] = $value;
		}

		function _getDataIdentifier($key = '', $upper = false) {
			$dataIdentifier = '';
			if ($key == '') {
				$dataIdentifier = $this->_paymentIdentifier;
			}
			else {
				$dataIdentifier = $this->_paymentIdentifier.'_'.$key;
			}
				
			return $upper ? strtoupper($dataIdentifier) : strtolower($dataIdentifier);
		}

		function sendPartialCancel($oID, $articles, $totals, $currency) {
			require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
			require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_partialcancel_request.php');
			$language = $_SESSION['language'];
			if (file_exists(DIR_FS_LANGUAGES . $language . '/modules/payment/' . $this->_getDataIdentifier() . '.php')) {
				require_once DIR_FS_LANGUAGES . $language . '/modules/payment/' . $this->_getDataIdentifier() . '.php';
			} else {
				require_once DIR_FS_LANGUAGES . 'german/modules/payment/' . $this->_getDataIdentifier() . '.php';
			}

			$req = new ipl_partialcancel_request($this->api_url);
			$req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

			$rebatedecrease = $this->_currencyToSmallerUnit($rebatedecrease);
			$rebatedecreasegross = $this->_currencyToSmallerUnit($rebatedecreasegross);
			$shippingdecrease = $this->_currencyToSmallerUnit($shippingdecrease);
			$shippingdecreasegross = $this->_currencyToSmallerUnit($shippingdecreasegross);

			$req->set_cancel_params($oID,
			$totals['rebatedecrease'],
			$totals['rebatedecreasegross'],
			$totals['shippingdecrease'],
			$totals['shippingdecreasegross'],
			$currency
			);

			foreach ($articles as $id => $quantity) {
				$req->add_canceled_article($id, $quantity);
			}

			$internalError = $req->send();

			// log xml
			$_xmlreq 	= (string)utf8_decode($req->get_request_xml());
			$_xmlresp 	= (string)utf8_decode($req->get_response_xml());
			$this->_logError($_xmlreq, 'XML request (partialcancel)');
			$this->_logError($_xmlresp, 'XML response (partialcancel)');

			$success 	= false;
			$status		= 0;
			if ($internalError) {
				$infoText = $internalError['error_message'];
				$this->_logError('Internal error (partialcancel)', $infoText);
			}
			else if ($req->has_error()) {
				$infoText = utf8_decode($req->get_merchant_error_message());
			}
			else {
				/* update rate details after partial cancel request for transaction credit */
				if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
					$dueUpdate = $req->get_due_update();
					// TODO: calculate tax
					$feeTaxAmount = 0.0;

					xtc_db_query("UPDATE billpay_bankdata SET " .
						"  rate_surcharge = " . $dueUpdate['calculation']['surcharge']/100 . 
						", rate_total_amount = " . $dueUpdate['calculation']['total']/100 . 
						", rate_dues = '" . $this->serializeDueDateArray($dueUpdate['dues']) . "'" .
						", rate_interest_rate = " . $dueUpdate['calculation']['interest']/100 . 
						", rate_anual_rate = " . $dueUpdate['calculation']['anual']/100 . 
						", rate_base_amount = " . $dueUpdate['calculation']['base']/100 . 
						", rate_fee = " . $dueUpdate['calculation']['fee']/100 . 
						", rate_fee_tax = " . $feeTaxAmount . 
						" WHERE orders_id='".$oID."'");

					global $xtPrice;
					require_once DIR_FS_LANGUAGES . $language . '/modules/payment/'.$this->_getDataIdentifier().'.php';
					xtc_db_query("UPDATE " . TABLE_ORDERS_TOTAL
					. " SET text = '<strong>"
					. $xtPrice->xtcFormat($dueUpdate['calculation']['surcharge']/100, true)
					. "</strong>'"
					. " WHERE orders_id = $oID"
					. " AND title = '<strong>" . MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE . ":</strong>'"
					);
					xtc_db_query("UPDATE " . TABLE_ORDERS_TOTAL
					. " SET text = '<strong>"
					. $xtPrice->xtcFormat($dueUpdate['calculation']['fee']/100, true)
					. "</strong>'"
					. " WHERE orders_id = $oID"
					. " AND title = '<strong>" . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE . ":</strong>'"
					);
					xtc_db_query("UPDATE " . TABLE_ORDERS_TOTAL
					. " SET text = '<strong>"
					. $xtPrice->xtcFormat($dueUpdate['calculation']['total']/100, true)
					. "</strong>'"
					. " WHERE orders_id = $oID"
					. " AND title = '<strong>" . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TOTAL . ":</strong>'"
					);
				}

				$infoText 	= MODULE_PAYMENT_BILLPAY_HISTORY_INFO_PARTIAL_CANCEL;
				$status		= null;
				$success 	= true;
			}

			$this->addHistoryEntry($oID, $infoText, $status);
			
			if ($internalError || $req->has_error()) {
				$this->addHistoryEntry($oID, MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_ERROR_CUSTOMER_CARE, $status);
			}
			
			return $success;
		}

		function _addressCompare($req) {
			global $order;

			// shipping_details
			$addressCompare = (int)count(array_intersect_assoc($order->billing, $order->delivery));
			// actually this feature is inactive on the other side (2010/03/30)
			// billing and delivery are both have 12 array entries
			if ($addressCompare < 12) {
				// if addresses don't match set shipping address
				$this->_set_shipping_details($req);
			}
			else {
				$req->set_shipping_details(TRUE);
			}

			return $req;
		}

		function _getPaymentType() {}

		function _preauthorize() {
			global $order, $insert_id;
			
			/** ajax one page checkout  */
			if ($_SESSION['billpay_is_ajax'] == true && isset($_SESSION['billpay_data_arr'])) {
				$data_arr = $_SESSION['billpay_data_arr'];
				$is_ajax = true;
				
				if(isset($_SESSION['billpay_dob']))
					$this->_formDob = $_SESSION['billpay_dob'];
				else			
					$this->_formDob = $this->_checkPaymentSelection($data_arr);
			}
			else {
				$data_arr = $_POST;
				$this->_formDob = $this->_checkPaymentSelection($data_arr);
			}

			if(isset($data_arr['b2bflag']) && $data_arr['b2bflag'] == 1) {
				$add_flag = '_b2b';
			}
			else {
				$add_flag = '_b2c';
			}

			/** onepage checkout specific change */
			if(isset($_SESSION['billpay_gender'])) {
				$this->_formGender = $_SESSION['billpay_gender'];
			}
			else {
				$this->_formGender = $this->_getDataIdentifier('gender'.$add_flag, $data_arr);
			}			
			if (!$this->_formGender) {
				$this->_formGender = $this->_getDataIdentifier('gender', $data_arr);
			}
			$_SESSION['billpay_gender'] = $this->_formGender;
			/** EOF onepage checkout specific change */
			
			$err_redir = false;
			$active = TRUE;
			if ($active === TRUE) {
				if (!isset($_SESSION['billpay_preauth_req']) ) {
					$this->_logError('Preauthorization object not found in session');
					xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(utf8_decode(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT)), 'SSL'));
				}
				else {
					require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_api.php');
					require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_preauthorize_request.php');

					$req = unserialize($_SESSION['billpay_preauth_req']);

					$success = TRUE;
					$hidePayment = FALSE;
					$redirMessage = '';
					$logMessage = '';

					$internalError = $req->send();

					if ($internalError) {
						$this->_logError('internal error preauth req', $internalError['error_message']);
						$success = FALSE;
						$redirMessage = MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT;
						$logMessage = MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT;
					}
					$xmlreq = (string)utf8_decode($req->get_request_xml());
					$xmlresp =	(string)utf8_decode($req->get_response_xml());
					if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
						if (strpos($xmlresp, '<email_attachment>')) {
							$debug = substr($xmlresp, 0, strpos($xmlresp, '<email_attachment>'))
							. '<email_attachment>[DEBUG SKIPPED]</email_attachment><standard_information>[DEBUG SKIPPED]';
							$debug .= substr($xmlresp, strpos($xmlresp, '</standard_information>'));
							$xmlresp = $debug;
						}
					}

					$this->_logError($xmlreq, 'XML request Vorauthorisierung');
					$this->_logError($xmlresp, 'XML response Vorauthorisierung');

					if ($success == TRUE) {
						if ($req->get_status() == 'DENIED') {
							$hidePayment = TRUE;
						}

						if (!$req->has_error()) {
							$this->_setTransactionId(utf8_decode((string)$req->get_bptid()));

							xtc_db_query('INSERT INTO billpay_bankdata (tx_id, account_holder, account_number, bank_code, bank_name, invoice_reference, api_reference_id) VALUES '.
									'("'.$this->_getTransactionId().'", '.
									'"'.$req->get_account_holder().'", '.
									'"'.$req->get_account_number().'", '.
									'"'.$req->get_bank_code().'", '.
									'"'.$req->get_bank_name().'", '.
									'"'.$req->get_invoice_reference().'", '.
									'"'.$this->_getTransactionId().'")');
							if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
								$numberRates = $_SESSION['bp_rate_result']['numberRates'];
								$ratePlan = $_SESSION['bp_rate_result']['rateplan'][$numberRates];
								xtc_db_query('UPDATE billpay_bankdata SET'
								. ' rate_surcharge = ' . (float)$ratePlan['calculation']['surcharge'] / 100
								. ', rate_total_amount =' . (float)$ratePlan['calculation']['total'] / 100
								. ', rate_count = ' . $numberRates
								. ', rate_dues = "' . $this->serializeDueDateArray($ratePlan['dues']) . '"'
								. ', rate_interest_rate = ' . (float)$ratePlan['calculation']['interest'] / 100
								. ', rate_anual_rate = ' . (float)$ratePlan['calculation']['anual'] / 100
								. ', rate_base_amount = ' . (float)$ratePlan['calculation']['base'] / 100
								. ', rate_fee = ' . (float)$ratePlan['calculation']['fee'] / 100
								. ', rate_fee_tax = ' . 0.19 * (float)$ratePlan['calculation']['fee'] / 100
								. ' WHERE tx_id = "' . $this->_getTransactionId() . '"'
								);
							}

							unset($_SESSION['billpay_data_arr']);
							unset($_SESSION['billpay_fee_cost']);
							unset($_SESSION['billpay_fee_tax']);
							unset($_SESSION['billpay_preauth_req']);
						}
						else {
							$success = FALSE;
							if ($this->testmode == MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_TEST) {
								$redirMessage = "HAENDLER: " . $req->get_merchant_error_message() . "KUNDE: " . $req->get_customer_error_message() . "ERROR CODE: " . $req->get_error_code();
							}
							else {
								$redirMessage = $req->get_customer_error_message();
							}

							$logMessage = $this->_errorMessage($req->get_error_code(), $req->get_merchant_error_message(), $req->get_customer_error_message());
						}
					}
					else {
						$hidePayment = TRUE;
					}

					if ($hidePayment == TRUE) {
						$_SESSION['billpay_hide_payment_method'] = TRUE;
					}

					if ($success == FALSE) 	{
						$this->_logError($logMessage, 'QUERY ERROR Vorauthorisierung');
						$this->_displayGMerror($redirMessage);

						if(defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE') &&
						constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE') == 'True') {
							$redirMessage = utf8_decode($redirMessage);
						}
						if ($is_ajax) {
							xtc_redirect(xtc_href_link('checkout.php', 'error_message='.urlencode($redirMessage), 'SSL'));
						}
						else {
							xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode($redirMessage), 'SSL'));
						}
					}
				}
			}
		}

		function _error_redirect($err_msg) {
			$this->_displayGMerror($err_msg);

			/** ajax one page checkout  */
			if ($_SESSION['billpay_is_ajax'] == true && isset($_SESSION['billpay_data_arr'])) {
				$is_ajax = true;
			}
			
			if(defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE') &&
			constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE') == 'True') {
				$err_msg = utf8_decode($err_msg);
			}
			if ($is_ajax) {
				$_SESSION['checkout_payment_error'] = 'payment_error=' . $this->code . '&error=' . $err_msg;
			}
			else {
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode($err_msg), 'SSL'));
			}
		}

		function _validateGeneralValues($data_arr) {
			$eulaValue = $this->_getDataValue('eula', $data_arr);
			if(!isset($eulaValue)) {
				$err_redir = true;
				$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_EULA);
			}

			if($err_redir == true) {
				$this->_error_redirect($err_msg);
			}

			return true;
		}

		function _validateB2BValues($data_arr) {
			$companyName = $this->_getDataValue('company_name', $data_arr);
			if (!$companyName) {
				$companyName = $order->customer['company'];
			}
				
			$taxNumber =  $this->_getDataValue('tax_number', $data_arr);
			if (!$taxNumber) {
				$taxNumber = $_SESSION['customer_vat_id'];
			}
				
			$legalForm 		= $this->_getDataValue('legal_form', $data_arr);
			$registerNumber = $this->_getDataValue('register_number', $data_arr);
			$holderName		= $this->_getDataValue('holder_name', $data_arr);
			$genderB2B		= $this->_getDataValue('gender_b2b', $data_arr);

			$this->_setDataValue('company_name', $companyName);
			$this->_setDataValue('tax_number', $taxNumber);
			$this->_setDataValue('legal_form', $legalForm);
			$this->_setDataValue('register_number', $registerNumber);
			$this->_setDataValue('holder_name', $holderName);
			$this->_setDataValue('gender_b2b', $genderB2B);
				
			if(!isset($companyName) || $companyName == '') {
				$err_redir = true;
				$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY);
			}
			else if(!isset($legalForm) || $legalForm == '') {
				$err_redir = true;
				$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY);
			}

			$customerGender = $this->_getCustomerGender();
			if(!$customerGender) {
				if(!$this->_formGender || $this->_formGender == '') {
					$err_redir = true;
					$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_TITLE);
				}
			}

			if($err_redir == true) {
				$this->_error_redirect($err_msg);
			}
			return true;
		}

		function _validateB2CValues($data_arr) {
			$dobDay 	= $this->_getDataValue('dob_day', $data_arr);
			$dobMonth 	= $this->_getDataValue('dob_month', $data_arr);
			$dobYear 	= $this->_getDataValue('dob_year', $data_arr);
			$gender 	= $this->_getDataValue('gender_b2c', $data_arr);
				
			$this->_setDataValue('dob_day', $dobDay);
			$this->_setDataValue('dob_month', $dobMonth);
			$this->_setDataValue('dob_year', $dobYear);
			$this->_setDataValue('gender_b2c', $gender);
				
			if(isset($data_arr['payment']) && $data_arr['payment'] == strtolower($this->_paymentIdentifier)) {
				$this->_checkBankValues($data_arr);
			}

			$_customerDob = $this->_getCustomerDob();

			if(!$_customerDob) {
				if(!isset($dobDay) ||$dobDay=="00" || $dobDay=="" || $dobDay=="0") {
					$err_redir = true;
					$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE);
				}
				else if(!isset($dobMonth) || $dobMonth=="00" || $dobMonth=="" || $dobMonth=="0") {
					$err_redir = true;
					$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE);
				}
				else if(!isset($dobYear) || $dobYear=="00" || $dobYear=="" || $dobYear=="0") {
					$err_redir = true;
					$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE);
				}
			}

			$customerGender = $this->_getCustomerGender();
			if(!$customerGender) {
				if(!$this->_formGender || $this->_formGender == '') {
					$err_redir = true;
					$err_msg = urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER);
				}
			}

			if($err_redir == true) {
				$this->_error_redirect($err_msg);
			}
			return true;
		}

		function pre_confirmation_check($vars = '') {
			global $order;
				
			// DO INPUT VALIDATION

			unset($_SESSION['billpay_preselect']);

			if(isset($vars) && $vars!=null) {
				$_SESSION['billpay_data_arr'] = $vars;
			}
				
			// ajax one page checkout
			if (is_array($_SESSION['billpay_data_arr']) && !empty($_SESSION['billpay_data_arr'])) {
				$data_arr = $_SESSION['billpay_data_arr'];				
				$_SESSION['billpay_is_ajax'] = true;
			}
			else {
				$data_arr = $_POST;
			}
			
			$this->_formDob = $this->_checkPaymentSelection($data_arr);
			$_SESSION['billpay_dob'] = $this->_formDob;
			
			if ($this->_paymentIdentifier == 'BILLPAY') {
				if(isset($data_arr['b2bflag']) && $data_arr['b2bflag'] == 1) {
					$this->_formGender = $this->_getDataValue('gender_b2b', $data_arr); 
					$_SESSION['billpay_preselect'] = 'b2b';
					$this->_validateB2BValues($data_arr);
				}
				else {
					$this->_formGender = $this->_getDataValue('gender_b2c', $data_arr);
					$_SESSION['billpay_preselect'] = 'b2c';
					$this->_validateB2CValues($data_arr);
				}
			}
			else {
				$this->_formGender =  $this->_getDataValue('gender_b2c', $data_arr);
				$this->_validateB2CValues($data_arr);
			}
			$_SESSION['billpay_gender'] = $this->_formGender;
			$this->_validateGeneralValues($data_arr);
		}

		function confirmation() {
			return false;
		}

		function process_button() {
			global $order, $order_total_modules;
			
			if(isset($_SESSION['billpay_dob']))
				$this->_formDob = $_SESSION['billpay_dob'];

			if(isset($_SESSION['billpay_gender']))
				$this->_formGender = $_SESSION['billpay_gender'];		
				
			// ajax one page checkout
			// bp_vars is only set in one page checkout

			if (is_array($_SESSION['billpay_data_arr']) && !empty($_SESSION['billpay_data_arr'])) {
				$data_arr = $_SESSION['billpay_data_arr'];
				$_SESSION['billpay_is_ajax'] = true;
			}
			else {
				$data_arr = $_POST;
			}
			
			// include preauthorize lib
			require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_api.php');
			require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_preauthorize_request.php');

			$billpay_paymenttype = $this->_getPaymentType();

			$req = new ipl_preauthorize_request($this->api_url, $billpay_paymenttype);
			$req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);

			$group = 'p';
			$config = $this->getModuleConfig();

			// TODO: adapt to one page checkout
			//$data_arr = $_POST;
				
			if(isset($data_arr['b2bflag']) && $data_arr['b2bflag'] == 1 && $this->_is_b2b_allowed($config)) {
				$group = 'b';

				$req->set_company_details(
				$this->_encodeValue($data_arr['billpay_company_name']),
				$this->_encodeValue($data_arr['billpay_legal_form']),
				$this->_encodeValue($data_arr['billpay_register_number']),
				$this->_encodeValue($data_arr['billpay_holder_name']),
				$this->_encodeValue($data_arr['billpay_tax_number'])
				);
			}

			$req = $this->_set_customer_details($req, $group);
			$req = $this->_add_articles($req);
			$req = $this->_addressCompare($req);

			/* collect order totals */
			$totalResult 		= $this->_add_order_totals($req, $order_total_modules, $order);
			$orderTotalGross 	= $totalResult['totals']['orderTotalGross'];
			$shippingGross		= $totalResult['totals']['billpayShippingGross'];
			$rebateGross		= $totalResult['totals']['billpayRebateGross'];
			$req 				= $totalResult['req'];
				
			/* add bank data for direct debit */
			$req = $this->_addBankData($req, $data_arr);
			
			/* set fraud detection parameters */
			$req->set_fraud_detection($this->getEncryptedSessionId());

			$req->set_terms_accepted(true);
			$req->set_capture_request_necessary(false);

			// fetch the order history for customers only (not guests)
			if ($this->_getCustomerGroup() != 'g') {
				// fetch & add history
				$req = $this->_add_order_history($req);
			}

			if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
				// If no rateplan is present, pre_confirmation_check() will have thrown an error already
				// and we won't ever get here
				// Are current base amount and total amount the same as in rateplan?
				$currentBase = $this->_currencyToSmallerUnit($orderTotalGross - $shippingGross);
				$currentTotal = $this->_currencyToSmallerUnit($orderTotalGross);
				$ratePlanBase = $_SESSION['bp_rate_result']['base'];
				$ratePlanTotal = $_SESSION['bp_rate_result']['total'];
				// -> Wenn nicht, Fehler und Ratenplan invalidieren, ansonsten Werte in Preauth packen
				//if ($currentBase !=  $ratePlanBase || $currentTotal != $ratePlanTotal) {
				//	$this->_logError("end - base: " . ($order->info['total'] - $order->info['billpayShippingGross']) . ", total: " . $order->info['total'] . ", billpay rebate gross: " . $rebateGross);
				//	$this->_logError("Basket values changed after rateplan calculation. Current base: $currentBase, rate plan base: $ratePlanBase, current total: $currentTotal, rate plan total: $ratePlanTotal");
				//} else {
					$req = $this->_addPreauthTcDetails($req, $_SESSION['bp_rate_result']['numberRates'],
					$_SESSION['bp_rate_result']['rateplan'][$_SESSION['bp_rate_result']['numberRates']]['calculation']['total']);
				//}
			}
			$_SESSION['billpay_preauth_req'] = serialize($req);
			
			// Set total amount to be compared in after_process
			$_SESSION['billpay_total_amount'] = $this->_currencyToSmallerUnit($orderTotalGross);
		}


		function before_process() {
			global $order;

			if(isset($_SESSION['billpay_preauth_error'])) {
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode($_SESSION['billpay_preauth_error']), 'SSL'));
			}

			//$this->_logError("before_process", "before_process");
			
			$this->_preauthorize();
		}

		function after_process() {
			global $insert_id, $order;

			// persist reference for payment information
			$invoiceReference = $this->generateInvoiceReference($insert_id);
			xtc_db_query("UPDATE billpay_bankdata SET orders_id = " . $insert_id . ", invoice_reference = '" . $invoiceReference . "' WHERE tx_id='".$this->_getTransactionId()."'");

			// set initial status of the created order if necessary
			if ($this->order_status) {
				xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
			}

			xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS_HISTORY . '(orders_id, orders_status_id, date_added, customer_notified, comments) '.
					'VALUES (' . $insert_id . ", " . $this->order_status . ", now(), 0, '" . MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER . "')");

			$totalUnits = $this->_currencyToSmallerUnit($GLOBALS['ot_total']->output[0]['value']);
			if($_SESSION['billpay_total_amount'] != $totalUnits) {
				xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->error_status."' WHERE orders_id='".$insert_id."'");
				xtc_db_query("INSERT INTO ".TABLE_ORDERS_STATUS_HISTORY." (orders_id, orders_status_id, date_added, comments) VALUES (".$insert_id.", ".$this->error_status.", now(), '" . MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CONTACT_BILLPAY . "')");
				$this->_logError('Invalid total value after order has been created. Expected: ' . $_SESSION['billpay_total_amount'] . ', found: ' . $totalUnits, 'after process error');
				$this->_displayGMerror(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT);
				$_SESSION['billpay_hide_payment_method'] = TRUE;
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT), 'SSL'));
			}

			if ($this->_getTransactionId()) {
				require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_api.php');
				require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_update_order_request.php');

				$req = new ipl_update_order_request($this->api_url);
				$req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
				$req->set_update_params($this->_getTransactionId(), $insert_id);

				// create mapping for id upate list
				$query = xtc_db_query("SELECT orders_products_id FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id='".$insert_id."' ORDER BY orders_products_id ASC");
				if (xtc_db_num_rows($query)) {
					$idMapping = array();
					foreach($_SESSION['cart']->contents as $tmpID => $data) {
						if (isset($data['qty'])) {
							$idMapping[] = array($tmpID, -1);
						}
					}

					$count = 0;
					while ($res = xtc_db_fetch_array($query)) {
						$targetId = $res['orders_products_id'];
						$idMapping[$count][1] = $targetId;
						++$count;
					}

					foreach ($idMapping as $entry) {
						$req->add_id_update($entry[0], $entry[1]);
					}

					$internalError = $req->send();
					if ($internalError) {
						$this->_logError($internalError['error_message'], 'WARNING: Error sending update order request. Must use tx_id as api reference');
					}

					$this->_logError($req->get_request_xml(), 'update order request XML');
					$this->_logError($req->get_response_xml(), 'update order response XML');

					if (!$req->has_error() && !$internalError) {
						// update order id and api reference id
						xtc_db_query("UPDATE billpay_bankdata SET api_reference_id='" . $insert_id . "' WHERE tx_id='".$this->_getTransactionId()."'");
					}
					else {
						// update only order id (txid will be used as reference for api and as invoice reference)
						xtc_db_query("UPDATE billpay_bankdata SET orders_id = " . $insert_id . " WHERE tx_id='".$this->_getTransactionId()."'");
						$this->_logError($req->get_error_code(), 'ERROR code returned when sending update order request');
					}
				}

				unset($_SESSION['billpay_transaction_id']);
				unset($_SESSION['billpay_total_amount']);
				unset($_SESSION['billpay_preselect']);
				unset($_SESSION['bp_rate_result']);
				unset($_SESSION['rr_data']);
			}
			else {
				$this->_logError('Transaction ID not found in session', 'ERROR in after_process');
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message='.urlencode(MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT), 'SSL'));
			}
		}

		function output_error() {
		}

		function _getTransactionId() {
			return $_SESSION['billpay_transaction_id'];
		}

		function _setTransactionId($transid) {
			$_SESSION['billpay_transaction_id'] = $transid;
		}

		function _getCountry($isocode) {
			global $order;
			
			if ($isocode == '3') {
				return strtolower($order->billing['country']['iso_code_3']);
			} else if ($isocode == '2') {
				return strtolower($order->billing['country']['iso_code_2']);
			} else {
				return 'deu';
			}
		}
		
		function _getCurrency() {
			global $order;

			// prefer order over session
			if (!empty($order->info['currency'])) {
				return (string)$order->info['currency'];
			}
			else if (!empty($_SESSION['currency'])) {
				return (string)$_SESSION['currency'];
			}
		}


		/*
		 0: Lastschrift					|		0: Bezahlt
		 1: Kreditkarte					|		1: Offen
		 2: Vorkasse						|		2: Mahnwesen
		 3: Nachnahme					|		3: Inkasso
		 4: Paypal						|		4: Ueberbezahlt
		 5: Sofortueberweisung/Giropay	|		5: Unterbezahlt
		 6: Rechnung						|		6: Geplatzt
		 7: Billpay (Rechnung)
		 100: Other
		 */
		function _getOrderStatus ($_orderStatus = NULL) {
			return 0;

			if (!is_null($_orderStatus)) {
				if ($_orderStatus == constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ORDER_STATUS')) {
					return 1;
				}
			}
		}

		function _getPaymentMethod($_paymentMethod = NULL) {
			switch($_paymentMethod) {
				case 'moneybookers_elv':
				case 'micropayment_debit':
					return 0;
					break;
				case 'cc':
				case 'moneybookers_cc':
				case 'micropayment_cc':
				case 'worldpay':
					return 1;
					break;
				case 'banktransfer':
				case 'eustandardtransfer':
					return 2;
					break;
				case 'cod':
					return 3;
					break;
				case 'paypal':
				case 'paypalexpress':
					return 4;
					break;
				case 'pn_sofortueberweisung':
				case 'moneybookers_sft':
				case 'moneybookers_giropay':
				case 'giropay':
					return 5;
					break;
				case 'invoice':
					return 6;
					break;
				case 'billpay':
					return 7;
					break;
				default:
					return 100;
					break;
			}

			return 100;
		}

		function _currencyToSmallerUnit($price_float = NULL) {
			if (!is_null($price_float)) {
				$_price = $price_float * 100;
				return round($_price);
			}
			return ;
		}

		/**
		 * Returns net or gross price in cents
		 *
		 * @param $valuePrice the base price value
		 * @param $valueTax the tax amount as integer
		 * @param $calculateTax convert price from net to gross or from gross to net
		 * @param $isGrossPrice if the supplied price includes tax (gross price). else it is a net price
		 */
		function _getPrice($valuePrice, $valueTax, $calculateTax = 1, $isGrossPrice = true) {
			if($calculateTax == 1) {
				if ((!is_null($valuePrice)) && (!is_null($valueTax))) {
					$taxAmount = (float)($valuePrice * $valueTax / 100);
					$taxUnits = (int)$this->_currencyToSmallerUnit($taxAmount);
					$priceNetUnits = (int)$this->_currencyToSmallerUnit($valuePrice);
						
					if ($isGrossPrice == true) {
						$priceGrossUnits = $priceNetUnits - $taxUnits;	// gross price. convert to net price
					}
					else {
						$priceGrossUnits = $priceNetUnits + $taxUnits;	// net price. convert to gross price
					}
					return $priceGrossUnits;
				}
			}
			else {
				$price = (int)$this->_currencyToSmallerUnit($valuePrice);	// do not convert the price
				return $price;
			}
			return 0;
		}


		function _getOrderHistory($_customerId = NULL) {
			$_return = array();

			if (!is_null($_customerId)) {
				// SQL for order history
				$_queryOrder = xtc_db_query('SELECT ' .
													'`orders_id`, `date_purchased`, ' .
													'`payment_method`, `orders_status`, ' .
													'`currency` ' .
											'FROM ' .
				TABLE_ORDERS .
											' WHERE ' .
													'`account_type` != 1 ' .
											'AND ' .
													'`customers_id` = ' . $_customerId .
											' LIMIT 10');

				// SQL for each order total
				$_sqlTotal = 'SELECT `value` FROM ' . TABLE_ORDERS_TOTAL . ' WHERE `orders_id` = %s and `class` = "ot_total" LIMIT 0 , 1';

				while ($_resultOrder = xtc_db_fetch_array($_queryOrder)) {
					// fetch order total
					$_queryTotal = xtc_db_query(sprintf($_sqlTotal, $_resultOrder['orders_id']));
					$_resultTotal = xtc_db_fetch_array($_queryTotal);
					$_totalAmount = $this->_currencyToSmallerUnit($_resultTotal['value']);

					// assign current order to array
					$_return[] = array(
					'hid' => utf8_encode($_resultOrder['orders_id']),
					'hdate' => utf8_encode($this->_formatDate('Ymd H:i:s', $_resultOrder['date_purchased'])),
					'hamount' => -23,//'hamount' => utf8_encode(isset($_totalAmount) ? $_totalAmount : 0),
					'hcurrency' => utf8_encode($_resultOrder['currency']),
					'hpaymenttype' => utf8_encode($this->_getPaymentMethod($_resultOrder['payment_method'])),
					'hstatus' => utf8_encode($this->_getOrderStatus($_resultOrder['orders_status']))
					);

					// free ressources
					unset($_queryTotal, $_resultTotal, $_totalAmount);
				}

			}
			return $_return;
		}

		function _getCustomerIp() {	
			global $HTTP_SERVER_VARS;
			$config = $this->_getIPconfig();
			
			if($config == true && isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']) && !empty($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {			
				$ip = NULL;
				$ip_array = array();	
				
				foreach (explode(',', $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']) as $x_ip ) {
       					  $x_ip = trim($x_ip);
       					  $ip_array[] = $x_ip;
					 }
					 return $ip_array[0];			
			} else {
				if (!empty($_SESSION['tracking']['ip'])) {
					return $_SESSION['tracking']['ip'];
				} else if (!empty($_SERVER['REMOTE_ADDR'])) {
					return $_SERVER['REMOTE_ADDR'];
				}
			}
			return;
		}

		function _getCustomerGender() {
			global $order;

			if (!empty($_SESSION['customer_gender'])) {
				return $_SESSION['customer_gender'];
			}
			elseif (!empty($order->customer['gender'])) {
				return $order->customer['gender'];
			}
			return ;
		}

		function _getCustomerSalutation($customerGender = NULL) {
			$_gender = '';

			if (!is_null($customerGender)) {
				$_gender = (string)$customerGender;
			}
			else {
				$_gender = $this->_getCustomerGender();
			}

			if (!empty($_gender)) {
				switch ($_gender) {
					case 'm':
						return constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SALUTATION_MALE');
						break;
					case 'f':
						return constant('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SALUTATION_FEMALE');
						break;
				}
			}

			return ;
		}

		function _getCustomerId() {
			if (!empty($_SESSION['customer_id'])) {
				return (int)$_SESSION['customer_id'];
			}
			return ;
		}

		function _getCustomerGroup() {
			if (isset($_SESSION['customers_status']['customers_status_id'])) {
				// default values
				// 0 = admin, 1 = guest, 2 = new customer, 3 = merchant
				switch($_SESSION['customers_status']['customers_status_id']) {
					case '0':
					case '3':
						return 'e';
						break;
					case '2':
						return 'n';
						break;
					case '1':
					default:
						return 'g';
						break;
				}
			}
			return 'n';
		}

		function _getCustomerDob() {
			$_custId = $this->_getCustomerId();

			if (!empty($_custId)) {
				$_query = xtc_db_query('SELECT customers_dob AS dob FROM ' . TABLE_CUSTOMERS . ' WHERE customers_id = ' . $_custId . ' LIMIT 0 , 1');
				$_result = xtc_db_fetch_array($_query);
				// check if customer have a date of birth
				if ($_result['dob'] != '0000-00-00 00:00:00') {
					$_dobCheck = $this->_formatDate('Y-m-d', $_result['dob']);

					if (!empty($_dobCheck)) {
						return $_dobCheck;
					}
				}
			}
			return;
		}

		function _getCustomerPhone() {
			$custId = $this->_getCustomerId();
			if (!empty($custId)) {
				$query = xtc_db_query('SELECT customers_telephone AS phone FROM ' . TABLE_CUSTOMERS
				. ' WHERE customers_id = ' . $custId . ' LIMIT 0 , 1');
				$result = xtc_db_fetch_array($query);
				return $result['phone'];
			}
		}

		function _checkPaymentSelection($data_arr) {
			if ((!empty($data_arr['payment'])) && ($data_arr['payment'] == strtolower($this->_paymentIdentifier))) {
				$dob 		=  $this->_getDataValue('dob', $data_arr);
				$dobDay 	=  $this->_getDataValue('dob_day', $data_arr);
				$dobMonth 	=  $this->_getDataValue('dob_month', $data_arr);
				$dobYear 	=  $this->_getDataValue('dob_year', $data_arr);

				if (!empty($dob)) {
					$_dobCheck = $this->_formatDate('Ymd', $dob);

					if (!empty($_dobCheck)) {
						return $_dobCheck;
					}
				}
				elseif (!empty($dobYear) && !empty($dobMonth) && !empty($dobDay)) {
					if  ((int)$dobYear >= $this->_getMinYear() && (int)$dobYear <= $this->_getMaxYear()) {
						$dobCombined = (int)$dobYear.'-'.(int)$dobMonth.'-'.(int)$dobDay;
						$dobCheck = $this->_formatDate('Ymd', (string)$dobCombined);

						if (!empty($dobCheck)) {
							return $dobCheck;
						}
						else {
							return $this->_getCustomerDob();
						}
					}
				}
			}
		}

		function _formatDate($dateStyle = NULL, $dateString = NULL) {
			if ((!is_null($dateStyle)) && (!is_null($dateString))) {
				$_checkStamp = strtotime($dateString);

				if (($_checkStamp != FALSE) && ($_checkStamp != -1)) {
					return date($dateStyle, $_checkStamp);
				}
			}
			return;
		}

		function _getSelectDobDay() {
			return $this->_genSelectDob('day', 1, 31, 'asc');
		}

		function _getSelectDobMonth() {
			return $this->_genSelectDob('month', 1, 12, 'asc');
		}

		function _getSelectDobYear() {
			return $this->_genSelectDob('year', $this->_getMinYear(), $this->_getMaxYear(), 'asc');
		}

		function _genSelectGender($id = 'b2c') {
			if ($id == 'b2c') {
				$width 				= '122';
				$selectedGender 	= $this->_getDataValue('gender_b2c');
			}
			else {
				$width 				= '80';
				$selectedGender 	= $this->_getDataValue('gender_b2b');
			}

			$gender = $this->_getDataValue('gender');
			$genderSelectHTML = '<select name="'.$this->_getDataIdentifier('gender_'.$id).'" style="width:' . $width . 'px;">';
			if(isset($gender) && $gender == "m") {
				$genderSelectHTML .= '<option value="m" ' . ($selectedGender == 'm' ? 'selected' : '') . '>' . ($id == 'b2c' ? MODULE_PAYMENT_BILLPAY_TEXT_MALE : MODULE_PAYMENT_BILLPAY_TEXT_MR) . '</option>';
			}
			else if(isset($gender) && $gender == "f") {
				$genderSelectHTML .= '<option value="f" ' . ($selectedGender == 'f' ? 'selected' : '') . '>' . ($id == 'b2c' ? MODULE_PAYMENT_BILLPAY_TEXT_FEMALE : MODULE_PAYMENT_BILLPAY_TEXT_MRS) . '</option>';
			}
			$genderSelectHTML .= '<option value="">---</option>';
			$genderSelectHTML .= '<option value="m" ' . ($selectedGender == 'm' ? 'selected' : '') . '>' . ($id == 'b2c' ? MODULE_PAYMENT_BILLPAY_TEXT_MALE : MODULE_PAYMENT_BILLPAY_TEXT_MR) . '</option>';
			$genderSelectHTML .= '<option value="f" ' . ($selectedGender == 'f' ? 'selected' : '') . '>' . ($id == 'b2c' ? MODULE_PAYMENT_BILLPAY_TEXT_FEMALE : MODULE_PAYMENT_BILLPAY_TEXT_MRS) . '</option>';
			$genderSelectHTML .= '</select><span class="inputRequirement">&nbsp;*&nbsp;</span>';
			return $genderSelectHTML;
		}


		function _genSelectDob($genName, $genFrom, $genTo, $sortDirection) {
			$identifier = $this->_getDataIdentifier('dob_'.strtolower($genName));
			$dobSelectHTML = '<select name="'.$identifier.'" style="width:60px">';

			$value = $this->_getDataValue('dob_'.$genName);
			if(isset($value) && $value > 0) {
				$dobSelectHTML .= '<option value="'.$value.'">'.$value.'</option>';
			}
			$dobSelectHTML .= '<option value="00">---</option>';

			if ($sortDirection == 'desc') {
				for ($i = $genTo; $i >= $genFrom;) {
					$iMod = sprintf('%02d', (int)$i);
					$dobSelectHTML .= '<option value="' . $iMod . '">&nbsp;&nbsp;' . $iMod . '&nbsp;&nbsp;</option>';
					$i--;
				}
			}
			else {
				for ($i = $genFrom; $i <= $genTo;) {
					$iMod = sprintf('%02d', (int)$i);
					$dobSelectHTML .= '<option value="' . $iMod . '">&nbsp;&nbsp;' . $iMod . '&nbsp;&nbsp;</option>';
					$i++;
				}
			}

			$dobSelectHTML .= '</select>';

			return $dobSelectHTML;
		}

		function _getMinYear() {
			return (int)date('Y') - 100;
		}

		function _getMaxYear() {
			return (int)date('Y') - 15;
		}

		function _createTotals($classname) {
			global $xtPrice;

			$ot = new $classname($xtPrice);
			return $ot;
		}

		/** calculate sum of current shopping cart */
		// Is this still in use?
		function _calculateCartTax($cart = NULL) {
			//return $order->info[tax];
			if(!isset($cart) || $cart == "")
			$cart = $_SESSION['cart'];

			$gval=0;
			foreach ($cart->tax as $key => $value)
			{
				if ($value['value'] > 0 )
				$gval += $value['value'];
			}
			return $gval;
		}

		function _errorMessage($_code, $_msgMerchant, $_msgCustomer) {
			$_errorTpl  =	'Code: ' 			. "\t\t" . '%s' . "\n";
			$_errorTpl .=	'Merchant MSG: ' 	. "\t\t" . '%s' . "\n";
			$_errorTpl .=	'Customer MSG: ' 	. "\t\t" . '%s'	. "\n";

			$_errorMsg = sprintf($_errorTpl,
			(string)utf8_decode($_code),
			(string)utf8_decode($_msgMerchant),
			(string)utf8_decode($_msgCustomer)
			);

			return $_errorMsg;
		}

		function _logError($logMessage, $logType = NULL) {
			$_write = FALSE;

			if ((!empty($this->_logPath)) && (is_writable($this->_logPath)) && $this->enableLog=='True') {
				$_data = 'LOG BEGINS:' . "\t" . date('r') . "\n\n";
				$_data .= '------------------< '. strtoupper($logType) . ' >------------------';
				$_data .= "\n\n" . $logMessage;
				$_data .= "\n\n" . '------------------< EOF >------------------' . "\n\n";

				if ((function_exists('version_compare')) && (version_compare(PHP_VERSION, '5.0.0', '>='))) {
					$_write = file_put_contents($this->_logPath, $_data, FILE_APPEND);
				}
				else { // PHP4 workaround
					$handle = fopen($this->_logPath, 'a');

					if (fwrite($handle, $_data) != FALSE) {
						$_write = TRUE;
					}

					fclose($handle);
				}
			}
			return $_write;
		}

		function isModuleInstalled($moduleName) {
			if(defined('MODULE_ORDER_TOTAL_INSTALLED')) {
				$totalModules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

				foreach ($totalModules as $installedModule) {
					$splitted = explode('.', $installedModule);
					if (trim($splitted[0]) == $moduleName) {
						return TRUE;
					}
				}

				return in_array(strtolower(trim($moduleName)), $totalModules);
			}
			else {
				return FALSE;
			}
		}

		function generateInvoiceReference($orderID) {
			return 'BP' . $orderID . '/' . $this->bp_merchant;
		}

		function autoinstall() {
			$backlink = xtc_href_link(FILENAME_MODULES, 'set=payment&module=billpay', 'SSL');
			$html = $this->billpayCallback->getBillpayRegistrationFormPage(
			$backlink,
			xtc_catalog_href_link(),
			$this->_paymentIdentifier
			);
			return $html;
		}

		function _install_b2b_option()
		{}

		function install() 	{
			if (isset($_GET['autoinstall']) && ($_GET['autoinstall'] == '1')) {
				print $this->autoinstall();
				exit();
			}
			else {
				// make sure we get a clean state
				$this->remove();

				// fetch next sort order
				switch ($this->_paymentIdentifier) {
					case "BILLPAY";
					$sortOrder = 3;
					break;
					case "BILLPAYDEBIT";
					$sortOrder = 4;
					break;
					case "BILLPAYTRANSACTIONCREDIT";
					$sortOrder = 5;
					break;
					default:
						$sortOrder = 6;
						break;
				}

				$language = $_SESSION['language'];
				if (file_exists(DIR_FS_LANGUAGES . $language . '/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php')) {
					require_once DIR_FS_LANGUAGES . $language . '/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php';
				} else {
					require_once DIR_FS_LANGUAGES . 'german/modules/payment/' . strtolower($this->_paymentIdentifier) . '.php';
				}

				// install new configuration
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_TESTMODE', 'Testmodus', '6', '0', 'xtc_cfg_select_option(array(\'" . MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_TEST . "\', \'" . MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_LIVE . "\'), ', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_LOGGING', '', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_LOGGING_ENABLE', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_ID', 'ShopID', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_SHIPPING_TAX', '',  '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_SORT_ORDER', '".$sortOrder."', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_ALLOWED', 'DE',   '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_ORDER_STATUS', '0', '6', '0', 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_MERCHANT_ID', '0', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_PORTAL_ID', '0', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_SECURE', '0', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_TABLE', 'payment_billpay', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_MIN_AMOUNT', '', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_TESTAPI_URL_BASE', 'https://test-api.billpay.de/xml/offline', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_API_URL_BASE', 'https://api.billpay.de/xml', '6', '0', now())");
				xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_".$this->_paymentIdentifier."_UTF8_ENCODE', 'True', '6', '0', 'xtc_cfg_select_option(array(\'False\', \'True\'), ', now())");

				
				//check if login data is already set
				$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_MERCHANT_ID"');
		      	$rs_check_status = xtc_db_fetch_array($check_status);
		      	if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
		          		xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_BILLPAY_MERCHANT_ID', '0', '6', '0', now())");		      	
		      	}
		      	
				$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_PORTAL_ID"');
		      	$rs_check_status = xtc_db_fetch_array($check_status);
		      	if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
		          		xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_BILLPAY_PORTAL_ID', '0', '6', '0', now())");		      	
		      	}
		      	
				$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_SECURE"');
		      	$rs_check_status = xtc_db_fetch_array($check_status);
		      	if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
		          		xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_BILLPAY_SECURE', '0', '6', '0', now())");		      	
		      	}
				
				
				//check if HTTP_X_FORWARDED FOR is already installed
		      	$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_HTTP_X"');
		      	$rs_check_status = xtc_db_fetch_array($check_status);
		      	if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
		          		xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_BILLPAY_HTTP_X', 'False', '6', '0', 'xtc_cfg_select_option(array(\'False\', \'True\'), ', now())");		      	
		      	}
		      	
				$this->_install_b2b_option();

				// insert status. check if activation and cancellation status already exist
				$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED"');
				$rs_check_status = xtc_db_fetch_array($check_status);
				if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
					$res = xtc_db_query('SELECT max(orders_status_id) + 1 AS nextId FROM ' . TABLE_ORDERS_STATUS);
					$a = xtc_db_fetch_array($res);
					$nextId = $a['nextId'];

					xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES (" . $nextId . ", '1', '" . MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE . "')");
					xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES ('" . $nextId . "', '2', '" . MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE . "')");
					xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED', '" . $nextId . "', '6', '0', now())");
				}
				$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED"');
				$rs_check_status = xtc_db_fetch_array($check_status);
				if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
					$res = xtc_db_query('SELECT max(orders_status_id) + 1 AS nextId FROM ' . TABLE_ORDERS_STATUS);
					$a = xtc_db_fetch_array($res);
					$nextId = $a['nextId'];

					xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES (" . $nextId . ", '1', '" . MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE . "')");
					xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES ('" . $nextId . "', '2', '" . MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE . "')");
					xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED', '" . $nextId . "', '6', '0', now())");
				}

				$check_status = xtc_db_query('SELECT count(*) AS number FROM ' . TABLE_CONFIGURATION . ' where configuration_key = "MODULE_PAYMENT_BILLPAY_STATUS_ERROR"');
				$rs_check_status = xtc_db_fetch_array($check_status);
				if($rs_check_status['number'] == 0 || $rs_check_status['number'] == '') {
					$res = xtc_db_query('SELECT max(orders_status_id) + 1 AS nextId FROM ' . TABLE_ORDERS_STATUS);
					$a = xtc_db_fetch_array($res);
					$nextId = $a['nextId'];

					xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES (" . $nextId . ", '1', '" . MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE . "')");
					xtc_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) VALUES ('" . $nextId . "', '2', '" . MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE . "')");
					xtc_db_query('INSERT INTO ' . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_BILLPAY_STATUS_ERROR', '" . $nextId . "', '6', '0', now())");
				}

				// billpay_bankdata table
				$check_query = xtc_db_query("SHOW TABLES LIKE 'billpay_bankdata'");
				if (xtc_db_num_rows($check_query) == 0) {
					// create new table if it does not exist yet
					xtc_db_query(
						"CREATE TABLE IF NOT EXISTS `billpay_bankdata` (
							`api_reference_id` varchar(64) NOT NULL,
							`account_holder` varchar(100) NOT NULL,
							`account_number` varchar(50) NOT NULL,
							`bank_code` varchar(50) NOT NULL,
							`bank_name` varchar(100) NOT NULL,
							`invoice_reference` varchar(250) NOT NULL,
							`invoice_due_date` varchar(9) default NULL,
							`tx_id` varchar(64) NOT NULL,
							`orders_id` int(11) unsigned default NULL,
							`rate_surcharge` decimal(12,4) DEFAULT NULL,
							`rate_total_amount` decimal(12,4) DEFAULT NULL,
							`rate_count` int(10) unsigned DEFAULT NULL,
							`rate_dues` text,
							`rate_interest_rate` decimal(12,4) DEFAULT NULL,
							`rate_anual_rate` decimal(12,4) DEFAULT NULL,
							`rate_base_amount` decimal(12,4) DEFAULT NULL,
							`rate_fee` decimal(12,4) DEFAULT NULL,
							`rate_fee_tax` decimal(12,4) DEFAULT NULL
						)"
						);
				} else {
					// Example data 20110305#8415:20110405#6211:20110505#6211:20110605#6211:20110705#6211:20110805#6211
					// Date is empty before activation: #8415:#6211:#6211:#6211:#6211:#6211
					// if table exists already, check if tc columns exist and add them if necessary
					$columns = array(
		      			"rate_surcharge" => "decimal(12,4) DEFAULT NULL",
						"rate_total_amount" => "decimal(12,4) DEFAULT NULL",
						"rate_count" => "int(10) unsigned DEFAULT NULL",
						"rate_dues" => "text",
						"rate_interest_rate" => "decimal(12,4) DEFAULT NULL",
						"rate_anual_rate" => "decimal(12,4) DEFAULT NULL",
						"rate_base_amount" => "decimal(12,4) DEFAULT NULL",
						"rate_fee" => "decimal(12,4) DEFAULT NULL",
						"rate_fee_tax" => "decimal(12,4) DEFAULT NULL"
						);
						foreach ($columns as $columnName => $columnType) {
							$check_query = xtc_db_query(
							"SELECT * FROM information_schema.COLUMNS
							WHERE TABLE_SCHEMA = '" . DB_DATABASE . "'
							AND TABLE_NAME = 'billpay_bankdata'
							AND COLUMN_NAME='$columnName'"
							);
							if (xtc_db_num_rows($check_query) == 0) {
								// create tc columns if they do not exist yet
								xtc_db_query(
							"ALTER TABLE `billpay_bankdata`
								ADD `$columnName` $columnType
							");
							}
						}
				}

				// create partial cancel buffer table
				xtc_db_query(
			     	"CREATE TABLE IF NOT EXISTS `billpay_edit_orders_buffer` (
			     		`id` INT NOT NULL AUTO_INCREMENT,
			     		`orders_id` INT NOT NULL,
			     		`entity_type` SMALLINT NOT NULL,
			     		`value_units_1` INTEGER NOT NULL,
			     		`value_units_2` INTEGER NOT NULL,
			     		`quantity` INTEGER NOT NULL,
			     		`reference` varchar(64) NULL,
			     		PRIMARY KEY (id)
			     	)"
		     	);
			    
			    // install totals for transaction credit
				if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
			     	$otBillpayTcSurcharge = new ot_billpaytc_surcharge();
			     	$otBillpayTcSurcharge->install();
			    }
			}
		}

		function remove() {
			// remove billpay configuration values
			xtc_db_query('DELETE FROM ' . TABLE_CONFIGURATION . ' '.
		   					'WHERE configuration_key LIKE "MODULE_PAYMENT_'.$this->_paymentIdentifier.'\_%" '.
		   						'AND configuration_key <> "MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED" ' .
		   						'AND configuration_key <> "MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED" ' .
		   						'AND configuration_key <> "MODULE_PAYMENT_BILLPAY_STATUS_ERROR"');
			
			// remove totals for transation credit
			if ($this->_paymentIdentifier == 'BILLPAYTRANSACTIONCREDIT') {
				$otBillpayTcSurcharge = new ot_billpaytc_surcharge();
				$otBillpayTcSurcharge->remove();
			}
		}

		function keys() {
			// configuration options will be displayed
			// in the here defined order at "admin/payment methods"
			$config_array = array('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_STATUS',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING_ENABLE',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTMODE',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_LOGGING',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ORDER_STATUS',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_ALLOWED',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_SORT_ORDER',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_MIN_AMOUNT',
						 'MODULE_PAYMENT_BILLPAY_MERCHANT_ID',
						 'MODULE_PAYMENT_BILLPAY_PORTAL_ID',
						 'MODULE_PAYMENT_BILLPAY_SECURE',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_API_URL_BASE',
						 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_TESTAPI_URL_BASE',
			  			 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_UTF8_ENCODE',
						 'MODULE_PAYMENT_BILLPAY_HTTP_X');
			
			if(defined('MODULE_PAYMENT_'.$this->_paymentIdentifier.'_B2BCONFIG')) {
				array_push($config_array, 'MODULE_PAYMENT_'.$this->_paymentIdentifier.'_B2BCONFIG');
			}

			return $config_array;
		}

		function _getPaymentStatus($req, $config) {
			if($req->is_invoice_allowed() == true) {
				$config['static_limit_invoice'] = $req->get_static_limit_invoice();
			}

			if($req->is_invoicebusiness_allowed() == true) 	{
				$config['static_limit_invoicebusiness'] = $req->get_static_limit_invoicebusiness();
			}

			if($req->is_direct_debit_allowed() == true) 	{
				$config['static_limit_directdebit'] = $req->get_static_limit_direct_debit();
			}
			if ($req->is_hire_purchase_allowed() == true) {
				$config['static_limit_transactioncredit'] = $req->get_static_limit_hire_purchase();
				$config['min_value_transactioncredit'] = $req->get_hire_purchase_min_value();
				$config['terms'] = $req->get_terms();
			}
			return $config;
		}

		function getModuleConfig() {
			global $order;
			$country = $order->billing['country']['iso_code_3'];
			$currency = $order->info['currency'];
			$language = $this->_getLanguage();

			if (isset($_SESSION['billpay_module_config'][$country][$currency])) {
				$config = $_SESSION['billpay_module_config'][$country][$currency];
				if ($config == false) {
					$this->_logError('Fetching module config failed previously. Billpay payment not available.');
				}
				return $config;
			}

			$this->_logError($this->api_url, 'module config check api url for '.$this->_paymentIdentifier);

			require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_api.php');
			require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_module_config_request.php');

			$req = new ipl_module_config_request($this->api_url);
			$req->set_default_params($this->bp_merchant, $this->bp_portal, $this->bp_secure);
			$req->set_locale($country, $currency, $language);

			$internalError = $req->send();

			if ($internalError) {
				$this->_logError($internalError['error_message'], 'internal error module config');
				$config = false;
			}
			else {
				$this->_logError($req->get_request_xml(), 'XML request ModuleConfig');
				$this->_logError($req->get_response_xml(), 'XML response ModuleConfig');
				if ($req->has_error()) {
					$config = false;
					$this->_logError($req->get_merchant_error_message() . '(Error code: ' . $req->get_error_code() . ')', 'Error fetching module config');
				}
				else {
					$config = array();
					$config = $this->_getPaymentStatus($req, $config);
				}
			}
			$_SESSION['billpay_module_config'][$country][$currency] = $config;

			return $config;
		}


		/**
		 * Build rate plan and calculation details that will be displayed on invoice and email confirmation
		 * @param $orderId
		 * @param $isHTML
		 */
		function buildTCPaymentInfo($apiReference, $order, $isHTML, $isEMail = false) {
			require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');

			$rate_details_query = xtc_db_query("SELECT rate_surcharge, rate_total_amount, rate_count, " .
			"rate_dues, rate_interest_rate, rate_anual_rate, rate_base_amount, rate_fee, " .
			"rate_fee_tax FROM billpay_bankdata WHERE api_reference_id = '".$apiReference . "'");

			if (!xtc_db_num_rows($rate_details_query)) {
				return '';
			} else {
				$data = xtc_db_fetch_array($rate_details_query);
				$dueList = $data['rate_dues'];

				$trimmedDueList = trim($dueList);
				if (!empty($trimmedDueList)) {
					$fontSizes = array('8', '9', '7');
					if ($isEMail) {
						$fontSizes = array('10', '10', '8');
					}
					$infoText =	'<div style="font-size: ' . $fontSizes[0] . 'px;">';
					if ($isEMail) {
						$infoText .= '<br/>';
					}
					$infoText .= MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_INVOICE_INFO1.'<br /><br />';
					$dueDateArray = $this->unserializeDueDates($trimmedDueList);
					$rateCount = 1;
					foreach ($dueDateArray as $entry) {
						$infoText .= $rateCount . '. ' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_RATE . ': ';
						$infoText .= xtc_format_price_order($entry['value'] / 100, 1, $order->info['currency']);

						$trimmedDueDate = trim($entry['date']);
						if (!empty($trimmedDueDate)) {
							$infoText .= ' (' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_RATEDUE_TEXT;
							$trimmedDueDate = substr($trimmedDueDate, 0, 4).'-'.substr($trimmedDueDate, 4, 2).'-'.substr($trimmedDueDate, 6);
							$infoText .= ' ' . xtc_date_short($trimmedDueDate) . ')';
						}
						$infoText .= '<br />';

						++$rateCount;
					}
					$infoText .= '</div><br />';

					$currency 	= $order->info['currency'];
					$otherCosts = $data['rate_total_amount'] - $data['rate_surcharge'] - $data['rate_fee'] - $data['rate_base_amount'];

					$infoText .= '<strong>' . MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_PRICE_CALC_TEXT . '</strong><br />';
					$infoText .= '<table style="font: ' . $fontSizes[1] . 'px Arial, Helvetica, sans-serif;">';
					$infoText .= '<tr><td>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_CART_AMOUNT_TEXT.'</td><td>=</td><td>'.xtc_format_price_order($data['rate_base_amount'], 1, $currency).'</td></tr>';
					$infoText .= '<tr><td>'.MODULE_PAYMENT_BILLPAYTC_SURCHARGE_TEXT.'</td><td>+</td><td></td></tr>';
					$infoText .= '<tr><td>('.xtc_format_price_order($data['rate_base_amount'], 1, $currency).' x '.round($data['rate_interest_rate'], 2) .' x '.$data['rate_count'].' / 100)</td><td>=</td><td>'.xtc_format_price_order($data['rate_surcharge'], 1, $currency).'</td></tr>';
					// require_once(DIR_FS_CATALOG . 'includes/modules/order_total/ot_billpaytc_surcharge.php');
					if(class_exists('ot_billpaytc_surcharge')
					&& defined('MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE_STATUS')
					&& constant('MODULE_ORDER_TOTAL_BILLPAYTC_SURCHARGE_STATUS') == 'true') {
						$class_name = 'ot_billpaytc_surcharge';
						$billpayTcSurcharge = new $class_name;
						$tax = $billpayTcSurcharge->calculateTax($data['rate_fee']);
						$taxString = ' (' . MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX1
						. ' ' . xtc_format_price_order($tax, 1, $currency) . ' '
						. MODULE_ORDER_TOTAL_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TAX2 . ')';
					}
					$infoText .= '<tr><td>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TRANSACTION_FEE_TEXT.$taxString.'</td><td>+</td><td>'.xtc_format_price_order($data['rate_fee'], 1, $currency).'</td></tr>';
					$infoText .= '<tr><td>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_OTHER_COSTS_TEXT.'</td><td>+</td><td>'.xtc_format_price_order($otherCosts, 1, $currency).'</td></tr>';

					$infoText .= '<tr><td>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TOTAL_AMOUNT_TEXT.'</td><td>=</td><td>'.xtc_format_price_order($data['rate_total_amount'], 1, $currency).'</td></tr>';
					$infoText .= '<tr><td>'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_ANUAL_RATE_TEXT.'</td><td>=</td><td>'.round($data['rate_anual_rate'], 2) . '%</td></tr>';
					$infoText .= '</table>';

					$infoText .= '<br /><div style="font-size: ' . $fontSizes[2] . 'px">'.MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_EXAMPLE_TEXT . '</div><br />';

					if (!$isHTML) {
						$infoText = strip_tags($infoText);
					}
				}
				return $infoText;
			}
		}

		/**
		 * Create a string representation from special formatted array that can be stored in the database
		 *
		 * Result:
		 * Example data (incl. date): 20110305#8415:20110405#6211:20110505#6211:20110605#6211:20110705#6211:20110805#6211
		 * Example data (before activation): #8415:#6211:#6211:#6211:#6211:#6211
		 *
		 * @param $dueDateArray array
		 */
		function serializeDueDateArray($dueDateArray) {
			$serializedDueDateList = '';
			foreach ($dueDateArray as $entry) {
				if (!empty($serializedDueDateList)) {
					$serializedDueDateList .= ':';
				}
				$date = $entry['date'] ? $entry['date'] : '';
				$serializedDueDateList .= $date.'#'.$entry['value'];
			}
			return $serializedDueDateList;
		}

		/**
		 * Create array representation out of serialized due date string (Format specification input param see 'serializeDueDateArray')
		 *
		 * @param $serializedDueDates string
		 */
		function unserializeDueDates($serializedDueDates) {
			$dueListParts =	explode(":", $serializedDueDates);

			$result = array();
			foreach ($dueListParts as $entry) {
				$entryParts = explode("#", $entry);

				$result[] = array('date' => $entryParts[0],
				'value' => $entryParts[1]);
			}
			return $result;
		}

		function _getLanguage() {
			return $_SESSION['language_code'];
		}
		
		function getTermsOfServiceText() {
			return MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK;
		}
		
		function _getIPconfig() {
			if(defined('MODULE_PAYMENT_BILLPAY_HTTP_X') &&
				constant('MODULE_PAYMENT_BILLPAY_HTTP_X') == 'True') {
				return true;		
			} else {
				return false;		
			}
		}
		
		function getEncryptedSessionId() {
			return md5(session_id());
		}
				
	}
}

?>
