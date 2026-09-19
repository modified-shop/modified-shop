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
    'TEXT_TEAMBANK_ORDERS_HEADING' => 'Teambank Details',
    'TEXT_TEAMBANK_PENDING_HEADING' => 'Needs attention',
    'TEXT_TEAMBANK_PENDING_INFO' => 'The automatic reconciliation handed this transaction over to you. The reason is in the order status history. Check its status with the payment service, then hand it back to the reconciliation. The order has to sit on the temporary order status of its payment module for that, so the reconciliation does not overwrite a decision that was already made.',
    'TEXT_TEAMBANK_PENDING_SUBMIT' => 'Reconcile again',
    'TEXT_TEAMBANK_PENDING_SUCCESS' => 'The transaction was handed back to the automatic reconciliation.',
    'TEXT_TEAMBANK_PENDING_ERROR' => 'The transaction could not be handed back. Put the order back on the temporary order status of its payment module first.',
    'TEXT_TEAMBANK_PENDING_REVERSED' => 'The order was cancelled, which set its totals to zero. It cannot go back to the reconciliation because of that. Settle the transaction with the payment service and place a new order if one is needed.',
    'TEXT_TEAMBANK_NO_INFORMATION' => 'Keine Zahlungsdetails vorhanden',
    
    'TEXT_TEAMBANK_TRANSACTION' => 'Zahlungsdetails',
    'TEXT_TEAMBANK_TRANSACTION_STATE' => 'Status:',
    'TEXT_TEAMBANK_TRANSACTION_ID' => 'ID:',
    'TEXT_TEAMBANK_TRANSACTION_CUSTOMER' => 'Kunde:',
    'TEXT_TEAMBANK_TRANSACTION_TOTAL' => 'Gesamtbetrag:',
    'TEXT_TEAMBANK_TRANSACTION_REFUNDED' => 'R&uuml;ckzahlungen',
    'TEXT_TEAMBANK_TRANSACTION_BALANCE' => 'offener Betrag:',
    'TEXT_TEAMBANK_TRANSACTION_CLEARING' => 'bezahlt am:',
    'TEXT_TEAMBANK_TRANSACTION_VALID' => 'g&uuml;ltig bis:',
    
    'TEXT_TEAMBANK_TRANSACTIONS_STATUS' => 'Transaktionen',
    'TEXT_TEAMBANK_TRANSACTIONS_STATE' => 'Status:',
    'TEXT_TEAMBANK_TRANSACTIONS_ID' => 'ID:',
    'TEXT_TEAMBANK_TRANSACTIONS_AMOUNT' => 'Betrag:',
    
    'TEXT_TEAMBANK_TRACKING_TRACE' => 'Track &amp; Trace',
    'TEXT_TEAMBANK_TRACKING_NO_INFO' => 'keine Versandinformationen verf&uuml;gbar',
    'TEXT_TEAMBANK_TRACKING_SUBMIT' => 'Versand best&auml;tigen',

    'TEXT_TEAMBANK_CAPTURED_SUCCESS' => 'Versand an die Teambank erfolgreich best&auml;tigt.',
    'TEXT_TEAMBANK_CAPTURED_ERROR' => 'Versand an die Teambank nicht erfolgreich.',

    'TEXT_TEAMBANK_REFUND' => 'R&uuml;ckzahlung',
    'TEXT_TEAMBANK_REFUND_AMOUNT' => 'Betrag:',
    'TEXT_TEAMBANK_REFUND_SUBMIT' => 'R&uuml;ckzahlung',    
    'TEXT_TEAMBANK_REFUND_SUCCESS' => 'R&uuml;ckzahlung erfolgreich best&auml;tigt.',
    'TEXT_TEAMBANK_REFUND_ERROR' => 'R&uuml;ckzahlung nicht erfolgreich.',
    'TEXT_TEAMBANK_ERROR_AMOUNT' => 'Bitte geben Sie einen g&uuml;ltigen Betrag ein.',
  );
  
  // define 
  foreach ($lang_array as $key => $val) {
    defined($key) or define($key, $val);
  }
