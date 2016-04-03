<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalAuth.php');


// used classes
use PayPal\Api\FlowConfig; 
use PayPal\Api\Presentation; 
use PayPal\Api\WebProfile; 
use PayPal\Api\InputFields; 


class PayPalCommon extends PayPalAuth {


  function __construct() {

  }
  
  
  function link_encoding($string) {
    $string = str_replace('&amp;', '&', $string);
    
    return $string;
  }
  
  
  function encode_utf8($string) {
    if (is_array($string)) {
      foreach ($string as $key => $value) {
        $string[$key] = $this->encode_utf8($value);
      }
    } else {
      $string = decode_htmlentities($string);
      $string = utf8_encode($string);
    }
    
    return $string;
  }

  
  function decode_utf8($string) {   
    if (is_array($string)) {
      foreach ($string as $key => $value) {
        $string[$key] = $this->decode_utf8($value);
      }
    } else {
      $string = decode_utf8($string);
    }
    
    return $string;
  }
  
  
  function save_config($sql_data_array) {
    if (is_array($sql_data_array) && count($sql_data_array) > 0) {
      foreach ($sql_data_array as $sql_data) {        
        $this->delete_config($sql_data['config_key']);
        xtc_db_perform(TABLE_PAYPAL_CONFIG, $sql_data);
      }
    }
  }


  function delete_config($config_key) {
    xtc_db_query("DELETE FROM ".TABLE_PAYPAL_CONFIG." WHERE config_key = '".xtc_db_input($config_key)."'");
  }


  function get_config($config_key) {
    $config_query = xtc_db_query("SELECT config_value 
                                    FROM ".TABLE_PAYPAL_CONFIG." 
                                   WHERE config_key = '".xtc_db_input($config_key)."'");
    $config = xtc_db_fetch_array($config_query);
    
    return $config['config_value'];
  }


  function get_totals($totals, $calc_total = false) {
        
    for ($i = 0, $n = sizeof($totals); $i < $n; $i ++) {
      switch(((isset($totals[$i]['code'])) ? $totals[$i]['code'] : $totals[$i]['class'])) {
        case 'ot_subtotal_no_tax':
          break;
        case 'ot_subtotal':
          $this->details->setSubtotal($this->details->getSubtotal() + $totals[$i]['value']);
          break;
        case 'ot_total':
          $this->amount->setTotal($totals[$i]['value']);
          break;
        case 'ot_shipping':
          $this->details->setShipping($totals[$i]['value']);
          break;
        case 'ot_tax':
          if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 
              && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1
              ) 
          {
            $this->details->setTax($this->details->getTax() + $totals[$i]['value']);
          }
          break;
        /*           
        case 'ot_discount':
        case 'ot_bonus_fee':
        case 'ot_coupon':
        case 'ot_gv':
          $this->details->setShippingDiscount($this->details->getShippingDiscount() + (($totals[$i]['value'] > 0) ? $totals[$i]['value'] : $totals[$i]['value'] * (-1)));
          break;
        case 'ot_ps_fee':
        case 'ot_cod_fee':
        case 'ot_loworderfee':
          $this->details->setHandlingFee($this->details->getHandlingFee() + $totals[$i]['value']);
          break;
        */
        default:
          if($totals[$i]['value'] < 0) {
            $this->details->setShippingDiscount($this->details->getShippingDiscount() + $totals[$i]['value']);
          } else {
            $this->details->setHandlingFee($this->details->getHandlingFee() + $totals[$i]['value']);
          }
          break;
      }
    }

    if ($calc_total === true) {
      $total = 0;
      $total += $this->details->getSubtotal();
      $total += $this->details->getShipping();
      $total += $this->details->getTax();
      $total += $this->details->getHandlingFee();
      $total += $this->details->getShippingDiscount();
      $total += $this->details->getInsurance();
      $total += $this->details->getGiftWrap();
      $total += $this->details->getFee();
  
      $this->amount->setTotal($total);
    }
  }


  function fix_totals($totals) {
          
    for ($i = 0, $n = sizeof($totals); $i < $n; $i ++) {
      switch(((isset($totals[$i]['code'])) ? $totals[$i]['code'] : $totals[$i]['class'])) {
        case 'ot_tax':
          $this->details->setTax($this->details->getTax() + $totals[$i]['value']);
          $this->amount->setTotal($this->amount->getTotal() + $totals[$i]['value']);
          break;            
      }
    }
  }


