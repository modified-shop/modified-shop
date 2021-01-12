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
require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaAutoload.php');


class KlarnaPaymentBase extends KlarnaAutoload {

  function __construct() {

  }


  function init() {    
    $this->klarna_version = '1.01';
    
    $this->title = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE');
    $this->description = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION');
    $this->sort_order = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER') : '');
    $this->enabled = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
    $this->info = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO');
    $this->extended_description = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_VERSION').$this->klarna_version;
    
    if ($this->check() > 0) {
      $this->order_status = DEFAULT_ORDERS_STATUS_ID;
      if ((int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID') > 0) {
        $this->order_status = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID');
      }
    }
    
    KlarnaAutoload::register();
  }


  function update_status() {
    global $order;
    
    if ($this->enabled == true
        && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')
        && (int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE') > 0
        ) 
    {
      $check_flag = false;
      $check_query = xtc_db_query("SELECT zone_id 
                                     FROM ".TABLE_ZONES_TO_GEO_ZONES." 
                                    WHERE geo_zone_id = '".(int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE')."' 
                                      AND zone_country_id = '".$order->billing['country']['id']."' 
                                 ORDER BY zone_id");
      while($check = xtc_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
      }
      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
    
    if (isset($_SESSION['klarna'])) {
      if ($_SESSION['klarna']['sendto'] != $_SESSION['sendto']
          || $_SESSION['klarna']['sendto_id'] != $this->get_country_id($_SESSION['sendto'])
          || $_SESSION['klarna']['billto'] != $_SESSION['billto']
          || $_SESSION['klarna']['billto_id'] != $this->get_country_id($_SESSION['billto'])
          || ($_SESSION['klarna']['time_created'] + 3600) < time()
          )
      {
        unset($_SESSION['klarna']);
      }
    }
    
    if (!isset($_SESSION['klarna'])) {
      $this->getKlarnaSession();
    }
        
    if ($this->enabled == true
        && isset($_SESSION['klarna'])
        && array_key_exists('methods', $_SESSION['klarna'])
        && is_array($_SESSION['klarna']['methods'])
        )
    {
      $this->enabled = false;
      foreach ($_SESSION['klarna']['methods'] as $methods) {
        if ($this->klarna_code == $methods['identifier']) {
          $this->enabled = true;
          break;
        }
      }
      if ($this->enabled === false) {
        $this->logger->log('klarna', 'not available: '.$this->klarna_code, array('methods' => $_SESSION['klarna']['methods']));
      }
    } else {
      $this->enabled = false;
    }

    if ($this->enabled == true
        && isset($_SESSION['klarna'])
        && array_key_exists($this->klarna_code, $_SESSION['klarna'])
        && array_key_exists('show_form', $_SESSION['klarna'][$this->klarna_code])
        && $_SESSION['klarna'][$this->klarna_code]['show_form'] == 'false'
        )
    {
      $this->enabled = false;
    }
  }


  function javascript_validation() {
    $js = false;
    if (!isset($_SESSION['klarna'])
        || !array_key_exists($this->klarna_code, $_SESSION['klarna'])
        || !array_key_exists('payment_method', $_SESSION['klarna'][$this->klarna_code])
        || $_SESSION['klarna'][$this->klarna_code]['payment_method'] != $this->klarna_code
        )
    {
      $order_array = $this->getOrderData();
      
      $js = 'if (payment_value == "'.$this->code.'" 
                 && klarna_'.$this->klarna_code.'_result === false
                 && error == 0
                 )
             {
               Klarna.Payments.authorize({ 
                  payment_method_category: "'.$this->klarna_code.'", 
                  auto_finalize: false
                }, {
                  billing_address: 
                    '.json_encode($order_array['billing_address']).'
                  ,
                  shipping_address:
                    '.json_encode($order_array['shipping_address']).'
                  
                }, function(result) {                  
                  if (result.approved !== undefined
                      && result.approved === true
                      )
                  {
                    klarna_'.$this->klarna_code.'_result = true;
                    
                    $("#checkout_payment").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][payment_method]" value="'.$this->klarna_code.'">\');
                    $.each(result, function (key, val) {
                      $("#checkout_payment").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][\'+key+\']" value="\'+val+\'">\');
                    });
                  
                    var check = check_form_payment();
                    if (check === true) {
                      $("#checkout_payment").submit();
                    }
                  }
               });
               
               return false;
             }';
    }
    return $js;
  }


  function selection() {
    return array(
      'id' => $this->code, 
      'module' => $this->title, 
      'description' => $this->info,
    );
  }


  function payment_action() {
    return;
  }


  function pre_confirmation_check() {    
    if (isset($_POST['klarna'])) {
      $_SESSION['klarna'] = array_merge($_SESSION['klarna'], $_POST['klarna']);
            
      if (array_key_exists($this->klarna_code, $_SESSION['klarna'])
          && array_key_exists('show_form', $_SESSION['klarna'][$this->klarna_code])
          && $_SESSION['klarna'][$this->klarna_code]['show_form'] == 'false'
          )
      {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }
      return false;
    } elseif (isset($_SESSION['klarna'])
              && array_key_exists($this->klarna_code, $_SESSION['klarna'])
              && array_key_exists('authorization_token', $_SESSION['klarna'][$this->klarna_code])
              )
    {
      return false;
    }
    unset($_SESSION['klarna']);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
  }


  function confirmation() {
    return false;
  }


  function process_button() {
    if (isset($_SESSION['klarna'])
        && array_key_exists($this->klarna_code, $_SESSION['klarna'])
        && array_key_exists('finalize_required', $_SESSION['klarna'][$this->klarna_code])
        && $_SESSION['klarna'][$this->klarna_code]['finalize_required'] != false
        && (!isset($_SESSION['klarna'][$this->klarna_code]['authorization_token'])
            || $_SESSION['klarna'][$this->klarna_code]['authorization_token'] == ''
            )
        )
    {
      $js = '
        <script>
          var klarna_'.$this->klarna_code.'_result = false;
          
          window.klarnaAsyncCallback = function () {
            Klarna.Payments.init({
              client_token: "'.$_SESSION['klarna']['client_token'].'"
            });
          }
        
          window.onload = function() {
            $("#checkout_confirmation").on("submit", function(event) {
              if (klarna_'.$this->klarna_code.'_result == false) {
                event.preventDefault();
                            
                Klarna.Payments.finalize({
                  payment_method_category: "'.$this->klarna_code.'"
                },
                function(result) {
                  console.debug(result);
                                
                  $("#checkout_confirmation").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][payment_method]" value="'.$this->klarna_code.'">\');
                  $.each(result, function (key, val) {
                    $("#checkout_confirmation").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][\'+key+\']" value="\'+val+\'">\');
                  });
                
                  if (result.authorization_token !== undefined) {
                    klarna_'.$this->klarna_code.'_result = true;
                    $("#checkout_confirmation").submit();
                  } else {
                    $(location).attr("href", "'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL').'");
                  }
                });
              }
            });            
          }
        </script>
        <script src="https://x.klarnacdn.net/kp/lib/v1/api.js" async></script>';
      
      return $js;
    }
  }


