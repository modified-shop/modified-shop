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

class payone_otrans extends PayonePayment {

	var $payone_genre = 'onlinetransfer';

  var $code;
  var $form_action_url;
  var $banktransfertypes;

  var $config;
  var $payone;
  var $personal_data;
  var $delivery_data;
  var $payment_method;
  var $params;
  var $builder;

	function __construct() {
		$this->code = 'payone_otrans';
		parent::__construct();

		$this->form_action_url = '';
		$this->banktransfertypes = array(
			'sofortueberweisung' => 'PNT',
			'giropay' => 'GPY',
			'eps' => 'EPS',
			'pfefinance' => 'PFF',
			'pfcard' => 'PFC',
			'ideal' => 'IDL',
		);
	}

	function _paymentDataFormProcess($active_genre_identifier) {
	  global $order;
	  
	  $payment_smarty = new Smarty();
	  $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
    
    $genre_config = $this->config[$active_genre_identifier];
    $global_config = $genre_config['global_override'] == 'true' ? $genre_config['global'] : $this->config['global'];

    $available_genre = array();
    foreach ($genre_config['types'] as $type_name => $type_config) {
      if ($type_config['active'] == 'true') {
        $available_genre[] = $type_name;
      }
    }
        
    if (in_array($_POST[$this->code.'_type'], $available_genre)) {
      $bank_group = '';
      $bgroups = $this->payone->getBankGroups();
      switch ($_POST[$this->code.'_type']) {
        case 'sofortueberweisung':
          if ($order->billing['country']['iso_code_2'] == 'CH') {
            $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'],
                                     'bankcode' => '', 
                                     'bankaccount' => '', 
                                     );
          } else {
            $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'],
                                     'iban' => '', 
                                     'bic' => '', 
                                     );
          }
          break;
        case 'giropay':
          $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'], 
                                   'iban' => '', 
                                   'bic' => '', 
                                   );
          break;
        case 'eps':
          $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'],
                                   );
          $bank_group = $bgroups['eps'];
          break;
        case 'pfefinance':
        case 'pfcard':
          $required_fields = array();
          break;
        case 'ideal':
          $required_fields = array('bankaccountholder' => $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'],
                                   );
          $bank_group = $bgroups['ideal'];
          break;        
      }
      
      // assign to session
      $_SESSION[$this->code]['otrans_type'] = $_POST[$this->code.'_type'];
    }
    
    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
    $payment_smarty->assign('otrans_type', $_POST[$this->code.'_type']);
    $payment_smarty->assign('required_fields', $required_fields);
    $payment_smarty->assign('bank_group', $bank_group);
        
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_otrans_form.html');
		
		return $module_form;
	}

	function _paymentDataForm($active_genre_identifier) {
	  $payment_smarty = new Smarty();
    $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
    
		$genre_config = $this->config[$active_genre_identifier];
    $payment_smarty->assign('genre_config', $genre_config['types']);
    $payment_smarty->assign('code', $this->code);
    
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_type_selection.html');
		
		$return = array(
			array('title' => '', 
			      'field' => $module_form),
		);
		return $return;
	}

	function pre_confirmation_check() {
		parent::pre_confirmation_check();
	}

	function confirmation() {
    $confirmation = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'),
                          'fields' => array(array('title' => '',
                                                  'field' => constant('paymenttype_'.$_POST[$this->code.'_type']),
                                            )));

		return $confirmation;
	}

	function process_button() {
		$active_genre = $this->_getActiveGenreIdentifier();
		if ($active_genre === false) {
			return false;
		}
		
    return $this->_paymentDataFormProcess($active_genre);
	}

	function before_process() {
		parent::before_process();
        
    $valid_request = array('bankaccountholder', 'bankgrouptype', 'bankcode', 'bankaccount', 'bankcountry', 'iban', 'bic');

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		  foreach ($valid_request as $key) {
		    if (isset($_POST[$key])) {
		      $_SESSION[$this->code]['otrans_'.$key] = $_POST[$key];
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
    
    $bankgroup = '';
		if ($_SESSION[$this->code]['otrans_type'] == 'eps' || $_SESSION[$this->code]['otrans_type'] == 'ideal') {
    	$bankgroup = $_SESSION[$this->code]['otrans_bankgrouptype'];
		}
    $_SESSION[$this->code]['otrans_bankcountry'] = ((isset($_SESSION[$this->code]['otrans_bankcountry'])) ? $_SESSION[$this->code]['otrans_bankcountry'] : $order->billing['country']['iso_code_2']);
    
		$this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_OnlineBankTransfer();
		$this->payment_method->setOnlinebanktransfertype($this->banktransfertypes[$_SESSION[$this->code]['otrans_type']]);
		$this->payment_method->setBankcountry($_SESSION[$this->code]['otrans_bankcountry']);
		if ($_SESSION[$this->code]['otrans_type'] == 'sofortueberweisung' && $_SESSION[$this->code]['otrans_country'] == 'CH') {
			$this->payment_method->setBankaccount($_SESSION[$this->code]['otrans_bankaccount']);
			$this->payment_method->setBankcode($_SESSION[$this->code]['otrans_bankcode']);
		} else {
			$this->payment_method->setIban($_SESSION[$this->code]['otrans_iban']);
			$this->payment_method->setBic($_SESSION[$this->code]['otrans_bic']);
		}
		$this->payment_method->setBankgrouptype($bankgroup);
		$this->payment_method->setSuccessurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id());
		$this->payment_method->setBackurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id());
		$this->payment_method->setErrorurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code);

    // set order_id for deleting canceld order
    $_SESSION['tmp_payone_oID'] = $_SESSION['tmp_oID'];
    
    $request_parameters = parent::_request_parameters('sb');
    
		$this->params = array_merge($standard_parameters, $request_parameters);
		$this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
    parent::_build_service_authentification('sb');
    parent::_parse_response_payone_api();
  }
  
  function after_process() {        
		parent::after_process();
		unset($_SESSION[$this->code]);
	}
}
