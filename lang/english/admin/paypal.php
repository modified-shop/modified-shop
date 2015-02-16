<?php
/* --------------------------------------------------------------
   $Id: paypal.php 4202 2013-01-10 20:27:44Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
--------------------------------------------------------------*/
/* ACHTUNG ! Texte nicht ändern da Status abfrage im Programm */
define('HEADING_TITLE','PayPal Transactions');
define('TABLE_HEADING_PAYPAL_ID','Transaction-Id');
define('TABLE_HEADING_NAME','Name');
define('TABLE_HEADING_TXN_TYPE','T.-Typ');
define('TABLE_HEADING_PAYMENT_TYPE','Payment method');
define('TABLE_HEADING_PAYMENT_STATUS','Payment status');
define('TABLE_HEADING_PAYMENT_AMOUNT','Amount');
define('TABLE_HEADING_ORDERS_ID','Order ID');
define('TABLE_HEADING_ORDERS_STATUS','Order status');
define('TABLE_HEADING_ACTION','Action');
define('TEXT_PAYPAL_TRANSACTION_HISTORY','Transaction process');
define('TEXT_PAYPAL_PENDING_REASON','Reason');
define('TEXT_PAYPAL_CAPTURE_TRANSACTION','Make Capture');
define('TEXT_PAYPAL_TRANSACTION_DETAIL','Transaction details');
define('TEXT_PAYPAL_TXN_ID','Payment method/Code');
define('TEXT_PAYPAL_COMPANY','Company');
define('TEXT_PAYPAL_PAYER_EMAIL','E-Mail');
define('TEXT_PAYPAL_RECEIVER_EMAIL','Payee');
define('TEXT_PAYPAL_CARTITEM','Products count');
define('TEXT_PAYPAL_VERSAND','Delivery');
define('TEXT_PAYPAL_TOTAL','Total');
define('TEXT_PAYPAL_FEE','Fee');
define('TEXT_PAYPAL_ORDER_ID','Order ID');
define('TEXT_PAYPAL_PAYMENT_STATUS','Status');
define('TEXT_PAYPAL_PAYMENT_DATE','Date');
define('TEXT_PAYPAL_PAYMENT_TIME','Time');
define('TEXT_PAYPAL_KUNDE','Customer');
define('TEXT_PAYPAL_ADRESS','Address');
define('TEXT_PAYPAL_PAYMENT_TYPE','Payment type');
define('TEXT_PAYPAL_ADRESS_STATUS','Address status');
define('TEXT_PAYPAL_PAYER_EMAIL_STATUS','Payer status');
define('TEXT_PAYPAL_NETTO','Netto');
define('TEXT_PAYPAL_DETAIL','Details');
define('TEXT_PAYPAL_TYPE','Type');
define('TEXT_PAYPAL_PAYMENT_REASON','Reason');
define('TEXT_PAYPAL_TRANSACTION_TOTAL','Original payment:');
define('TEXT_PAYPAL_TRANSACTION_LEFT','Remainder:');
define('TEXT_PAYPAL_AMOUNT','Amount of repayment:');
define('TEXT_PAYPAL_REFUND_TRANSACTION','Arrange repayment');
define('TEXT_PAYPAL_REFUND_NOTE','Notice for Payer <br />(optional):');
define('TEXT_PAYPAL_OPTIONS','Payment options');
define('TEXT_PAYPAL_TRANSACTION_AUTH_TOTAL','Reserved sum:');
define('TEXT_PAYPAL_TRANSACTION_AMOUNT','Capture Amount:');
define('TEXT_PAYPAL_TRANSACTION_AUTH_CAPTURED','Total Capture:');
define('TEXT_PAYPAL_TRANSACTION_AUTH_OPEN','Open Capture:');
define('TEXT_PAYPAL_ACTION_REFUND','Refund Payment (until 60 days after transaction)');
define('TEXT_PAYPAL_ACTION_CAPTURE','Capture Amount');
define('REFUND','Refund');
define('TEXT_PAYPAL_PAYMENT','PayPal-Paymentstatus');
define('TEXT_PAYPAL_TRANSACTION_CONNECTED','Connected Transactions');
define('TEXT_PAYPAL_TRANSACTION_ORIGINAL','Original Transaction');
define('TEXT_PAYPAL_SEARCH_TRANSACTION','Search Transactions');
define('TEXT_PAYPAL_FOUND_TRANSACTION','Found Transactions');
define('STATUS_COMPLETED','Completed');
define('STATUS_VERIFIED','verified');
define('STATUS_UNVERIFIED','Not verified');
define('STATUS_PENDING','Pending');
define('STATUS_REFUNDED','Refunded');
define('STATUS_REVERSED','Reversed');
define('STATUS_DENIED','Denied');
define('STATUS_CASE','Customer conflict');
define('STATUS_CANCELED_REVERSAL','Charge back');
define('STATUS_CANCELLED_REVERSA','Charge back');
define('STATUS_EXPIRED','Expired');
define('STATUS_FAILED','Failed');
define('STATUS_IN-PROGRESS','In progress');
define('STATUS_PARTIALLY_REFUNDE','Partially refunded');
define('STATUS_PROCESSED','Processed');
define('STATUS_VOIDED','Voided');
define('STATUS_OPENCAPTURE','Withdrawn');
define('STATUS_CREATED', 'Created');
define('TYPE_INSTANT','Instant');
define('TYPE_ECHECK','Bank transfer');
define('REASON_NOT_AS_DESCRIBE','Product not as described!');
define('REASON_NON_RECEIPT','Product not received!');
define('TYPE_REFUNDED','Repayment');
define('TYPE_REVERSED','-Payment sent');
define('TEXT_DISPLAY_NUMBER_OF_PAYPAL_TRANSACTIONS','Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> Transactions)');
// define NOTES
define('TEXT_PAYPAL_NOTE_REFUND_INFO','Until 60 days after sending the original payment can carry you out a complete or a partial restitution. If you receive a repayment to arrange, you from PayPal a refund of charges, including the partial fees for partial restitutions.
<br /><br />In order to arrange a repayment too, you enter the amount into the field amount of repayment, and click you on far one.');
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
$PAYPAL_INST_ORDER_STATUS_TMP_NAME='PayPal canceled';
$PAYPAL_INST_ORDER_STATUS_SUCCESS_NAME='Pending PP sold';
$PAYPAL_INST_ORDER_STATUS_PENDING_NAME='Open PP pending';
$PAYPAL_INST_ORDER_STATUS_REJECTED_NAME='PayPal rejected';
?>
