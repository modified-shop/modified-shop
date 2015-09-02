<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// error messages
define('NOTE_ADDRESS_CHANGED', 'The address has changed.');
define('ADDRESSES_MUST_BE_EQUAL','With the payment method choosen billing and delivery address must correspond!');
define('INSTALLMENT_TYPE_NOT_SELECTED', 'No type selected.');
define('PAYDATA_INCOMPLETE', 'The indications for the payment method are incomplete.');
define('PAYMENT_ERROR', 'An error occurred while processing.');
define('ERROR_MUST_CONFIRM_MANDATE', 'Please confirm that you want to grant the SEPA direct debit mandate.');
 
// credit risk check
define('CREDIT_RISK_HEADING', 'Credit check');
define('BUTTON_CONFIRM', 'Yes, perform credit check');
define('BUTTON_NOCONFIRM', 'No, don\'t execute a verification');
define('TEXT_CREDIT_RISK_INFO', 'A credit assessment is being executed.');
define('TEXT_CREDIT_RISK_COMFIRM', 'Do you want to agree with this?');
define('CREDIT_RISK_FAILED', 'Please choose another payment method.');
define('CREDIT_RISK_CONFIGURATION', 'Credit check');
define('CR_ACTIVE', 'active');
define('CR_OPERATING_MODE', 'Operating mode');
define('CR_TIMEOFCHECK', 'Point in time of the evaluation');
define('CR_TIMEOFCHECK_BEFORE', 'before choice of the payment method');
define('CR_TIMEOFCHECK_AFTER', 'after the choice of the payment method');
define('CR_TYPEOFCHECK', 'Method of evaluation');
define('CR_TYPEOFCHECK_ISCOREHARD', 'Infoscore (hard criteria)');
define('CR_TYPEOFCHECK_ISCOREALL', 'Infoscore (all criterions)');
define('CR_TYPEOFCHECK_ISCOREBSCORE', 'Infoscore (all criterions + score of boni');
define('CR_NEWCLIENTDEFAULT', 'Default value for new customers');
define('CR_VALIDITY', 'Validity');
define('CR_MIN_CART_VALUE', 'Minimum value of goods');
define('CR_MAX_CART_VALUE', 'Maximum value of goods');
define('CR_CHECKFORGENRE', 'Evaluation with');
define('CR_ERROR_MODE', 'Error behavior');
define('CR_ERROR_MODE_ABORT', 'Cancel process');
define('CR_ERROR_MODE_CONTINUE', 'continue');
define('CR_NOTICE', 'Notice');
define('CR_CONFIRMATION', 'Inquiry of approval');
define('CR_ABTEST', 'A/B testing');
 
// address check
define('TEXT_ADDRESS_CHECK_HEADING', 'Correct address');
define('TEXT_ADDRESS_CHECK_CHANGED', 'corrected');
define('AC_ACTIVE', 'active');
define('AC_OPERATING_MODE', 'Operating mode');
define('AC_BILLING_ADDRESS', 'Billing address');
define('AC_DELIVERY_ADDRESS', 'Shipping address');
define('AC_AUTOMATIC_CORRECTION', 'Automatic correction');
define('AC_ERROR_MODE', 'Error behavior');
define('AC_MIN_CART_VALUE', 'Minimum value of goods');
define('AC_MAX_CART_VALUE', 'Maximum value of goods');
define('AC_VALIDITY', 'Validity');
define('AC_ERROR_MESSAGE', 'Error message');
define('AC_PSTATUS_MAPPING', 'Persons status mapping');
define('AC_BACHECK_NONE', 'don\'t check');
define('AC_BACHECK_BASIC', 'Basic');
define('AC_BACHECK_PERSON', 'Person (only DE!)');
define('AC_AUTOMATIC_CORRECTION_NO', 'no');
define('AC_AUTOMATIC_CORRECTION_YES', 'yes');
define('AC_AUTOMATIC_CORRECTION_USER', 'Users decision');
define('AC_ERROR_MODE_ABORT', 'Cancel process');
define('AC_ERROR_MODE_REENTER', 'Retype');
define('AC_ERROR_MODE_CHECK', 'Perform subsequent credit check');
define('AC_ERROR_MODE_CONTINUE', 'continue');
define('DAYS', 'Days');
define('ERROR_MESSAGE_INFO', 'Use {payone_error} as a placeholder for the response of the PAYONE platform');
define('AC_PSTATUS_NOPCHECK', 'No person audit executed');
define('AC_PSTATUS_FULLNAMEKNOWN', 'First name and surname are known');
define('AC_PSTATUS_LASTNAMEKNOWN', 'Surname is known');
define('AC_PSTATUS_NAMEUNKNOWN', 'First name and surname unknown');
define('AC_PSTATUS_NAMEADDRAMBIGUITY', 'Ambiguity in name to address');
define('AC_PSTATUS_UNDELIVERABLE', 'not deliverable (any more)');
define('AC_PSTATUS_DEAD', 'Person deceased');
define('AC_PSTATUS_POSTALERROR', 'Wrong postal address');
 
