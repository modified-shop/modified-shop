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
  'TEXT_PAYPAL_ORDERS_HEADING' => 'PayPal Details',
  'TEXT_PAYPAL_NO_INFORMATION' => 'Keine Zahlungsdetails vorhanden',
  
  // transaction
  'TEXT_PAYPAL_TRANSACTION' => 'Zahlungsdetails',
  'TEXT_PAYPAL_TRANSACTION_ADDRESS' => 'Adresse:',
  'TEXT_PAYPAL_TRANSACTION_METHOD' => 'Zahlart:',
  'TEXT_PAYPAL_TRANSACTION_EMAIL' => 'E-Mail Adresse:',
  'TEXT_PAYPAL_TRANSACTION_ACCOUNT_STATE' => 'Account Status:',
  'TEXT_PAYPAL_TRANSACTION_INTENT' => 'Intent:',
  'TEXT_PAYPAL_TRANSACTION_STATE' => 'Status:',
  
  
  // transactions
  'TEXT_PAYPAL_TRANSACTIONS_STATUS' => 'Transaktionen',
  'TEXT_PAYPAL_TRANSACTIONS_PAYMENT' => 'Zahlung:',
  'TEXT_PAYPAL_TRANSACTIONS_REASON' => 'Grund:',
  'TEXT_PAYPAL_TRANSACTIONS_STATE' => 'Status:',
  'TEXT_PAYPAL_TRANSACTIONS_TOTAL' => 'Betrag:',
  'TEXT_PAYPAL_TRANSACTIONS_VALID' => 'G&uuml;ltig bis:',
  'TEXT_PAYPAL_TRANSACTIONS_ID' => 'ID:',
  
  
  // instruction
  'TEXT_PAYPAL_INSTRUCTIONS' => 'Zahlungsanweisung',
  'TEXT_PAYPAL_INSTRUCTIONS_CHECKOUT' => 'Bitte &uuml;berweisen Sie den Betrag von %s bis sp&auml;testens %s auf folgendes Konto:',
  'TEXT_PAYPAL_INSTRUCTIONS_AMOUNT' => 'Betrag:',
  'TEXT_PAYPAL_INSTRUCTIONS_REFERENCE' => 'Verwendungszweck:',
  'TEXT_PAYPAL_INSTRUCTIONS_PAYDATE' => 'Zahlbar bis:',
  'TEXT_PAYPAL_INSTRUCTIONS_ACCOUNT' => 'Bank:',
  'TEXT_PAYPAL_INSTRUCTIONS_HOLDER' => 'Inhaber:',
  'TEXT_PAYPAL_INSTRUCTIONS_IBAN' => 'IBAN:',
  'TEXT_PAYPAL_INSTRUCTIONS_BIC' => 'BIC:',
  
  
  // refund
  'TEXT_PAYPAL_REFUND' => 'R&uuml;ckzahlung',
  'TEXT_PAYPAL_REFUND_LEFT' => 'Anzahl m&ouml;glicher R&uuml;ckzahlungen: ',
  'TEXT_PAYPAL_REFUND_COMMENT' => 'Kommentar:',
  'TEXT_PAYPAL_REFUND_AMOUNT' => 'Betrag:',
  'TEXT_PAYPAL_REFUND_SUBMIT' => 'R&uuml;ckzahlung',
  
  
  // capture
  'TEXT_PAYPAL_CAPTURE' => 'Zahlung erfassen',
  'TEXT_PAYPAL_CAPTURE_LEFT' => 'Anzahl m&ouml;glicher Erfassungen: ',
  'TEXT_PAYPAL_CAPTURE_IS_FINAL' => 'Letzte Erfassung:',
  'TEXT_PAYPAL_CAPTURE_AMOUNT' => 'Betrag:',
  'TEXT_PAYPAL_CAPTURE_SUBMIT' => 'Zahlung erfassen',
  'TEXT_PAYPAL_CAPTURED' => 'Zahlung erfasst',
  
  
  // error
  'TEXT_PAYPAL_ERROR_AMOUNT' => 'Bitte geben Sie einen Betrag ein.',
  
  
  // diverse
  'MODULE_PAYMENT_PAYPAL_TEXT_ORDER' => 'Ihre Bestellung bei '.STORE_NAME,


  // status
  'TEXT_PAYPAL_NO_STATUS_CHANGE' => 'keine Status&auml;nderung',
  
);


// define 
foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>