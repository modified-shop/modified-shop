<?php
/* --------------------------------------------------------------
   $Id
   modified - community made shopping
   http://www.modified-shop.org
   Copyright (c) 2009 - 2012 modified
   --------------------------------------------------------------
   Released under the GNU General Public License
--------------------------------------------------------------*/
/* ACHTUNG ! Texte nicht &auml;ndern da Status abfrage im Programm */
define('HEADING_TITLE','PayPal Transaktionen');
define('TABLE_HEADING_PAYPAL_ID','Transaktions-Id');
define('TABLE_HEADING_NAME','Name');
define('TABLE_HEADING_TXN_TYPE','T.-Typ');
define('TABLE_HEADING_PAYMENT_TYPE','Zahlungsweise');
define('TABLE_HEADING_PAYMENT_STATUS','Zahlungsstatus');
define('TABLE_HEADING_PAYMENT_AMOUNT','Summe');
define('TABLE_HEADING_ORDERS_ID','Bestellnr.');
define('TABLE_HEADING_ORDERS_STATUS','Bestellstatus');
define('TABLE_HEADING_ACTION','Aktion');
define('TEXT_PAYPAL_TRANSACTION_HISTORY','Tranksaktionsverlauf');
define('TEXT_PAYPAL_PENDING_REASON','Grund');
define('TEXT_PAYPAL_CAPTURE_TRANSACTION','Make Capture');
define('TEXT_PAYPAL_TRANSACTION_DETAIL','Transaktionsdetails');
define('TEXT_PAYPAL_TXN_ID','Zahlungsart/Code');
define('TEXT_PAYPAL_COMPANY','Firma');
define('TEXT_PAYPAL_PAYER_EMAIL','E-Mail');
define('TEXT_PAYPAL_RECEIVER_EMAIL','Zahlungsempf&auml;nger');
define('TEXT_PAYPAL_CARTITEM','Artikel Zeilen');
define('TEXT_PAYPAL_VERSAND','Versand');
define('TEXT_PAYPAL_TOTAL','Brutto');
define('TEXT_PAYPAL_FEE','Geb&uuml;hr');
define('TEXT_PAYPAL_ORDER_ID','Bestellnummer');
define('TEXT_PAYPAL_PAYMENT_STATUS','Status');
define('TEXT_PAYPAL_PAYMENT_DATE','Datum');
define('TEXT_PAYPAL_PAYMENT_TIME','Uhrzeit');
define('TEXT_PAYPAL_KUNDE','Kunde');
define('TEXT_PAYPAL_ADRESS','Versand an');
define('TEXT_PAYPAL_PAYMENT_TYPE','Zahlungsweise');
define('TEXT_PAYPAL_ADRESS_STATUS','Status der Adresse');
define('TEXT_PAYPAL_PAYER_EMAIL_STATUS','Status des Absenders');
define('TEXT_PAYPAL_NETTO','Netto');
define('TEXT_PAYPAL_DETAIL','Details');
define('TEXT_PAYPAL_TYPE','Art');
define('TEXT_PAYPAL_PAYMENT_REASON','Grund');
define('TEXT_PAYPAL_TRANSACTION_TOTAL','Urspr&uuml;ngliche Zahlung:');
define('TEXT_PAYPAL_TRANSACTION_LEFT','Restbetrag:');
define('TEXT_PAYPAL_AMOUNT','R&uuml;ckzahlungsbetrag:');
define('TEXT_PAYPAL_REFUND_TRANSACTION','R&uuml;ckzahlung veranlassen');
define('TEXT_PAYPAL_REFUND_NOTE','Hinweis f&uuml;r den K&auml;ufer <br />(optional):');
define('TEXT_PAYPAL_OPTIONS','Zahlungsoptionen');
define('TEXT_PAYPAL_TRANSACTION_AUTH_TOTAL','Reservierte Summe:');
define('TEXT_PAYPAL_TRANSACTION_AMOUNT','Capture Amount:');
define('TEXT_PAYPAL_TRANSACTION_AUTH_CAPTURED','Total Capture:');
define('TEXT_PAYPAL_TRANSACTION_AUTH_OPEN','Open Capture:');
define('TEXT_PAYPAL_ACTION_REFUND','Zahlung erstatten (bis 60 Tage nach Transaktion)');
define('TEXT_PAYPAL_ACTION_CAPTURE','Capture Amount');
define('REFUND','Erstatten');
define('TEXT_PAYPAL_PAYMENT','PayPal-Zahlungsstatus');
define('TEXT_PAYPAL_TRANSACTION_CONNECTED','Dazugeh&ouml;rige Transaktionen');
define('TEXT_PAYPAL_TRANSACTION_ORIGINAL','Urspr&uuml;ngliche Transaktion');
define('TEXT_PAYPAL_SEARCH_TRANSACTION','Suche nach Transaktionen');
define('TEXT_PAYPAL_FOUND_TRANSACTION','Gefundene Transaktionen');
define('STATUS_COMPLETED','Abgeschlossen');
define('STATUS_VERIFIED','verifiziert');
define('STATUS_UNVERIFIED','Nicht Verifiziert');
define('STATUS_PENDING','Pending');
define('STATUS_REFUNDED','Zur&uuml;ckgezahlt');
define('STATUS_REVERSED','Reversed');
define('STATUS_DENIED','Storniert');
define('STATUS_CASE','K&auml;uferkonflikt');
define('STATUS_CANCELED_REVERSAL','R&uuml;cklastschrift');
define('STATUS_CANCELLED_REVERSA','R&uuml;cklastschrift');
define('STATUS_EXPIRED','Abgelaufen');
define('STATUS_FAILED','Fehlgeschlagen');
define('STATUS_IN-PROGRESS','In Bearbeitung');
define('STATUS_PARTIALLY_REFUNDE','Teilweise Zur&uuml;ckgezahlt');
define('STATUS_PROCESSED','Abgeschlossen');
define('STATUS_VOIDED','Voided');
define('STATUS_OPENCAPTURE','Reserviert');
define('STATUS_CREATED', 'Erstellt');
define('TYPE_INSTANT','Sofort');
define('TYPE_ECHECK','&Uuml;berweisung');
define('REASON_NOT_AS_DESCRIBE','Produkt nicht wie beschrieben!');
define('REASON_NON_RECEIPT','Produkt nicht erhalten!');
define('TYPE_REFUNDED','R&uuml;ckzahlung');
define('TYPE_REVERSED','-Zahlung gesendet');
define('TYPE_REFUNDED','R&uuml;ckzahlung');
define('TEXT_DISPLAY_NUMBER_OF_PAYPAL_TRANSACTIONS','Angezeigt werden <b>%d</b> bis <b>%d</b> (von insgesamt <b>%d</b> Transaktionen)');
// define NOTES
define('TEXT_PAYPAL_NOTE_REFUND_INFO','Bis 60 Tage nach dem Senden der urspr&uuml;nglichen Zahlung k&ouml;nnen Sie eine vollst&auml;ndige oder eine Teilr&uuml;ckzahlung leisten. Wenn Sie eine R&uuml;ckzahlung veranlassen, erhalten Sie von PayPal eine Geb&uuml;hrenr&uuml;ckerstattung, einschlie&szlig;lich der Teilgeb&uuml;hren f&uuml;r Teilr&uuml;ckzahlungen.
<br /><br />Um eine R&uuml;ckzahlung zu veranlassen, geben Sie den Betrag in das Feld R&uuml;ckzahlungsbetrag ein, und klicken Sie auf Weiter. ');
define('TEXT_PAYPAL_NOTE_CAPTURE_INFO','');
// errors
define('REFUND_SUCCESS','Refund Success');
define('CAPTURE_SUCCESS','Capture Success');
define('ERROR_10009','The partial refund amount must be less than or equal to the remaining amount');
// capture
define('ERROR_10610','Amount specified exceeds allowable limit');
define('ERROR_10602','Authorization has already been completed');
define('ERROR_81251','Internal Service Error');
// Bestell-Status nur zur Installation
$PAYPAL_INST_ORDER_STATUS_TMP_NAME='PayPal Abbruch';
$PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME='Offen PP bezahlt';
$PAYPAL_INST_ORDER_STATUS_PENDING_NAME='Offen PP wartend';
$PAYPAL_INST_ORDER_STATUS_REJECTED_NAME='PayPal abgelehnt';
?>