// api
define('STATUS_UPDATED_BY_PAYONE', 'Status updated by PAYONE');
define('COMMENT_ERROR', 'comment_error');
define('COMMENT_REDIRECTION_INITIATED', 'comment_redirection_initiated');
define('COMMENT_AUTH_APPROVED', 'Payment approved');
define('COMMENT_PREAUTH_APPROVED', 'Payment approved');
define('VOUCHER_OR_DISCOUNT', 'voucher_or_discount');
define('MISC_HANDLING', 'misc_handling');
define('SHIPPING_COST', 'shipping_cost');
 
// payment
define('paymenttype_visa', 'Visa');
define('paymenttype_mastercard', 'Mastercard');
define('paymenttype_amex', 'American Express');
define('paymenttype_cartebleue', 'Carte Bleue');
define('paymenttype_dinersclub', 'Diners Club');
define('paymenttype_discover', 'Discover');
define('paymenttype_jcb', 'JCB');
define('paymenttype_maestro', 'Maestro');
define('paymenttype_billsafe', 'BillSAFE');
define('paymenttype_klarna', 'Klarna');
define('paymenttype_commerzfinanz', 'CommerzFinanz');
define('paymenttype_lastschrift', 'Direct debit');
define('paymenttype_invoice', 'Sale on account');
define('paymenttype_prepay', 'Cash in advance');
define('paymenttype_cod', 'Cash on delivery');
define('paymenttype_paypal', 'PayPal');
define('paymenttype_sofortueberweisung', 'Sofort&uuml;berweisung');
define('paymenttype_giropay', 'GiroPay');
define('paymenttype_eps', 'EPS');
define('paymenttype_pfefinance', 'Post-Finance EFinance');
define('paymenttype_pfcard', 'Post-Finance Card');
define('paymenttype_ideal', 'iDEAL');
 
// payment form
define('selection_type', 'Payment method:');
define('customers_dob', 'Date of birth (DD.MM.YYYY):');
define('customers_telephone', 'Phone:');
define('personalid', 'Personal ID:');
define('addressaddition', 'Additional address:');
 
// installment
define('TEXT_KLARNA_CONFIRM', ' I agree with the data processing required for the execution of the sale on account and an identity and credit check by Klarna. I can revoke my %s at any time with effect for the future. Terms and conditions of the dealer apply.');
define('TEXT_KLARNA_ERROR_CONDITIONS', 'If you do not accept the conditions of invoice from Klarna, we unfortunately can not accept your order!');
define('TEXT_KLARNA_INVOICE', 'For more information on sale on account, see the');
define('KLARNA_STOREID', 'Klarna SoreID');
define('KLARNA_COUNTRIES', 'Klarna Countries');
 
// otrans
define('onlinetransfer_type', 'Type:');
define('bankaccountholder', 'Account holder:');
define('iban', 'IBAN:');
define('bic', 'BIC:');
define('ideal', 'Bank group:');
define('eps', 'Bank group:');
define('bankaccount', 'Account number:');
define('bankcode', 'Bank code:');
 
