<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class no_payment {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

    $this->code = 'no_payment';
    $this->title = MODULE_PAYMENT_NO_PAYMENT_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_NO_PAYMENT_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_PAYMENT_NO_PAYMENT_SORT_ORDER')) ? MODULE_PAYMENT_NO_PAYMENT_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_PAYMENT_NO_PAYMENT_STATUS') && MODULE_PAYMENT_NO_PAYMENT_STATUS == 'True') ? true : false);
    $this->info = MODULE_PAYMENT_NO_PAYMENT_TEXT_INFO;
    
    if ($this->check() > 0) {
      if ((int) MODULE_PAYMENT_NO_PAYMENT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_NO_PAYMENT_ORDER_STATUS_ID;
      }
    }
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
    return false;
  }

  function process_button() {
    return false;
  }

  function before_process() {
    return false;
  }

  function after_process() {
    global $insert_id;

    if (isset($this->order_status) && $this->order_status) {
      $orders_query = xtc_db_query("SELECT *
                                      FROM ".TABLE_ORDERS."
                                     WHERE orders_id = '".$insert_id."'");
      $orders = xtc_db_fetch_array($orders_query);
      
      if ($this->order_status != $orders['orders_status']) {
        xtc_db_query("UPDATE ".TABLE_ORDERS." 
                         SET orders_status = '".$this->order_status."' 
                       WHERE orders_id = '".(int)$insert_id."'");

        $sql_data_array = array(
          'orders_id' => (int)$insert_id,
          'orders_status_id' => $this->order_status,
          'date_added' => 'now()',
        );
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }
  }

  function get_error() {
    return false;
  }

  function check() {
    if (!isset ($this->_check)) {
      if (defined('MODULE_PAYMENT_NO_PAYMENT_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_NO_PAYMENT_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PAYMENT_NO_PAYMENT_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_NO_PAYMENT_ALLOWED', '', '6', '0', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_PAYMENT_NO_PAYMENT_ZONE', '0',  '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_NO_PAYMENT_SORT_ORDER', '0',  '6', '0', now())");
    xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_PAYMENT_NO_PAYMENT_ORDER_STATUS_ID', '0',  '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
  }

  function remove() {
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE 'MODULE_PAYMENT_NO_PAYMENT_%'");
  }

  function keys() {
    return array (
      'MODULE_PAYMENT_NO_PAYMENT_STATUS', 
      'MODULE_PAYMENT_NO_PAYMENT_ORDER_STATUS_ID', 
    );
  }
}
