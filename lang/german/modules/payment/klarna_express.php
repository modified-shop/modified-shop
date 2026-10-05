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
include(DIR_FS_CATALOG.'lang/german/modules/payment/klarna_shared.php');

$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE'] = 'Klarna Express Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_SELECTION'] = 'Klarna';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_INFO'] = 'Die Zahlungsart haben Sie bereits bei Klarna gew&auml;hlt.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_DESCRIPTION'] = 'Der Klarna Express Checkout zeigt einen Button im Warenkorb und auf der Produktseite. Der Kunde gibt seine Adresse in Klarna ein und setzt den Checkout im Shop ab Versandart fort, bei aktivem Kurz-Checkout ab der Bestellbest&auml;tigung. Die Zahlart w&auml;hlt der Kunde bei Klarna. Die Bestellung l&auml;uft &uuml;ber dieses Modul, die &uuml;brigen Klarna Zahlarten m&uuml;ssen nicht aktiviert sein. Der Button erscheint nicht bei Warenk&ouml;rben mit ausschlie&szlig;lich virtuellen Artikeln.<br /><br />Client-ID: Im Klarna Partner Portal unter <b>Payment settings &gt; Client Identifiers</b> anlegen. Die Shop-Domain (z.B. https://www.meinshop.de, ohne Pfad) muss dort unter <b>Allowed Origins</b> eingetragen sein, sonst l&auml;dt der Button nicht. Benutzername und Passwort stimmen mit den &uuml;brigen Klarna Modulen &uuml;berein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE'] = 'Der Klarna Express Checkout wurde abgebrochen.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_CALLBACK'] = 'Der Klarna Express Checkout konnte nicht gestartet werden. Bitte versuchen Sie es erneut oder gehen Sie zur Kasse.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_ADDRESS'] = 'Die Adresse von Klarna ist unvollst&auml;ndig oder wird vom Shop nicht beliefert. Bitte gehen Sie zur Kasse und geben Sie Ihre Adresse dort ein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_UNAVAILABLE'] = 'Der Klarna Express Checkout ist f&uuml;r diese Bestellung nicht verf&uuml;gbar. Bitte gehen Sie zur Kasse.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_ADD'] = 'Der Artikel konnte nicht in den Warenkorb gelegt werden. Bitte pr&uuml;fen Sie Ihre Auswahl.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_STOCK'] = 'Der Artikel ist in dieser Menge nicht lieferbar. Bitte &auml;ndern Sie die Menge.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_ORDER_VALUE'] = 'Der Warenwert liegt au&szlig;erhalb des zul&auml;ssigen Bestellwerts. Bitte gehen Sie zum Warenkorb.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_TOKEN'] = 'Die Seite ist abgelaufen. Bitte laden Sie die Seite neu.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_SESSION'] = 'Die Klarna Sitzung ist abgelaufen oder die Bestellung wurde ge&auml;ndert. Bitte w&auml;hlen Sie die Zahlungsart erneut.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_JS_ERROR_SHIPPING'] = '* Bitte best&auml;tigen Sie die gew&auml;hlte Versandart mit dem Button unter der Versandauswahl.\n\n';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_TEXT_ACCEPT_ADDRESS'] = 'Ich habe meine Rechnungs- und Versandadresse &uuml;berpr&uuml;ft. Die Angaben sind korrekt.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_TITLE'] = 'Client-ID';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_CLIENT_ID_DESC'] = 'Client-ID aus dem Klarna Partner Portal (Payment settings &gt; Client Identifiers). Die Shop-Domain muss dort unter Allowed Origins eingetragen sein.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_TITLE'] = 'Kurz-Checkout';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_SHORT_CHECKOUT_DESC'] = 'Nach dem Express Checkout direkt zur Bestellbest&auml;tigung wechseln. Versandart, Zustimmungen und Kommentar w&auml;hlt der Kunde dort, die Zahlungsseite entf&auml;llt. Bei Nein beginnt der Checkout bei der Versandart.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_TITLE'] = 'Button im Warenkorb';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_CART_DESC'] = 'Klarna Express Button im Warenkorb anzeigen.';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_LOCATION_TITLE'] = 'Button auf Artikelseite oder im Warenkorb-Layer';
$lang_array['MODULE_PAYMENT_'.$klarna_code.'_BUTTON_LOCATION_DESC'] = 'Wo der Klarna Express Button zus&auml;tzlich zum Warenkorb erscheint. Artikelseite: der Artikel wird mit Menge und Auswahl zuerst in den Warenkorb gelegt. Warenkorb-Layer: im Warenkorb-Layer von tpl_modified_nova auf allen Seiten au&szlig;er Warenkorb und Checkout. Klarna erlaubt nur einen Button pro Seite.';
// option labels of the select above, the admin looks them up as CFG_TXT_<value>
$lang_array['CFG_TXT_OFF'] = 'Aus';
$lang_array['CFG_TXT_PRODUCT_PAGE'] = 'Artikelseite';
$lang_array['CFG_TXT_CART_LAYER'] = 'Warenkorb-Layer';

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
