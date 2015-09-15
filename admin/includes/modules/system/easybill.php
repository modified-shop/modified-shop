<?php
/* -----------------------------------------------------------------------------------------
   $Id: easybill.php 4242 2013-01-11 13:56:09Z gtb-modified $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');  

class easybill {
  var $code, $title, $description, $enabled;

  function __construct() {

    $this->code = 'easybill';
    $this->title = MODULE_EASYBILL_TEXT_TITLE;
    $this->description = MODULE_EASYBILL_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_EASYBILL_STATUS == 'True') ? true : false);

  }

  function process($file) {

  }

  function display() {
    $tax_class_array = array (array ('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = xtc_db_query("SELECT tax_class_id, tax_class_title FROM ".TABLE_TAX_CLASS." ORDER BY tax_class_title");
    while ($tax_class = xtc_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array ('id' => $tax_class['tax_class_id'], 'text' => $tax_class['tax_class_title']);
    }
  
    return array('text' => '<br/>' . MODULE_EASYBILL_STANDARD_TAX_CLASS_TITLE . '<br/>' . MODULE_EASYBILL_STANDARD_TAX_CLASS_DESC .
                           '<br/>' . xtc_draw_pull_down_menu('configuration[MODULE_EASYBILL_STANDARD_TAX_CLASS]', $tax_class_array, MODULE_EASYBILL_STANDARD_TAX_CLASS) . '<br/>' .
                           '<br/>' . xtc_button(BUTTON_REVIEW_APPROVE) . '&nbsp;' .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=easybill')));
  }

  function getPaymentInstalled() {
    if (defined('MODULE_PAYMENT_INSTALLED') && xtc_not_null(MODULE_PAYMENT_INSTALLED)) {
      $payment_module = explode(';', MODULE_PAYMENT_INSTALLED);
    }
    for ($i=0, $n=sizeof($payment_module); $i<$n; $i++) {
      $this->payment_module[$i] = substr($payment_module[$i], 0, -4);  
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EASYBILL_PAYMENT_TEXT_".strtoupper($this->payment_module[$i])."'");
      if (xtc_db_num_rows($check_query) == 0) {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_PAYMENT_TEXT_".strtoupper($this->payment_module[$i])."', '',  '111', '15', '', now())");
        define('MODULE_EASYBILL_PAYMENT_TEXT_'.strtoupper($this->payment_module[$i]).'_DESC', 'Bitte den Rechnungstext eingeben:');
        define('MODULE_EASYBILL_PAYMENT_TEXT_'.strtoupper($this->payment_module[$i]).'_TITLE', '<b>Zahlart '.$this->getPaymentTitle($this->payment_module[$i]).'</b>');
      } else {
        $key[] = 'MODULE_EASYBILL_PAYMENT_TEXT_'.strtoupper($this->payment_module[$i]);
        define('MODULE_EASYBILL_PAYMENT_TEXT_'.strtoupper($this->payment_module[$i]).'_DESC', 'Bitte den Rechnungstext eingeben:');
        define('MODULE_EASYBILL_PAYMENT_TEXT_'.strtoupper($this->payment_module[$i]).'_TITLE', '<b>Zahlart '.$this->getPaymentTitle($this->payment_module[$i]).'</b>');
      }
    }
    return $key;
  }

  function getPaymentTitle($payment_method) {

    if (file_exists(DIR_FS_CATALOG . 'lang/'.$_SESSION['language'].'/modules/payment/'.$payment_method.'.php')) {
      include_once (DIR_FS_CATALOG . 'lang/'.$_SESSION['language'].'/modules/payment/'.$payment_method.'.php');
      if (defined(strtoupper('MODULE_PAYMENT_'.$payment_method.'_TEXT_TITLE'))) {
        $payment_method = constant(strtoupper('MODULE_PAYMENT_'.$payment_method.'_TEXT_TITLE'));
      }
    }

    return $payment_method;
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EASYBILL_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
      // Update Check
      if ($this->_check > 0) {
        $check_update_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EASYBILL_STANDARD_TAX_CLASS'");
        if (xtc_db_num_rows($check_update_query) < 1) {
          xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_STANDARD_TAX_CLASS', '1',  '111', '11', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        }
      }
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_STATUS', 'False',  '111', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_API', '',  '111', '2', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_BILLINGID', 'easyBill',  '111', '3', 'xtc_cfg_select_option(array(\'easyBill\', \'Shop\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_BILLCREATE', 'manuell',  '111', '4', 'xtc_cfg_select_option(array(\'manuell\', \'auto\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_BILLSAVE', 'easyBill',  '111', '5', 'xtc_cfg_select_option(array(\'easyBill\', \'Shop\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_PAYMENT', 'moneyorder',  '111', '6', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_DO_AUTO_PAYMENT', 'True',  '111', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_NO_AUTO_PAYMENT', 'moneyorder',  '111', '8', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_DO_STATUS_CHANGE', 'False',  '111', '9', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_EASYBILL_STATUS_CHANGE', '0', '111', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_STANDARD_TAX_CLASS', '1',  '111', '11', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");

    // Tabellenstruktur anlegen
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_EASYBILL." (
                            easybill_id INT(11) NOT NULL AUTO_INCREMENT,
                            orders_id INT(11) NOT NULL,
                            customers_id INT(11) NOT NULL,
                            easybill_customers_id INT(11) NOT NULL,
                            billing_id INT(11) NOT NULL,
                            billing_date DATETIME NOT NULL,
                            payment int(1) NOT NULL DEFAULT '0',
                            PRIMARY KEY (`easybill_id`),
                            INDEX orders_id (orders_id),
                            INDEX customers_id (customers_id))
                 ");
    xtc_db_query("CREATE TABLE IF NOT EXISTS ".TABLE_EASYBILL_DATEV." (
                            datev_id INT(11) NOT NULL AUTO_INCREMENT,
                            customers_datev_id INT(11) NOT NULL,
                            customers_id INT(11) NOT NULL,
                            PRIMARY KEY (`datev_id`),
                            INDEX customers_id (customers_id))
                 ");
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    xtc_db_query("DROP TABLE ".TABLE_EASYBILL);
    xtc_db_query("DROP TABLE ".TABLE_EASYBILL_DATEV);
  }

  function keys() {
    $key = array('MODULE_EASYBILL_STATUS',
                 'MODULE_EASYBILL_API',
                 'MODULE_EASYBILL_BILLINGID',
                 'MODULE_EASYBILL_BILLCREATE',
                 'MODULE_EASYBILL_PAYMENT',
                 'MODULE_EASYBILL_BILLSAVE',
                 'MODULE_EASYBILL_DO_AUTO_PAYMENT',
                 'MODULE_EASYBILL_NO_AUTO_PAYMENT',
                 'MODULE_EASYBILL_DO_STATUS_CHANGE',
                 'MODULE_EASYBILL_STATUS_CHANGE'
                );
    $payment = $this->getPaymentInstalled();
    return array_merge($key, (is_array($payment)?$payment:array()));
  }
}
?>