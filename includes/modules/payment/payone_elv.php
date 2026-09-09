<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_EXTERNAL.'payone/classes/PayonePayment.php');

class payone_elv extends PayonePayment {

	var $payone_genre = 'accountbased';

  var $code;
  var $form_action_url;
  var $payolution_mandate_url;
  var $elvtypes;

  var $pg_config;
  var $payone;
  var $global_config;
  var $personal_data;
  var $delivery_data;
  var $payment_method;
  var $params;
  var $builder;

	function __construct() {
		$this->code = 'payone_elv';
		parent::__construct();
		$this->form_action_url = '';

    $this->payolution_mandate_url = 'https://payment.payolution.com/payolution-payment/infoport/sepa/mandate.pdf';

		$this->elvtypes = array(
			'payolution_debit' => 'PYD',
		);
	}

	function _isPayolutionDebitAvailable() {
		global $order;

		return isset($this->pg_config['types']['payolution_debit']['active'])
		       && $this->pg_config['types']['payolution_debit']['active'] == 'true'
		       && is_object($order)
		       && empty($order->billing['company'])
		       && empty($order->customer['company']);
	}

	function _setDebitPaymentBankData($payment_method) {
		$payment_method->setBankcountry($_SESSION[$this->code]['bankcountry']);
		if (!empty($_SESSION[$this->code]['iban'])) {
			$payment_method->setIban($_SESSION[$this->code]['iban']);
			if (!empty($_SESSION[$this->code]['bic'])) {
				$payment_method->setBic($_SESSION[$this->code]['bic']);
			}
		} else {
			$payment_method->setBankaccount($_SESSION[$this->code]['bankaccount']);
			$payment_method->setBankcode($_SESSION[$this->code]['bankcode']);
		}
	}

	function selection() {
		if ($this->pg_config['types']['lastschrift']['active'] == 'true'
		    || $this->_isPayolutionDebitAvailable()
		    )
		{
			$selection = parent::selection();
      if (is_array($selection)) {
        $selection['description'] = '';
      }
		} else {
			$selection = false;
		}
		
		return $selection;
	}

	function _getAddressBookIso2($ab_id) {
		$t_query = "SELECT c.countries_iso_code_2
						      FROM ".TABLE_ADDRESS_BOOK." ab
						      JOIN ".TABLE_COUNTRIES." c 
						           ON c.countries_id = ab.entry_country_id
						     WHERE ab.address_book_id = '".$ab_id."'";
		$t_result = xtc_db_query($t_query, 'db_link', false);
		$iso2 = false;
		while ($t_row = xtc_db_fetch_array($t_result)) {
			$iso2 = $t_row['countries_iso_code_2'];
		}
		return $iso2;
	}

	function _paymentDataForm($active_genre_identifier) {
	  global $order;
	  
	  $payment_smarty = new Smarty();
    $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
    
    if ($this->_isPayolutionDebitAvailable()) {
	      $required_fields = array(
	        'customers_dob' => ((isset($_SESSION[$this->code]['customers_dob'])) ? $_SESSION[$this->code]['customers_dob'] : ''),
	      );
      $payment_smarty->assign('required_fields', $required_fields);
      $payment_smarty->assign('confirm_text', TEXT_PAYOLUTION_CONFIRM);
      $payment_smarty->assign('sepa_text', sprintf(TEXT_PAYOLUTION_CONFIRM_SEPA, $this->get_sepa_madate()));
    }
    $payment_smarty->assign('genre_specific', $this->pg_config['genre_specific']);
    
		$sepa_countries_all = $this->payone->getSepaCountries();
		$sepa_countries_active = $this->pg_config['genre_specific']['sepa_account_countries'];
		$sepa_countries = array();
		foreach ($sepa_countries_all as $sepa_country) {
			if (in_array($sepa_country['countries_iso_code_2'], $sepa_countries_active)) {
				$sepa_countries[] = $sepa_country;
			}
		}
    $payment_smarty->assign('sepa_countries', $sepa_countries);

    $sendto_iso2 = $this->_getAddressBookIso2($_SESSION['sendto']);
    $payment_smarty->assign('sendto_iso2', ((isset($_SESSION[$this->code]['bankcountry']) && $_SESSION[$this->code]['bankcountry'] != '') ? $_SESSION[$this->code]['bankcountry'] : $sendto_iso2));
		$payment_smarty->assign('bankdata', array(
			'iban' => ((isset($_SESSION[$this->code]['iban'])) ? $_SESSION[$this->code]['iban'] : ''),
			'bic' => ((isset($_SESSION[$this->code]['bic'])) ? $_SESSION[$this->code]['bic'] : ''),
			'bankaccount' => ((isset($_SESSION[$this->code]['bankaccount'])) ? $_SESSION[$this->code]['bankaccount'] : ''),
			'bankcode' => ((isset($_SESSION[$this->code]['bankcode'])) ? $_SESSION[$this->code]['bankcode'] : ''),
		));

    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_elv_form.html');
		
		$return = array(
			array('title' => '', 'field' => $module_form),
		);
		return $return;
	}
  
