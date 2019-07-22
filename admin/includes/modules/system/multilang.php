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

class multilang
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'multilang';
        $this->title = MODULE_MULTILANG_TEXT_TITLE;
        $this->description = MODULE_MULTILANG_TEXT_DESCRIPTION;
        $this->sort_order = ((defined('MODULE_MULTILANG_SORT_ORDER')) ? MODULE_MULTILANG_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_MULTILANG_STATUS') && MODULE_MULTILANG_STATUS == 'true') ? true : false);
    }

    function process($file) 
    {
        //do nothing
    }

    function display() 
    {
        return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                               xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                     );
    }

    function check() 
    {
        if(!isset($this->_check)) {
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_MULTILANG_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MULTILANG_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MULTILANG_ADD_LANGUAGE', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MULTILANG_ADD_DEFAULT_LANGUAGE', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MULTILANG_X_DEFAULT', 'en',  '6', '1', 'xtc_cfg_pull_down_language_code(', now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_MULTILANG_%'");
    }

    function keys() 
    {
        return array(
          'MODULE_MULTILANG_STATUS',
          'MODULE_MULTILANG_ADD_LANGUAGE',
          'MODULE_MULTILANG_ADD_DEFAULT_LANGUAGE',
          'MODULE_MULTILANG_X_DEFAULT',
        );
    }    
}
?>