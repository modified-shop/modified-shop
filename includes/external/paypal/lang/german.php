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
  
  // template
  'TEXT_PAYPALINSTALLMENT_HEADING' => 'Zahlen sie bequem und einfach in monatlichen Raten',
  'TEXT_PAYPALINSTALLMENT_DESCRIPTION' => 'Ihre Ratenzahlung und den passenden Finanzierungsplan können Sie im Rahmen des Bestellprozesses auswählen. Ihr Antrag erfolgt komplett online und wird in wenigen Schritten hier im Shop abgeschlossen.',

  'TEXT_PAYPALINSTALLMENT_RATING_PLAN' => 'Finanzierung ab %s in %s Raten mit Ratenzahlung Powered by PayPal',
  'TEXT_PAYPALINSTALLMENT_RATING_PLAN_SHORT' => 'Finanzierung ab %s im Monat mit',

  'TEXT_PAYPALINSTALLMENT_LEGAL' => 'Repr&auml;sentatives Beispiel gem. &sect; 6a PAngV',
  'TEXT_PAYPALINSTALLMENT_NOMINAL_RATE' => 'fester Sollzinssatz',
  'TEXT_PAYPALINSTALLMENT_APR' => 'effektiver Jahreszins',
  'TEXT_PAYPALINSTALLMENT_TOTAL_COST' => 'Gesamtbetrag',
  'TEXT_PAYPALINSTALLMENT_TOTAL_NETTO' => 'Nettodarlehensbetrag',
  'TEXT_PAYPALINSTALLMENT_TOTAL_INTEREST' => 'Zinsbetrag',
  'TEXT_PAYPALINSTALLMENT_MONTHLY_PAYMENT' => 'monatliche Raten in H&ouml;he von je',

  'TEXT_PAYPALINSTALLMENT_NOTICE' => 'Finanzierung verf&uuml;gbar ab %s bis %s Warenkorbwert mit',
  'TEXT_PAYPALINSTALLMENT_NOTICE_PRODUCT' => 'Sie k&ouml;nnen diesen Artikel auch finanzieren!',
  'TEXT_PAYPALINSTALLMENT_NOTICE_CART' => 'Sie k&ouml;nnen diesen Warenkorb auch finanzieren!',
  'TEXT_PAYPALINSTALLMENT_NOTICE_CHECKOUT' => 'Sie k&ouml;nnen diese Bestellung auch finanzieren!',
  
  'TEXT_PAYPALINSTALLMENT_CREDITOR' => 'Darlehensgeber',
  'TEXT_PAYPALINSTALLMENT_INFO_LINK' => 'Informationen zu m&ouml;glichen Raten',

);


// define 
foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>