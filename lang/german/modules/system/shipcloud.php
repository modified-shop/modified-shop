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
  define('MODULE_SHIPCLOUD_TEXT_TITLE', 'Shipcloud - die neue Generation des Paketversandes');
  define('MODULE_SHIPCLOUD_TEXT_DESCRIPTION', 'Bequem Paketscheine aus dem Shop heraus drucken.');
  define('MODULE_SHIPCLOUD_STATUS_TITLE', 'Status');
  define('MODULE_SHIPCLOUD_STATUS_DESC', 'Modul aktivieren');
  define('MODULE_SHIPCLOUD_API_TITLE', '<hr noshade>API');
  define('MODULE_SHIPCLOUD_API_DESC', 'API Key von Shipcloud');
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
  define('MODULE_SHIPCLOUD_LOG_TITLE', '<hr noshade>Log');
  define('MODULE_SHIPCLOUD_LOG_DESC', 'die Logdatei wird im Ordner /log abgelegt.');
  define('MODULE_SHIPCLOUD_EMAIL_TITLE', '<hr noshade>E-Mail Benachrichtigung');
  define('MODULE_SHIPCLOUD_EMAIL_DESC', 'Soll der Kunde per E-Mail benachrichtigt werden?');
  define('MODULE_SHIPCLOUD_EMAIL_TYPE_TITLE', '<hr noshade>Benachrichtigung');
  define('MODULE_SHIPCLOUD_EMAIL_TYPE_DESC', 'Soll der Kunde vom Shop oder von Shipcloud benachrichtigt werden ?<br><Hinweis:</b>F&uuml;r eine Benachrichtigung vom Shop muss ein Webhook auf diese URL: '.xtc_catalog_href_link('callback/shipcloud/callback.php', '', 'SSL', false).' in Shipcloud erstelt werden.');
}

define('SHIPMENT.TRACKING.SHIPCLOUD_LABEL_CREATED', 'Paketschein bei Shipcloud erstellt');
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

define('EXAMPLE.EVENT', 'Test');
?>