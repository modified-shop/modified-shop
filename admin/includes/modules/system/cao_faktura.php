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

class cao_faktura {
  var $code, $title, $description, $enabled;

  function __construct() {
     $this->code = 'cao_faktura';
     $this->title = MODULE_CAO_FAKTURA_TEXT_TITLE;
     $this->description = MODULE_CAO_FAKTURA_TEXT_DESCRIPTION;
     $this->sort_order = defined('MODULE_CAO_FAKTURA_SORT_ORDER') ? MODULE_CAO_FAKTURA_SORT_ORDER : '';
     $this->enabled = ((defined('MODULE_CAO_FAKTURA_STATUS') && MODULE_CAO_FAKTURA_STATUS == 'true') ? true : false);
  }

  function process($file) {
    if (isset($_POST['password']) && $_POST['password'] != '') {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".xtc_db_input(md5($_POST['password']))."'
                     WHERE configuration_key = 'MODULE_CAO_FAKTURA_PASSWORD'");
    }
  }

  function display() {
    return array('text' => '<br/><b>'.MODULE_CAO_FAKTURA_PASSWORD_TITLE.'</b>'.
                           '<br/>'.MODULE_CAO_FAKTURA_PASSWORD_DESC. 
                           '<br/>'.xtc_draw_input_field('password', '').'<br/>'.
                           '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=cao_faktura')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . "
                                    WHERE configuration_key = 'MODULE_CAO_FAKTURA_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }
    
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAO_FAKTURA_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAO_FAKTURA_EMAIL', '',  '6', '1', '', now())");  
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAO_FAKTURA_PASSWORD', '',  '6', '1', '', now())");  
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    $key = array(
      'MODULE_CAO_FAKTURA_STATUS',
      'MODULE_CAO_FAKTURA_EMAIL',
    );

    return $key;
  }
}
?>