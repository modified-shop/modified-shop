<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneyorder.php,v 1.10 2003/01/29); www.oscommerce.com
   (c) 2003 nextcommerce
 (moneyorder.php,v 1.7 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (cash.php 1102 2005-07-24)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class cash {
  var $code, $title, $description, $enabled;
  // BOF - Hendrik - 2010-08-11 - php5 compatible
  //function cash() {
  function __construct() {
  // EOF - Hendrik - 2010-08-11 - php5 compatible
    global $order;

    $this->code = 'cash';
    $this->title = MODULE_PAYMENT_CASH_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_CASH_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_CASH_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_CASH_STATUS == 'True') ? true : false);
    $this->info = MODULE_PAYMENT_CASH_TEXT_INFO;
    if ((int) MODULE_PAYMENT_CASH_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_CASH_ORDER_STATUS_ID;
    }

    if (is_object($order)) {
      $this->update_status();
    }

    if (defined('MODULE_PAYMENT_CASH_TEXT_EMAIL_FOOTER')) {
      $this->email_footer = MODULE_PAYMENT_CASH_TEXT_EMAIL_FOOTER;
    } else {
      $this->email_footer = '';
    }
  }

  function update_status() {
    global $order;

    // BOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
    if( MODULE_PAYMENT_CASH_NEG_SHIPPING != '' ) {
      $neg_shpmod_arr = explode(',',MODULE_PAYMENT_CASH_NEG_SHIPPING);
      foreach( $neg_shpmod_arr as $neg_shpmod ) {
        $nd=$neg_shpmod.'_'.$neg_shpmod;
        if( $_SESSION['shipping']['id']==$nd || $_SESSION['shipping']['id']==$neg_shpmod ) {
          $this->enabled = false;
          break;
        }
      }
    }
    //if ($_SESSION['shipping']['id'] != 'selfpickup_selfpickup') {
    //  $this->enabled = false;
    //}
    // EOF - Hendrik - 2010-07-15 - exlusion config for shipping modules


    if (($this->enabled == true) && ((int) MODULE_PAYMENT_CASH_ZONE > 0)) {
      $check_flag = false;
      $check_query = xtc_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_CASH_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
      while ($check = xtc_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        }
        elseif ($check['zone_id'] == $order->billing['zone_id']) {
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
    return array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    return array ('title' => MODULE_PAYMENT_CASH_TEXT_DESCRIPTION);
  }

  function process_button() {
    return false;
  }

  function before_process() {
    return false;
  }

  function after_process() {
    global $insert_id;
    //BOF - DokuMan - 2010-08-23 - Also update status in TABLE_ORDERS_STATUS_HISTORY
    //if ($this->order_status)
    //  xtc_db_query("UPDATE ". TABLE_ORDERS ." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
    if (isset($this->order_status) && $this->order_status) {
        xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
        xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS_HISTORY." SET orders_status_id='".$this->order_status."' WHERE orders_id='".$insert_id."'");
    }
    //EOF - DokuMan - 2010-08-23 - Also update status in TABLE_ORDERS_STATUS_HISTORY
  }

  function get_error() {
    return false;
  }

  function check() {
    if (!isset ($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_CASH_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_CASH_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_CASH_ALLOWED', '',   '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_CASH_SORT_ORDER', '0', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_CASH_ZONE', '0',  '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_CASH_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");

    // BOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_CASH_NEG_SHIPPING', '', '6', '99', now())");
    // EOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
  }

  function remove() {
    xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
  }

  function keys() {
    return array (  'MODULE_PAYMENT_CASH_STATUS',
            'MODULE_PAYMENT_CASH_ALLOWED',
            'MODULE_PAYMENT_CASH_ZONE',
            'MODULE_PAYMENT_CASH_ORDER_STATUS_ID',
            'MODULE_PAYMENT_CASH_SORT_ORDER',
            'MODULE_PAYMENT_CASH_NEG_SHIPPING' );    // Hendrik - 2010-07-15 - exlusion config for shipping modules
  }
}
?>