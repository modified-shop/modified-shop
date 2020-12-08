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
  'TEXT_KLARNA_ORDERS_HEADING' => 'Klarna Details',
  
  'TEXT_KLARNA_TRANSACTION' => 'Transaktion',
  'TEXT_KLARNA_TRANSACTION_METHOD' => 'Zahlungsmethode:',
  'TEXT_KLARNA_TRANSACTION_REFERENCE' => 'Bestellung:',
  'TEXT_KLARNA_TRANSACTION_STATUS' => 'Status:',
  'TEXT_KLARNA_TRANSACTION_FRAUD_STATUS' => 'Betrugsstatus:',
  'TEXT_KLARNA_TRANSACTIONS_TOTAL' => 'Gesamtbetrag:',
  'TEXT_KLARNA_TRANSACTIONS_CAPTURED' => 'Aktiviert:',
  'TEXT_KLARNA_TRANSACTIONS_REFUNDED' => 'Zur&uuml;ckerstattet:',
  'TEXT_KLARNA_TRANSACTIONS_REMAINING' => 'Nicht aktiviert:',
  
  'TEXT_KLARNA_TRANSACTIONS_STATUS' => 'Aktivit&auml;tsprotokoll',
  'TEXT_KLARNA_CAPTURE' => 'Aktivierung',
  'TEXT_KLARNA_REFUND' => 'R&uuml;ckerstattung',
  'TEXT_KLARNA_TRANSACTION_ID' => 'ID:',
  'TEXT_KLARNA_TRANSACTION_TOTAL' => 'Betrag:',
  'TEXT_KLARNA_TRANSACTION_REFERENCE' => 'Referenz:',
  'TEXT_KLARNA_TRANSACTION_ERROR_AMOUNT' => 'Bitte geben Sie einen g&uuml;ltigen Betrag ein.',

  'TEXT_KLARNA_TRANSACTION_CAPTURE' => 'Der Betrag wurde erfolgreich aktiviert.',
  'TEXT_KLARNA_TRANSACTION_REFUND' => 'Der Betrag wurde erfolgreich erstattet.',
  'TEXT_KLARNA_TRANSACTION_CANCEL' => 'Die Bestellung wurde erfolgreich storniert.',
  
  'TEXT_KLARNA_CAPTURE_AMOUNT' => 'Betrag:',
  'TEXT_KLARNA_CAPTURE_MAX_AMOUNT' => 'Maximaler Betrag:',

  'TEXT_KLARNA_REFUND_AMOUNT' => 'Betrag:',
  'TEXT_KLARNA_REFUND_MAX_AMOUNT' => 'Maximaler Betrag:',
  
  'TEXT_KLARNA_CAPTURE_SUBMIT' => 'Aktivieren',
  'TEXT_KLARNA_REFUND_SUBMIT' => 'R&uuml;ckerstatten',
  'TEXT_KLARNA_CANCEL_SUBMIT' => 'Bestellung Stornieren',

  'TEXT_KLARNA_CHECKOUT_ERROR' => 'Es ist ein Fehler bei der Verarbeitung der Zahlung aufgetreten.',
);

foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
