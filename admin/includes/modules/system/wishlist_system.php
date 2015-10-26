<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class wishlist_system {
  var $code, $title, $description, $enabled;

  function __construct() {
     $this->code = 'wishlist_system';
     $this->title = MODULE_WISHLIST_SYSTEM_TEXT_TITLE;
     $this->description = MODULE_WISHLIST_SYSTEM_TEXT_DESCRIPTION;
     $this->sort_order = defined('MODULE_WISHLIST_SYSTEM_SORT_ORDER') ? MODULE_WISHLIST_SYSTEM_SORT_ORDER : 0;
     $this->enabled = ((MODULE_WISHLIST_SYSTEM_STATUS == 'true') ? true : false);
   }

  function process($file) {
  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=wishlist_system')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . "
                                    WHERE configuration_key = 'MODULE_WISHLIST_SYSTEM_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }
    
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_WISHLIST_SYSTEM_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  

    xtc_db_query("CREATE TABLE IF NOT EXISTS customers_wishlist LIKE customers_basket");
    xtc_db_query("CREATE TABLE IF NOT EXISTS customers_wishlist_attributes LIKE customers_basket_attributes");
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
    xtc_db_query("DROP TABLE `customers_wishlist`");
    xtc_db_query("DROP TABLE `customers_wishlist_attributes`");
  }

  function keys() {
    $key = array(
      'MODULE_WISHLIST_SYSTEM_STATUS',
    );

    return $key;
  }
}
?>