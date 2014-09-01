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

class payone_cc extends PayonePayment {
	var $payone_genre = 'creditcard';

	function __construct() {
		$this->code = 'payone_cc';		
		parent::PayonePayment();
		
		$this->tmpOrders = '';
		$this->form_action_url = $this->payone->getFormActionURL();
	}

	function selection() {
		$selection = parent::selection();

		return $selection;
	}

  function before_process() {
		if (isset($_GET['pseudocardpan'])) {
			$_SESSION[$this->code]['pseudocardpan'] = $_GET['pseudocardpan'];
		}
		if (isset($_GET['truncatedcardpan'])) {
			$_SESSION[$this->code]['truncatedcardpan'] = $_GET['truncatedcardpan'];
		}
  }
  
	function _paymentDataFormProcess($active_genre_identifier) {
	  $payment_smarty = new Smarty();
	  $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';

		$error = parent::get_error();
		if ($error != '') {
		  $payment_smarty->assign('error', $error['error']);
		}
	  	  
		$genre_config = $this->config[$active_genre_identifier];
    $payment_smarty->assign('genre_specific', $genre_config['genre_specific']);

    $standard_parameters = parent::_standard_parameters('creditcardcheck');
		$standard_parameters['responsetype'] = 'REDIRECT';
		$standard_parameters['storecarddata'] = 'yes';
		$standard_parameters['encoding'] = 'UTF-8';
		$standard_parameters['successurl'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id();
		$standard_parameters['errorurl'] = ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_CONFIRMATION.'?'.xtc_session_name().'='.xtc_session_id().'&conditions=true&payment_error='.$this->code;
		$standard_parameters['hash'] = $this->payone->computeHash($standard_parameters, $this->global_config['key']);
    
    // not in hash but needed as hidden field
		$cctypes = $this->payone->getTypesForGenre($active_genre_identifier);		
		for ($i=0, $n=count($cctypes); $i<$n; $i++) {
		  if ($cctypes[$i]['typekey'] == $_SESSION[$this->code]['cardtype']) {
		    $standard_parameters['cardtype'] = $cctypes[$i]['shorttype'];
		    break;
		  }
		}

		$ccexpires_years = array();
		for($y = 0, $base = date('y'); $y < 10; $y++) {
			$ccexpires_years[] = $base + $y;
		}
    $payment_smarty->assign('ccexpires_years', $ccexpires_years);

		$ccexpires_months = array();
		for($m = 1; $m <= 12; $m++) {
			$ccexpires_months[] = sprintf('%02d', $m);
		}
    $payment_smarty->assign('ccexpires_months', $ccexpires_months);
    
    $hidden = array();
		foreach($standard_parameters as $key => $value) {
			$hidden[] = xtc_draw_hidden_field($key, $value);
		}
    $payment_smarty->assign('hidden', implode("\n", $hidden)."\n");
    
    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
    $payment_smarty->caching = 0;
    $module_form = $payment_smarty->fetch('checkout_payone_cc_form.html');
		
		return $module_form;
	}

	function _paymentDataForm($active_genre_identifier) {
	  $payment_smarty = new Smarty();
    $payment_smarty->template_dir = DIR_FS_EXTERNAL.'payone/templates/';
    
		$genre_config = $this->config[$active_genre_identifier];
    $payment_smarty->assign('genre_config', $genre_config['types']);
    $payment_smarty->assign('code', $this->code);
    
    $payment_smarty->assign('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');
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
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $_SESSION[$this->code]['cardtype'] = $_POST[$this->code.'_type'];
    }
	}

	function confirmation() {
    $genre_identifier = $this->_getActiveGenreIdentifier();
		$cctypes = $this->payone->getTypesForGenre($genre_identifier);		
		for ($i=0, $n=count($cctypes); $i<$n; $i++) {
		  if ($cctypes[$i]['typekey'] == $_SESSION[$this->code]['cardtype']) {
		    $type = $cctypes[$i]['typename'];
		  }
		}

    $confirmation = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'),
                          'fields' => array(array('title' => '',
                                                  'field' => constant($type),
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

	function after_process() {
	  global $order, $insert_id;
    
    if (!isset($insert_id) || $insert_id == '') {
		  $insert_id = $_SESSION['tmp_oID'];
		}
		
		if (!isset($_SESSION['tmp_payone_oID'])) {
      $this->payone->log("(pre-)authorizing $this->code payment");
      $standard_parameters = parent::_standard_parameters();

      $this->personal_data = new Payone_Api_Request_Parameter_Authorization_PersonalData();
      parent::_set_customers_standard_params();

      $this->delivery_data = new Payone_Api_Request_Parameter_Authorization_DeliveryData();
      parent::_set_customers_shipping_params();

      $this->payment_method = new Payone_Api_Request_Parameter_Authorization_PaymentMethod_CreditCard();
      $this->payment_method->setSuccessurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PROCESS.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setBackurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id());
      $this->payment_method->setErrorurl(((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.FILENAME_CHECKOUT_PAYMENT.'?'.xtc_session_name().'='.xtc_session_id().'&payment_error='.$this->code);
      $this->payment_method->setPseudocardpan($_SESSION[$this->code]['pseudocardpan']);

      // set order_id for deleting canceld order
      $_SESSION['tmp_payone_oID'] = $_SESSION['tmp_oID'];

      $request_parameters = parent::_request_parameters('cc');
    
      $this->params = array_merge($standard_parameters, $request_parameters);
      $this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
      parent::_build_service_authentification('cc');
      parent::_parse_response_payone_api();
    }
     
		parent::after_process();
		unset($_SESSION[$this->code]);
	}

}
?>