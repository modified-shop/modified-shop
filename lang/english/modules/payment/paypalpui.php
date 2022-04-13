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
  'MODULE_PAYMENT_PAYPALPUI_TEXT_TITLE' => 'Pay upon invoice',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_ADMIN_TITLE' => 'Pay upon invoice via PayPal',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_INFO' => 'By clicking on the button, you agree to the <a target="_blank" href="https://www.ratepay.com/legal-payment-terms">terms of payment</a> and <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">performance of a risk check</a> from the payment partner, Ratepay. You also agree to PayPal’s <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/ua/privacy-full?locale.x=eng_DE&_ga=1.187051880.1362749179.1647260107">privacy statement</a>. If your request to purchase upon invoice is accepted, the purchase price claim will be assigned to Ratepay, and you may only pay Ratepay, not the merchant.',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> In order for Pay upon invoice to work correctly the following webhooks must be set in the PayPal configuration so that the status is switched correctly:<ul><li>PAYMENT.CAPTURE.COMPLETED</li><li>PAYMENT.CAPTURE.DENIED</li></ul>',
  'MODULE_PAYMENT_PAYPALPUI_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_PAYPALPUI_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_PAYPALPUI_STATUS_TITLE' => 'Enable Pay upon invoice via PayPal',
  'MODULE_PAYMENT_PAYPALPUI_STATUS_DESC' => 'Do you want to accept PayPal Pay upon invoice payments?',
  'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_PAYPALPUI_SORT_ORDER_DESC' => 'Sort order of the view. Lowest numeral will be displayed first',
  'MODULE_PAYMENT_PAYPALPUI_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_PAYPALPUI_ZONE_DESC' => 'If a zone is choosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_PAYPALPUI_LP' => '<br /><br /><a target="_blank" href="http://www.paypal.com/de/webapps/mpp/referral/paypal-business-account2?partner_id=EHALBVD4M2RQS"><strong>Create PayPal account now.</strong></a>',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_EXTENDED_DESCRIPTION' => '<strong><font color="red">ATTENTION:</font></strong> Please setup PayPal configuration under "Partner Modules" -> "PayPal" -> <a href="'.xtc_href_link('paypal_config.php').'"><strong>"PayPal Configuration"</strong></a>!',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_HEADING' => 'Note',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_ERROR_MESSAGE' => 'The payment with Pay upon invoice was cancelled',
  
  'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED' => 'The combination of your name and address could not be validated. Please correct your data and try again. You can find further information in the <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a target="_blank" href="https://www.ratepay.com/kontakt">contact form</a>.',
  'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR' => 'It is not possible to use the selected payment method. This decision is based on automated data processing. You can find further information in the <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a target="_blank" href="https://www.ratepay.com/kontakt">contact form</a>.',
  'MALFORMED_REQUEST_JSON' => 'It is not possible to use the selected payment method. This decision is based on automated data processing. You can find further information in the <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a target="_blank" href="https://www.ratepay.com/kontakt">contact form</a>.',

  'MODULE_PAYMENT_PAYPALPUI_TEXT_DOB' => 'Date of birth (e.g. 21/05/1970):',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_TELEPHONE' => 'Phone number:',
  'MODULE_PAYMENT_PAYPALPUI_TEXT_SERVICE' => 'Customer service: %s',
  
  'JS_DOB_ERROR' => 'Your date of birth needs to be entered in the following form DD/MM/YYYY (e.g. 21/05/1970)',
  'JS_TELEPHONE_ERROR' => 'For this payment method we need your phone number.',
  
  'MODULE_PAYMENT_PAYPALPUI_TEXT_LEGAL' => 'By clicking on the button, you agree to the <a target="_blank" href="https://www.ratepay.com/legal-payment-terms">terms of payment</a> and <a target="_blank" href="https://www.ratepay.com/legal-payment-dataprivacy">performance of a risk check</a> from the payment partner, Ratepay. You also agree to PayPal’s <a target="_blank" href="https://www.paypal.com/de/webapps/mpp/ua/privacy-full?locale.x=eng_DE&_ga=1.187051880.1362749179.1647260107">privacy statement</a>. If your request to purchase upon invoice is accepted, the purchase price claim will be assigned to Ratepay, and you may only pay Ratepay, not the merchant.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>