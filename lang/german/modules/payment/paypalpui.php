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
  'MODULE_PAYMENT_PAYPALPUI_TEXT_TITLE' => 'Rechnung',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_ADMIN_TITLE' => 'Rechnung via PayPal',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_INFO' => 'Mit Klicken auf den Button akzeptieren Sie die <a target="_blank" href="https://www.ratepay.com/legal-payment-terms">Ratepay Zahlungsbedingungen</a> und erkl&auml;ren sich mit der Durchf&uuml;hrung einer <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Risikopr&uuml;fung durch Ratepay</a>, unseren Partner, einverstanden. Sie akzeptieren auch PayPal&rsquo;s <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/ua/rechnungskauf-mit-ratepay?locale.x=en_DE&_ga=1.121064910.716429872.1643889674">Datenschutzerkl&auml;rung</a>. Falls Ihre Transaktion erfolgreich per Kauf auf Rechnung abgewickelt werden kann, wird der Kaufpreis an Ratepay abgetreten und Sie d&uuml;rfen nur an Ratepay &uuml;berweisen, nicht an den H&auml;ndler.',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_DESCRIPTION' => '<strong><font color="red">ACHTUNG:</font></strong> Damit der Bestellstatus korrekt gesetzt wird, m&uuml;ssen folgende <a href="'.xtc_href_link('paypal_webhook.php', '').'">Webhooks</a> in der PayPal Konfiguration eingestellt werden , damit der Status korrekt umgestellt wird:<ul><li>PAYMENT.CAPTURE.COMPLETED</li><li>PAYMENT.CAPTURE.DECLINED</li><li>PAYMENT.CAPTURE.DENIED</li><li>PAYMENT.CAPTURE.PENDING</li></ul>',
  'MODULE_PAYMENT_PAYPALPUI_ALLOWED_TITLE' => 'Erlaubte Zonen',
  'MODULE_PAYMENT_PAYPALPUI_ALLOWED_DESC' => 'Das Modul kann f&uuml;r die folgenden Zonen verwendet werden.',
  'MODULE_PAYMENT_PAYPALPUI_STATUS_TITLE' => 'PayPal Rechnung aktivieren',
  'MODULE_PAYMENT_PAYPALPUI_STATUS_DESC' => 'M&ouml;chten Sie Zahlungen per Rechnung akzeptieren?',
  'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
  'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt',
  'MODULE_PAYMENT_PAYPALPUI_ZONE_TITLE' => 'Zahlungszone',
  'MODULE_PAYMENT_PAYPALPUI_ZONE_DESC' => 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.',
  'MODULE_PAYMENT_PAYPALPUI_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Jetzt PayPal Konto hier erstellen.</strong></a>',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ACHTUNG:</font></strong> Bitte nehmen Sie noch die Einstellungen unter "Partner Module" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Konfiguration"</strong></a> vor!',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_HEADING' => 'Hinweis',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_MESSAGE' => 'Kauf auf Rechnung steht Ihnen momentan leider nicht zur Verf&uuml;gung, bitte w&auml;hlen Sie eine andere Zahlungsart.',  
  
  'MODULE_PAYMENT_PAYPALPUI_PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED' => 'Die Kombination aus Ihrem Namen und Ihrer Adresse konnte nicht validiert werden. Bitte korrigieren Sie Ihre Daten und versuchen Sie es erneut. Weitere Informationen finden Sie in der <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay-Datenschutzerkl&auml;rung</a> oder Sie k&ouml;nnen Ratepay &uuml;ber dieses <a target="_blank" href="https://www.ratepay.com/kontakt">Kontaktformular</a> kontaktieren.',
  'MODULE_PAYMENT_PAYPALPUI_PAYMENT_SOURCE_DECLINED_BY_PROCESSOR' => 'Die gew&auml;hlte Zahlungsart kann nicht genutzt werden. Diese Entscheidung basiert auf einem automatisierten Datenverarbeitungsverfahren. Weitere Informationen finden Sie in der <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay-Datenschutzerkl&auml;rung</a> oder Sie k&ouml;nnen Ratepay &uuml;ber dieses <a target="_blank" href="https://www.ratepay.com/kontakt">Kontaktformular</a> kontaktieren.',
  'MODULE_PAYMENT_PAYPALPUI_PAYMENT_SOURCE_CANNOT_BE_USED' => 'Die gew&auml;hlte Zahlungsart kann nicht genutzt werden. Diese Entscheidung basiert auf einem automatisierten Datenverarbeitungsverfahren. Weitere Informationen finden Sie in der <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay-Datenschutzerkl&auml;rung</a> oder Sie k&ouml;nnen Ratepay &uuml;ber dieses <a target="_blank" href="https://www.ratepay.com/kontakt">Kontaktformular</a> kontaktieren.',
  'MODULE_PAYMENT_PAYPALPUI_BILLING_ADDRESS_INVALID' => 'Ihre Rechnungsadresse konnte nicht validiert werden.',
  'MODULE_PAYMENT_PAYPALPUI_SHIPPING_ADDRESS_INVALID' => 'Ihre Versandadresse konnte nicht validiert werden.',
    
  'MALFORMED_REQUEST_JSON' => 'Die Kombination aus Ihrem Namen und Ihrer Adresse konnte nicht validiert werden. Bitte korrigieren Sie Ihre Daten und versuchen Sie es erneut. Weitere Informationen finden Sie in der <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay-Datenschutzerkl&auml;rung</a> oder Sie k&ouml;nnen Ratepay &uuml;ber dieses <a target="_blank" href="https://www.ratepay.com/kontakt">Kontaktformular</a> kontaktieren.',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_DOB' => 'Geburtsdatum (z.B. 21.05.1970):',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_TELEPHONE' => 'Telefonnummer:',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_SERVICE' => 'Kundenservice: %s',
  
  'JS_DOB_ERROR' => 'Ihr Geburtsdatum muss im Format TT.MM.JJJJ (z.B. 21.05.1970) eingegeben werden.',
  'JS_TELEPHONE_ERROR' => 'F&uuml;r diese Zahlart ben&ouml;tigen wir Ihre Telefonnummer.',
  
  'MODULE_PAYMENT_PAYPALPUI_TEXT_LEGAL' => 'Mit Klicken auf den Button akzeptieren Sie die <a target="_blank" href="https://www.ratepay.com/legal-payment-terms">Ratepay Zahlungsbedingungen</a> und erkl&auml;ren sich mit der Durchf&uuml;hrung einer <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Risikopr&uuml;fung durch Ratepay</a>, unseren Partner, einverstanden. Sie akzeptieren auch PayPal&rsquo;s <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/ua/rechnungskauf-mit-ratepay?locale.x=en_DE&_ga=1.121064910.716429872.1643889674">Datenschutzerkl&auml;rung</a>. Falls Ihre Transaktion erfolgreich per Kauf auf Rechnung abgewickelt werden kann, wird der Kaufpreis an Ratepay abgetreten und Sie d&uuml;rfen nur an Ratepay &uuml;berweisen, nicht an den H&auml;ndler.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>