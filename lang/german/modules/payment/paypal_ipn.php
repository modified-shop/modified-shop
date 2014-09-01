<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypal.php 998 2005-07-07 14:18:20Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(paypal.php,v 1.7 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (paypal.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_TITLE', 'PayPal');
define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_DESCRIPTION', 'PayPal IPN');
//define('MODULE_PAYMENT_PAYPAL_IPN_LOGO','<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x50.gif" />');
define('MODULE_PAYMENT_PAYPAL_IPN_LOGO','<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x47.gif" align="middle" />');
//define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_INFO','Zahlen Sie bequem und sicher mit PayPal ' . MODULE_PAYMENT_PAYPAL_IPN_LOGO);
define('MODULE_PAYMENT_PAYPAL_IPN_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_PAYPAL_IPN_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, die f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_PAYPAL_IPN_STATUS_TITLE' , 'PayPal Modul aktivieren');
define('MODULE_PAYMENT_PAYPAL_IPN_STATUS_DESC' , 'M&ouml;chten Sie Zahlungen per PayPal akzeptieren?');
define('MODULE_PAYMENT_PAYPAL_IPN_ID_TITLE' , 'E-Mail-Adresse');
define('MODULE_PAYMENT_PAYPAL_IPN_ID_DESC' , 'E-Mail-Adresse, die f&uuml;r PayPal verwendet wird');
define('MODULE_PAYMENT_PAYPAL_IPN_CURRENCY_TITLE' , 'Transaktionsw&auml;hrung');
define('MODULE_PAYMENT_PAYPAL_IPN_CURRENCY_DESC' , 'W&auml;hrung, die f&uuml;r Kreditkartentransaktionen verwendet wird');
define('MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt');
define('MODULE_PAYMENT_PAYPAL_IPN_ZONE_TITLE' , 'Zahlungszone');
define('MODULE_PAYMENT_PAYPAL_IPN_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_PAYPAL_IPN_ORDER_STATUS_ID_TITLE' , 'Bestellstatus nach erfolgreicher Zahlung');
define('MODULE_PAYMENT_PAYPAL_IPN_ORDER_STATUS_ID_DESC' , 'Bestellungen, die mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID_TITLE','Bestellstatus bei offener Zahlung'); 
define('MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID_DESC','Bestellungen, die mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_CURL_TITLE', 'cURL');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_CURL_DESC', 'cURL Anbindung oder normale Weiterleitung.');

define('MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT_TITLE', 'PayPal Link Bestellung:');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT_DESC', ' am Ende des Bestellvorganges anzeigen');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_EMAIL_TITLE', 'PayPal Link E-Mail:');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_EMAIL_DESC', 'in die Auftragsbest&auml;tigungs E-Mail einfügen');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT_TITLE', 'PayPal Link Kundenkonto:');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT_DESC', 'Im Kundenkonto bei der Bestellung anzeigen');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX_TITLE', 'Testbetrieb (Sandbox)');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX_DESC', 'Nur zu Testzwecken f&uuml;r Entwickler');
define('MODULE_PAYMENT_PAYPAL_IPN_SBID_TITLE', 'E-Mail-Adresse f&uuml;r Testbetrieb (Sandbox)');
define('MODULE_PAYMENT_PAYPAL_IPN_SBID_DESC', 'E-Mail-Adresse, die f&uuml;r den Testbetrieb verwendet wird');

//Paypal Seitengestaltung
define('MODULE_PAYMENT_PAYPAL_IPN_IMAGE_TITLE','PayPal Shop-Logo');
define('MODULE_PAYMENT_PAYPAL_IPN_IMAGE_DESC','Logo-Datei, die bei PayPal angezeigt werden soll.</ br>Achtung: Wird nur übertragen, wenn der Shop mit SSL arbeitet.</ br>Das Bild darf max. 750px breit und 90px hoch sein.</ br>Aufgerufen wird die Datei aus: '.DIR_WS_CATALOG.'lang/SPRACHE/modules/payment/images/');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BACK_TITLE','PayPal Shop-Logo Hintergrundfarbe');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BACK_DESC','Hintergrundfarbe, die bei PayPal angezeigt werden soll. z.B. FEE8B9');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BORD_TITLE', 'PayPal Shop-Logo Rahmenfarbe');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BORD_DESC','Rahmenfarbe, die bei PayPal angezeigt werden soll. z.B. E4C558');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_SITE_TITLE', 'PayPal Seiten-Farbe');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_SITE_DESC','Seitenfarbe, die bei PayPal angezeigt werden soll. z.B. E4C558');
define('MODULE_PAYMENT_PAYPAL_IPN_CBT_TITLE', 'Text f&uuml;r R&uuml;ckkehr-Schaltfl&auml;che');
define('MODULE_PAYMENT_PAYPAL_IPN_CBT_DESC','Text, der auf der R&uuml;ckkehr-Schaltfl&auml;che bei PayPal angezeigt werden soll.');

