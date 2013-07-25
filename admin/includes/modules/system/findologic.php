<?php
/* -----------------------------------------------------------------------------------------
   $Id: findologic.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003   nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2005 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: billiger.php 950 2005-05-14 16:45:21Z mz $)
   (c) 2008 Gambio OHG (billiger.php 2008-11-11 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_FINDOLOGIC_TEXT_TITLE', 'FINDOLOGIC Shopsuche - stop searching - find!');
define('MODULE_FINDOLOGIC_TEXT_DESCRIPTION', 'FINDOLOGIC ist die ultimative Suchl&ouml;sung f&uuml;r Ihren Online-Shop. Ihre Kunden finden blitzschnell den gew&uuml;nschten Artikel und kaufen ihn!<span style="color: #ff8c00;"> Dabei sind Umsatzsteigerungen unserer Kunden im zweistelligen Prozentbereich belegt.</span><br /><br />Mit dem Partnercode <span style="color:red;font-weight:bold;">qXgXj</span> sparen Sie 50&euro; AKtivierungsgeb&uuml;hr!<br /><br /><a href="https://secure.findologic.com/bestellung?partner=qXgXj" target="_blank"><strong><u>Jetzt anmelden!</u></strong></a>');

define('MODULE_FINDOLOGIC_SHOP_ID_TITLE' , '<hr noshade>Shopkey');
define('MODULE_FINDOLOGIC_SHOP_ID_DESC' , 'Ihr Shopkey<br />Sie finden den Shopkey im FINDOLOGIC Kundenaccount &rarr; Men&uuml; Account &rarr; Stammdaten.');

//define('MODULE_FINDOLOGIC_SHOP_URL_TITLE' , '<hr noshade>Shop-URL'); // Changed to static value
//define('MODULE_FINDOLOGIC_SHOP_URL_DESC' , 'Die URL Ihres Onlineshops<br /><strong>WICHTIG:</strong> Vergessen Sie bei den URLs nicht den Slash am Ende, da es sonst zu Problemen bei der Darstellung der Ergebnisse kommt.<br />Sie finden die Shop-URL im FINDOLOGIC Kundenaccount &rarr; Men&uuml; Account &rarr; Stammdaten.'); // Changed to static value

define('MODULE_FINDOLOGIC_SERVICE_URL_TITLE' , '<hr noshade>FINDOLOGIC/Service-URL');
define('MODULE_FINDOLOGIC_SERVICE_URL_DESC' , 'Die FINDOLOGIC/Service-URL Ihres Onlineshops<br /><strong>WICHTIG:</strong> Vergessen Sie bei den URLs nicht den Slash am Ende, da es sonst zu Problemen bei der Darstellung der Ergebnisse kommt.<br />Sie finden die FINDOLOGIC/Service-URL im FINDOLOGIC Kundenaccount &rarr; Men&uuml; Account &rarr; Stammdaten.');

//define('MODULE_FINDOLOGIC_NET_PRICE_TITLE' , '<hr noshade>Netto Preise?'); // Changed to static value
//define('MODULE_FINDOLOGIC_NET_PRICE_DESC' , 'M&ouml;chten Sie Nettp-Preise exportieren?'); // Changed to static value

//define('MODULE_FINDOLOGIC_ALIVE_TEST_TIMEOUT_TITLE' , '<hr noshade>Alive Test Timeout'); // Changed to static value
//define('MODULE_FINDOLOGIC_ALIVE_TEST_TIMEOUT_DESC' , 'Timeout Zeit in Sekunden einstellen (default: 1)'); // Changed to static value

//define('MODULE_FINDOLOGIC_REQUEST_TIMEOUT_TITLE' , '<hr noshade>Request Timeout'); // Changed to static value
//define('MODULE_FINDOLOGIC_REQUEST_TIMEOUT_DESC' , 'Timeout Zeit in Sekunden einstellen (default: 3)'); // Changed to static value

define('MODULE_FINDOLOGIC_EXPORT_FILENAME_TITLE' , '<hr noshade>Dateiname');
define('MODULE_FINDOLOGIC_EXPORT_FILENAME_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.
(Verzeichnis export/)');

//define('MODULE_FINDOLOGIC_REVISION_TITLE' , '<hr noshade>Version'); // Changed to static value
//define('MODULE_FINDOLOGIC_REVISION_DESC' , 'Die Versionsnummer des Moduls'); // Changed to static value

define('MODULE_FINDOLOGIC_LANG_TITLE' , '<hr noshade>Sprache');
define('MODULE_FINDOLOGIC_LANG_DESC' , 'Sprache der Artikel, die Sie exportieren wollen');

define('MODULE_FINDOLOGIC_CUSTOMER_GROUP_TITLE' , '<hr noshade>Kundengruppe:');
define('MODULE_FINDOLOGIC_CUSTOMER_GROUP_DESC' , 'Bitte w&auml;hlen Sie die Kundengruppe, die Basis f&uuml;r den Exportierten Preis bildet. (Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie Gast):');

define('MODULE_FINDOLOGIC_CURRENCY_TITLE' , '<hr noshade>W&auml;hrung:');
define('MODULE_FINDOLOGIC_CURRENCY_DESC' , 'W&auml;hrung in der Exportdatei');

define('MODULE_FINDOLOGIC_STATUS_DESC','Modul aktivieren?');
define('MODULE_FINDOLOGIC_STATUS_TITLE','Status');


// include needed functions
class findologic {
  var $code, $title, $description, $enabled;

  function findologic() {
    global $order;

     $this->code = 'findologic';
     $this->title = MODULE_FINDOLOGIC_TEXT_TITLE;
     $this->description = MODULE_FINDOLOGIC_TEXT_DESCRIPTION;
     $this->sort_order = MODULE_FINDOLOGIC_SORT_ORDER;
     $this->enabled = ((MODULE_FINDOLOGIC_STATUS == 'True') ? true : false);
     $this->CAT=array();
     $this->PARENT=array();
   }

  function process($file) {

  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button('OK') .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=findologic')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_FINDOLOGIC_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_SHOP_ID', 'ABCDEFABCDEFABCDEFABCDEFABCDEFAB',  '6', '1', '', now())");
    //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_SHOP_URL', 'http://www.mein-laden.de/shop/',  '6', '1', '', now())"); // Changed to static value
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_SERVICE_URL', 'http://srvXY.findologic.com/ps/mein-laden.de/',  '6', '1', '', now())");
    //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_NET_PRICE', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())"); // Changed to static value
    //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_ALIVE_TEST_TIMEOUT', '1',  '6', '1', '', now())"); // Changed to static value
    //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_REQUEST_TIMEOUT', '3',  '6', '1', '', now())"); // Changed to static value
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_EXPORT_FILENAME', 'findologic.csv',  '6', '1', '', now())");
    //xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_REVISION', '204',  '6', '1', '', now())"); // Changed to static value
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_LANG', 'de',  '6', '1', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_CUSTOMER_GROUP', '1',  '6', '1', '', now())"); // interne Funktion vorhanden?
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_CURRENCY', 'EUR',  '6', '1', '', now())"); // interne Funktion vorhanden?
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
  }

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    //return array('MODULE_FINDOLOGIC_STATUS','MODULE_FINDOLOGIC_SHOP_ID','MODULE_FINDOLOGIC_SHOP_URL','MODULE_FINDOLOGIC_SERVICE_URL','MODULE_FINDOLOGIC_NET_PRICE','MODULE_FINDOLOGIC_ALIVE_TEST_TIMEOUT','MODULE_FINDOLOGIC_REQUEST_TIMEOUT','MODULE_FINDOLOGIC_EXPORT_FILENAME','MODULE_FINDOLOGIC_REVISION','MODULE_FINDOLOGIC_LANG','MODULE_FINDOLOGIC_CUSTOMER_GROUP','MODULE_FINDOLOGIC_CURRENCY');
    return array('MODULE_FINDOLOGIC_STATUS','MODULE_FINDOLOGIC_SHOP_ID','MODULE_FINDOLOGIC_SERVICE_URL','MODULE_FINDOLOGIC_EXPORT_FILENAME','MODULE_FINDOLOGIC_LANG','MODULE_FINDOLOGIC_CUSTOMER_GROUP','MODULE_FINDOLOGIC_CURRENCY'); // Changed to static value
  }
}
?>