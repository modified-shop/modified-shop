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

class semknox_system {
  var $code, $title, $description, $enabled;

  function __construct() {
    $this->code = 'semknox_system';
    $this->title = MODULE_SEMKNOX_SYSTEM_TEXT_TITLE;
    $this->description = MODULE_SEMKNOX_SYSTEM_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_SEMKNOX_SYSTEM_SORT_ORDER')) ? MODULE_SEMKNOX_SYSTEM_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_SEMKNOX_SYSTEM_STATUS') && MODULE_SEMKNOX_SYSTEM_STATUS == 'true') ? true : false);

    $this->languages = xtc_get_languages();

    if ($this->check() > 0) {      
      $check_api_query = xtc_db_query("SELECT * 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key LIKE 'MODULE_SEMKNOX_SYSTEM_API_%'");
      $check_api_num = xtc_db_num_rows($check_api_query);

      if ($check_api_num != (count($this->languages) * 2)) {
          $this->install_language();
      }        
    }
  }

  function process($file) {
  }

  function display() {
    return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                 );
  }

  function check() {
    if(!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . " 
                                    WHERE configuration_key = 'MODULE_SEMKNOX_SYSTEM_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SEMKNOX_SYSTEM_STATUS', 'true', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS', 'false', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_SEMKNOX_SYSTEM_COLOR', '#7c2759', '6', '2', now())");

    require_once(DIR_FS_ADMIN.DIR_WS_MODULES.'categories/semknox_categories.php');
    $semknox_categories = new semknox_categories();
    if ($semknox_categories->check() < 1) {
      $semknox_categories->install();
    }
  }

  function install_language() {
    foreach ($this->languages as $language) {
      xtc_db_query("INSERT IGNORE INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_SEMKNOX_SYSTEM_API_".strtoupper($language['id'])."', '', '6', '2', now())");
      xtc_db_query("INSERT IGNORE INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_SEMKNOX_SYSTEM_PROJECT_".strtoupper($language['id'])."', '', '6', '2', now())");
    }
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SEMKNOX_SYSTEM_%'");

    require_once(DIR_FS_ADMIN.DIR_WS_MODULES.'categories/semknox_categories.php');
    $semknox_categories = new semknox_categories();
    if ($semknox_categories->check() > 0) {
      $semknox_categories->remove();
    }
  }

  function keys() {
    $keys = array(
      'MODULE_SEMKNOX_SYSTEM_STATUS',
      'MODULE_SEMKNOX_SYSTEM_DEFAULT_CSS',
      'MODULE_SEMKNOX_SYSTEM_COLOR',
    );
    
    foreach ($this->languages as $language) {
      $keys[] =  'MODULE_SEMKNOX_SYSTEM_API_'.strtoupper($language['id']);
      $keys[] =  'MODULE_SEMKNOX_SYSTEM_PROJECT_'.strtoupper($language['id']);
    }
    
    return $keys;
  }    
}
?>