  function get_shipping_cost() {
    global $order, $PHP_SELF;
    
    $shipping_cost = $order->info['shipping_cost'];
    
    if ($shipping_cost > 0) {
      if (basename($PHP_SELF) == FILENAME_CHECKOUT_PAYMENT) {
        $shipping_modul = explode('_',$order->info['shipping_class']);
        $shipping_tax_class = constant('MODULE_SHIPPING_'.strtoupper($shipping_modul[0]).'_TAX_CLASS');
        $shipping_tax_rate = xtc_get_tax_rate($shipping_tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        $shipping_cost = $order->info['shipping_cost'] * (1 + ($shipping_tax_rate / 100));
      }
    }
    return $shipping_cost;
  }


  function get_profile($id) {
  
    // auth
    $apiContext = $this->apiContext();
  
    // set WebProfile
    $webProfile = new WebProfile();
      
    try {
      $webProfileList = $webProfile->get($id, $apiContext);
      $valid = true;
    } catch (Exception $ex) {
      $this->LoggingManager->log(print_r($ex, true), 'DEBUG');
      $valid = false;
    }
  
    // set array
    $list_array = array();
  
    if ($valid === true) {      
      $profile = $webProfileList;        
      $flowConfig = $profile->getFlowConfig();
      $inputFields = $profile->getInputFields();
      $presentation = $profile->getPresentation();
    
      $list_array[] = array(
        'id' => $profile->getId(),
        'name' => $profile->getName(),
        'status' => (($this->get_config('PAYPAL_STANDARD_PROFILE') == $profile->getId()) ? true : false),
        'flow_config' => array(
          'landing_page_type' => ((is_object($flowConfig)) ? $flowConfig->getLandingPageType() : ''),
        ),
        'input_fields' => array(
          'allow_note' => ((is_object($inputFields)) ? $inputFields->getAllowNote() : ''),
          'no_shipping' => ((is_object($inputFields)) ? $inputFields->getNoShipping() : ''),
          'address_override' => ((is_object($inputFields)) ? $inputFields->getAddressOverride() : ''),
        ),
        'presentation' => array(
          'brand_name' => ((is_object($presentation)) ? $presentation->getBrandName() : ''),
          'logo_image' => ((is_object($presentation)) ? $presentation->getLogoImage() : ''),
          'locale_code' => ((is_object($presentation)) ? $presentation->getLocaleCode() : ''),
        ),
      );
    }
      
    return $list_array;    
  }


  function login_customer($customer) {
    global $econda;
    
    // include needed function
    require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');

    // check if customer exists
    $check_customer_query = xtc_db_query("SELECT customers_id, 
                                                 customers_vat_id, 
                                                 customers_firstname,
                                                 customers_lastname, 
                                                 customers_gender, 
                                                 customers_password, 
                                                 customers_email_address, 
                                                 customers_default_address_id
                                            FROM ".TABLE_CUSTOMERS." 
                                           WHERE customers_email_address = '".xtc_db_input($customer['info']['email_address'])."' 
                                             AND account_type = '0'");
    if (xtc_db_num_rows($check_customer_query) < 1) {
      $this->create_account($customer);
    } else {
      if (SESSION_RECREATE == 'True') {
        xtc_session_recreate();
      }
      $check_customer = xtc_db_fetch_array($check_customer_query);
 
 			$check_country_query = xtc_db_query("SELECT entry_country_id, 
			                                            entry_zone_id 
			                                       FROM ".TABLE_ADDRESS_BOOK." 
			                                      WHERE customers_id = '".(int) $check_customer['customers_id']."' 
			                                        AND address_book_id = '".$check_customer['customers_default_address_id']."'");
			$check_country = xtc_db_fetch_array($check_country_query);

			$_SESSION['customer_gender'] = $check_customer['customers_gender'];
			$_SESSION['customer_first_name'] = $check_customer['customers_firstname'];
			$_SESSION['customer_last_name'] = $check_customer['customers_lastname'];
			$_SESSION['customer_email_address'] = $check_customer['customers_email_address'];
			$_SESSION['customer_id'] = $check_customer['customers_id'];
			$_SESSION['customer_vat_id'] = $check_customer['customers_vat_id'];
			$_SESSION['customer_default_address_id'] = $check_customer['customers_default_address_id'];
			$_SESSION['customer_country_id'] = $check_country['entry_country_id'];
			$_SESSION['customer_zone_id'] = $check_country['entry_zone_id'];

			xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO." 
			                 SET customers_info_date_of_last_logon = now(), 
			                     customers_info_number_of_logons = customers_info_number_of_logons+1 
			               WHERE customers_info_id = '".(int) $_SESSION['customer_id']."'");
			xtc_write_user_info((int) $_SESSION['customer_id']);

			// restore cart contents
			$_SESSION['cart']->restore_contents();

			// restore wishlist contents
			if (isset($_SESSION['wishlist'])
			    && is_object($_SESSION['wishlist'])
			    )
			{
			  $_SESSION['wishlist']->restore_contents();
			}
			
			if (isset($econda) && is_object($econda)) {
			  $econda->_loginUser();			
      }
      if ($_SESSION['old_customers_basket'] === true) {
        unset($_SESSION['old_customers_basket']);
        unset($_SESSION['paypal']);
        xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'info_message_3='.strtolower('TEXT_SAVED_BASKET')),'NONSSL'); 
      }
    }
     
  }
  
  
  function create_account($customer) {
        
    // include needed function
    require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
    require_once (DIR_FS_INC.'xtc_create_password.inc.php');
    require_once (DIR_FS_INC.'generate_customers_cid.inc.php');

    $password = xtc_create_password(8);
    
    $sql_data_array = array('customers_cid' => generate_customers_cid(true),
                            'customers_status' => DEFAULT_CUSTOMERS_STATUS_ID,
                            'customers_firstname' => $customer['customers']['customers_firstname'],
                            'customers_lastname' => $customer['customers']['customers_lastname'],
                            'customers_email_address' => $customer['info']['email_address'],
                            'customers_telephone' => $customer['info']['telephone'],
                            'customers_password' => xtc_encrypt_password($password),
                            'customers_date_added' => 'now()',
                            'customers_last_modified' => 'now()',
                            'password_request_time' => 'now()',
                            );

    if (ACCOUNT_GENDER == 'true') {
      $sql_data_array['customers_gender'] = $customer['info']['gender'];
    }
    if (ACCOUNT_DOB == 'true') {
      $sql_data_array['customers_dob'] = xtc_date_raw($customer['info']['dob']);
    }
    xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);

    $customer_id = xtc_db_insert_id();
    
    $data = $customer['customers'];
    $data['gender'] = $customer['info']['gender'];
    
    $address_id = $this->create_address_book($customer_id, $data);
    
    xtc_db_query("UPDATE " . TABLE_CUSTOMERS . " 
                     SET customers_default_address_id = '" . (int)$address_id . "' 
                   WHERE customers_id = '" . (int)$customer_id . "'");
    
    $sql_data_array = array('customers_info_id' => (int)$customer_id,
                            'customers_info_number_of_logons' => '1',
                            'customers_info_date_account_created' => 'now()');
    xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);
    
    // send password with order mail
    $_SESSION['paypal_express_new_customer'] = 'true';
    
    // login
    $this->login_customer($customer);
  }


