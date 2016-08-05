<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


$lang_array = array(
  'TEXT_PAYPAL_CONFIG_HEADING_TITLE' => 'PayPal Konfiguration',

  'TEXT_PAYPAL_CONFIG_CLIENT_LIVE' => 'Client ID Live:',
  'TEXT_PAYPAL_CONFIG_CLIENT_LIVE_INFO' => 'Erstellen Sie in Ihrem PayPal Account eine neue App f&uuml;r diese Daten.',

  'TEXT_PAYPAL_CONFIG_SECRET_LIVE' => 'Secret Live:',
  'TEXT_PAYPAL_CONFIG_SECRET_LIVE_INFO' => 'Erstellen Sie in Ihrem PayPal Account eine neue App f&uuml;r diese Daten.',

  'TEXT_PAYPAL_CONFIG_CLIENT_SANDBOX' => 'Client ID Sandbox:',
  'TEXT_PAYPAL_CONFIG_CLIENT_SANDBOX_INFO' => 'Erstellen Sie in Ihrem PayPal Account eine neue App f&uuml;r diese Daten.',

  'TEXT_PAYPAL_CONFIG_SECRET_SANDBOX' => 'Secret Sandbox:',
  'TEXT_PAYPAL_CONFIG_SECRET_SANDBOX_INFO' => 'Erstellen Sie in Ihrem PayPal Account eine neue App f&uuml;r diese Daten.',

  'TEXT_PAYPAL_CONFIG_MODE' => 'Modus:',
  'TEXT_PAYPAL_CONFIG_MODE_INFO' => '',

  'TEXT_PAYPAL_CONFIG_INVOICE_PREFIX' => 'Bestellnummer Pr&auml;fix:',
  'TEXT_PAYPAL_CONFIG_INVOICE_PREFIX_INFO' => 'Frei w&auml;hlbare Zeichenfolge (Pr&auml;fix), die der Bestellnummer vorangestellt und f&uuml;r die Erzeugung der PayPal-Rechnungsnummer genutzt wird.<br />Dadurch k&ouml;nnen mehrere Shops mit nur einer PayPal App arbeiten. Konflikte bei gleichen Bestellnummern werden vermieden. Jede Bestellung erh&auml;lt eine eigene Rechnungs-Nummer im PayPal Konto.',

  'TEXT_PAYPAL_CONFIG_TRANSACTION' => 'Transaktion:',
  'TEXT_PAYPAL_CONFIG_TRANSACTION_INFO' => 'W&auml;hlen Sie die Art der Transaktion.<br/><br/><b>Hinweis:</b> Bei PayPal Plus wird immer ein Sale gemacht.',

  'TEXT_PAYPAL_CONFIG_CAPTURE' => 'Manuell erfassen:',
  'TEXT_PAYPAL_CONFIG_CAPTURE_INFO' => 'Wollen Sie Zahlungen bei PayPal manuell erfassen (Capture)?<br/><br/><b>Hinweis:</b> Dazu ist es notwendig, dass die Transaktion auf Authorize gesetzt ist.',

  'TEXT_PAYPAL_CONFIG_CART' => 'Warenkorb:',
  'TEXT_PAYPAL_CONFIG_CART_INFO' => 'Soll der Warenkorb zu PayPal &uuml;bertragen werden?<br/><br/><b>Hinweis:</b> Diese Einstellung kann Probleme bei der Verwendung von ot-Modulen unter "Module" -> "Zusammenfassung" mit sich bringen, die einen Rabatt oder Aufschlag gew&auml;hren ("Rabatt [ot_discount]", "Rabatt Coupons [ot_coupon]", "Gutscheine [ot_gv]", "Rabatt &amp; Zuschlag auf Zahlungsarten [ot_payment]", etc.).<br/>Empfohlene Einstellung: "Nein"',

  'TEXT_PAYPAL_CONFIG_STATE_SUCCESS' => 'Status Erfolg:',
  'TEXT_PAYPAL_CONFIG_STATE_SUCCESS_INFO' => 'Status bei erfolgreicher Bestellung',

  'TEXT_PAYPAL_CONFIG_STATE_REJECTED' => 'Status Abgelehnt:',
  'TEXT_PAYPAL_CONFIG_STATE_REJECTED_INFO' => 'Status bei abgelehnter Bestellung',

  'TEXT_PAYPAL_CONFIG_STATE_PENDING' => 'Status Warten:',
  'TEXT_PAYPAL_CONFIG_STATE_PENDING_INFO' => 'Status bei erfolgreicher Bestellung, die aber seitens PayPal noch nicht best&auml;tigt wurde',

  'TEXT_PAYPAL_CONFIG_STATE_CAPTURED' => 'Status erfasst:',
  'TEXT_PAYPAL_CONFIG_STATE_CAPTURED_INFO' => 'Status ausgel&ouml;st durch ein Capture',
  
  'TEXT_PAYPAL_CONFIG_STATE_REFUNDED' => 'Status R&uuml;ckzahlung:',
  'TEXT_PAYPAL_CONFIG_STATE_REFUNDED_INFO' => 'Status ausgel&ouml;st duch ein Refund',
  
  'TEXT_PAYPAL_CONFIG_STATE_TEMP' => 'Status Temp:',
  'TEXT_PAYPAL_CONFIG_STATE_TEMP_INFO' => 'Status bei einer nicht best&auml;tigten Bestellung',

  'TEXT_PAYPAL_CONFIG_LOG' => 'Log:',
  'TEXT_PAYPAL_CONFIG_LOG_INFO' => 'Soll ein Log geschrieben werden?',

  'TEXT_PAYPAL_CONFIG_LOG_LEVEL' => 'Log Level:',
  'TEXT_PAYPAL_CONFIG_LOG_LEVEL_INFO' => '<b>Hinweis:</b> Im Livebetrieb wird nur bis Level FINE geloggt.',
  
  'BUTTON_PAYPAL_STATUS_INSTALL' => 'Bestellstatus installieren',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}


// orders status
$PAYPAL_INST_ORDER_STATUS_TMP_NAME = 'PayPal abgebrochen';
$PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME = 'PayPal bezahlt';
$PAYPAL_INST_ORDER_STATUS_PENDING_NAME = 'PayPal wartend';
$PAYPAL_INST_ORDER_STATUS_CAPTURED_NAME = 'PayPal erfasst';
$PAYPAL_INST_ORDER_STATUS_REFUNDED_NAME = 'PayPal erstattet';
$PAYPAL_INST_ORDER_STATUS_REJECTED_NAME = 'PayPal abgelehnt';
?>