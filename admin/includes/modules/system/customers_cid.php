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

class customers_cid
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'customers_cid';
        $this->title = MODULE_CUSTOMERS_CID_TEXT_TITLE;
        $this->description = MODULE_CUSTOMERS_CID_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_CUSTOMERS_CID_SORT_ORDER;
        $this->enabled = ((MODULE_CUSTOMERS_CID_STATUS == 'true') ? true : false);
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_CUSTOMERS_CID_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CUSTOMERS_CID_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) VALUES ('MODULE_CUSTOMERS_CID_NEXT', '1',  '6', '1', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) VALUES ('MODULE_CUSTOMERS_CID_FORMAT', 'KD-{n}-{d}-{m}-{y}',  '6', '1', now())");
    }

    function remove()
    {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_CUSTOMERS_CID_%'");
    }

    function keys() 
    {
        return array('MODULE_CUSTOMERS_CID_STATUS',
                     'MODULE_CUSTOMERS_CID_FORMAT',
                     'MODULE_CUSTOMERS_CID_NEXT');
    }    
}
?>