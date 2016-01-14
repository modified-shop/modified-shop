<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (defined('_VALID_XTC')) {
  define('MODULE_SHIPCLOUD_TEXT_TITLE', 'shipcloud - die neue Generation des Paketversandes');
  define('MODULE_SHIPCLOUD_TEXT_DESCRIPTION', 'Bequem Paketscheine aus dem Shop heraus drucken.');
  define('MODULE_SHIPCLOUD_STATUS_TITLE', 'Status');
  define('MODULE_SHIPCLOUD_STATUS_DESC', 'Modul aktivieren');
  define('MODULE_SHIPCLOUD_API_TITLE', '<hr noshade>API');
  define('MODULE_SHIPCLOUD_API_DESC', 'API Key von shipcloud');
  define('MODULE_SHIPCLOUD_PARCEL_TITLE', '<hr noshade>Paketgr&ouml;ssen');
  define('MODULE_SHIPCLOUD_PARCEL_DESC', 'Bitte geben Sie die Paketgr&ouml;ssen in cm folgendermassen ein: L&auml;nge,Breite,H&ouml;he;<br/>Meherer Paketmasse k&ouml;nnen mit Semikolon (;) getrennt angegeben werden. zB: 20,40,30;15,20,20;');
  define('MODULE_SHIPCLOUD_COMPANY_TITLE', '<hr noshade>Kundendetails<br/>');
  define('MODULE_SHIPCLOUD_COMPANY_DESC', 'Firma:');
  define('MODULE_SHIPCLOUD_FIRSTNAME_TITLE', '');
  define('MODULE_SHIPCLOUD_FIRSTNAME_DESC', 'Vorname:');
  define('MODULE_SHIPCLOUD_LASTNAME_TITLE', '');
  define('MODULE_SHIPCLOUD_LASTNAME_DESC', 'Nachname:');
  define('MODULE_SHIPCLOUD_ADDRESS_TITLE', '');
  define('MODULE_SHIPCLOUD_ADDRESS_DESC', 'Adresse:');
  define('MODULE_SHIPCLOUD_POSTCODE_TITLE', '');
  define('MODULE_SHIPCLOUD_POSTCODE_DESC', 'PLZ:');
  define('MODULE_SHIPCLOUD_CITY_TITLE', '');
  define('MODULE_SHIPCLOUD_CITY_DESC', 'Stadt:');
  define('MODULE_SHIPCLOUD_TELEPHONE_TITLE', '');
  define('MODULE_SHIPCLOUD_TELEPHONE_DESC', 'Telefon:');
  define('MODULE_SHIPCLOUD_ACCOUNT_IBAN_TITLE', '');
  define('MODULE_SHIPCLOUD_ACCOUNT_IBAN_DESC', 'IBAN:');
  define('MODULE_SHIPCLOUD_ACCOUNT_BIC_TITLE', '');
  define('MODULE_SHIPCLOUD_ACCOUNT_BIC_DESC', 'BIC:');
  define('MODULE_SHIPCLOUD_BANK_NAME_TITLE', '<hr noshade>Bankdetails<br/>');
  define('MODULE_SHIPCLOUD_BANK_NAME_DESC', 'Bank:');
  define('MODULE_SHIPCLOUD_BANK_HOLDER_TITLE', '');
  define('MODULE_SHIPCLOUD_BANK_HOLDER_DESC', 'Kontoinhaber:');
  define('MODULE_SHIPCLOUD_LOG_TITLE', '<hr noshade>Log');
  define('MODULE_SHIPCLOUD_LOG_DESC', 'die Logdatei wird im Ordner /log abgelegt.');
  define('MODULE_SHIPCLOUD_EMAIL_TITLE', '<hr noshade>E-Mail Benachrichtigung');
  define('MODULE_SHIPCLOUD_EMAIL_DESC', 'Soll der Kunde per E-Mail benachrichtigt werden?');
  define('MODULE_SHIPCLOUD_EMAIL_TYPE_TITLE', '<hr noshade>Benachrichtigung');
  define('MODULE_SHIPCLOUD_EMAIL_TYPE_DESC', 'Soll der Kunde vom Shop oder von shipcloud benachrichtigt werden ?<br><Hinweis:</b>F&uuml;r eine Benachrichtigung vom Shop muss ein Webhook auf diese URL: '.xtc_catalog_href_link('callback/shipcloud/callback.php', '', 'SSL', false).' in shipcloud erstelt werden.');
}

