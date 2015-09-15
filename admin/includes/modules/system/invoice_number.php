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

if (!class_exists('invoice_number')) {
    class invoice_number
    {
        var $code, $title, $description, $enabled;

        function __construct() 
        {
            $this->code = 'invoice_number';
            $this->properties['process_key'] = true;
            $this->properties['btn_edit'] = MODULE_INVOICE_NUMBER_TEXT_BTN;
            $this->title = MODULE_INVOICE_NUMBER_TEXT_TITLE;
            $this->description = MODULE_INVOICE_NUMBER_TEXT_DESCRIPTION;
            $this->sort_order = MODULE_INVOICE_NUMBER_SORT_ORDER;
            $this->enabled = ((MODULE_INVOICE_NUMBER_STATUS == 'True') ? true : false);
            if ($this->enabled) {
                $this->description .= '<p>'.MODULE_INVOICE_NUMBER_STATUS_DESC .': '.MODULE_INVOICE_NUMBER_STATUS_INFO.'</p>';
            }
        }

        function process($file) 
        {
            //do nothing
        }

        function display() 
        {
            return array('text' => 
                    '<br>' . xtc_button(BUTTON_REVIEW_APPROVE) . '&nbsp;' .
                    xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                    );
        }

        function check() 
        {
            if(!isset($this->_check)) {
              $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_INVOICE_NUMBER_STATUS'");
              $this->_check = xtc_db_num_rows($check_query);
            }
            return $this->_check;
        }

        function install() 
        {
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_INVOICE_NUMBER_STATUS', 'True',  '6', '1', now())");
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_INVOICE_NUMBER_IBN_BILLNR', '1',  '6', '1', now())");
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT', '100{n}-{d}-{m}-{y}',  '6', '1', now())");
            $this->install_db();
        }

        function remove()
        {
            xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_INVOICE_NUMBER_%'");
            //$this->uninstall_db();
        }

        function keys() 
        {
            return array('MODULE_INVOICE_NUMBER_IBN_BILLNR','MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT');
        }
        
        function install_db() 
        {
            xtc_db_query("ALTER TABLE " . TABLE_ORDERS . " ADD `ibn_billnr` VARCHAR(32);");
            xtc_db_query("ALTER TABLE " . TABLE_ORDERS . " ADD `ibn_billdate` DATE NOT NULL;");
        }
        
        function uninstall_db() 
        {
            xtc_db_query("ALTER TABLE " . TABLE_ORDERS . " DROP `ibn_billnr`;");
            xtc_db_query("ALTER TABLE " . TABLE_ORDERS . " DROP `ibn_billdate`;");
        }
    }
}
?>
