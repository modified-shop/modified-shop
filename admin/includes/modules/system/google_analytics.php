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

class google_analytics {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;

  function __construct() {
    $this->code = 'google_analytics';
    $this->title = MODULE_GOOGLE_ANALYTICS_TEXT_TITLE;
    $this->description = MODULE_GOOGLE_ANALYTICS_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_GOOGLE_ANALYTICS_SORT_ORDER')) ? MODULE_GOOGLE_ANALYTICS_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_GOOGLE_ANALYTICS_STATUS') && MODULE_GOOGLE_ANALYTICS_STATUS == 'true') ? true : false);
  }

  function process($file) {
      //do nothing
  }

  function display() {
    return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                 );
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_GOOGLE_ANALYTICS_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE configuration_key = 'MODULE_GOOGLE_ANALYTICS_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_TAG_ID', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_ADS_ID', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_ADS_CONVERSION_ID', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_ECOMMERCE', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_COUNT_ADMIN', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_LINKID', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_ANALYTICS_DISPLAY', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_GOOGLE_ANALYTICS_%'");
  }

  function keys() {
    return array(
      'MODULE_GOOGLE_ANALYTICS_STATUS',
      'MODULE_GOOGLE_ANALYTICS_TAG_ID',
      'MODULE_GOOGLE_ANALYTICS_ADS_ID',
      'MODULE_GOOGLE_ANALYTICS_ADS_CONVERSION_ID',
      'MODULE_GOOGLE_ANALYTICS_ECOMMERCE',
      'MODULE_GOOGLE_ANALYTICS_COUNT_ADMIN',
      'MODULE_GOOGLE_ANALYTICS_LINKID',
      'MODULE_GOOGLE_ANALYTICS_DISPLAY',
    );
  }    
}
