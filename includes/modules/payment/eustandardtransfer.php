<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com
   (c) 2006 XT-Commerce (eustandardtransfer.php 998 2005-07-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class eustandardtransfer {
  var $code, $title, $description, $enabled;
  // class constructor
  // BOF - Hendrik - 2010-08-11 - php5 compatible
  //function eustandardtransfer() {
  function __construct() {
  // EOF - Hendrik - 2010-08-11 - php5 compatible

    $this->code = 'eustandardtransfer';
    $this->title = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE;
    $this->description = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION;
    $this->description2 = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION2;
    $this->sort_order = MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER;
    $this->info = MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO;
    $this->enabled = ((MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS == 'True') ? true : false);
    $this->logo = xtc_image(DIR_WS_ICONS . 'elv.jpg');

    $this->update_status(); // Hendrik - 2010-07-15 - exlusion config for shipping modules
  } 
  // class methods
  
  // BOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
  function update_status() {
    global $order;
    if( MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING != '' ) {
      $neg_shpmod_arr = explode(',',MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING);
      foreach( $neg_shpmod_arr as $neg_shpmod ) {
        $nd=$neg_shpmod.'_'.$neg_shpmod;
        if( $_SESSION['shipping']['id']==$nd || $_SESSION['shipping']['id']==$neg_shpmod ) { 
          $this->enabled = false;
          break;
        }
      }
    }
  } 
  // EOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
  
  
  function javascript_validation() {
    return false;
  }

  function selection() {
    $content = array();
    $content = array_merge($content, array (array ('title' => ' ',
                                                    'field' => '<div align="left">'.$this->logo.'</div>')));
    return array ('id' => $this->code, 'module' => $this->title, 'fields' => $content,'description' => $this->info);
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    $confirmation = array ('title' => $this->title, 
                           'fields' => array (array ('title' => MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION, 
                                                     'field' => MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION2)
                                              ));
    return $confirmation;
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
    //xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
    if (isset($this->order_status) && $this->order_status) {
      xtc_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");
      xtc_db_query("UPDATE ".TABLE_ORDERS_STATUS_HISTORY." SET orders_status_id='".$this->order_status."' WHERE orders_id='".$insert_id."'");
    }
    //EOF - DokuMan - 2010-08-23 - Also update status in TABLE_ORDERS_STATUS_HISTORY
  }

  function output_error() {
    return false;
  }

  function check() {
    if (!isset ($this->check)) {
      $check_query = xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS'");
      $this->check = xtc_db_num_rows($check_query);
    }
    return $this->check;
  }

  function install() {
    xtc_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED', 'DE', '6', '0', now())");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH', '---', '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC', '---',  '6', '1', now());");
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER', '0',  '6', '0', now())");

    // BOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
    xtc_db_query("insert into ".TABLE_CONFIGURATION." (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING', '', '6', '99', now())");
    // EOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
  }

  function remove() {
    xtc_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
  }

  function keys() {
    $keys = array ('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC', 
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER',
            'MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING'       // Hendrik - 2010-07-15 - exlusion config for shipping modules
          );

    return $keys;
  }
}
?>