// ELV
define('SEPA_MANDATE_HEADING', 'SEPA direct debit');
define('SEPA_MANDATE_INFO', 'For redeeming the amount by direct debit from your bank account, we need a SEPA direct debit mandate.');
define('SEPA_MANDATE_CONFIRM_LABEL', 'I would like to grant a mandate (electronic transmission)');
define('NOTE_GERMAN_ACCOUNT', 'or pay as usual with your known bank details (only for German bank accounts)');
define('ELV_IBAN', 'IBAN:');
define('ELV_BIC', 'BIC:');
define('ELV_ACCOUNT_HOLDER', 'Account holder:');
define('ELV_BANKCODE', 'Bank code:');
define('ELV_ACCOUNT_NUMBER', 'Account number:');
define('ELV_COUNTRY', 'Country:');
define('ELV_COUNTRY_DE', 'Germany');
define('ELV_COUNTRY_AT', 'Austria');
define('ELV_COUNTRY_NL', 'Netherlands');
define('SEPA_COUNTRIES', 'List of supported SEPA direct debit countries');
define('SEPA_DISPLAY_KTOBLZ', 'Additional fields bank account/bank code');
define('SEPA_DISPLAY_KTOBLZ_NOTE', 'Show additional fields for account number/bank code (only german bank accounts)');
define('SEPA_USE_MANAGEMANDATE', 'Enable grant of mandate');
define('SEPA_USE_MANAGEMANDATE_NOTE', 'The grant of mandate takes place with the charged request "managemandate". The request includes a bank account check. However, no inquiry for the POS blocklist is possible with this.');
define('SEPA_DOWNLOAD_PDF', 'Download mandate as PDF');
define('SEPA_DOWNLOAD_PDF_NOTE', 'Offer download of the SEPA direct debit mandate as PDF file (only available if you expensed the product "SEPA mandates as PDF" at PAYONE)');
define('DOWNLOAD_MANDATE_HERE', 'You can now download the mandate as part of the SEPA direct debit payment here: ');
define('MANDATE_PDF', 'PDF-File');
define('CHECK_BANKDATA', 'Check account data');
define('DONT_CHECK', 'don\'t check account data');
define('CHECK_BASIC', 'Basic');
define('CHECK_POS', 'with POS blocklist');
 
// cc
define('TEXT_CARDOWNER', 'Card holder:');
define('TEXT_CARDTYPE', 'Card type:');
define('TEXT_CARDNO', 'Card number:');
define('TEXT_CARDEXPIRES', 'Valid until (Month / Year):');
define('TEXT_CARDCHECKNUM', 'Check digit:');
define('TEXT_CHECK_DATA', 'Please check your data.');
 
// orders status
define('ORDERS_STATUS_CONFIGURATION', 'Orders status configuration');
define('ORDERS_STATUS_TMP', 'temporary status');
define('ORDERS_STATUS_PENDING', 'Payment receipt insecure/expected');
define('ORDERS_STATUS_PAID', 'Payment successful');
define('ORDERS_STATUS_DENIED', 'Payment failed/refused');
define('ORDERS_STATUS_APPROVED', 'Payment approved');
define('ORDERS_STATUS_APPOINTED', 'Payment appointed');
define('ORDERS_STATUS_CAPTURE', 'Payment capture');
define('ORDERS_STATUS_UNDERPAID', 'Payment insufficient');
define('ORDERS_STATUS_CANCELATION', 'Payment canceled');
define('ORDERS_STATUS_REFUND', 'Payment refund');
define('ORDERS_STATUS_DEBIT', 'Payment collection');
define('ORDERS_STATUS_TRANSFER', 'Payment transaction');
define('ORDERS_STATUS_REMINDER', 'Payment reminder');
define('ORDERS_STATUS_VAUTHORIZATION', 'Payment vAuth');
define('ORDERS_STATUS_VSETTLEMENT', 'Payment vSettlement');
define('ORDERS_STATUS_INVOICE', 'Payment sale on account');
define('TEXT_EXTERN_CALLBACK_URL', 'URL status forwarding');
define('TEXT_EXTERN_CALLBACK_TIMEOUT', 'Timeout');
 
