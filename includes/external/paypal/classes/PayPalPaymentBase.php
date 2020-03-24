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
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalCommon.php');


class PayPalPaymentBase extends PayPalCommon {


  function __construct() {

  }


  function init($class) {
    global $order;

    $this->code = $class;
    $this->paypal_version = '1.41';

    $this->admin_access_array = array(
      'paypal_info',
      'paypal_config',
      'paypal_module',
      'paypal_profile',
      'paypal_webhook',
    );

    $this->title = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_TITLE') : '');
    if (defined('DIR_WS_INSTALLER') && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ADMIN_TITLE')) {
      $this->title = constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ADMIN_TITLE');
    }
    $this->info = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_INFO') : '');
    $this->description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_DESCRIPTION').((defined('RUN_MODE_ADMIN') && defined('MODULE_PAYMENT_'.strtoupper($this->code).'_LP')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_LP') : '') : '');
    $this->extended_description = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_EXTENDED_DESCRIPTION')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_EXTENDED_DESCRIPTION') : '');
  
    $this->sort_order = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER')) ? constant('MODULE_PAYMENT_'.strtoupper($this->code).'_SORT_ORDER') : '');
    $this->enabled = ((defined('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') && constant('MODULE_PAYMENT_'.strtoupper($this->code).'_STATUS') == 'True') ? true : false);
  
    if ($this->check_install() === true) {
      $this->order_status_success = (($this->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_rejected = (($this->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_pending = (($this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_capture = (($this->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_CAPTURED_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->order_status_tmp = (($this->get_config('PAYPAL_ORDER_STATUS_TMP_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_TMP_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->tmpStatus = $this->order_status_tmp;
      $this->tmpOrders = true;
      $this->loglevel = $this->get_config('PAYPAL_LOG_LEVEL');
  
      $payment_sale = array(
        'paypalplus',
        'paypalpluslink',
        'paypalinstallment',
      );
      $this->transaction_type = $this->get_config('PAYPAL_TRANSACTION_TYPE');
      if (in_array($this->code, $payment_sale)) {
        $this->transaction_type = 'sale';
      }
    }
  
    if (is_object($order) && !defined('RUN_MODE_ADMIN')) {
      $this->update_status();
    }
    
    if ($this->check_install() && version_compare($this->paypal_version, $this->get_config('PAYPAL_VERSION'), '>')) {
      $this->paypal_update();
    }
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
    return false;
  }


  function confirmation() {
    return false;
  }


  function process_button() {
    return false;
  }


  function before_process() {
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
  
    if ($check['orders_status'] != $this->order_status_pending) {
      $this->update_order('', $this->order_status_pending, $insert_id);    
    }
    unset($_SESSION['paypal']);
  }


  function success() {
    global $last_order;
  
    if (!isset($last_order) || $last_order == '') {
      return;
    }
    
    return $this->get_payment_instructions($last_order);
  }


  function get_payment_instructions($orders_id) {
    $payment = $this->get_order_details($orders_id);
  
    if (isset($payment['instruction'])) {
       
      $fields = array(
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_AMOUNT,
          'field' => $payment['instruction']['amount']['total'].' '.$payment['instruction']['amount']['currency'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_REFERENCE,
          'field' => $payment['instruction']['reference'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_PAYDATE,
          'field' => $payment['instruction']['date'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT,
          'field' => $payment['instruction']['bank']['name'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_HOLDER,
          'field' => $payment['instruction']['bank']['holder'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_IBAN,
          'field' => $payment['instruction']['bank']['iban'],
        ),
        array(
          'title' => TEXT_PAYPAL_INSTRUCTIONS_BIC,
          'field' => $payment['instruction']['bank']['bic'],
        ),
      );
      
      $title = sprintf(TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT, $payment['instruction']['amount']['total'].' '.$payment['instruction']['amount']['currency'], $payment['instruction']['date']);
      if ($fields[2]['field'] == '') {
        unset($fields[2]);
        $fields = array_values($fields);
        $title = sprintf(TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT_SHORT, $payment['instruction']['amount']['total'].' '.$payment['instruction']['amount']['currency']);
      }
      
      $success = array(
        array ('title' => $title,
               'class' => $this->code,
               'fields' => $fields
               ),
        );
  
      return $success;
    }
  }


  function save_payment_instructions($orders_id) {
    $payment = $this->get_order_details($orders_id);
  
    if (isset($payment['instruction'])) {
      
      $sql_data_array = array(
        'orders_id' => $orders_id,
        'amount' => $payment['instruction']['amount']['total'],
        'currency' => $payment['instruction']['amount']['currency'],
        'reference' => $payment['instruction']['reference'],
        'date' => date('Y-m-d', strtotime($payment['instruction']['date'])),
        'name' => $payment['instruction']['bank']['name'],
        'holder' => $payment['instruction']['bank']['holder'],
        'iban' => $payment['instruction']['bank']['iban'],
        'bic' => $payment['instruction']['bank']['bic'],
      );
    
      xtc_db_perform(TABLE_PAYPAL_INSTRUCTIONS, $sql_data_array);
    }
  }
  
  
  function admin_order($oID) {
    return false;
  }


  function get_error() {
    $error = false;
    if (isset($_GET['payment_error']) && $_GET['payment_error'] != '') {
      $error = array(
        'title' => constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_HEADING'),
        'error' => decode_htmlentities(constant('MODULE_PAYMENT_'.strtoupper($this->code).'_TEXT_ERROR_MESSAGE'))
      );
    }
    return $error;
  }


  function output_error() {
    return false;
  }


  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM ".TABLE_CONFIGURATION." 
                                    WHERE configuration_key = 'MODULE_PAYMENT_".strtoupper($this->code)."_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }


  function check_install() {
    if (!isset($this->_check_install)) {
      $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_PAYPAL_CONFIG."'");
      if (xtc_db_num_rows($check_query) > 0) {
        $this->_check_install = true;
      } else {
        $this->_check_install = false;
      }
    }
    return $this->_check_install;
  }
    
  
  function checkout_button() {
    global $PHP_SELF;
  
    if ($this->enabled === true
        && $_SESSION['cart']->show_total() > 0
        && (!isset($_SESSION['allow_checkout']) || $_SESSION['allow_checkout'] == 'true')
        ) 
    {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'epaypal_'.strtolower($_SESSION['language_code']).'.gif')) ? 'epaypal_'.strtolower($_SESSION['language_code']).'.gif' : 'epaypal_en.gif');
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="paypalcartbutton"');
        $checkout_button = '<a href="'.xtc_href_link(basename($PHP_SELF), 'action=paypal_cart_checkout').'">'.$image.'</a>';

        return $checkout_button;
      }
    }
  }


  function product_checkout_button() {    
    if ($this->enabled === true) {
      $unallowed_modules = explode(',', $_SESSION['customers_status']['customers_status_payment_unallowed']);
      if (!in_array($this->code, $unallowed_modules)) {
        $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'epaypal_'.strtolower($_SESSION['language_code']).'.gif')) ? 'epaypal_'.strtolower($_SESSION['language_code']).'.gif' : 'epaypal_en.gif');
        $checkout_button = xtc_image_submit(DIR_WS_BASE.DIR_WS_ICONS.$image, IMAGE_BUTTON_IN_CART, 'id="paypalcartexpress" name="paypalcartexpress"');

        return $checkout_button;
      }
    }
  }


  function create_paypal_link($orders_id = '', $cleanlink = false) {
    global $last_order, $PHP_SELF;
  
    if ($orders_id == '') {
      $orders_id = $last_order;
    }
      
    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_PAYPAL_PAYMENT."
                                  WHERE orders_id = '".(int)$orders_id."'");
  
    if (xtc_db_num_rows($check_query) < 1) {
      require_once (DIR_WS_CLASSES . 'order.php');
      $order = new order($orders_id);
      $hash = md5($order->customer['email_address']);
      if (defined('RUN_MODE_ADMIN')) {
        $link = xtc_catalog_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$orders_id.'&key='.$hash, 'SSL');
      } else {
        $link = xtc_href_link('callback/paypal/'.$this->code.'.php', 'oID='.$orders_id.'&key='.$hash, 'SSL');
      }
    
      if ($cleanlink === true) {
        return $link;
      }
      
      $image = ((is_file(DIR_FS_CATALOG.DIR_WS_ICONS.'epaypal_'.strtolower($_SESSION['language_code']).'.gif')) ? 'epaypal_'.strtolower($_SESSION['language_code']).'.gif' : 'epaypal_en.gif');
      if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) {
        $image = xtc_image_button(DIR_WS_ICONS.$image, '', 'id="paypalcartbutton"');
      } else {
        $image = '<img src="'.((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG.DIR_WS_ICONS.$image.'" id="paypalcartbutton" />';
      }
      $checkout_button = '<a href="'.$link.'">'.$image.'</a>';

      return $checkout_button;
    }
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


  function remove_order($orders_id) {

    $check_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ORDERS." 
                                  WHERE orders_id = '".(int)$orders_id."'");
    if (xtc_db_num_rows($check_query) > 0) {
      $check = xtc_db_fetch_array($check_query);
      if ($_SESSION['customer_id'] == $check['customers_id']) {
        require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
        xtc_remove_order((int)$orders_id, ((STOCK_LIMITED == 'true') ? 'on' : false));
      }
    }
  }


  function install() {

    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_STATUS', 'True', '6', '1', NULL, now(), '', 'xtc_cfg_select_option(array(\'True\', \'False\'),' )");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_SORT_ORDER', '0', '6', '2', NULL, now(), '', '')");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ALLOWED', '', '6', '3', NULL, now(), '', '')");
    
    if ($this->code != 'paypalinstallment') {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_".strtoupper($this->code)."_ZONE', '0', '6', '4', NULL, now(), 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(')");
    }
    
    if (!defined('MODULE_PAYMENT_PAYPAL_SECRET')) {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPAL_SECRET', '".md5(uniqid())."', '6', '3', NULL, now(), '', '')");
    }
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_PAYMENT." ( 
                    paypal_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL default '0', 
                    payment_id varchar(64) NOT NULL default '', 
                    payer_id varchar(64) NOT NULL default '', 
                    transaction_id varchar(64) NOT NULL default '', 
                    PRIMARY KEY (paypal_id), 
                    KEY idx_orders_id (orders_id),
                    KEY idx_payment_id (payment_id)
                  );");
  
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_CONFIG." (
                    config_id int(11) NOT NULL auto_increment, 
                    config_key varchar(128) NOT NULL,
                    config_value text NOT NULL,
                    PRIMARY KEY (config_id), 
                    KEY idx_config_key (config_key)
                  );");

    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_IPN." (
                    paypal_ipn_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL,
                    transaction_id varchar(64) NOT NULL default '',
                    payment_status varchar(64) NOT NULL default '',
                    PRIMARY KEY (paypal_ipn_id), 
                    KEY idx_orders_id (orders_id)
                  );");

    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_INSTRUCTIONS." (
                    paypal_inctructions_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL DEFAULT '0',
                    amount decimal(15,4) DEFAULT NULL,
                    currency varchar(8) DEFAULT NULL,
                    reference varchar(128) DEFAULT NULL,
                    date date DEFAULT NULL,
                    name varchar(128) DEFAULT NULL,
                    holder varchar(128) DEFAULT NULL,
                    iban varchar(34) DEFAULT NULL,
                    bic varchar(11) DEFAULT NULL,
                    PRIMARY KEY (paypal_inctructions_id),
                    KEY idx_orders_id (orders_id)
                  );");
  
    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    foreach ($this->admin_access_array as $admin_access) {
      if (!isset($admin[$admin_access])) {
        xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `".$admin_access."` INT(1) DEFAULT '0' NOT NULL");
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '9' WHERE customers_id = 'groups' LIMIT 1");        
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '1' LIMIT 1");        
        
        if (defined('RUN_MODE_ADMIN') && $_SESSION['customer_id'] > 1) {
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
        }
      }
    }
  
    $status_query = xtc_db_query("SELECT *
                                    FROM ".TABLE_ORDERS_STATUS."
                                   LIMIT 1");
    $status = xtc_db_fetch_array($status_query);
    if (!isset($status['sort_order'])) {
      xtc_db_query("ALTER TABLE ".TABLE_ORDERS_STATUS." ADD `sort_order` int(11) NOT NULL DEFAULT '0'");
    }
  
    // check tabs
    if ($this->code == 'paypalplus') {
      if ($this->get_config('MODULE_PAYMENT_PAYPALPLUS_USE_TABS') == '') {
        $sql_data_array = array(
          'config_key' => 'MODULE_PAYMENT_PAYPALPLUS_USE_TABS',
          'config_value' => '1'
        );
        xtc_db_perform(TABLE_PAYPAL_CONFIG, $sql_data_array);
      }
    }

    // check express button
    if ($this->code == 'paypalcart') {
      if ($this->get_config('MODULE_PAYMENT_PAYPALCART_SHOW_PRODUCT') == '') {
        $sql_data_array = array(
          'config_key' => 'MODULE_PAYMENT_PAYPALCART_SHOW_PRODUCT',
          'config_value' => '1'
        );
        xtc_db_perform(TABLE_PAYPAL_CONFIG, $sql_data_array);
      }
    }
  }


  function remove() {
    $check_query = xtc_db_query("SELECT configuration_key 
                                   FROM ".TABLE_CONFIGURATION." 
                                  WHERE configuration_key LIKE 'MODULE_PAYMENT_PAYPAL%_STATUS'");
    if (xtc_db_num_rows($check_query) == 1) {			
      xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_PAYMENT);
      xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_CONFIG);
      xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_IPN);
      xtc_db_query("DROP TABLE IF EXISTS ".TABLE_PAYPAL_INSTRUCTIONS);


  
      $admin_query = xtc_db_query("SELECT * 
                                     FROM ".TABLE_ADMIN_ACCESS."
                                    LIMIT 1");
      $admin = xtc_db_fetch_array($admin_query);
      foreach ($this->admin_access_array as $admin_access) {
        if ($admin_access != 'paypal_info' 
            && $admin_access != 'paypal_module' 
            && isset($admin[$admin_access])
            )
        {
          xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP COLUMN `".$admin_access."`");
        }
      }
    }

    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_PAYMENT_".strtoupper($this->code)."\_%'");
  }


  function status_install($stati = '') {

    // install order status
    if (!is_array($stati) 
        || (is_array($stati) && count($stati) < 1)
        )
    {
      $stati = array(
        'PAYPAL_INST_ORDER_STATUS_TMP_NAME' => 'PAYPAL_ORDER_STATUS_TMP_ID',
        'PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME' => 'PAYPAL_ORDER_STATUS_SUCCESS_ID',
        'PAYPAL_INST_ORDER_STATUS_PENDING_NAME' => 'PAYPAL_ORDER_STATUS_PENDING_ID',
        'PAYPAL_INST_ORDER_STATUS_CAPTURED_NAME' => 'PAYPAL_ORDER_STATUS_CAPTURED_ID',
        'PAYPAL_INST_ORDER_STATUS_REFUNDED_NAME' => 'PAYPAL_ORDER_STATUS_REFUNDED_ID',
        'PAYPAL_INST_ORDER_STATUS_REJECTED_NAME' => 'PAYPAL_ORDER_STATUS_REJECTED_ID',
      );
    }
    
    foreach($stati as $statusname => $statusid) {
      $languages_query = xtc_db_query("SELECT * 
                                         FROM " . TABLE_LANGUAGES . " 
                                     ORDER BY sort_order");
      while($languages = xtc_db_fetch_array($languages_query)) {
        if (file_exists(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal_config.php')) {
          include(DIR_FS_LANGUAGES.$languages['directory'].'/admin/paypal_config.php');
        }
        if (${$statusname} != '') {
          $check_query = xtc_db_query("SELECT orders_status_id 
                                         FROM " . TABLE_ORDERS_STATUS . " 
                                        WHERE orders_status_name = '" .xtc_db_input(${$statusname}). "' 
                                          AND language_id = '".(int)$languages['languages_id']."' 
                                        LIMIT 1");
          $status = xtc_db_fetch_array($check_query);
          if (xtc_db_num_rows($check_query) < 1 || (${$statusid} && $status['orders_status_id'] != ${$statusid}) ) {
            if (!${$statusid}) {
              $status_query = xtc_db_query("SELECT max(orders_status_id) as status_id FROM " . TABLE_ORDERS_STATUS);
              $status = xtc_db_fetch_array($status_query);
              ${$statusid} = $status['status_id'] + 1;
            }
            $check_query = xtc_db_query("SELECT orders_status_id 
                                           FROM " . TABLE_ORDERS_STATUS . " 
                                          WHERE orders_status_id = '".(int)${$statusid} ."' 
                                            AND language_id='".(int)$languages['languages_id']."'");
            if (xtc_db_num_rows($check_query) < 1) {
              $sql_data_array = array(
                'orders_status_id' => (int)${$statusid},
                'language_id' => (int)$languages['languages_id'],
                'orders_status_name' => ${$statusname},
              );
              xtc_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
              $sql_data_array = array(
                array(
                  'config_key' => $statusid,
                  'config_value' => (int)${$statusid},
                )
              );
              $this->save_config($sql_data_array);
            }
          } else {
            ${$statusid} = $status['orders_status_id'];
          }
        }
      }
    }
  }
  
  
  function paypal_update() {
    $table_array = array(
      array('column' => 'transaction_id', 'default' => "varchar(64) NOT NULL default ''"),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_PAYMENT." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_PAYMENT." ADD ".$table['column']." ".$table['default']."");
      }
    }
    
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_PAYPAL_INSTRUCTIONS." (
                    paypal_inctructions_id int(11) NOT NULL auto_increment, 
                    orders_id int(11) NOT NULL DEFAULT '0',
                    amount decimal(15,4) DEFAULT NULL,
                    currency varchar(8) DEFAULT NULL,
                    reference varchar(128) DEFAULT NULL,
                    date date DEFAULT NULL,
                    name varchar(128) DEFAULT NULL,
                    holder varchar(128) DEFAULT NULL,
                    iban varchar(34) DEFAULT NULL,
                    bic varchar(11) DEFAULT NULL,
                    PRIMARY KEY (paypal_inctructions_id),
                    KEY idx_orders_id (orders_id)
                  );");
    
    // add new column
    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    foreach ($this->admin_access_array as $admin_access) {
      if (!isset($admin[$admin_access])) {
        xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `".$admin_access."` INT(1) DEFAULT '0' NOT NULL");
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '9' WHERE customers_id = 'groups' LIMIT 1");        
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '1' LIMIT 1");        
        if (defined('RUN_MODE_ADMIN') && $_SESSION['customer_id'] > 1) {
          xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET ".$admin_access." = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
        }
      }
    }

    // drop old column
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ADMIN_ACCESS." LIKE 'paypal_payment'");
    if (xtc_db_num_rows($check_query) == 1) {
      xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP `paypal_payment`");
    }
    
    $sql_data_array = array(
      array(
        'config_key' => 'PAYPAL_VERSION',
        'config_value' => $this->paypal_version,
      )
    );
    $this->save_config($sql_data_array);
    
    if ($this->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY') == '') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_INSTALLMENT_BANNER_DISPLAY',
          'config_value' => '1',
        )
      );
      $this->save_config($sql_data_array);
    }

    if ($this->get_config('PAYPAL_INSTALLMENT_BANNER_COLOR') == '') {
      $sql_data_array = array(
        array(
          'config_key' => 'PAYPAL_INSTALLMENT_BANNER_COLOR',
          'config_value' => 'white',
        )
      );
      $this->save_config($sql_data_array);
    }
    
    if (!defined('MODULE_PAYMENT_PAYPAL_SECRET')) {
      xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_PAYPAL_SECRET', '".md5(uniqid())."', '6', '3', NULL, now(), '', '')");
    }
    
    //check tables
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_CONFIG." LIKE 'config_id'");
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_CONFIG." ADD `config_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_IPN." LIKE 'paypal_ipn_id'");
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_IPN." ADD `paypal_ipn_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_PAYPAL_INSTRUCTIONS." LIKE 'paypal_inctructions_id'");
    if (xtc_db_num_rows($check_query) == 0) {
      xtc_db_query("ALTER TABLE ".TABLE_PAYPAL_INSTRUCTIONS." ADD `paypal_inctructions_id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
    }
  }

}
?>