//Weiterleitung URLs
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_TITLE', 'URL nach Zahlung');
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_DESC','Die URL, auf die nach Abschluss der Zahlung weitergeleitet wird, z. B. eine URL auf Ihrer Website zeigt "Vielen Dank für Ihre Zahlung". <br />'.HTTP_SERVER.DIR_WS_CATALOG);
define('MODULE_PAYMENT_PAYPAL_IPN_NOTIFY_TITLE', 'URL für PayPal IPN Zahlungsinformationen');
define('MODULE_PAYMENT_PAYPAL_IPN_NOTIFY_DESC','Die URL, auf die für die PayPal Zahlungsinformationen weitergeleitet wird (IPN POST MESSAGES).<br />'.HTTP_SERVER.DIR_WS_CATALOG);
define('MODULE_PAYMENT_PAYPAL_IPN_CANCEL_TITLE', 'URL für PayPal Fehler');
define('MODULE_PAYMENT_PAYPAL_IPN_CANCEL_DESC','Die URL, auf die bei PayPal-Fehlern weitergeleitet wird.<br />'.HTTP_SERVER.DIR_WS_CATALOG);

//Emails
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_PAID_TITLE','E-Mail an Shopbetreiber bei erfolgreicher Zahlung');
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_PAID_DESC','Wenn auf "True" gesetzt, erhalten Sie nach jeder erfolgreichen Zahlung eine E-Mail, in der Sie &uuml;ber die Zahlung sowie den neuen Bestellstatus informiert werden.<br /><br /> Bitte beachten Sie: Bei fehlerhaften Zahlungen (Falsche Betr&auml;ge, Doppelzahlungen, nicht zuzuordnen etc.) werden Sie grunds&auml;tzlich immer per E-Mail ausf&uuml;hrlich informiert.');
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_CUSTOMER_TITLE','E-Mail an Kunden bei &Auml;nderung des Bestellstatus');
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_CUSTOMER_DESC','Wenn auf "True" gesetzt, erh&auml;lt Ihr Kunde automatisch eine E-Mail, falls das PayPal-Modul den Bestellstatus nach Zahlungseingang automatisch ge&auml;ndert hat.');

//###Diese Texte sehen die Kunden###
define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_INFO','Zahlen Sie bequem und sicher mit PayPal ' . MODULE_PAYMENT_PAYPAL_IPN_LOGO);

//Paypal Linktexte
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT','Jetzt mit PayPal bezahlen');
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT2','Sie erhalten den PayPal Zahlungs-Link auch automatisch mit Ihrer Auftragsbest&auml;tigungs E-Mail! Sie k&ouml;nnen damit die Zahlung auch sp&auml;ter vornehmen');
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_EMAIL', "Jetzt mit PayPal bezahlen. Klicken Sie bitte auf den folgenden Link:\n");
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_ORDER', " - Bestellnummer: ");

//PayPal Variablen
define('MODULE_PAYMENT_PAYPAL_IPN_VAR_CBT', "zurück zum Shop"); //cbt

//Style Schaltfläche
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_LINK', 'style="padding:5px; color:#555555; background: #f8f8f8; border: 1px solid #8c8c8c; text-decoration: none; cursor: pointer;"'); //web28 2010-06-23 define link color
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_TOP', '<div style="margin-top:25px;">');
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_LOGO', '<div style="margin-top: 5px; float: left;">' . MODULE_PAYMENT_PAYPAL_IPN_LOGO . '</div>');
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_TEXT', '<div style="clear: both; color:#496686; font-weight: bold; padding:10px;">' . MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT2.'</div>');

//PAYPAL NOTIFY: paypal_ipn_notify.php
define('MODULE_PAYMENT_PAYPAL_IPN_COMMENT_STATUS','Automatisch durch PayPal IPN-ADV Modul');
define('MODULE_PAYMENT_PAYPAL_IPN_SUBJECT_OK','PayPal-Zahlung erhalten und verbucht');
define('MODULE_PAYMENT_PAYPAL_IPN_UNKNOWN','unbekannt');

define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1','Die Zahlung konnte keiner Bestellung zugeordnet werden, da sie manuell ohne Bestellnummer und nicht über einen Zahlungs-Link vorgenommen wurde. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1A','Bestellnummer "%s" wurde nicht in der Datenbank gefunden und ist ungültig. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1B','Betrag in falscher Währung erhalten. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1C','Zu niedrigen Betrag erhalten (Offener Restbetrag: ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1D','Zu hohen Betrag erhalten (Zuviel gezahlter Betrag: ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1E','Doppelte Zahlung erhalten. Diese Bestellung wurde bereits am %s per PayPal bezahlt. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1F','Ungültiger Empfangsstatus. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1G','Die Zahlung wurde noch nicht auf Ihrem PayPal-Konto verbucht (Status: Pending) und bedarf möglicherweise einer manuellen Akzeptanz. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1H','Zahlung wurde an falschen Empfänger gesendet. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_SUB1','Problem mit erhaltener PayPal-Zahlung');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_INFO1','Die Zahlung wurde ignoriert und der Bestellstatus der dazugehörigen Bestellung nicht geändert.');

define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG2','Zu hohen Betrag erhalten (zuviel gezahlter Betrag: ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_SUB2','Zu hohe PayPal-Zahlung erhalten');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_INFO2','Die Zahlung wurde trotzdem als erhalten verbucht und der Bestellstatus dieser Bestellung auf %s gesetzt.');

define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG3','WARNUNG! PayPal meldet INVALID für diese IPN-Zahlungsbestätigung');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_SUB3','INVALID für PayPal-Zahlungsvorgang!');

//PAYPAL RETURN: paypal_ipn_return.php
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_HEADER','Vielen Dank');
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_TEXT1','Wir bedanken uns für Ihre PayPal-Zahlung in Höhe von');
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_TEXT2','für Ihre Bestellung vom');
?>