<?php
/* -----------------------------------------------------------------------------------------
   $Id: coupon_admin.php 2094 2011-08-15 14:56:49Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(coupon_admin.php,v 1.1.2.5 2003/05/13); www.oscommerce.com
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('TOP_BAR_TITLE', 'Statistik');
define('HEADING_TITLE', 'Rabatt Coupons');
define('HEADING_TITLE_STATUS', 'Status : ');
define('TEXT_CUSTOMER', 'Kunde:');
define('TEXT_COUPON', 'Coupon Name');
define('TEXT_COUPON_ALL', 'Alle Coupons');
define('TEXT_COUPON_ACTIVE', 'Aktive Coupons');
define('TEXT_COUPON_INACTIVE', 'Inaktive Coupons');
define('TEXT_SUBJECT', 'Betreff:');
define('TEXT_FROM', 'von:');
define('TEXT_FREE_SHIPPING', 'Versandkostenfrei');
define('TEXT_MESSAGE', 'Nachricht:');
define('TEXT_SELECT_CUSTOMER', 'Kunde ausw&auml;hlen');
define('TEXT_ALL_CUSTOMERS', 'Alle Kunden');
define('TEXT_NEWSLETTER_CUSTOMERS', 'Alle Newsletter Abonnenten');
define('TEXT_CONFIRM_DELETE', 'Mit dieser Funktion erh&auml;lt der selektierte Coupon den Status <b>inaktiv</b>. Eine Re-Aktivierung ist zu einem späteren Zeitpunkt nicht mehr möglich.<br /><br />Soll dieser Coupon wirklich den Status inaktiv erhalten?');

define('TEXT_TO_REDEEM', 'Sie k&ouml;nnen den Gutschein bei Ihrer Bestellung einl&ouml;sen. Dazu geben Sie Ihren Gutschein-Code in das daf&uuml;r vorgesehene Feld ein, und klicken Sie den "Einl&ouml;sen"-Button.');
define('TEXT_IN_CASE', ' Falls es wider Erwarten zu Problemen beim verbuchen kommen sollte.');
define('TEXT_VOUCHER_IS', 'Ihr Gutschein-Code lautet: ');
define('TEXT_REMEMBER', 'Bewahren Sie Ihren Gutschein-Code gut auf, damit Sie von diesem Angebot profitieren k&ouml;nnen');
define('TEXT_VISIT', 'wenn Sie uns das n&auml;chste mal unter ' . HTTP_SERVER . DIR_WS_CATALOG. ' besuchen.');
define('TEXT_ENTER_CODE', ' und den Code eingeben ');

define('TABLE_HEADING_ACTION', 'Aktion');

define('CUSTOMER_ID', 'Kunden Nr.');
define('CUSTOMER_NAME', 'Kunden Name');
define('REDEEM_DATE', 'eingel&ouml;st am');
define('IP_ADDRESS', 'IP Adresse');

define('TEXT_REDEMPTIONS', 'Einl&ouml;sung');
define('TEXT_REDEMPTIONS_TOTAL', 'Insgesamt:');
define('TEXT_REDEMPTIONS_CUSTOMER', 'F&uuml;r diesen Kunden:');
define('TEXT_NO_FREE_SHIPPING', 'Nicht Versandkostenfrei');

define('NOTICE_EMAIL_SENT_TO', 'Notiz: E-Mail versendet an: %s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Fehler: Kein Kunde ausgew&auml;hlt.');
define('COUPON_NAME', 'Coupon Name');
define('COUPON_AMOUNT', 'Coupon Wert');
define('COUPON_CODE', 'Coupon Code');
define('COUPON_STARTDATE', 'g&uuml;ltig ab');
define('COUPON_FINISHDATE', 'g&uuml;ltig bis');
define('COUPON_FREE_SHIP', 'Versandkostenfrei');
define('COUPON_DESC', 'Coupon Beschreibung');
define('COUPON_MIN_ORDER', 'Coupon Mindestbestellwert');
define('COUPON_USES_COUPON', 'Anzahl/Verwendungen pro Coupon');
define('COUPON_USES_USER', 'Anzahl/Verwendungen pro Kunde');
define('COUPON_PRODUCTS', 'Liste der g&uuml;ltigen Artikel');
define('COUPON_CATEGORIES', 'Liste der g&uuml;ltigen Kategorien');
define('VOUCHER_NUMBER_USED', 'Anzahl Verwendet');
define('DATE_CREATED', 'erstellt am');
define('DATE_MODIFIED', 'ge&auml;ndert am');
define('TEXT_HEADING_NEW_COUPON', 'Neuen Coupon erstellen');
define('TEXT_NEW_INTRO', 'Bitte geben Sie die folgende Informationen f&uuml;r den neuen Coupon an.<br />');

define('COUPON_NAME_HELP', 'Eine Kurzbezeichnung f&uuml;r den Coupon');
define('COUPON_AMOUNT_HELP', 'Tragen Sie hier den Rabatt f&uuml;r diesen Coupon ein. Entweder einen festen Betrag oder einen prozentualen Rabatt wie z.B. 10%');
define('COUPON_CODE_HELP', 'Hier k&ouml;nnen Sie einen eigenen Code eintragen (max. 16 Zeichen). Lassen Sie das Feld frei, dann wird dieser Code automatisch generiert.');
define('COUPON_STARTDATE_HELP', 'Das Datum ab dem der Coupon g&uuml;ltig ist');
define('COUPON_FINISHDATE_HELP', 'Das Datum an dem der Coupon abl&auml;uft');
define('COUPON_FREE_SHIP_HELP', 'Coupon f&uuml;r eine versandkostenfreie Lieferung. <strong>Achtung:</strong> Der Coupon Wert wird <b>jetzt</b> ber&uuml;cksichtigt! Der Mindestbestellwert bleibt g&uuml;ltig.');
define('COUPON_DESC_HELP', 'Beschreibung des Coupons f&uuml;r den Kunden');
define('COUPON_MIN_ORDER_HELP', 'Mindestbestellwert ab dem dieser Coupon g&uuml;ltig ist');
define('COUPON_USES_COUPON_HELP', 'Tragen Sie hier ein wie oft dieser Coupon eingel&ouml;st werden darf. Lassen Sie das Feld frei, dann ist die Benutzung unlimitiert.');
define('COUPON_USES_USER_HELP', 'Tragen Sie hier ein wie oft ein Kunde diesen Coupon einl&ouml;sen darf. Lassen Sie das Feld frei, dann ist die Benutzung unlimitiert.');
define('COUPON_PRODUCTS_HELP', 'Eine durch Komma getrennte Liste von product_ids f&uuml;r die dieser Coupon g&uuml;ltig ist. Ein leeres Feld bedeutet keine Einschr&auml;nkung.');
define('COUPON_CATEGORIES_HELP', 'Eine durch Komma getrennte Liste von Kategorien (cpaths) f&uuml;r die dieser Coupon g&uuml;ltig ist. Ein leeres Feld bedeutet keine Einschr&auml;nkung.');
define('COUPON_ID', 'cID');
define('BUTTON_DELETE_NO_CONFIRM', 'ohne Abfrage l&ouml;schen');
define('TEXT_NONE', 'keine Einschr&auml;nkung');
define('TEXT_COUPON_DELETE', 'L&ouml;schen');
define('TEXT_COUPON_STATUS', 'Status');
define('TEXT_COUPON_DETAILS', 'Coupon Daten');
define('TEXT_COUPON_EMAIL', 'E-Mail Versand');
define('TEXT_COUPON_OVERVIEW', '&Uuml;bersicht');
define('TEXT_COUPON_EMAIL_PREVIEW', 'Best&auml;tigung');
define('TEXT_COUPON_MINORDER', 'Mindestbestellwert');
define('TEXT_VIEW', 'Listenansicht');
define('TEXT_VIEW_SHORT', 'Anzeige');
//BOF - web28 - 2011-04-13 - ADD Coupon message infos
define('COUPON_MINORDER_INFO', "\nMindestbestellwert: ");
define('COUPON_RESTRICT_INFO', "\nDieser Coupon ist nur für bestimmte Artikel g&uuml;ltig!");
define('COUPON_INFO', "\nCouponwert: ");
define('COUPON_FREE_SHIPPING', 'Versandkostenfrei');
define('COUPON_LINK_TEXT', '\n\nDetails');
define('COUPON_CATEGORIES_RESTRICT', '\nG&uuml;ltig f&uuml;r diese Kategorien');
define('COUPON_PRODUCTS_RESTRICT', '\nG&uuml;ltig f&uuml;r diese Artikel');
define('COUPON_NO_RESTRICT', '\nG&uuml;ltig f&uuml;r alle Artikel');;
//EOF - web28 - 2011-04-13 - ADD Coupon message infos

//BOF - web28 - 2011-07-05 - ADD error message
define('ERROR_NO_COUPON_NAME', 'FEHLER: Kein Couponname ');
define('ERROR_NO_COUPON_AMOUNT', 'FEHLER: Kein Couponwert ');
//EOF - web28 - 2011-07-05 - ADD error message
?>