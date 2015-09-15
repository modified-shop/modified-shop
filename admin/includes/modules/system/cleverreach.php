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

// include needed functions
class cleverreach {
  var $code, $title, $description, $enabled;

  function __construct() {
     $this->code = 'cleverreach';
     $this->title = MODULE_CLEVERREACH_TEXT_TITLE;
     $this->description = MODULE_CLEVERREACH_TEXT_DESCRIPTION;
     $this->sort_order = defined('MODULE_CLEVERREACH_SORT_ORDER') ? MODULE_CLEVERREACH_SORT_ORDER : 0;
     $this->enabled = ((MODULE_CLEVERREACH_STATUS == 'true') ? true : false);
   }

  function process($file) {

  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=cleverreach')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . "
                                    WHERE configuration_key = 'MODULE_CLEVERREACH_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }
    
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CLEVERREACH_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, date_added) VALUES ('MODULE_CLEVERREACH_APIKEY', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, date_added) VALUES ('MODULE_CLEVERREACH_NAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, date_added) VALUES ('MODULE_CLEVERREACH_GROUP', '',  '6', '1', '', now())");
  }

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    $key = array('MODULE_CLEVERREACH_STATUS',
                 'MODULE_CLEVERREACH_APIKEY',
                 'MODULE_CLEVERREACH_NAME',
                 'MODULE_CLEVERREACH_GROUP');

    return $key;
  }
}
?>