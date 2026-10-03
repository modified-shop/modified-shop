<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$klarna_code = 'KLARNA_EXPRESS';
include(DIR_FS_CATALOG.'lang/german/modules/payment/klarna.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna Express Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_SELECTION'] = 'Klarna';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_INFO'] = 'Die Zahlungsart haben Sie bereits bei Klarna gew&auml;hlt.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_DESCRIPTION'] = 'Der Klarna Express Checkout zeigt einen Button im Warenkorb. Der Kunde gibt seine Adresse in Klarna ein und setzt den Checkout im Shop ab Versandart fort. Die Zahlart w&auml;hlt der Kunde bei Klarna. Die Bestellung l&auml;uft &uuml;ber dieses Modul, die &uuml;brigen Klarna Zahlarten m&uuml;ssen nicht aktiviert sein. Der Button erscheint nicht bei Warenk&ouml;rben mit ausschlie&szlig;lich virtuellen Artikeln.<br /><br />Client-ID: Im Klarna Partner Portal unter <b>Payment settings &gt; Client Identifiers</b> anlegen. Die Shop-Domain (z.B. https://www.meinshop.de, ohne Pfad) muss dort unter <b>Allowed Origins</b> eingetragen sein, sonst l&auml;dt der Button nicht. Benutzername und Passwort stimmen mit den &uuml;brigen Klarna Modulen &uuml;berein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'Der Klarna Express Checkout wurde abgebrochen.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_CALLBACK'] = 'Der Klarna Express Checkout konnte nicht gestartet werden. Bitte versuchen Sie es erneut oder gehen Sie zur Kasse.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_ADDRESS'] = 'Die Adresse von Klarna ist unvollst&auml;ndig oder wird vom Shop nicht beliefert. Bitte gehen Sie zur Kasse und geben Sie Ihre Adresse dort ein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_TITLE'] = 'Client-ID';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_DESC'] = 'Client-ID aus dem Klarna Partner Portal (Payment settings &gt; Client Identifiers). Die Shop-Domain muss dort unter Allowed Origins eingetragen sein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_TITLE'] = 'Kurz-Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_DESC'] = 'Nach dem Express Checkout direkt zur Bestellbest&auml;tigung wechseln. Noch ohne Funktion, der Checkout beginnt immer bei der Versandart.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_TITLE'] = 'Button im Warenkorb';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_DESC'] = 'Klarna Express Button im Warenkorb anzeigen.';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
