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
  'MODULE_PAYMENT_PAYPALACDC_TEXT_TITLE' => 'Kreditkarte',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_ADMIN_TITLE' => 'Kreditkarte (ohne Hinweis auf PayPal)',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'paypal_creditcard.png', '', 'SSL', false).'" />' : ''),
  'MODULE_PAYMENT_PAYPALACDC_TEXT_DESCRIPTION' => 'Im letzten Step im Checkout werden Sie gebeten die Kreditkartendaten einzugeben, um hier Ihre Bestellung zu bezahlen.<br /><br /><strong><font color="red">ACHTUNG:</font></strong> Damit der Bestellstatus korrekt gesetzt wird, m&uuml;ssen folgende <a href="'.xtc_href_link('paypal_webhook.php', '').'">Webhooks</a> in der PayPal Konfiguration eingestellt werden , damit der Status korrekt umgestellt wird:<ul><li>PAYMENT.CAPTURE.COMPLETED</li><li>PAYMENT.CAPTURE.DECLINED</li><li>PAYMENT.CAPTURE.DENIED</li><li>PAYMENT.CAPTURE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPALACDC_ALLOWED_TITLE' => 'Erlaubte Zonen',
  'MODULE_PAYMENT_PAYPALACDC_ALLOWED_DESC' => 'Das Modul kann f&uuml;r die folgenden Zonen verwendet werden.',
  'MODULE_PAYMENT_PAYPALACDC_STATUS_TITLE' => 'Kreditkarte aktivieren',
  'MODULE_PAYMENT_PAYPALACDC_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen per PayPal Card akzeptieren?',
  'MODULE_PAYMENT_PAYPALACDC_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
  'MODULE_PAYMENT_PAYPALACDC_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt',
  'MODULE_PAYMENT_PAYPALACDC_ZONE_TITLE' => 'Zahlungszone',
  'MODULE_PAYMENT_PAYPALACDC_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
  'MODULE_PAYMENT_PAYPALACDC_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Jetzt PayPal Konto hier erstellen.</strong></a>',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ACHTUNG:</font></strong> Bitte nehmen Sie noch die Einstellungen unter "Partner Module" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Konfiguration"</strong></a> vor!',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_HEADING' => 'Hinweis',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MESSAGE' => 'Die Zahlung mit Kreditkarte wurde abgebrochen',  
  'MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG' => 'Die Zahlung kann leider nicht durchgef&uuml;hrt werden.',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_CARDNUMBER' => 'Kartennummer',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_CARDHOLDER' => 'Karteninhaber',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_EXPIRATION' => 'G&uuml;ltig bis',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_CVV' => 'Pr&uuml;fziffer',

  'MODULE_PAYMENT_PAYPALACDC_TEXT_CARDNUMBER_PLACEHOLDER' => 'Kartennummer',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_EXPIRATION_PLACEHOLDER' => 'MM/JJ',
  'MODULE_PAYMENT_PAYPALACDC_TEXT_CVV_PLACEHOLDER' => 'Pr&uuml;fziffer',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