  function get_sepa_madate() {
    $filename = 'sepa_mandate.pdf';
    
    if (!is_file(SQL_CACHEDIR.$filename)
        || filemtime(SQL_CACHEDIR.$filename) < (time() - 86400)
        )
    {
      $fp = fopen(SQL_CACHEDIR.$filename, 'w+');

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->payolution_mandate_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
    }
    
    return xtc_href_link('cache/'.$filename, '', 'SSL', false);
  }
  
	function pre_confirmation_check() {
		global $order;

		parent::pre_confirmation_check();

		$payment_fields = array('p1_elv_country', 'p1_elv_accountnumber', 'p1_elv_bankcode', 'p1_elv_iban', 'p1_elv_bic', 'p1_elv_conditions', 'p1_elv_sepa', 'customers_dob');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			foreach($payment_fields as $field) {
				if (isset($_POST[$field]) && !is_scalar($_POST[$field])) {
					$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
					xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
				}
			}
			$_SESSION[$this->code] = array(
				'bankcountry' => ((isset($_POST['p1_elv_country'])) ? (string)$_POST['p1_elv_country'] : ''),
				'bankaccount' => ((isset($_POST['p1_elv_accountnumber'])) ? (string)$_POST['p1_elv_accountnumber'] : ''),
				'bankcode' => ((isset($_POST['p1_elv_bankcode'])) ? (string)$_POST['p1_elv_bankcode'] : ''),
				'iban' => ((isset($_POST['p1_elv_iban'])) ? (string)$_POST['p1_elv_iban'] : ''),
				'bic' => ((isset($_POST['p1_elv_bic'])) ? (string)$_POST['p1_elv_bic'] : ''),
				'conditions' => ((isset($_POST['p1_elv_conditions'])) ? (string)$_POST['p1_elv_conditions'] : false),
				'sepa' => ((isset($_POST['p1_elv_sepa'])) ? (string)$_POST['p1_elv_sepa'] : false),
			);

			if (isset($_POST['customers_dob'])) {
				$_SESSION[$this->code]['customers_dob'] = (string)$_POST['customers_dob'];
			}
		}

    $this->pg_config = $this->config[$this->_getActiveGenreIdentifier()];

		$lastschrift_active = $this->pg_config['types']['lastschrift']['active'] == 'true';
		$payolution_debit_available = $this->_isPayolutionDebitAvailable();
		if (!$lastschrift_active && !$payolution_debit_available) {
			$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
		}
		$_SESSION[$this->code]['elv_type'] = (($payolution_debit_available) ? 'payolution_debit' : 'lastschrift');

		if ($_SESSION[$this->code]['elv_type'] == 'payolution_debit') {
			$date = ((isset($_SESSION[$this->code]['customers_dob'])) ? xtc_date_raw($_SESSION[$this->code]['customers_dob']) : '');
			if (!ctype_digit((string)$date)
			    || strlen($date) != 8
			    || !checkdate((int)substr($date, 4, 2), (int)substr($date, 6, 2), (int)substr($date, 0, 4))
			    )
			{
				$_SESSION['payone_error'] = ENTRY_DATE_OF_BIRTH_ERROR;
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
			}
		}

		$bankcountry = $_SESSION[$this->code]['bankcountry'];
		$valid_country = in_array($bankcountry, $this->pg_config['genre_specific']['sepa_account_countries']);
		$valid_iban = $_SESSION[$this->code]['iban'] != '';
		$valid_account = $bankcountry === 'DE'
		                 && $_SESSION[$this->code]['bankaccount'] != ''
		                 && $_SESSION[$this->code]['bankcode'] != '';
		if (!$valid_country || (!$valid_iban && !$valid_account)) {
			$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
		}
    
