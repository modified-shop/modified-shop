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

class checkout_express
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'checkout_express';
        $this->title = MODULE_CHECKOUT_EXPRESS_TEXT_TITLE;
        $this->description = MODULE_CHECKOUT_EXPRESS_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_CHECKOUT_EXPRESS_SORT_ORDER;
        $this->enabled = ((MODULE_CHECKOUT_EXPRESS_STATUS == 'true') ? true : false);
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_CHECKOUT_EXPRESS_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CHECKOUT_EXPRESS_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_CHECKOUT_EXPRESS_CONTENT', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
        xtc_db_query("CREATE TABLE ".TABLE_CUSTOMERS_CHECKOUT." (
                                     customers_id int(11) NOT NULL,
                                     checkout_shipping VARCHAR(128) NOT NULL,
                                     checkout_shipping_address INT(11) NOT NULL,
                                     checkout_payment VARCHAR(128) NOT NULL,
                                     checkout_payment_address INT(11) NOT NULL,
                                     PRIMARY KEY (customers_id)
                                   ) ENGINE=MYISAM");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_CHECKOUT_EXPRESS_%'");
        xtc_db_query("DROP TABLE ".TABLE_CUSTOMERS_CHECKOUT);
    }

    function keys() 
    {
        return array('MODULE_CHECKOUT_EXPRESS_STATUS',
                     'MODULE_CHECKOUT_EXPRESS_CONTENT');
    }    
}
?>