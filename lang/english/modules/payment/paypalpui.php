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
  'MODULE_PAYMENT_PAYPALPUI_TEXT_INFO' => ((!defined('RUN_MODE_ADMIN') && function_exists('xtc_href_link')) ? '<img src="'.xtc_href_link(DIR_WS_ICONS.'paypal.png', '', 'SSL', false).'" />' : ''),
  'MODULE_PAYMENT_PAYPALPUI_TEXT_DESCRIPTION' => 'After "confirm" your will be routet to PayPal to pay your order.<br />Back in shop you will get your order-mail.<br />PayPal is the safer way to pay online. We keep your details safe from others and can help you get your money back if something ever goes wrong.',
  'MODULE_PAYMENT_PAYPALPUI_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALPUI_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_PAYPALPUI_STATUS_TITLE' => 'Enable PayPal',
  'MODULE_PAYMENT_PAYPALPUI_STATUS_DESC' => 'Do you want to accept PayPal payments?',
  'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALPUI_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALPUI_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALPUI_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_MESSAGE' => 'PayPal payment has been canceled',
  
  'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED' => 'The combination of your name and address could not be validated. Please correct your data and try again. You can find further information in the <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a target="_blank" href="https://www.ratepay.com/kontakt">contact form</a>.',
  'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR' => 'It is not possible to use the selected payment method. This decision is based on automated data processing. You can find further information in the <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a target="_blank" href="https://www.ratepay.com/kontakt">contact form</a>.',
  'MALFORMED_REQUEST_JSON' => 'It is not possible to use the selected payment method. This decision is based on automated data processing. You can find further information in the <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a target="_blank" href="https://www.ratepay.com/kontakt">contact form</a>.',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_DOB' => 'Date of birth (e.g. 21/05/1970):',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_TELEPHONE' => 'Phone number:',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_SERVICE' => 'Customer service: %s',
  
  'JS_DOB_ERROR' => 'Your date of birth needs to be entered in the following form DD/MM/YYYY (e.g. 21/05/1970)',
  'JS_TELEPHONE_ERROR' => 'For this payment method we need your phone number.',
  
  'MODULE_PAYMENT_PAYPALPUI_TEXT_LEGAL' => 'Mit Klicken auf den Button akzeptieren Sie die <a target="_blank" href="https://www.ratepay.com/legal-payment-terms">Ratepay Zahlungsbedingungen</a> und erkl&auml;ren sich mit der Durchf&uuml;hrung einer <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Risikopr&uuml;fung durch Ratepay</a>, unseren Partner, einverstanden. Sie akzeptieren auch PayPal&rsquo;s <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/ua/rechnungskauf-mit-ratepay?locale.x=en_DE&_ga=1.121064910.716429872.1643889674">Datenschutzerkl&auml;rung</a>. Falls Ihre Transaktion erfolgreich per Kauf auf Rechnung abgewickelt werden kann, wird der Kaufpreis an Ratepay abgetreten und Sie d&uuml;rfen nur an Ratepay &uuml;berweisen, nicht an den H&auml;ndler.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>