    if ($_SESSION[$this->code]['elv_type'] == 'lastschrift') {
      $this->payone->log("verfication $this->code payment data");
      $standard_parameters = parent::_standard_parameters();    
      unset($standard_parameters['request']);
  
      $request_parameters = array(
        'aid' => $this->global_config['subaccount_id'],
        'key' => $this->global_config['key'],
      );

      $params = array_merge($standard_parameters, $request_parameters, $_SESSION[$this->code]);
  
      $builder = new Payone_Builder($this->payone->getPayoneConfig());
      $service = $builder->buildServiceVerificationBankAccountCheck();
  
      $request = new Payone_Api_Request_BankAccountCheck($params);
      $this->payone->log("elv BankAccountCheck request");
  
      $response = $service->check($request);
      $this->payone->log("elv BankAccountCheck response ".$response->getStatus());

      if ($response instanceof Payone_Api_Response_Error
          || $response instanceof Payone_Api_Response_BankAccountCheck_Blocked
          || $response instanceof Payone_Api_Response_BankAccountCheck_Invalid
          ) 
      {
        $this->payone->log("ERROR verification bankaccount: ".$response->getErrorcode().' - '.$response->getErrormessage());
        $_SESSION['payone_error'] = $response->getCustomermessage();
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }
    
    }

