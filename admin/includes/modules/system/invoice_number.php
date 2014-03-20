<?php
/* -----------------------------------------------------------------------------------------
Module invoice_number installer
Version 1.00
(c) 2014 by www.rpa-com.de
   ---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );


if ($_SESSION['language_code'] == 'de') {
    define('MODULE_INVOICE_NUMBER_STATUS_TITLE','Status');
    define('MODULE_INVOICE_NUMBER_STATUS_DESC','Module status');
    define('MODULE_INVOICE_NUMBER_STATUS_INFO','Aktiv');
    define('MODULE_INVOICE_NUMBER_TEXT_TITLE', 'Modul Neue Rechnungsnummer');
    define('MODULE_INVOICE_NUMBER_TEXT_BTN', 'Bearbeiten');
    define('MODULE_INVOICE_NUMBER_TEXT_DESCRIPTION', ' Modul Neue Rechnungsnummer');    
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_TITLE', 'N&aumlchste Rechnungsnummer');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_DESC', 'Bei Zuweisung einer Bestellung wird diese Nummer als n&auml;chstes vergeben.');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT_TITLE', 'Rechnungsnummer Format');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT_DESC', 'Aufbauschema Rechn.Nr.: {n}=laufende Nummer, {d}=Tag, {m}=Monat, {y}=Jahr, <br>z.B. "100{n}-{d}-{m}-{y}" ergibt "10099-28-02-2007"');
} else {
    define('MODULE_INVOICE_NUMBER_STATUS_TITLE','Status');
    define('MODULE_INVOICE_NUMBER_STATUS_DESC','Module status');
    define('MODULE_INVOICE_NUMBER_STATUS_INFO','activ');
    define('MODULE_INVOICE_NUMBER_TEXT_TITLE', 'Modul New invoice number ');
    define('MODULE_INVOICE_NUMBER_TEXT_BTN', 'Edit');
    define('MODULE_INVOICE_NUMBER_TEXT_DESCRIPTION', 'Modul New invoice number');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_TITLE', 'Next invoice number');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_DESC', 'When assigning an invoice number, this number is given next.');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT_TITLE', 'Invoice number format');
    define('MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT_DESC', 'Format invoice number.: {n}=number, {d}=day, {m}=month, {y}=year, <br>example. "100{n}-{d}-{m}-{y}" => "10099-28-02-2007"');
}


if ( !class_exists( "easypopulate" ) ) {
    class invoice_number
    {
        var $code, $title, $description, $enabled;

        function invoice_number() 
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