define('SHIPMENT.TRACKING.SHIPCLOUD_LABEL_CREATED', 'Paketschein bei shipcloud erstellt');
define('SHIPMENT.TRACKING.LABEL_CREATED', 'Paketschein erstellt');
define('SHIPMENT.TRACKING.PICKED_UP', 'Paket durch Zusteller abgeholt');
define('SHIPMENT.TRACKING.TRANSIT', 'Paket ist auf dem Weg');
define('SHIPMENT.TRACKING.OUT_FOR_DELIVERY', 'Paket wird zugestellt');
define('SHIPMENT.TRACKING.DELIVERED', 'Paket zugestellt');
define('SHIPMENT.TRACKING.AWAITS_PICKUP_BY_RECEIVER', 'Warten auf Abholung durch Kunden');
define('SHIPMENT.TRACKING.CANCELED', 'Paketschein wurde gel&uuml;scht');
define('SHIPMENT.TRACKING.DELAYED', 'Auslieferung verz&ouml;gert sich');
define('SHIPMENT.TRACKING.EXCEPTION', 'Ein Problem wurde festgestellt');
define('SHIPMENT.TRACKING.NOT_DELIVERED', 'nicht zugestellt');
define('SHIPMENT.TRACKING.NOTIFICATION', 'Interne Mitteilung: Tracking- Ereignisse innerhalb der Sendung ben&ouml;tigt aufw&auml;ndigere Informationen.');
define('SHIPMENT.TRACKING.UNKNOWN', 'Status unbekannt');

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class shipcloud {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

     $this->code = 'shipcloud';
     $this->title = MODULE_SHIPCLOUD_TEXT_TITLE;
     $this->description = MODULE_SHIPCLOUD_TEXT_DESCRIPTION;
     $this->enabled = ((MODULE_SHIPCLOUD_STATUS == 'True') ? true : false);
   }

  function process($file) {
  }

  function display() {
    return array('text' => '<div align="center">' . xtc_button('OK') .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=shipcloud')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . " 
                                    WHERE configuration_key = 'MODULE_SHIPCLOUD_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_STATUS', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_API', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_EMAIL', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_EMAIL_TYPE', 'Shop',  '6', '1', 'xtc_cfg_select_option(array(\'Shop\', \'shipcloud\'), ', now())");    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_LOG', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");    
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_COMPANY', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_FIRSTNAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_LASTNAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_ADDRESS', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_POSTCODE', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_CITY', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_TELEPHONE', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_BANK_HOLDER', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_BANK_NAME', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_ACCOUNT_IBAN', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPCLOUD_ACCOUNT_BIC', '',  '6', '1', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPCLOUD_PARCEL', '20,40,30;15,20,20;', '6', '1', 'xtc_cfg_textarea(', now())");

    $table_array = array(
      array('column' => 'sc_label_url', 'default' => 'VARCHAR(512) NOT NULL'),
      array('column' => 'sc_id', 'default' => 'VARCHAR(256) NOT NULL'),
      array('column' => 'sc_date_added', 'default' => 'DATETIME NOT NULL'),
      array('column' => 'sc_date_pickup', 'default' => 'DATETIME NOT NULL'),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_ORDERS_TRACKING." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_ORDERS_TRACKING." ADD ".$table['column']." ".$table['default']."");
      }
    }

    $admin_query = xtc_db_query("SELECT * 
                                   FROM ".TABLE_ADMIN_ACCESS."
                                  LIMIT 1");
    $admin = xtc_db_fetch_array($admin_query);
    if (!isset($admin['shipcloud_pickup'])) {
      xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD `shipcloud_pickup` INT(1) DEFAULT '0' NOT NULL");
      xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET shipcloud_pickup = '1' WHERE customers_id = '1' LIMIT 1");        
      if ($_SESSION['customer_id'] > 1) {
        xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET shipcloud_pickup = '1' WHERE customers_id = '".$_SESSION['customer_id']."' LIMIT 1") ;
      }
    }
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    return array('MODULE_SHIPCLOUD_STATUS',
                 'MODULE_SHIPCLOUD_API',
                 'MODULE_SHIPCLOUD_EMAIL',
                 'MODULE_SHIPCLOUD_EMAIL_TYPE',
                 'MODULE_SHIPCLOUD_PARCEL',
                 'MODULE_SHIPCLOUD_COMPANY',
                 'MODULE_SHIPCLOUD_FIRSTNAME',
                 'MODULE_SHIPCLOUD_LASTNAME',
                 'MODULE_SHIPCLOUD_ADDRESS',
                 'MODULE_SHIPCLOUD_POSTCODE',
                 'MODULE_SHIPCLOUD_CITY',
                 'MODULE_SHIPCLOUD_TELEPHONE',
                 'MODULE_SHIPCLOUD_BANK_NAME',
                 'MODULE_SHIPCLOUD_BANK_HOLDER',
                 'MODULE_SHIPCLOUD_ACCOUNT_IBAN',
                 'MODULE_SHIPCLOUD_ACCOUNT_BIC',
                 'MODULE_SHIPCLOUD_LOG',
                 );
  }
}
?>