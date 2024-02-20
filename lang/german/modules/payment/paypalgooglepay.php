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
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_TITLE' => 'GooglePay via PayPal',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_ADMIN_TITLE' => 'GooglePay via PayPal',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_INFO' => '<img src="https://developers.google.com/static/pay/api/images/brand-guidelines/google-pay-mark.png" style="max-height: 60px;"/>',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_DESCRIPTION' => 'Sie werden nach dem "Best&auml;tigen" zu GooglePay geleitet, um hier Ihre Bestellung zu bezahlen.<br />Danach gelangen Sie zur&uuml;ck in den Shop und erhalten Ihre Bestell-Best&auml;tigung.<br />Jetzt schneller bezahlen mit unbegrenztem PayPal-K&auml;uferschutz - nat&uuml;rlich kostenlos.',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ALLOWED_TITLE' => 'Erlaubte Zonen',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ALLOWED_DESC' => 'Das Modul kann f&uuml;r die folgenden Zonen verwendet werden.',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_STATUS_TITLE' => 'GooglePay via PayPal aktivieren',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen per PayPal GooglePay akzeptieren?',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ZONE_TITLE' => 'Zahlungszone',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Jetzt PayPal Konto hier erstellen.</strong></a>',

  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ACHTUNG:</font></strong> Bitte nehmen Sie noch die Einstellungen unter "Partner Module" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Konfiguration"</strong></a> vor!',

  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_ERROR_HEADING' => 'Hinweis',
  'MODULE_PAYMENT_PAYPALGOOGLEPAY_TEXT_ERROR_MESSAGE' => 'Die Zahlung mit GooglePay via PayPal wurde abgebrochen',  
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
