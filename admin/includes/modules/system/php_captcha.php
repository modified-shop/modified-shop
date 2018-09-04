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


class php_captcha
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'php_captcha';
        $this->title = MODULE_SYSTEM_PHP_CAPTCHA_TEXT_TITLE;
        $this->description = MODULE_SYSTEM_PHP_CAPTCHA_TEXT_DESCRIPTION;
        $this->enabled = ((MODULE_SYSTEM_PHP_CAPTCHA_STATUS == 'true') ? true : false);
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SYSTEM_PHP_CAPTCHA_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_USE_COLOR', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_USE_SHADOW', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_CODE_LENGTH', '6',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_NUM_LINES', '70',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_MIN_FONT', '24',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_MAX_FONT', '28',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_BACKGROUND_RGB', '192,192,192',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_LINES_RGB', '220,148,002',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_CHARS_RGB', '112,112,112',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_WIDTH', '240',  '6', '1', '', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_PHP_CAPTCHA_HEIGHT', '50',  '6', '1', '', now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SYSTEM_PHP_CAPTCHA_%'");
    }

    function keys() 
    {
        return array(
          'MODULE_SYSTEM_PHP_CAPTCHA_STATUS',
          'MODULE_SYSTEM_PHP_CAPTCHA_USE_COLOR',
          'MODULE_SYSTEM_PHP_CAPTCHA_USE_SHADOW',
          'MODULE_SYSTEM_PHP_CAPTCHA_CODE_LENGTH',
          'MODULE_SYSTEM_PHP_CAPTCHA_NUM_LINES',
          'MODULE_SYSTEM_PHP_CAPTCHA_MIN_FONT',
          'MODULE_SYSTEM_PHP_CAPTCHA_MAX_FONT',
          'MODULE_SYSTEM_PHP_CAPTCHA_BACKGROUND_RGB',
          'MODULE_SYSTEM_PHP_CAPTCHA_LINES_RGB',
          'MODULE_SYSTEM_PHP_CAPTCHA_CHARS_RGB',
          'MODULE_SYSTEM_PHP_CAPTCHA_WIDTH',
          'MODULE_SYSTEM_PHP_CAPTCHA_HEIGHT',
        );
    }    
}
?>