<?php
/* -----------------------------------------------------------------------------------------
   $Id: worldpay_junior.php 4762 2013-05-10 16:12:34Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2008 osCommerce(worldpay_junior.php 1807 2008-01-13 ); www.oscommerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="http://www.worldpay.com" target="_blank" style="text-decoration: underline; font-weight: bold;">WorldPay Webseite besuchen</a>');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE', 'In Pr&uuml;fung: Transaktion in Demo Modus durchgef&uuml;hrt.');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_SUCCESSFUL_TRANSACTION', 'Die Zahlungs Transaktion wurde erfolgreich durchgef&uuml;hrt!');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_UNSUCCESSFUL_TRANSACTION', 'Ihre Zahlung war nicht erfolgreich!');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONTINUE_BUTTON', 'Klicken Sie hier um zu %s fortzufahren');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_TITLE', 'WorldPay Junior');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESC', 'Worldpay Zahlungsmodul');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS_TITLE', 'WorldPay Modul aktivieren');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per WorldPay akzeptieren?');
  
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED_TITLE' , 'Erlaubte Zonen');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID_TITLE', 'Worldpay Installations ID');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID_DESC', 'Ihre WorldPay Installations ID');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD_TITLE', 'Zahlungs Antwort Passwort');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD_DESC', 'Ein Passwort, das in der Callback Antwort zur&uuml;ck gesendet wird (angegeben im WorldPay Kunden Management System)');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD_TITLE', 'MD5 Geheimnis f&uuml;r Transaktions Passwort');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD_DESC', 'Das geheime MD5 Verschl&uuml;sselungs-Passwort um Transaktions-Antworten zu validieren (angegeben im WorldPay Kunden Management System)');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD_TITLE', 'Transaktions Methode');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD_DESC', 'Die Bearbeitungs-Methode, die f&uuml;r jede Transaktionen genutzt werden soll.');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE_TITLE', 'Test Modus');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE_DESC', 'Transaktionen im Test-Modus durchf&uuml;hren?');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

  //define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREAUTH_TITLE', 'Pre-Auth'); // Wird nicht benutzt
  //define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREAUTH_DESC', 'Der Modus, in dem gearbeitet wird (A = Pay Now, E = Pre Auth). Wird ignoriert, falls PreAuth auf False.'); // Wird nicht benutzt

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE_TITLE', 'Zahlungszone');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID_TITLE', 'Tempor&auml;rer Bestellstatus');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID_DESC', 'Bestellstatus f&uuml;r noch nicht abgeschlossene Transaktionen');

  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
  define('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen.');
?>