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

require_once (DIR_FS_EXTERNAL.'/payone/classes/PayonePayment.php');

class payone_elv extends PayonePayment {
	var $payone_genre = 'accountbased';

	function __construct() {
		$this->code = 'payone_elv';
		parent::PayonePayment();
		$this->form_action_url = '';
	}

	function selection() {
		if ($this->pg_config['types']['lastschrift']['active'] == 'true') {
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
	  $payment_smarty = new Smarty();
    $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';

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
    $payment_smarty->assign('sendto_iso2', ((isset($_SESSION[$this->code]['country']) && $_SESSION[$this->code]['country'] != '') ? $_SESSION[$this->code]['country'] : $sendto_iso2));

    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_elv_form.html');
		
		$return = array(
			array('title' => '', 'field' => $module_form),
		);
		return $return;
	}

	function pre_confirmation_check() {
		parent::pre_confirmation_check();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$_SESSION[$this->code] = array(
				'country' => ((isset($_POST['p1_elv_country'])) ? $_POST['p1_elv_country'] : ''),
				'accountnumber' => ((isset($_POST['p1_elv_accountnumber'])) ? $_POST['p1_elv_accountnumber'] : ''),
				'bankcode' => ((isset($_POST['p1_elv_bankcode'])) ? $_POST['p1_elv_bankcode'] : ''),
				'iban' => ((isset($_POST['p1_elv_iban'])) ? $_POST['p1_elv_iban'] : ''),
				'bic' => ((isset($_POST['p1_elv_bic'])) ? $_POST['p1_elv_bic'] : ''),
			);
		}
    
    $t_has_accbank = false;
		if (empty($_SESSION[$this->code]['accountnumber']) !== true && empty($_SESSION[$this->code]['bankcode']) !== true) {
			$t_has_accbank = true;
		}
    
    $t_has_sepa = false;
		if (empty($_SESSION[$this->code]['iban']) !== true && empty($_SESSION[$this->code]['bic']) !== true) {
			$t_has_sepa = true;
		}

		if (($t_has_accbank === true || $t_has_sepa === true) !== true || empty($_SESSION[$this->code]['country']) === true) {
			$_SESSION['payone_error'] = PAYDATA_INCOMPLETE;
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
	  
		if ($this->pg_config['genre_specific']['sepa_use_managemandate'] == 'true') {
		  $this->payone->log("managemandate $this->code payment");
			$standard_parameters = parent::_standard_parameters();
			unset($standard_parameters['request']);
						
      $this->personal_data = new Payone_Api_Request_Parameter_ManageMandate_PersonalData();
      parent::_set_customers_standard_params();

			$this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_DebitPayment();
		  if (isset($_SESSION[$this->code]['iban']) && $_SESSION[$this->code]['iban'] != '' && isset($_SESSION[$this->code]['bic']) && $_SESSION[$this->code]['bic'] != '') {
        $this->payment_method->setIban($_SESSION[$this->code]['iban']);
        $this->payment_method->setBic($_SESSION[$this->code]['bic']);
      } else {
        $this->payment_method->setBankaccount($_SESSION[$this->code]['accountnumber']);
        $this->payment_method->setBankcode($_SESSION[$this->code]['bankcode']);
      }
			$this->payment_method->setBankcountry($_SESSION[$this->code]['country']);

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

      $this->payone->log("elv managemandate request:\n".print_r($manage_mandate_request, true));
			$manage_mandate_result = $mandate_service->managemandate($manage_mandate_request);
			$this->payone->log("managemandate result:\n".print_r($manage_mandate_result, true));
			
			$error = false;	
			if ($manage_mandate_result instanceof Payone_Api_Response_Error) {
				$this->payone->log("ERROR retrieving SEPA mandate: ".$manage_mandate_result->getErrorcode().' - '.$manage_mandate_result->getErrormessage());
				$_SESSION['payone_error'] = $manage_mandate_result->getCustomermessage();
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
			}
			else if ($manage_mandate_result instanceof Payone_Api_Response_Management_ManageMandate_Approved) {
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
		if (isset($_SESSION['tmp_oID']) === false)
		{
			# we're on the first run of checkout_process
			if ($this->pg_config['genre_specific']['sepa_use_managemandate'] == 'true')
			{
				if (isset($_POST['mandate_confirm']) !== true && $_SESSION['payone_elv_sepa_mandate_mustconfirm'] == true)
				{
					unset($_SESSION['payone_elv_sepa_mandate_id']);
          $_SESSION['payone_error'] = ERROR_MUST_CONFIRM_MANDATE;
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
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

		$this->delivery_data = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
		parent::_set_customers_shipping_params();
    
		$this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_DebitPayment();
    $this->payment_method->setBankcountry($_SESSION[$this->code]['country']);
		//$payment_method->setBankaccountholder($_SESSION[$this->code]['accountholder']);
		
		if (isset($_SESSION[$this->code]['iban']) && $_SESSION[$this->code]['iban'] != '' && isset($_SESSION[$this->code]['bic']) && $_SESSION[$this->code]['bic'] != '') {
      $this->payment_method->setIban($_SESSION[$this->code]['iban']);
      $this->payment_method->setBic($_SESSION[$this->code]['bic']);
    } else {
      $this->payment_method->setBankaccount($_SESSION[$this->code]['accountnumber']);
  		$this->payment_method->setBankcode($_SESSION[$this->code]['bankcode']);
		}
		
		if (isset($_SESSION[$this->code]['sepa_mandate_id'])) {
			$this->payment_method->setMandateIdentification($_SESSION[$this->code]['sepa_mandate_id']);
		}
		
    $request_parameters = parent::_request_parameters('elv');

		$this->params = array_merge($standard_parameters, $request_parameters);
		$this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
    parent::_build_service_authentification('elv');
    parent::_parse_response_payone_api();

		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
	}

	function after_process() {
		parent::after_process();
		unset($_SESSION[$this->code]);
	}
}
?>