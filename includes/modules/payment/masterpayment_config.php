<?php
/****************************************************** 
 * Masterpayment Modul for modified eCommerce Shopsoftware 
 * Version 3.5.1
 * Copyright (c) 2010-2012 by K-30 | Florian Ressel 
 *
 * support@k-30.de | www.k-30.de
 * ----------------------------------------------------
 *
 * $Id: masterpayment_config.php 04.06.2012 21:06 $
 *	
 *	The Modul based on:
 *  XT-Commerce - community made shopping
 *  http://www.xt-commerce.com
 *
 *  Copyright (c) 2003 XT-Commerce
 *
 *	Released under the GNU General License
 *
 ******************************************************/
 
define('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ALLOWED', '');

require_once(DIR_FS_CATALOG . 'includes/external/masterpayment/MasterpaymentActions.class.php');

  class masterpayment_config extends MasterpaymentActions {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $order;
	  
	  parent::__construct();

      $this->code		 	= 'masterpayment_config';
      $this->title 			= MODULE_PAYMENT_MASTERPAYMENT_CONFIG_TEXT_TITLE;
      $this->description 	= MODULE_PAYMENT_MASTERPAYMENT_CONFIG_TEXT_DESCRIPTION;
      $this->enabled 		= false;
      //BOF - Tomcraft - 2012-12-15 - do not set/show a default sort order, when module is not enabled
      /*
      $this->sort_order		= 10;	  
      */
      $this->sort_order = MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SORT_ORDER;
      //EOF - Tomcraft - 2012-12-15 - do not set/show a default sort order, when module is not enabled
    }

    function update_status() {
     	return false;
    }

    function javascript_validation() {
      	return false;
    }

    function selection() {
		return false;
    }

    function pre_confirmation_check() {
		return false;  
    }

    function confirmation() {
      	return true;
    }

    function process_button() {     	
		return false;
    }

    function before_process() {    
		return false;		
    }

    function after_process() {
		return false;		
    }

    function get_error() {
       	return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_MERCHANTID'");        
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }	

    function install() {  
      $this->installProcess();
 
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SORT_ORDER', '10', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_MERCHANTID', '', '6', '1', now());");      
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SECRETKEY', '', '6', '1', now());");   
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_INFRAME', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");   	   
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_BASKETDESCRIPTION', '', '6', '1', now());");	
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SEND_CUSTOMER_DATA', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SEND_PRODUCTS_DATA', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SHOW_CANCEL_BUTTON', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_TEMP', '".$this->getProcessStatusId()."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_SUCCESS', '".$this->getSuccessStatusID()."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_FAILURE', '".$this->getFailureStatusID()."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_CANCEL', '".$this->getCancelStatusID()."', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_DELETE_TEMP_ORDER', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");  
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SAVE_LOGS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ALLOWED', '', '6', '0', now())");		  	  	   
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	  
	  $this->uninstallProcess();
    }
	
    function keys() {
      return array('MODULE_PAYMENT_MASTERPAYMENT_CONFIG_MERCHANTID', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SECRETKEY', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_INFRAME', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_BASKETDESCRIPTION', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SEND_CUSTOMER_DATA', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SEND_PRODUCTS_DATA', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SHOW_CANCEL_BUTTON',  'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_TEMP', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_SUCCESS', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_FAILURE', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_ORDER_STATUS_ID_CANCEL', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_DELETE_TEMP_ORDER', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SAVE_LOGS', 'MODULE_PAYMENT_MASTERPAYMENT_CONFIG_SORT_ORDER');
    }
    
  }
?>
