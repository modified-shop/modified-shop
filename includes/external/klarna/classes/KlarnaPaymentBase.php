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

  var $code;
  var $title;
  var $info;
  var $description;
  var $extended_description;
  var $sort_order;
  var $enabled;
  var $order_status;
  var $_check;

  var $klarna_version;

  function __construct() {

  }


  function init() {    
    $this->klarna_version = '1.23';
    
    $this->title = defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE') ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE') : '';
    $this->description = defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION') ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION') : '';
    $this->sort_order = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER') : '');
    $this->enabled = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
    $this->info = defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO') ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO') : '';
    $this->extended_description = defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_VERSION') ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_VERSION').$this->klarna_version : '';
    
    if ($this->check() > 0) {
      $this->order_status = DEFAULT_ORDERS_STATUS_ID;
      if ((int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID') > 0) {
        $this->order_status = (int) constant('MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID');
      }
      
      if (!defined('MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID')) {
        $this->klarna_update();
      }
    }
    
    KlarnaAutoload::register();
  }


  function update_status() {
    global $order, $PHP_SELF;
    
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
          || (isset($_SESSION['klarna']['express'])
              && $_SESSION['klarna']['express'] === true
              && (!isset($_SESSION['cart']) || $_SESSION['klarna']['cart_id'] !== $_SESSION['cart']->cartID)
              )
          )
      {
        // an express session was authorized for one cart, a changed cart starts the normal flow
        unset($_SESSION['klarna']);
      }
    }
    
    if (!defined('RUN_MODE_ADMIN') 
        && !isset($_SESSION['klarna'])
        && strpos(basename($PHP_SELF), 'checkout') !== false
        && basename($PHP_SELF) != FILENAME_CHECKOUT_SUCCESS
        )
    {
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
        // log once per Klarna session, update_status() runs on every checkout page view
        if (!isset($_SESSION['klarna']['logged']) || !is_array($_SESSION['klarna']['logged'])) {
          $_SESSION['klarna']['logged'] = array();
        }
        if (!isset($_SESSION['klarna']['logged'][$this->klarna_code])) {
          $_SESSION['klarna']['logged'][$this->klarna_code] = true;
          $this->logger->log('klarna', 'not available: '.$this->klarna_code, array('methods' => $_SESSION['klarna']['methods']));
        }
      }
    } else {
      $this->enabled = false;
    }

    if ($this->enabled == true
        && isset($_SESSION['klarna'])
        && array_key_exists($this->klarna_code, $_SESSION['klarna'])
        && is_array($_SESSION['klarna'][$this->klarna_code])
        && array_key_exists('show_form', $_SESSION['klarna'][$this->klarna_code])
        && $_SESSION['klarna'][$this->klarna_code]['show_form'] == 'false'
        )
    {
      $this->enabled = false;
    }
  }


  function javascript_validation() {
    return false;
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
    if (isset($_SESSION['klarna'])) {
      $this->updateKlarnaSession($_SESSION['klarna']['session_id']);
    }
    return false;
  }


  function confirmation() {
    return false;
  }


  function process_button() {
    if (isset($_SESSION['klarna'])) {
      $order_array = $this->getOrderData();
      
      // an express session is already authorized, the order data only has to be finalized
      $express = (isset($_SESSION['klarna']['express']) && $_SESSION['klarna']['express'] === true);
      
      $data_js = '{
                  billing_address: 
                    '.json_encode($order_array['billing_address']).'
                  ,
                  shipping_address:
                    '.json_encode($order_array['shipping_address']).'
                  
                }';
      
      $result_js = '
                        $("#checkout_confirmation").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][payment_method]" value="'.$this->klarna_code.'">\');
                        $.each(finalresult, function (key, val) {
                          $("#checkout_confirmation").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][\'+key+\']" value="\'+val+\'">\');
                        });
                      
                        if (finalresult.authorization_token !== undefined) {
                          klarna_'.$this->klarna_code.'_result = true;
                          $("#checkout_confirmation").submit();
                        } else {
                          $(location).attr("href", "'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL').'");
                        }';
      
      if ($express === true) {
        $submit_js = '
                Klarna.Payments.finalize({
                  payment_method_category: "'.$this->klarna_code.'"
                }, '.$data_js.',
                function(finalresult) {'.$result_js.'
                });';
      } else {
        $submit_js = '
                Klarna.Payments.authorize({ 
                  payment_method_category: "'.$this->klarna_code.'", 
                  auto_finalize: false
                }, '.$data_js.', function(result) {
                  if (result.approved !== undefined
                      && result.approved === true
                      )
                  {
                    if (result.finalize_required === true) {
                      Klarna.Payments.finalize({
                        payment_method_category: "'.$this->klarna_code.'"
                      }, {},
                      function(finalresult) {'.$result_js.'
                      });
                    } else {
                      $("#checkout_confirmation").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][payment_method]" value="'.$this->klarna_code.'">\');
                      $.each(result, function (key, val) {
                        $("#checkout_confirmation").append(\'<input type="hidden" name="klarna['.$this->klarna_code.'][\'+key+\']" value="\'+val+\'">\');
                      });
                      
                      klarna_'.$this->klarna_code.'_result = true;
                      $("#checkout_confirmation").submit();
                    }
                  } else {
                    $(location).attr("href", "'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL').'");
                  }
               });';
      }
      
      $js = '
        <script>
          var klarna_'.$this->klarna_code.'_result = false;
          
          window.klarnaAsyncCallback = function () {
            Klarna.Payments.init({
              client_token: "'.$_SESSION['klarna']['client_token'].'"
            });
          }
        
          window.addEventListener("load", function() {
            $("#checkout_confirmation").on("submit", function(event) {
              if (klarna_'.$this->klarna_code.'_result == false) {
                event.preventDefault();
                '.$submit_js.'
              }
            });
          });
        </script>
        <script src="https://x.klarnacdn.net/kp/lib/v1/api.js" async></script>';
      
      return $js;
    }
  }


  function before_process() {
    global $order;

    if (isset($_POST['klarna']) && is_array($_POST['klarna'])) {
      // Klarna posts klarna[<category>][<key>], take over the array of this module only
      $posted = array();
      if (isset($_POST['klarna'][$this->klarna_code]) && is_array($_POST['klarna'][$this->klarna_code])) {
        $posted[$this->klarna_code] = $_POST['klarna'][$this->klarna_code];
      }
      $_SESSION['klarna'] = array_merge(((isset($_SESSION['klarna']) && is_array($_SESSION['klarna'])) ? $_SESSION['klarna'] : array()), $posted);
    }
    
    if (!isset($_SESSION['klarna'][$this->klarna_code])
        || !is_array($_SESSION['klarna'][$this->klarna_code])
        || !array_key_exists('authorization_token', $_SESSION['klarna'][$this->klarna_code])
        )
    {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
    
    try {
      $orders = new Klarna\Rest\Payments\Orders($this->connector, $_SESSION['klarna'][$this->klarna_code]['authorization_token']);
      $data = $orders->create($this->getOrderData());
      
      $_SESSION['klarna']['order_id'] = $data['order_id'];
      $_SESSION['klarna']['fraud_status'] = ((isset($data['fraud_status'])) ? strtoupper($data['fraud_status']) : '');
      
      if ($_SESSION['klarna']['fraud_status'] != 'ACCEPTED') {
        $order->info['order_status'] = $this->get_pending_status_id();
      }
    } catch (Exception $e) {
      // session and customer id let the merchant find an order Klarna created despite the timeout
      $this->logger->log('klarna', __FUNCTION__.': '.$e->getMessage(), array(
        'session_id' => ((isset($_SESSION['klarna']['session_id'])) ? $_SESSION['klarna']['session_id'] : ''),
        'customer_id' => ((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : 0),
      ));
      
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

    $order_status = $this->order_status;
    if (isset($_SESSION['klarna'])
        && array_key_exists('order_id', $_SESSION['klarna'])
        )
    {
      // an order under fraud review is captured only once Klarna accepts it
      $fraud_accepted = (isset($_SESSION['klarna']['fraud_status']) && $_SESSION['klarna']['fraud_status'] == 'ACCEPTED');
      if ($fraud_accepted === false) {
        $order_status = $this->get_pending_status_id();
      }
      
      $this->updateMerchantReference($_SESSION['klarna']['order_id'], $insert_id, '');
      
      $klarna_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_KLARNA_PAYMENTS."
                                     WHERE orders_id = '".(int)$insert_id."'");
      $row_created = false;
      if (xtc_db_num_rows($klarna_query) < 1) {
        $row_created = true;
        
        // the row gets its final state, a push or the admin resolver may act on the order from here on
        if ($fraud_accepted === true) {
          $row_status = (($this->capture_enabled()) ? 'CAPTURE_PENDING' : 'ACCEPTED');
        } else {
          $row_status = 'PENDING';
        }
        
        // before the insert nobody else can resolve the order, so the status moves first
        if ($check['orders_status'] != $order_status
            && $this->change_new_orders_status($insert_id, $check['orders_status'], $order_status, '') === true
            )
        {
          // the history entry and the acknowledge write below use the status the order has now
          $check['orders_status'] = $order_status;
        }
        
        $sql_data_array = array(
          'orders_id' => $insert_id,
          'klarna_order_id' => $_SESSION['klarna']['order_id'],
          'fraud_status' => $row_status,
          'notify_token' => ((isset($_SESSION['klarna_notify_token']) && is_string($_SESSION['klarna_notify_token'])) ? $_SESSION['klarna_notify_token'] : ''),
        );
        xtc_db_perform(TABLE_KLARNA_PAYMENTS, $sql_data_array);

        $this->insert_status_history($insert_id, $check['orders_status'], 'Klarna Order: '.$_SESSION['klarna']['order_id'].(($fraud_accepted === true) ? '' : ', fraud status: PENDING'));
      }
      
      // read again, the status change and the capture depend on the row, not on its state at insert time
      $state_query = xtc_db_query("SELECT klarna_order_id,
                                          fraud_status
                                     FROM ".TABLE_KLARNA_PAYMENTS."
                                    WHERE orders_id = '".(int)$insert_id."'");
      $state = xtc_db_fetch_array($state_query);
      if (is_array($state)
          && in_array($state['fraud_status'], array('CAPTURE_PENDING', 'ACCEPTED', 'PENDING'))
          )
      {
        // a new row got its status before the insert, a manual change since then wins
        if ($row_created === false && $check['orders_status'] != $order_status) {
          $this->change_orders_status($insert_id, $check['orders_status'], $order_status, '', $state['fraud_status']);
        }
        
        // a failed capture stays CAPTURE_PENDING, the admin page retries it
        if ($state['fraud_status'] == 'CAPTURE_PENDING') {
          $this->captureIfDue($insert_id, $state['klarna_order_id'], $order_status, null, true);
        }
      }
    } elseif ($check['orders_status'] != $order_status) {
      $this->update_order('', $order_status, $insert_id);
    }
        
    unset($_SESSION['klarna']);
    unset($_SESSION['klarna_notify_token']);
  }


  function success() {
    return false;
  }


	function get_error() {
		$error = false;
		if (isset($_GET['payment_error']) && $_GET['payment_error'] != '') {
			$error = array('title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
			               'error' => decode_utf8(decode_htmlentities(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE')))
			               );
		}
		
		return $error;
	}


  function check() {
    if (!isset ($this->_check)) {
      if (defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM ".TABLE_CONFIGURATION." 
                                      WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
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


  function get_pending_status_id() {
    if (defined('MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID')
        && (int)MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID > 0
        )
    {
      return (int)MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID;
    }
    
    return (int)DEFAULT_ORDERS_STATUS_ID;
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
    
    if (!isset($gender_array[$language_code])) {
      return;
    }
    
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
    
    return round($amount);
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
                    `fraud_status` varchar(16) NOT NULL DEFAULT '',
                    `notify_token` varchar(64) NOT NULL DEFAULT '',
                    PRIMARY KEY (`orders_id`),
                    KEY `idx_klarna_order_id` (`klarna_order_id`)
                  )");
    
    $this->klarna_update();
  }


  function klarna_update() {
    // the columns come first, so a failed ALTER is retried while the keys are still missing
    $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_KLARNA_PAYMENTS."'");
    if (xtc_db_num_rows($check_query) > 0) {
      $column_array = array(
        'fraud_status' => "varchar(16) NOT NULL DEFAULT ''",
        'notify_token' => "varchar(64) NOT NULL DEFAULT ''",
      );
      foreach ($column_array as $column_name => $column_definition) {
        $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_KLARNA_PAYMENTS." LIKE '".$column_name."'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("ALTER TABLE ".TABLE_KLARNA_PAYMENTS." ADD `".$column_name."` ".$column_definition);
          
          $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_KLARNA_PAYMENTS." LIKE '".$column_name."'");
          if (xtc_db_num_rows($check_query) < 1) {
            return;
          }
        }
      }
    }
    
    $config_array = array(
      'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID',
      'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID',
    );
    foreach ($config_array as $config_key) {
      $check_query = xtc_db_query("SELECT configuration_key
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = '".$config_key."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('".$config_key."', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      }
      defined($config_key) or define($config_key, '0');
    }
  }


  function remove() {
    // keep TABLE_KLARNA_PAYMENTS, it holds the Klarna order id of past orders
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
      'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID',
      'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID',
      'MODULE_PAYMENT_'.strtoupper($this->code).'_ORDER_STATUS_ID', 
      'MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER', 
      'MODULE_PAYMENT_'.strtoupper($this->code).'_CAPTURE',
    );
  }

}