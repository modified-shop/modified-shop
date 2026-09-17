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
    'TEXT_TEAMBANK_PENDING_HEADING' => 'Kl&auml;rung erforderlich',
    'TEXT_TEAMBANK_PENDING_INFO' => 'Der automatische Abgleich hat diesen Vorgang an Sie &uuml;bergeben. Der Grund steht im Bestellstatus-Verlauf. Pr&uuml;fen Sie den Status beim Zahlungsdienst und geben Sie den Vorgang danach wieder an den Abgleich zur&uuml;ck. Die Bestellung muss dazu auf dem tempor&auml;ren Bestellstatus des Zahlungsmoduls stehen, damit der Abgleich eine bereits getroffene Entscheidung nicht &uuml;berschreibt.',
    'TEXT_TEAMBANK_PENDING_SUBMIT' => 'Erneut abgleichen',
    'TEXT_TEAMBANK_PENDING_SUCCESS' => 'Der Vorgang wurde an den automatischen Abgleich zur&uuml;ckgegeben.',
    'TEXT_TEAMBANK_PENDING_ERROR' => 'Der Vorgang konnte nicht zur&uuml;ckgegeben werden. Setzen Sie die Bestellung zuvor auf den tempor&auml;ren Bestellstatus des Zahlungsmoduls.',
    'TEXT_TEAMBANK_PENDING_REVERSED' => 'Die Bestellung wurde storniert, dabei wurden ihre Summen auf 0 gesetzt. Ein erneuter Abgleich ist deshalb nicht m&ouml;glich. Kl&auml;ren Sie den Vorgang beim Zahlungsdienst und legen Sie bei Bedarf eine neue Bestellung an.',
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
