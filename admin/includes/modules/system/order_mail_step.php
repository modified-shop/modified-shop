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

class order_mail_step
{
    var $code, $title, $description, $enabled;

    function __construct() 
    {
        $this->code = 'order_mail_step';
        $this->title = MODULE_ORDER_MAIL_STEP_TEXT_TITLE;
        $this->description = MODULE_ORDER_MAIL_STEP_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_ORDER_MAIL_STEP_SORT_ORDER;
        $this->enabled = ((MODULE_ORDER_MAIL_STEP_STATUS == 'true') ? true : false);
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
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_MAIL_STEP_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ORDER_MAIL_STEP_STATUS', 'false',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_ORDER_MAIL_STEP_ORDERS_STATUS_ID', '1',  '6', '1', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_orders_status_name', now())");
        
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_ORDER_MAIL_STEP_SUBJECT', 'DE::Ihre Bestellung \{\$nr\} vom \{\$date\}||EN::Your order \{\$nr\} from \{\$date\}', '12', '39', 'xtc_cfg_input_email_language;MODULE_ORDER_MAIL_STEP_SUBJECT', now())");
    }

    function remove()
    {
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_ORDER_MAIL_STEP_%'");
    }

    function keys() 
    {
        return array(
          'MODULE_ORDER_MAIL_STEP_STATUS',
          'MODULE_ORDER_MAIL_STEP_ORDERS_STATUS_ID',
        );
    }    
}
?>