  function before_process() {
    global $order;

    if (isset($_POST['klarna'])) {
      $_SESSION['klarna'] = array_merge($_SESSION['klarna'], $_POST['klarna']);  
    }
    
    if (!array_key_exists($this->klarna_code, $_SESSION['klarna'])
        || !array_key_exists('authorization_token', $_SESSION['klarna'][$this->klarna_code])
        )
    {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    
    try {
      $orders = new Klarna\Rest\Payments\Orders($this->connector, $_SESSION['klarna'][$this->klarna_code]['authorization_token']);
      $data = $orders->create($this->getOrderData());
      
      $_SESSION['klarna']['order_id'] = $data['order_id'];
      $_SESSION['klarna']['fraud_status'] = $data['fraud_status'];
    } catch (Exception $e) {
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage());
      
      unset($_SESSION['klarna']);
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    
    return false;
  }


  function before_send_order() {
    return false;
  }


  function after_process() {
    global $insert_id;
        
    $check_query = xtc_db_query("SELECT orders_status
                                   FROM ".TABLE_ORDERS." 
                                  WHERE orders_id = '".(int)$insert_id."'");
    $check = xtc_db_fetch_array($check_query);

    if (isset($_SESSION['klarna'])
        && array_key_exists('order_id', $_SESSION['klarna'])
        )
    {
      $this->updateMerchantReference($_SESSION['klarna']['order_id'], $insert_id, '');
      
      $klarna_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_KLARNA_PAYMENTS."
                                     WHERE orders_id = '".(int)$insert_id."'");
      if (xtc_db_num_rows($klarna_query) < 1) {
        $sql_data_array = array(
          'orders_id' => $insert_id,
          'klarna_order_id' => $_SESSION['klarna']['order_id'],
        );
        xtc_db_perform(TABLE_KLARNA_PAYMENTS, $sql_data_array);

        $this->update_order('Klarna Order: '.$_SESSION['klarna']['order_id'], $check['orders_status'], $insert_id);
      }
      
      if ($this->code == 'klarna_checkout') {
        $result = $this->acknowledgeOrder($_SESSION['klarna']['order_id']);
        if ($result != '') {
          $this->update_order($result, $check['orders_status'], $insert_id);
        }
      }
      
      if (constant('MODULE_PAYMENT_'.strtoupper($this->code).'_CAPTURE') == 'True') {
        $this->captureCompleteOrder($insert_id, $_SESSION['klarna']['order_id']);
      }
    }
    
    if ($check['orders_status'] != $this->order_status) {
      $this->update_order('', $this->order_status, $insert_id);
    }
        
    unset($_SESSION['klarna']);
  }


  function success() {
    return false;
  }


	function get_error() {
		$error = false;
		if (isset($_GET['payment_error']) && $_GET['payment_error'] != '') {
			$error = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
			               'error' => utf8_decode(decode_htmlentities(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE')))
			               );
		}
		
		return $error;
	}


  function check() {
    if (!isset ($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }


  function checkout_button() {  
    if ($this->enabled === true
        && $_SESSION['cart']->show_total() > 0
        && (!isset($_SESSION['allow_checkout']) || $_SESSION['allow_checkout'] == 'true')
        ) 
    {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'klarna_'.strtolower($_SESSION['language_code']).'.gif')) ? 'klarna_'.strtolower($_SESSION['language_code']).'.gif' : 'klarna_de.gif');
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="klarnacartbutton"');
        $checkout_button = '<a href="'.xtc_href_link('checkout_klarna.php', '', 'SSL').'">'.$image.'</a>';

        return $checkout_button;
      }
    }
  }


  function get_method() {
    foreach ($_SESSION['klarna']['methods'] as $methods) {
      if ($this->klarna_code == $methods['identifier']) {
        return $methods;
        break;
      }
    }
  }


  function get_klarna_order($oID) {
    $check_query = xtc_db_query("SELECT klarna_order_id
                                   FROM ".TABLE_KLARNA_PAYMENTS."
                                  WHERE orders_id = '".(int)$oID."'");
    if (xtc_db_num_rows($check_query)) {
      $check = xtc_db_fetch_array($check_query);
      return $check['klarna_order_id'];
    }
  }


  function get_order() {
    global $order;
    
    $order_backup = $order;
    
    if (isset($_SESSION['shipping'])) {
      if (!class_exists('shipping')) {
        require_once (DIR_WS_CLASSES . 'shipping.php');
      }
      $shipping_modules = new shipping($_SESSION['shipping']);
    }
    
    if (!class_exists('order')) {
      require_once (DIR_WS_CLASSES . 'order.php');
    }
    $order = new order();
    
    if (!isset($order->delivery['country']['iso_code_2']) 
        || $order->delivery['country']['iso_code_2'] == ''
        )
    {
      $delivery_zone_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_COUNTRIES."
                                            WHERE countries_id = '".(int)((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)."'");
      $delivery_zone = xtc_db_fetch_array($delivery_zone_query);

      $order->delivery['country'] = array(
        'id' => $delivery_zone['countries_id'],
        'title' => $delivery_zone['countries_name'],
        'iso_code_2' => $delivery_zone['countries_iso_code_2'],
      );
      $order->delivery['country_id'] = $delivery_zone['countries_id'];
      $order->delivery['zone_id'] = 0;
    }

    if (!class_exists('order_total')) {
      require_once (DIR_WS_CLASSES . 'order_total.php');
    }
    $order_total_modules = new order_total();
    $order->totals = $order_total_modules->process();
    
    $result = $order;
    $order = $order_backup;
    
    return $result;
  }


  function update_order($comment, $orders_status, $orders_id) {
    $order_history_data = array(
      'orders_id' => (int)$orders_id,
      'orders_status_id' => (int)$orders_status,
      'date_added' => 'now()',
      'customer_notified' => '0',
      'comments' => $comment,
    );
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $order_history_data);
    
    xtc_db_query("UPDATE ".TABLE_ORDERS."
                     SET orders_status = '".(int)$orders_status."', 
                         last_modified = now() 
                   WHERE orders_id = '".(int)$orders_id."'");
  }


  function parse_gender($language_code, $gender) {
    $gender_array = array(
      'de' => array(
        'Herr' => 'm',
        'Frau' => 'f',
      ),
      'en' => array(
        'Mr' => 'm',
        'Ms' => 'f',
        'Mrs' => 'fr',
        'Miss' => 'fs',
      ),
    );
    
    if (isset($gender_array[$language_code][$gender])) {
      return $gender_array[$language_code][$gender];
    }
    
    $gender_array[$language_code] = array_flip($gender_array[$language_code]);
    if (isset($gender_array[$language_code][$gender])) {
      return $gender_array[$language_code][$gender];
    }
  }


  function get_country_id($address_id) {
    $address_query = xtc_db_query("SELECT entry_country_id
                                     FROM ".TABLE_ADDRESS_BOOK."
                                    WHERE address_book_id = '".(int)$address_id."'");
    $address = xtc_db_fetch_array($address_query);
    
    return $address['entry_country_id'];
  }


  function xtcAddTax($price, $tax) {
    $price += $price / 100 * $tax;
    return $price;
  }
  
  
  function format_amount($amount) {
    global $xtPrice;

    $amount = round($amount, 2);
    $amount = $amount * 100;
    
    return $amount;
  }


  function install() {
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ALLOWED', '',   '6', '0', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SORT_ORDER', '0', '6', '0', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ZONE', '0',  '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_CAPTURE', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");

    if (!defined('MODULE_PAYMENT_KLARNA_MERCHANT_ID')) {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_KLARNA_MERCHANT_ID', '', '6', '0', now())");    
    }
    if (!defined('MODULE_PAYMENT_KLARNA_SHARED_SECRET')) {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_KLARNA_SHARED_SECRET', '', '6', '0', now())");    
    }
    if (!defined('MODULE_PAYMENT_KLARNA_MODE')) {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_KLARNA_MODE', 'SANDBOX', '6', '1', 'xtc_cfg_select_option(array(\'SANDBOX\', \'LIVE\'), ', now());");
    }
    if (!defined('MODULE_PAYMENT_KLARNA_AJAX_SECRET')) {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_KLARNA_AJAX_SECRET', '".md5(uniqid())."', '6', '0', now())");    
    }
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS `".TABLE_KLARNA_PAYMENTS."` (
                    `orders_id` int(11) NOT NULL,
                    `klarna_order_id` varchar(256) NOT NULL,
                    PRIMARY KEY (`orders_id`),
                    KEY `idx_klarna_order_id` (`klarna_order_id`)
                  )");
    
    $address_book_query = xtc_db_query("SELECT * 
                                          FROM ".TABLE_ADDRESS_BOOK."
                                         LIMIT 1");
    $address_book = xtc_db_fetch_array($address_book_query);
    if (!isset($address_book['account_type'])) {
      xtc_db_query("ALTER TABLE ".TABLE_ADDRESS_BOOK." ADD `account_type` INT(1) DEFAULT '0' NOT NULL");
    }
  }


  function remove() {
    $check_query = xtc_db_query("SELECT configuration_key 
                                   FROM ".TABLE_CONFIGURATION." 
                                  WHERE configuration_key LIKE 'MODULE_PAYMENT_KLARNA%_STATUS'");
    if (xtc_db_num_rows($check_query) == 1) {			
      xtc_db_query("DROP TABLE IF EXISTS ".TABLE_KLARNA_PAYMENTS);
    }

    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." 
                        WHERE configuration_key LIKE 'MODULE_PAYMENT_".strtoupper($this->code)."\_%'");
  }


  function keys() {
    return array (
      'MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS', 
      'MODULE_PAYMENT_'.strtoupper($this->code).'_ALLOWED', 
      'MODULE_PAYMENT_'.strtoupper($this->code).'_ZONE',
      'MODULE_PAYMENT_KLARNA_MERCHANT_ID',
      'MODULE_PAYMENT_KLARNA_SHARED_SECRET',
      'MODULE_PAYMENT_KLARNA_MODE',
      'MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID', 
      'MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER', 
      'MODULE_PAYMENT_'.strtoupper($this->code).'_CAPTURE',
    );
  }

}