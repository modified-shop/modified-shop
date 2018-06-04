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

class system_dsgvo
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'system_dsgvo';
        $this->title = MODULE_SYSTEM_DSGVO_TEXT_TITLE;
        $this->description = MODULE_SYSTEM_DSGVO_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_SYSTEM_DSGVO_SORT_ORDER;
        $this->enabled = ((MODULE_SYSTEM_DSGVO_STATUS == 'true') ? true : false);
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SYSTEM_DSGVO_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SYSTEM_DSGVO_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_SYSTEM_DSGVO_CONTENT', '2',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `content_dsgvo` (
                        `customers_status` int(11) NOT NULL,
                        `content_group` int(11) NOT NULL,
                        `hash` varchar(32) NOT NULL,
                        `date_added` datetime NOT NULL,
                        PRIMARY KEY (`customers_status`,`content_group`,`hash`)
                      );");

        xtc_db_query("CREATE TABLE IF NOT EXISTS `customers_dsgvo` (
                        `customers_id` int(11) NOT NULL,
                        `content_group` int(11) NOT NULL,
                        `date_confirmed` datetime NOT NULL,
                        PRIMARY KEY (`customers_id`,`content_group`)
                      );");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SYSTEM_DSGVO_%'");
    }

    function keys() 
    {
        return array(
          'MODULE_SYSTEM_DSGVO_STATUS',
          'MODULE_SYSTEM_DSGVO_CONTENT',
        );
    }    
}
?>