  function create_address_book($customer_id, $data, $shipping = false) {
    
    $type = 'customers';
    if ($shipping === true) {
      $type = 'delivery';
    }
    
    $sql_data_array = array('customers_id' => $customer_id,
                            'entry_firstname' => $data[$type.'_firstname'],
                            'entry_lastname' => $data[$type.'_lastname'],
                            'entry_street_address' => $data[$type.'_street_address'],
                            'entry_postcode' => $data[$type.'_postcode'],
                            'entry_city' => $data[$type.'_city'],
                            'entry_country_id' => $data[$type.'_country_id'],
                            'address_date_added' => 'now()',
                            'address_last_modified' => 'now()'
                            );

    if (ACCOUNT_GENDER == 'true' && isset($data['gender'])) {
      $sql_data_array['entry_gender'] = $data['gender'];
    }
    if (ACCOUNT_COMPANY == 'true') {
      $sql_data_array['entry_company'] = $data[$type.'_company'];
    }
    if (ACCOUNT_SUBURB == 'true') {
      $sql_data_array['entry_suburb'] = $data[$type.'_suburb'];
    }
    if (ACCOUNT_STATE == 'true') {
      $sql_data_array['entry_zone_id'] = $data[$type.'_zone_id'];
      $sql_data_array['entry_state'] = $data[$type.'_state'];
    }
        
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

    $address_id = xtc_db_insert_id();
    
    return $address_id;
  }
  

  function get_shipping_address($customer_id, $data) {
    
    $where = '';
    if (ACCOUNT_COMPANY == 'true') {
      $where .= " AND entry_company = '".$data['delivery_company']."'";
    }
    if (ACCOUNT_SUBURB == 'true') {
      $where .= " AND entry_suburb = '".$data['delivery_suburb']."'";
    }
    if (ACCOUNT_STATE == 'true') {
      $where .= " AND entry_zone_id = '".$data['delivery_zone_id']."'";
      $where .= " AND entry_state = '".$data['delivery_state']."'";
    }

    $check_address_query = xtc_db_query("SELECT address_book_id
                                           FROM ".TABLE_ADDRESS_BOOK."
                                          WHERE customers_id = '".$customer_id."'
                                                ".$where."
                                            AND entry_firstname = '".xtc_db_input($data['delivery_firstname'])."'
                                            AND entry_lastname = '".xtc_db_input($data['delivery_lastname'])."'
                                            AND entry_street_address = '".xtc_db_input($data['delivery_street_address'])."'
                                            AND entry_postcode = '".xtc_db_input($data['delivery_postcode'])."'
                                            AND entry_city = '".xtc_db_input($data['delivery_city'])."'
                                            AND entry_lastname = '".xtc_db_input($data['delivery_lastname'])."'
                                            AND entry_lastname = '".xtc_db_input($data['delivery_lastname'])."'");
    if (xtc_db_num_rows($check_address_query) == 1) {
      $check_address = xtc_db_fetch_array($check_address_query);
      $address_id = $check_address['address_book_id'];
    } else {
      $address_id = $this->create_address_book($customer_id, $data, true);
    }
        
    return $address_id;
  }
  
  
}
?>