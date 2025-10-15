<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // needed classes
  require_once(DIR_FS_EXTERNAL.'Teambank/classes/TeambankPayment.php');

  class easyinvoice extends TeambankPayment {
  
    var $code;
    var $title;
    var $info;
    var $description;
    var $sort_order;
    var $enabled;
    var $order_status;
    var $order_status_success;
    var $_check;  
    var $properties;
    
    function __construct() {
      global $order, $main;
  
      $this->code = 'easyinvoice';
      $this->title = MODULE_PAYMENT_EASYINVOICE_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_EASYINVOICE_TEXT_DESCRIPTION;
      $this->info = MODULE_PAYMENT_EASYINVOICE_TEXT_INFO;
      $this->sort_order = defined('MODULE_PAYMENT_EASYINVOICE_SORT_ORDER') ? MODULE_PAYMENT_EASYINVOICE_SORT_ORDER : '';
      $this->enabled = ((defined('MODULE_PAYMENT_EASYINVOICE_STATUS') && MODULE_PAYMENT_EASYINVOICE_STATUS == 'True') ? true : false);
  
      if ($this->enabled === true) {
        if ((int) MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_ID;
        }
        if ((int) MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_SUCCESS_ID > 0) {
          $this->order_status_success = MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_SUCCESS_ID;
        }
        
        TeambankPayment::init($this->code);

        if (defined('RUN_MODE_ADMIN')) {
          $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULES, 'set=' . 'payment' . '&module=' . $this->code . '&action=custom') . '">' . BUTTON_EASYCREDIT_CHECK. '</a>';
        }

        if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
          $this->update_status();
        }
      }
    }
  
    function update_status() {
      global $order;
      
      if ($this->enabled === true
          && isset($_SESSION['sendto'])
          && isset($_SESSION['billto'])
          && $_SESSION['sendto'] !== $_SESSION['billto']
          )
      {
        $this->enabled = false;
      }
  
      if ($this->enabled === true
          && $order->billing['country']['iso_code_2'] != 'DE'
          )
      {
        $this->enabled = false;
      }
  
      if ($this->enabled === true
          && ($_SESSION['customers_status']['customers_status_show_price_tax'] != '1'
              || $_SESSION['customers_status']['customers_status_add_tax_ot'] != '0'
              )
          )
      {
        $this->enabled = false;
      }
      
      if ($this->enabled === true) {
        $this->total_amount = $this->calculate_total();

        $this->enabled = false;
        if ($this->WebshopDetails->getBillPaymentActive() === true
            && $this->total_amount >= $this->WebshopDetails->getMinBillingValue()
            && $this->total_amount < $this->WebshopDetails->getMaxBillingValue()
            )
        {
          $this->enabled = true;
        }
      }
    }  

    function selection() {
      global $order;
  
      //reset
      $this->ecCheckout->clear();
      $this->ecCheckout->save();
      
      //session
      unset($_SESSION['easycredit']);
      
      $presentment = $this->get_presentment($this->total_amount);    
      
      if ($presentment != '') {
        return array(
          'id' => $this->code, 
          'module' => $this->title, 
          'description' => $presentment,
        );
      }
    }
    
    function get_presentment($amount) {  
      $ec_smarty = new Smarty();
      $ec_smarty->assign('language', $_SESSION['language']);
      $ec_smarty->assign('paymenttype', 'BILL');
      $ec_smarty->assign('conditions_text', decode_utf8($this->WebshopDetails->getPrivacyApprovalForm()));
      $presentment = $ec_smarty->fetch(DIR_FS_EXTERNAL.'Teambank/templates/presentment.html');
    
      return $presentment;
    }
      
    function get_error() {
      if (isset($_GET['payment_error'])) {
        $error = array(
          'title' => MODULE_PAYMENT_EASYINVOICE_TEXT_ERROR_HEADING,
          'error' => MODULE_PAYMENT_EASYINVOICE_TEXT_ERROR_MESSAGE,
        );
        return $error;
      }
    }
  
    function check() {
      if (!isset ($this->_check)) {
        if (defined('MODULE_PAYMENT_EASYINVOICE_STATUS') && !defined('RUN_MODE_ADMIN')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM ".TABLE_CONFIGURATION." 
                                        WHERE configuration_key = 'MODULE_PAYMENT_EASYINVOICE_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }
  
    function install() {
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EASYINVOICE_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYINVOICE_ALLOWED', 'DE', '6', '0', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_EASYINVOICE_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYINVOICE_SORT_ORDER', '0',  '6', '0', now())");
      
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_SUCCESS_ID', '".DEFAULT_ORDERS_STATUS_ID."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_ID', '".DEFAULT_ORDERS_STATUS_ID."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYINVOICE_SHOP_ID', '', '6', '0', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYINVOICE_SHOP_TOKEN', '', '6', '0', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EASYINVOICE_SHOP_SECRET', '', '6', '0', now())");
      xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EASYINVOICE_LOG_LEVEL', 'error', '6', '1', 'xtc_cfg_select_option(array(\'debug\', \'error\'), ', now())");

      if (!defined('MODULE_PAYMENT_TEAMBANK_SECRET')) {
        $check_query = xtc_db_query("SELECT * 
                                       FROM ".TABLE_CONFIGURATION." 
                                      WHERE configuration_key = 'MODULE_PAYMENT_TEAMBANK_SECRET'");
        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_PAYMENT_TEAMBANK_SECRET', '".md5(uniqid())."', '6', '3', NULL, now(), '', '')");
        }
      }
      
      xtc_db_query("CREATE TABLE IF NOT EXISTS `easycredit` (
                    `orders_id` INT( 11 ) NOT NULL ,
                    `tbaId` VARCHAR( 512 ) NOT NULL ,
                    `technicalTbaId` VARCHAR( 512 ) NOT NULL ,
                    PRIMARY KEY ( `orders_id` )
                    )");                    
    }
  
    function remove() {
      xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE ('MODULE_PAYMENT_EASYINVOICE_%')");
    }
  
    function keys() {
      return array(
        'MODULE_PAYMENT_EASYINVOICE_STATUS', 
        'MODULE_PAYMENT_EASYINVOICE_SHOP_ID',
        'MODULE_PAYMENT_EASYINVOICE_SHOP_TOKEN',
        'MODULE_PAYMENT_EASYINVOICE_SHOP_SECRET',
        'MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_ID', 
        'MODULE_PAYMENT_EASYINVOICE_ORDER_STATUS_SUCCESS_ID', 
        'MODULE_PAYMENT_EASYINVOICE_SORT_ORDER',
        'MODULE_PAYMENT_EASYINVOICE_LOG_LEVEL',
      );
    }
    
  }
