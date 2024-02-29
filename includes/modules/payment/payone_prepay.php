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

class payone_prepay extends PayonePayment {

	var $payone_genre = 'accountbased';

  var $code;
  var $form_action_url;

  var $payone;
  var $personal_data;
  var $delivery_data;
  var $payment_method;
  var $params;
  var $builder;

	function __construct() {
		$this->code = 'payone_prepay';
		parent::__construct();
		$this->form_action_url = '';
	}

	function selection() {
		if ($this->pg_config['types']['prepay']['active'] == 'true') {
			$selection = parent::selection();
		} else {
			$selection = false;
		}
		return $selection;
	}

	function pre_confirmation_check() {
		parent::pre_confirmation_check();
	}

	function confirmation() {
		$confirmation = array(
			'title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE'),
		);
		return $confirmation;
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

		$this->payment_method = array();
		
    $request_parameters = parent::_request_parameters('vor');
    unset($request_parameters['payment']);

		$this->params = array_merge($standard_parameters, $request_parameters);
		$this->builder = new Payone_Builder($this->payone->getPayoneConfig());
    
    parent::_build_service_authentification('vor');
    parent::_parse_response_payone_api();

		xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
	}

	function after_process() {
		parent::after_process();
	}
}
