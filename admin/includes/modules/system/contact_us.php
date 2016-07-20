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

class contact_us
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'contact_us';
        $this->title = MODULE_CONTACT_US_TEXT_TITLE;
        $this->description = MODULE_CONTACT_US_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_CONTACT_US_SORT_ORDER;
        $this->enabled = ((MODULE_CONTACT_US_STATUS == 'true') ? true : false);
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_CONTACT_US_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CONTACT_US_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("CREATE TABLE IF NOT EXISTS `contact_us_log` (
                        `customers_id` INT( 11 ) NOT NULL ,
                        `customers_name` VARCHAR( 128 ) NOT NULL ,
                        `customers_email_address` VARCHAR( 255 ) NOT NULL ,
                        `customers_ip` VARCHAR( 50 ) NOT NULL,
                        `date_added' DATETIME DEFAULT '0000-00-00 00:00:00',
                        KEY idx_customers_id (customers_id)
                      ) ENGINE=MyISAM;");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_CONTACT_US_%'");
    }

    function keys() 
    {
        return array('MODULE_CONTACT_US_STATUS');
    }    
}
?>