		if ($_SESSION[$this->code]['elv_type'] == 'payolution_debit'
		    && (empty($_SESSION[$this->code]['conditions']) || empty($_SESSION[$this->code]['sepa']))
		    )
		{
			$_SESSION['payone_error'] = TEXT_PAYOLUTION_ERROR_CONDITIONS;
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL', true));
    }
	}

	function confirmation() {
		$confirmation = array(
			'title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'),
		);
		return $confirmation;
	}

	function process_button() {
	  global $order;
	  
		if ($this->pg_config['genre_specific']['sepa_use_managemandate'] == 'true' 
		    && $_SESSION[$this->code]['elv_type'] == 'lastschrift'
		    )
		{
		  $this->payone->log("managemandate $this->code payment");
			$standard_parameters = parent::_standard_parameters();
			unset($standard_parameters['request']);
						
      $this->personal_data = new Payone_Api_Request_Parameter_ManageMandate_PersonalData();
      parent::_set_customers_standard_params();

			$this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_DebitPayment();
			$this->_setDebitPaymentBankData($this->payment_method);

			$request_parameters = array(
        'aid' => $this->global_config['subaccount_id'],
        'key' => $this->global_config['key'],
        'currency' => $order->info['currency'],
			);

			$params = array_merge($standard_parameters, $request_parameters);
			$builder = new Payone_Builder($this->payone->getPayoneConfig());

			$mandate_service = $builder->buildServiceManagementManageMandate();
			$manage_mandate_request = new Payone_Api_Request_ManageMandate($params);
			$manage_mandate_request->setAid($this->global_config['subaccount_id']);
			$manage_mandate_request->setClearingType('elv');
			$manage_mandate_request->setPersonalData($this->personal_data);
			$manage_mandate_request->setPayment($this->payment_method);

			$this->payone->log("elv managemandate request");
			$manage_mandate_result = $mandate_service->managemandate($manage_mandate_request);
			$this->payone->log("managemandate result ".$manage_mandate_result->getStatus());
			
			$error = false;	
			if ($manage_mandate_result instanceof Payone_Api_Response_Error) {
				$this->payone->log("ERROR retrieving SEPA mandate: ".$manage_mandate_result->getErrorcode().' - '.$manage_mandate_result->getErrormessage());
				$_SESSION['payone_error'] = $manage_mandate_result->getCustomermessage();
				if ($_SESSION['payone_error'] == '') {
				  $_SESSION['payone_error'] = PAYMENT_ERROR;
				}
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
			} elseif ($manage_mandate_result instanceof Payone_Api_Response_Management_ManageMandate_Approved) {
				if ($manage_mandate_result->isApproved()) {
					$mandate_status = $manage_mandate_result->getMandateStatus();
					if ($mandate_status == 'pending' || $mandate_status == 'active') {
						$mandate_id = $manage_mandate_result->getMandateIdentification();
					}
					if ($mandate_status == 'pending') {
						$mandate_text = urldecode($manage_mandate_result->getMandateText());
					}
				} else {
					$this->payone->log('ERROR: SEPA mandate not approved');
					$error = true;
				}
			} else {
				$this->payone->log('ERROR retrieving SEPA mandate: unhandled response type');
				$error = true;
			}

			if ($error === true) {
				$_SESSION['payone_error'] = PAYMENT_ERROR;
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
			}

			if (isset($mandate_id)) {
				$_SESSION[$this->code]['sepa_mandate_id'] = $mandate_id;
				$_SESSION[$this->code]['sepa_download_pdf'] = $this->pg_config['genre_specific']['sepa_download_pdf'];
			}

			if (isset($mandate_text) == true) {
				$_SESSION['payone_elv_sepa_mandate_mustconfirm'] = true;
    
    	  $payment_smarty = new Smarty();
        $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
        
        $payment_smarty->assign('mandate_text', $mandate_text);

        $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
        $payment_smarty->caching = 0;
        $module_form = $payment_smarty->fetch('checkout_payone_elv_mandate.html');
				return $module_form;
			}
		}
	}

	function before_process() {
		parent::before_process();

		if (isset($_SESSION['tmp_oID']) === false) {
			# we're on the first run of checkout_process
			if ($_SESSION[$this->code]['elv_type'] == 'lastschrift') {
        if ($this->pg_config['genre_specific']['sepa_use_managemandate'] == 'true') {
          if (isset($_POST['mandate_confirm']) !== true 
              && $_SESSION['payone_elv_sepa_mandate_mustconfirm'] == true
              )
          {
            unset($_SESSION['payone_elv_sepa_mandate_id']);
            $_SESSION['payone_error'] = ERROR_MUST_CONFIRM_MANDATE;
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
          }
        }
			}
		}
	}

	function payment_action() {
	  global $order, $insert_id;
    
    if (!isset($insert_id) || $insert_id == '') {
		  $insert_id = $_SESSION['tmp_oID'];
		}

		$this->payone->log("(pre-)authorizing $this->code payment");
		$standard_parameters = parent::_standard_parameters();
		
		$this->personal_data = new Payone_Api_Request_Parameter_Authorization_PersonalData();
		parent::_set_customers_standard_params();
		if ($_SESSION[$this->code]['elv_type'] == 'payolution_debit') {
			$this->personal_data->setBirthday(xtc_date_raw($_SESSION[$this->code]['customers_dob']));
		}

		$this->delivery_data = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
		parent::_set_customers_shipping_params();
    
		$this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_DebitPayment();
		$this->_setDebitPaymentBankData($this->payment_method);
		//$payment_method->setBankaccountholder($_SESSION[$this->code]['accountholder']);
		
		if (isset($_SESSION[$this->code]['sepa_mandate_id'])) {
			$this->payment_method->setMandateIdentification($_SESSION[$this->code]['sepa_mandate_id']);
		}
		
		if ($_SESSION[$this->code]['elv_type'] == 'lastschrift') {
      $request_parameters = parent::_request_parameters('elv');
      $this->params = array_merge($standard_parameters, $request_parameters);
      $this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
      parent::_build_service_authentification('elv');
    } else {
      
      $debit = $this->payment_method;
      $this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_Financing();
	      $this->payment_method->setSuccessurl($this->getCheckoutSuccessUrl($insert_id));
      $this->payment_method->setBackurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setErrorurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code);
    
      // set order_id for deleting canceld order
      $_SESSION['tmp_payone_oID'] = $_SESSION['tmp_oID'];
    
	      $paydata_item = array(
	        array('key' => 'b2b', 'data' => 'no'),
	      );
      $paydata = new Payone_Api_Request_Parameter_Paydata_Paydata();
      foreach ($paydata_item as $item) {
        $paydata->addItem(
          new Payone_Api_Request_Parameter_Paydata_DataItem($item)
        );
      }
      $this->payment_method->setPaydata($paydata);

      $financingtype = $this->elvtypes[$_SESSION[$this->code]['elv_type']];
      $this->payment_method->setFinancingtype($financingtype);
      $this->payment_method->setBankData($debit);

      $request_parameters = parent::_request_parameters('fnc');
    
      $this->params = array_merge($standard_parameters, $request_parameters);		
      $this->builder = new Payone_Builder($this->payone->getPayoneConfig());
        
      parent::_build_service_authentification('fnc');
    }
    
    parent::_parse_response_payone_api();

		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
	}

	function after_process() {
		parent::after_process();
		unset($_SESSION[$this->code]);
	}
}