// global
define('TEXT_YES', 'Yes');
define('TEXT_NO', 'No');
define('ERROR_OCCURED', 'Error occurred');
define('BOX_PAYONE_CONFIG', 'PAYONE configuration');
define('BOX_PAYONE_LOGS', 'PAYONE API Log');
define('PAYONE_CONFIG_TITLE', 'PAYONE configuration');
define('PAYMENT_CONFIGURATION', 'Payment configuration');
define('GLOBAL_CONFIGURATION', 'Global parameters');
define('MERCHANT_ID', 'Merchant-ID');
define('PORTAL_ID', 'Portal-ID');
define('SUBACCOUNT_ID', 'Subaccount-ID');
define('KEY', 'Key');
define('OPERATING_MODE', 'Operating mode');
define('OPMODE_TEST', 'Test mode');
define('OPMODE_LIVE', 'Live mode');
define('AUTHORIZATION_METHOD', 'Authorization method');
define('AUTHMETHOD_AUTH', 'Instant authorization');
define('AUTHMETHOD_PREAUTH', 'Pre-authorization');
define('SEND_CART', 'Transfer cart');
define('SENDCART_TRUE', 'yes');
define('SENDCART_FALSE', 'no');
 
// payment genre
define('PAYMENT_GENRE', 'Payment method');
define('PAYMENTGENRE_CONFIGURATION', 'Payment methods configuration');
define('PG_ACTIVE', 'active');
define('PG_ORDER', 'Sort order');
define('PG_NAME', 'Internal name');
define('PG_MIN_CART_VALUE', 'Minimum value of goods');
define('PG_MAX_CART_VALUE', 'Maximum value of goods');
define('PG_OPERATING_MODE', 'Operating mode');
define('PG_GLOBAL_OVERRIDE', 'override global parameters');
define('PG_COUNTRIES', 'active countries');
define('PG_SCORING_ALLOWED', 'allowed scoring values');
define('PG_RED', 'red');
define('PG_YELLOW', 'yellow');
define('PG_GREEN', 'green');
define('PG_PAYMENT_TYPES', 'Payment method types');
define('PG_PAYMENTTYPE_VISA', 'Visa');
define('PG_PAYMENTTYPE_MASTERCARD', 'Mastercard');
define('PG_PAYMENTTYPE_AMEX', 'American Express');
define('PG_PAYMENTTYPE_CARTEBLEUE', 'Carte Bleue');
define('PG_PAYMENTTYPE_DINERSCLUB', 'Diners Club');
define('PG_PAYMENTTYPE_DISCOVER', 'Discover');
define('PG_PAYMENTTYPE_JCB', 'JCB');
define('PG_PAYMENTTYPE_MAESTRO', 'Maestro');
define('PG_PAYMENTTYPE_LASTSCHRIFT', 'Direct debit');
define('PG_PAYMENTTYPE_INVOICE', 'Sale on account');
define('PG_PAYMENTTYPE_PREPAY', 'Cash in advance');
define('PG_PAYMENTTYPE_COD', 'Cash on delivery');
define('PG_PAYMENTTYPE_PAYPAL', 'PayPal');
define('PG_PAYMENTTYPE_BILLSAFE', 'BillSAFE');
define('PG_PAYMENTTYPE_COMMERZFINANZ', 'CommerzFinanz');
define('PG_TYPE_ACTIVE', 'active');
define('PG_CHECK_CAV', 'Check digit inquiry');
define('PG_PAYMENTTYPE_SOFORTUEBERWEISUNG', 'Sofort&uuml;berweisung (&Uuml;berweisung by SOFORT)');
define('PG_PAYMENTTYPE_GIROPAY', 'GiroPay');
define('PG_PAYMENTTYPE_EPS', 'EPS');
define('PG_PAYMENTTYPE_PFEFINANCE', 'Post-Finance EFinance');
define('PG_PAYMENTTYPE_PFCARD', 'Post-Finance Card');
define('PG_PAYMENTTYPE_IDEAL', 'iDEAL');
define('OVERRIDE_DATA', 'Local parameters');
define('ADD_PAYMENT_GENRE', 'Add payment method');
define('PAYGENRE_CREDITCARD', 'Credit cards');
define('PAYGENRE_ONLINETRANSFER', 'Online transaction');
define('PAYGENRE_EWALLET', 'e-Wallet');
define('PAYGENRE_ACCOUNTBASED', 'Bank account based payment methods');
define('PAYGENRE_INSTALLMENT', 'Hire purchase/Factoring');
 
