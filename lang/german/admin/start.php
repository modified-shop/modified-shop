<?php
/* --------------------------------------------------------------
   $Id: start.php 2585 2012-01-03 14:25:49Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (start.php,v 1.1 2003/08/19); www.nextcommerce.org
   (c) 2006 xt:Commerce (start.php 890 2005-04-27); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('ATTENTION_TITLE','! ACHTUNG !');

// text for Warnings:
if (!defined('APS_INSTALL')) { //DokuMan - use alternative text for TEXT_FILE_WARNING when using APS package installation
  define('TEXT_FILE_WARNING_WRITABLE','<b>WARNUNG:</b><br />Folgende Dateien sind vom Server beschreibbar. Bitte &auml;ndern Sie die Zugriffsrechte (Permissions) dieser Datei aus Sicherheitsgr&uuml;nden. <b>(444)</b> in unix, <b>(read-only)</b> in Win32.');
} else {
  define('TEXT_FILE_WARNING_WRITABLE','<b>WARNUNG:</b><br />Folgende Dateien sind vom Server beschreibbar. Bitte &auml;ndern Sie die Zugriffsrechte (Permissions) dieser Datei aus Sicherheitsgr&uuml;nden. <b>(444)</b> in unix, <b>(read-only)</b> in Win32.<br />Falls die Installation durch ein Softwarepaket eines Providers ausgef&uuml;hrt wurde, sind die Zugriffsrechte evtl. anders anzupassen (HostEurope: <b>CHMOD 400</b> oder <b>CHMOD 440</b>)');
}
define('TEXT_FILE_WARNING','<b>WARNUNG:</b><br />Folgende Dateien m&uuml;ssen vom Server beschreibbar sein. Bitte &auml;ndern Sie die Zugriffsrechte (Permissions) dieser Dateien. <b>(777)</b> in unix, <b>(read-write)</b> in Win32.');
define('TEXT_FOLDER_WARNING','<b>WARNUNG:</b><br />Folgende Verzeichnisse m&uuml;ssen vom Server beschreibbar sein. Bitte &auml;ndern Sie die Zugriffsrechte (Permissions) dieser Verzeichnisse. <b>(777)</b> in unix, <b>(read-write)</b> in Win32.');
define('REPORT_GENERATED_FOR','Report f&uuml;r:');
define('REPORT_GENERATED_ON','Erstellt am:');
define('FIRST_VISIT_ON','Erster Besuch:');
define('HEADING_QUICK_STATS','Kurz&uuml;bersicht');
define('VISITS_TODAY','Besuche heute:');
define('UNIQUE_TODAY','Einzelne Besucher:');
define('DAILY_AVERAGE','T&auml;glicher Durchschnitt:');
define('TOTAL_VISITS','Besuche insgesammt:');
define('TOTAL_UNIQUE','Einzelbesucher insgesammt:');
define('TOP_REFFERER','Top Refferer:');
define('TOP_ENGINE','Top Suchmaschine:');
define('DAY_SUMMARY','30 Tage &Uuml;bersicht:');
define('VERY_LAST_VISITORS','Letzte 10 Besucher:');
define('TODAY_VISITORS','Besucher von heute:');
define('LAST_VISITORS','Letzte 100 Besucher:');
define('ALL_LAST_VISITORS','Alle Besucher:');
define('DATE_TIME','Datum / Uhrzeit:');
define('IP_ADRESS','IP-Adresse:');
define('OPERATING_SYSTEM','Betriebssystem:');
define('REFFERING_HOST','Referring Host:');
define('ENTRY_PAGE','Einstiegsseite:');
define('HOURLY_TRAFFIC_SUMMARY','St&uuml;ndliche Traffic Zusammenfassung');
define('WEB_BROWSER_SUMMARY','Web Browser &Uuml;bersicht');
define('OPERATING_SYSTEM_SUMMARY','Betriebssystem &Uuml;bersicht');
define('TOP_REFERRERS','Top 10 Referrer');
define('TOP_HOSTS','Top Ten Hosts');
define('LIST_ALL','Alle anzeigen');
define('SEARCH_ENGINE_SUMMARY','Suchmaschinen &Uuml;bersicht');
define('SEARCH_ENGINE_SUMMARY_TEXT',' ( Prozentangaben basieren auf die Gesamtzahl der Besuche &uuml;ber Suchmaschinen. )');
define('SEARCH_QUERY_SUMMARY','Suchanfragen &Uuml;bersicht');
define('SEARCH_QUERY_SUMMARY_TEXT',' ) ( Prozentangaben basieren auf die Gesamtzahl der Suchanfragen die geloggt wurden. )');
define('REFERRING_URL','Refferrer Url');
define('HITS','Hits');
define('PERCENTAGE','Prozentanteil');
define('HOST','Host');

// NEU HINZUGEFUEGT 04.12.2008 - Neue Startseite im Admin BOF

// BOF - vr 2010-04-01 -  Added missing definitions, see below
// define('HEADING_TITLE', 'Bestellungen');
// EOF - vr 2010-04-01 -  Added missing definitions
define('HEADING_TITLE_SEARCH', 'Bestell-Nr.:');
define('HEADING_TITLE_STATUS', 'Status:');
define('TABLE_HEADING_AFTERBUY', 'Afterbuy'); //Dokuman - 2009-05-27 - added missing definition
define('TABLE_HEADING_CUSTOMERS', 'Kunden');
define('TABLE_HEADING_ORDER_TOTAL', 'Gesamtwert');
define('TABLE_HEADING_DATE_PURCHASED', 'Bestelldatum');
define('TABLE_HEADING_STATUS', 'Status');
//define('TABLE_HEADING_ACTION', 'Aktion');
define('TABLE_HEADING_QUANTITY', 'Anzahl');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Artikel-Nr.');
define('TABLE_HEADING_PRODUCTS', 'Artikel');
define('TABLE_HEADING_TAX', 'MwSt.');
define('TABLE_HEADING_TOTAL', 'Gesamtsumme');
define('TABLE_HEADING_DATE_ADDED', 'hinzugef&uuml;gt am:');
define('ENTRY_CUSTOMER', 'Kunde:');
define('TEXT_DATE_ORDER_CREATED', 'Bestelldatum:');
define('TEXT_INFO_PAYMENT_METHOD', 'Zahlungsweise:');
define('TEXT_VALIDATING','Nicht best&auml;tigt');
define('TEXT_ALL_ORDERS', 'Alle Bestellungen');
define('TEXT_NO_ORDER_HISTORY', 'Keine Bestellhistorie verf&uuml;gbar');
define('TEXT_DATE_ORDER_LAST_MODIFIED','Letzte &Auml;nderung');

// BOF - Tomcraft - 2009-11-25 - Added missing definitions for /admin/start.php/
define('TOTAL_CUSTOMERS','Kunden gesamt');
define('TOTAL_SUBSCRIBERS','Newsletter Abos');
define('TOTAL_PRODUCTS_ACTIVE','Aktive Artikel');
define('TOTAL_PRODUCTS_INACTIVE','Inaktive Artikel');
define('TOTAL_PRODUCTS','Artikel gesamt');
define('TOTAL_SPECIALS','Sonderangebote');
// EOF - Tomcraft - 2009-11-25 - Added missing definitions for /admin/start.php/
// BOF - Tomcraft - 2009-11-30 - Added missing definitions for /admin/start.php/
define('UNASSIGNED', 'Nicht zugeordnet');
define('TURNOVER_TODAY', 'Umsatz heute');
define('TURNOVER_YESTERDAY', 'Umsatz gestern');
define('TURNOVER_THIS_MONTH', 'aktueller Monat');
define('TURNOVER_LAST_MONTH', 'letzter Monat (alle)');
define('TURNOVER_LAST_MONTH_PAID', 'letzter Monat (bezahlt)');
define('TOTAL_TURNOVER', 'Umsatz gesamt');
// EOF - Tomcraft - 2009-11-30 - Added missing definitions for /admin/start.php/

// BOF - vr 2010-04-01 -  Added missing definitions
// main heading
define('HEADING_TITLE', 'Willkommen im Adminbereich');
// users online
define('TABLE_CAPTION_USERS_ONLINE', 'Online');
define('TABLE_CAPTION_USERS_ONLINE_HINT', '***f&uuml;r Infos zu einem User - auf Namen des Users klicken***');
define('TABLE_HEADING_USERS_ONLINE_SINCE', 'Online seit');
define('TABLE_HEADING_USERS_ONLINE_NAME', 'Name');
define('TABLE_HEADING_USERS_ONLINE_LAST_CLICK', 'Letzter Klick');
define('TABLE_HEADING_USERS_ONLINE_INFO', 'Infos');
define('TABLE_CELL_USERS_ONLINE_INFO', 'mehr...');
// new customers
define('TABLE_CAPTION_NEW_CUSTOMERS', 'Kunden');
define('TABLE_CAPTION_NEW_CUSTOMERS_COMMENT', '(die letzten 15)');
define('TABLE_HEADING_NEW_CUSTOMERS_LASTNAME', 'Name');
define('TABLE_HEADING_NEW_CUSTOMERS_FIRSTNAME', 'Vorname');
define('TABLE_HEADING_NEW_CUSTOMERS_REGISTERED', 'angemeldet am');
define('TABLE_HEADING_NEW_CUSTOMERS_EDIT', 'bearbeiten');
define('TABLE_HEADING_NEW_CUSTOMERS_ORDERS', 'Bestellungen');
define('TABLE_CELL_NEW_CUSTOMERS_EDIT', 'bearbeiten...');
define('TABLE_CELL_NEW_CUSTOMERS_DELETE', 'l&ouml;schen...');
define('TABLE_CELL_NEW_CUSTOMERS_ORDERS', 'anzeigen...');
// new orders
define('TABLE_CAPTION_NEW_ORDERS', 'Bestellungen');
define('TABLE_CAPTION_NEW_ORDERS_COMMENT', '(die letzten 20)');
define('TABLE_HEADING_NEW_ORDERS_ORDER_NUMBER', 'Bestellnummer');
define('TABLE_HEADING_NEW_ORDERS_ORDER_DATE', 'Bestelldatum');
define('TABLE_HEADING_NEW_ORDERS_CUSTOMERS_NAME', 'Kundenname');
define('TABLE_HEADING_NEW_ORDERS_EDIT', 'bearbeiten');
define('TABLE_HEADING_NEW_ORDERS_DELETE', 'l&ouml;schen');
// newsfeed
define('TABLE_CAPTION_NEWSFEED', 'Besuchen Sie den');
// birthdays
define('TABLE_CAPTION_BIRTHDAYS', 'Geburtstagsliste');
define('TABLE_CELL_BIRTHDAYS_TODAY', 'Kunden, die heute Geburtstag haben');
define('TABLE_CELL_BIRTHDAYS_THIS_MONTH', 'Kunden, die noch in diesem Monat Geburtstag haben');
// EOF - vr 2010-04-01 -  Added missing definitions
define('HEADING_CAPTION_STATISTIC', 'Statistik');
// security check

// DB version check
define('ERROR_DB_VERSION_UPDATE', '<strong>WARNUNG:</strong> Ihre DB muss aktualisiert werden, bitte den <a href="'.DIR_WS_CATALOG.'_installer/">Installer</a> ausf&uuml;hren:');
define('ERROR_DB_VERSION_UPDATE_INFO', 'DB muss von Release %s auf %s aktualisiert werden.');

// EMail check
define('ERROR_EMAIL_CHECK', '<strong>WARNUNG:</strong> Folgende E-Mail-Adressen sind anscheinend fehlerhaft:');
define('ERROR_EMAIL_CHECK_INFO', '%s: &lt;%s&gt;');

// security check DB FILE permission
define('WARNING_DB_FILE_PRIVILEGES', '<strong>WARNUNG:</strong> FILE-Privileges sind in der Datenbank &rsquo;'.DB_DATABASE.'&lsquo; f&uuml;r den Shop-User &rsquo;'.DB_SERVER_USERNAME.'&lsquo; aktiviert!');

// register_globals check
define('WARNING_REGISTER_GLOBALS', '<strong>WARNUNG:</strong> Dieses Feature ist seit PHP 5.3.0 <strong>DEPRECATED</strong> (veraltet) und seit PHP 5.4.0 <strong>ENTFERNT</strong>. Bitte wenden Sie sich an Ihren Hoster um &quot;register_globals&quot; zu deaktivieren.');
?>