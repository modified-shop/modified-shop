<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class it_recht_kanzlei {
  var $code;
  var $title;
  var $sort_order;
  var $enabled;
  var $description;
  var $extended_description;

  function __construct() {
    $this->code = 'it_recht_kanzlei';
    $this->title = MODULE_API_IT_RECHT_KANZLEI_TEXT_TITLE;
    $this->description = MODULE_API_IT_RECHT_KANZLEI_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_API_IT_RECHT_KANZLEI_STATUS == 'true') ? true : false);
  }
 
  function process() {}

  // display
  function display() {
    return array('text' => '<br />' . 
                           '<br />' . 
                           xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=it_recht_kanzlei'))
                );
  }

  // check
  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_API_IT_RECHT_KANZLEI_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  // install
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TOKEN', '".md5(time() . rand(0,99999))."',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_VERSION', '1.0',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB', '3',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE', '2',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB', '9',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP', '4',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_AGB', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_DSE', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_WRB', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_API_IT_RECHT_KANZLEI_PDF_FILE', '/media/content/',  '6', '1', '', now())");
  }

  // remove
  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
  }

  // keys
  function keys() {
    return array('MODULE_API_IT_RECHT_KANZLEI_STATUS', 
                 'MODULE_API_IT_RECHT_KANZLEI_TOKEN', 
                 'MODULE_API_IT_RECHT_KANZLEI_VERSION',
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_AGB', 
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_DSE', 
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_WRB', 
                 'MODULE_API_IT_RECHT_KANZLEI_TYPE_IMP', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_AGB', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_DSE', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_WRB', 
                 'MODULE_API_IT_RECHT_KANZLEI_PDF_FILE', 
                 );
  }
}

?>