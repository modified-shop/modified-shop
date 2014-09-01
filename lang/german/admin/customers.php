<?php
/* --------------------------------------------------------------
   $Id: customers.php 2666 2012-02-23 11:38:17Z dokuman $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(customers.php,v 1.13 2002/06/15); www.oscommerce.com 
   (c) 2003 nextcommerce (customers.php,v 1.8 2003/08/15); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Kunden');
define('HEADING_TITLE_SEARCH', 'Suche:');

define('TABLE_HEADING_CUSTOMERSCID','Kundennummer');
define('TABLE_HEADING_FIRSTNAME', 'Vorname');
define('TABLE_HEADING_LASTNAME', 'Nachname');
define('TABLE_HEADING_ACCOUNT_CREATED', 'Zugang erstellt am');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_DATE_ACCOUNT_CREATED', 'Zugang erstellt am:');
define('TEXT_DATE_ACCOUNT_LAST_MODIFIED', 'letzte &Auml;nderung:');
define('TEXT_INFO_DATE_LAST_LOGON', 'letzte Anmeldung:');
define('TEXT_INFO_NUMBER_OF_LOGONS', 'Anzahl der Anmeldungen:');
define('TEXT_INFO_COUNTRY', 'Land:');
define('TEXT_INFO_NUMBER_OF_REVIEWS', 'Anzahl der Artikelbewertungen:');
define('TEXT_DELETE_INTRO', 'Wollen Sie diesen Kunden wirklich l&ouml;schen?');
define('TEXT_DELETE_REVIEWS', '%s Bewertung(en) l&ouml;schen');
define('TEXT_INFO_HEADING_DELETE_CUSTOMER', 'Kunden l&ouml;schen');
define('TYPE_BELOW', 'Bitte unten eingeben');
define('PLEASE_SELECT', 'Ausw&auml;hlen');
define('HEADING_TITLE_STATUS','Kundengruppe');
define('TEXT_ALL_CUSTOMERS','Alle Gruppen');
define('TEXT_INFO_HEADING_STATUS_CUSTOMER','Kundengruppe');
define('TABLE_HEADING_NEW_VALUE','Neuer Status');
define('TABLE_HEADING_DATE_ADDED','Datum');
define('TEXT_NO_CUSTOMER_HISTORY','--Keine &Auml;nderung bisher--');
define('TABLE_HEADING_GROUPIMAGE','Icon');
define('ENTRY_MEMO','Memo');
define('TEXT_DATE','Datum');
define('TEXT_TITLE','Titel');
define('TEXT_POSTER','Verfasser');
define('ENTRY_PASSWORD_CUSTOMER','Passwort:');
define('TABLE_HEADING_ACCOUNT_TYPE','Konto');
define('TEXT_ACCOUNT','Ja');
define('TEXT_GUEST','Nein');
define('NEW_ORDER','Neue Bestellung ?');
define('ENTRY_PAYMENT_UNALLOWED','Nicht erlaubte Zahlungsmodule:');
define('ENTRY_SHIPPING_UNALLOWED','Nicht erlaubte Versandmodule:');
define('ENTRY_NEW_PASSWORD','Neues Passwort:');

// NEU HINZUGEFUEGT 04.12.2008 - UMSATZANZEIGE BEI KUNDEN 03.12.2008
define('TABLE_HEADING_UMSATZ','Umsatz');

// BOF - web28 - 2010-05-28 - added  customers_email_address
define('TABLE_HEADING_EMAIL','E-Mail');
// EOF - web28 - 2010-05-28 - added  customers_email_address

define('TEXT_INFO_HEADING_ADRESS_BOOK', 'Adressbuch');
define('TEXT_INFO_DELETE', '<b>Diesen Adressbucheintrag l&ouml;schen?</b>');
define('TEXT_INFO_DELETE_DEFAULT', '<b>Dieser Adressbucheintrag kann nicht gel&ouml;scht werden!</b>'); 
?>