<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_EXCLUDE_PAYMENT_TEXT_TITLE', 'Zahlarten abh&auml;ngig von der Versandart');
define('MODULE_EXCLUDE_PAYMENT_TEXT_DESCRIPTION', '');
define('MODULE_EXCLUDE_PAYMENT_STATUS_TITLE' , 'Status');
define('MODULE_EXCLUDE_PAYMENT_STATUS_DESC' , 'Modul aktivieren?');
define('MODULE_EXCLUDE_PAYMENT_NUMBER_TITLE' , 'Anzahl der Versandarten');
define('MODULE_EXCLUDE_PAYMENT_NUMBER_DESC' , 'Anzahl der Versandarten die konfiguriert werden sollen.');

for ($module_exclude_payment_i = 1; $module_exclude_payment_i <= MODULE_EXCLUDE_PAYMENT_NUMBER; $module_exclude_payment_i ++) {
  define('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$module_exclude_payment_i.'_TITLE' , '<hr noshade>'.$module_exclude_payment_i.'. Versandart');
  define('MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$module_exclude_payment_i.'_DESC' , 'W&auml;hlen sie die Versandart bei der sie eine Zahlart ausschliessen wollen.');
  define('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$module_exclude_payment_i.'_TITLE' , $module_exclude_payment_i.'. Zahlart');
  define('MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$module_exclude_payment_i.'_DESC' , 'W&auml;hlen sie die Zahlart die sie ausschliessen wollen.');
}


class exclude_payment {
  var $code, $title, $description, $enabled, $num_exclude_payment;

  function __construct() {
    global $order;

    $this->code = 'exclude_payment';
    $this->title = MODULE_EXCLUDE_PAYMENT_TEXT_TITLE;
    $this->description = MODULE_EXCLUDE_PAYMENT_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_EXCLUDE_PAYMENT_STATUS == 'True') ? true : false);
    $this->num_exclude_payment = ((defined('MODULE_EXCLUDE_PAYMENT_NUMBER')) ? MODULE_EXCLUDE_PAYMENT_NUMBER : '');
    
    if ($this->check() > 0) {      
      $check_exclude_payment_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_EXCLUDE_PAYMENT_SHIPPING_%'");
      $check_exclude_payment_rows_query = xtc_db_num_rows($check_exclude_payment_query);

      if ($check_exclude_payment_rows_query != $this->num_exclude_payment) {
        $this->install_exclude_payment($check_exclude_payment_rows_query);
      }
    }
  }

  function process($file) {
    for ($i=1; $i<=$this->num_exclude_payment; $i++) {
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '".xtc_db_input(implode(',', $_POST['MODULE_EXCLUDE_PAYMENT_SHIPPING_'.$i]))."', last_modified = NOW() WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."'");
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '".xtc_db_input(implode(',', $_POST['MODULE_EXCLUDE_PAYMENT_PAYMENT_'.$i]))."', last_modified = NOW() WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."'");
    }
  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=exclude_payment')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EXCLUDE_PAYMENT_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_EXCLUDE_PAYMENT_NUMBER', '1', '6', '0', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_EXCLUDE_PAYMENT_SORT_ORDER', '0', '6', '0', now())");
  }

  function install_exclude_payment($number_of_exclude_payment) {
                  
    // backup old values
    xtc_backup_configuration($this->keys_exclude_payment($number_of_exclude_payment));

    // add new zone
    if ($number_of_exclude_payment <= $this->num_exclude_payment) {
      for ($i = (($number_of_exclude_payment==0) ? 1 : $number_of_exclude_payment); $i <= $this->num_exclude_payment; $i ++) {
        $check_exclude_payment_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."'");
        if (xtc_db_num_rows($check_exclude_payment_query) < 1) {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."', '', '6', '0', 'xtc_cfg_checkbox_unallowed_module(\'shipping\', \'MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."\',', now())");
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."', '0', '6', '0', 'xtc_cfg_checkbox_unallowed_module(\'payment\', \'MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."\',',  now())");
        }
      }      
    } else {
      // remove zone
      for ($i = $number_of_exclude_payment; $i >= $this->num_exclude_payment; $i --) {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_SHIPPING_".$i."'");      
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EXCLUDE_PAYMENT_PAYMENT_".$i."'");      
      }
    }

    // restore old values
    xtc_restore_configuration($this->keys_exclude_payment($this->num_exclude_payment));
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
  }

  function keys_exclude_payment($exclude_payment) {
    $keys_exclude_payment = array();
    for ($i = 1; $i <= $exclude_payment; $i ++) {
      $keys_exclude_payment[] = 'MODULE_EXCLUDE_PAYMENT_SHIPPING_' . $i;
      $keys_exclude_payment[] = 'MODULE_EXCLUDE_PAYMENT_PAYMENT_' . $i;
    }
    return $keys_exclude_payment;
  }

  function keys() {
    $keys = array('MODULE_EXCLUDE_PAYMENT_STATUS',
                  'MODULE_EXCLUDE_PAYMENT_NUMBER',
                  );
    $keys = array_merge($keys, $this->keys_exclude_payment($this->num_exclude_payment));

    return $keys;
  }
}
?>