// config
define('ACTIVE', 'active');
define('CONFIG_SAVE', 'Save configuration');
define('NO_PAYMENTGENRE_CONFIGURED', 'There is no payment method configured yet.');
define('ADDRESS_CHECK_CONFIGURATION', 'Address verification');
define('SELECT_ALL_COUNTRIES', 'activate all countries');
define('SELECT_NO_COUNTRY', 'deactivate all countries');
define('REMOVE_PAYMENT_GENRE', 'Remove payment method');
define('REMOVE_THIS_GENRE', 'Remove this payment method upon saving');
define('CONFIGURATION_SAVED', 'Configuration saved');
define('PAYMENTGENRE_ADDED', 'Payment method added');
define('PAYONE_ORDERS_HEADING', 'PAYONE payment');
define('TRANSACTIONS', 'Transactions');
define('TXID', 'Transactions-ID');
define('USERID', 'User-ID');
define('CREATED', 'created');
define('LAST_MODIFIED', 'last modification');
define('STATUS', 'Status');
define('TRANSACTION_STATUS', 'Transaction status');
define('NO_TRANSACTION_STATUS_RECEIVED', 'no transaction status received yet');
define('ERROR_OCCURRED', 'Error occurred');
define('ERROR_ADDRESSES_MUST_BE_EQUAL', 'With the payment method choosen billing and delivery address must correspond!');
define('TABLE_HEADING_CHECK', 'Choose');
define('DUMP_CONFIG', 'Export configuration');
define('CONFIGURATION_DUMPED_TO', 'Configuration saved to file');
define('ERROR_DUMPING_CONFIGURATION', 'There was an error during the export of the configuration.');
define('INSTALL_CONFIG', 'Install PAYONE');
 
// Capture
define('CAPTURE_TRANSACTION', 'Capture paymnt');
define('CAPTURE_AMOUNT', 'Amount');
define('CAPTURE_SUBMIT', 'Capture now');
define('AMOUNT_CAPTURED', 'Amount captured');
 
// Clearing
define('CLEARING_INTRO', 'Please transfer the billing amount to the following bank account:');
define('CLEARING_OUTRO', 'Your order will not be shipped until we receive your payment in our bank account.');
define('CLEARING_ACCOUNTHOLDER', 'Account holder: ');
define('CLEARING_ACCOUNT', 'Account number: ');
define('CLEARING_BANKCODE', 'Bank code: ');
define('CLEARING_IBAN', 'IBAN: ');
define('CLEARING_BIC', 'BIC: ');
define('CLEARING_BANK', 'Bank: ');
define('CLEARING_AMOUNT', 'Amount');
define('CLEARING_TEXT', 'Reference: ');
 
// Refund
define('REFUND_TRANSACTION', 'Credit advice');
define('REFUND_SUBMIT', 'Process credit advice');
define('REFUND_AMOUNT', 'Amount');
define('REFUND_BANKCOUNTRY', 'Country');
define('REFUND_COUNTRY_DE', 'Germany');
define('REFUND_COUNTRY_FR', 'France');
define('REFUND_COUNTRY_NL', 'Netherlands');
define('REFUND_COUNTRY_AT', 'Austria');
define('REFUND_COUNTRY_CH', 'Switzerland');
define('REFUND_BANKACCOUNT', 'Account number');
define('REFUND_BANKCODE', 'Bank code');
define('REFUND_BANKBRANCHCODE', 'Branch');
define('REFUND_BANKCHECKDIGIT', 'Check digit');
define('REFUND_IBAN', 'IBAN');
define('REFUND_BIC', 'BIC');
define('AMOUNT_REFUNDED', 'Amount credited');
 
// Log
define('PAYONE_LOGS_TITLE', 'PAYONE API Log');
define('EVENT_ID', 'Event-ID');
define('DATETIME', 'Point of time');
define('CUSTOMER', 'Customer (as long as known)');
define('START_DATE', 'Start');
define('END_DATE', 'End');
define('PAGE', 'Page');
define('SEARCH', 'Search');
define('SHOW', 'show');
define('EVENT_LOG_COUNT', 'Subevent-No.');
define('NO_LOGS', 'There are no entries for the chosen period.');
define('API', 'API');

?>