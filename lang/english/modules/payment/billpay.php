<?php

/* Default Messages */
define('MODULE_PAYMENT_BILLPAY_TEXT_TITLE', 'BillPay - Invoice');
define('MODULE_PAYMENT_BILLPAY_TEXT_DESCRIPTION', 'BillPay - Invoice');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_MESSAGE', 'BillPay Error Message');
define('MODULE_PAYMENT_BILLPAY_TEXT_INFO', '<div style="margin-top:6px"><img src="https://www.billpay.de/wp-content/uploads/2011/04/LogoSmall_0.png" alt="BillPay Logo" title="BillPay Logo" /></div>');

define('MODULE_PAYMENT_BILLPAY_ALLOWED_TITLE' , 'Allowed countries');
define('MODULE_PAYMENT_BILLPAY_ALLOWED_DESC' , 'Enter countries (eg. AT, DE) allowed for use this payment method. If empty, all countries are allowed');

define('MODULE_PAYMENT_BILLPAY_LOGGING_TITLE' , 'Absolute path for log-file');
define('MODULE_PAYMENT_BILLPAY_LOGGING_DESC' , 'Payment module will write all communication with BillPay server to the file. If empty, it will use default path (/includes/external/billpay/log).');

define('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID_TITLE' , 'Merchant ID');
define('MODULE_PAYMENT_BILLPAY_GS_MERCHANT_ID_DESC' , 'You will receive this data from BillPay');

define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_TITLE' , 'Default order status');
define('MODULE_PAYMENT_BILLPAY_ORDER_STATUS_DESC' , 'All orders made with this payment method and approved by BillPay, will be created with this status. (default setting: "BillPay pending")');

define('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID_TITLE' , 'Portal ID');
define('MODULE_PAYMENT_BILLPAY_GS_PORTAL_ID_DESC' , 'You will receive this data from BillPay');

define('MODULE_PAYMENT_BILLPAY_GS_SECURE_TITLE' , 'API password');
define('MODULE_PAYMENT_BILLPAY_GS_SECURE_DESC' , 'You will receive this data from BillPay');

define('MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY_TITLE', 'Public API Key');
define('MODULE_PAYMENT_BILLPAY_GS_PUBLIC_API_KEY_DESC', 'You will receive this data from BillPay');

define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_TITLE' , 'Display order');
define('MODULE_PAYMENT_BILLPAY_SORT_ORDER_DESC' , 'Order of display. Smallest number are displayed first.');

define('MODULE_PAYMENT_BILLPAY_STATUS_TITLE' , 'Enabled');
define('MODULE_PAYMENT_BILLPAY_STATUS_DESC' , 'Do you want to enable this payment method?');

define('MODULE_PAYMENT_BILLPAY_GS_TESTMODE_TITLE' , 'Enable test-mode');
define('MODULE_PAYMENT_BILLPAY_GS_TESTMODE_DESC' , 'In test-mode, detailed error messages are displayed. It should be deactivated in production environment.');

define('MODULE_PAYMENT_BILLPAY_ZONE_TITLE' , 'Tax zone');
define('MODULE_PAYMENT_BILLPAY_ZONE_DESC' , '');

define('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE_TITLE' , 'API url base');
define('MODULE_PAYMENT_BILLPAY_GS_API_URL_BASE_DESC' , 'Data provided by BillPay. Warning: URLs for live and test systems are different.');

define('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE_TITLE' , 'Test-API url base');
define('MODULE_PAYMENT_BILLPAY_GS_TESTAPI_URL_BASE_DESC' , 'Data provided by BillPay. Warning: URLs for live and test systems are different.');

define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_TITLE' , 'Logging enabled');
define('MODULE_PAYMENT_BILLPAY_LOGGING_ENABLE_DESC' , 'If enabled, all communication will be written to a log-file.');

define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_TITLE', 'Minimum order value');
define('MODULE_PAYMENT_BILLPAY_MIN_AMOUNT_DESC', 'For orders below this value, this payment method will be hidden.');

define('MODULE_PAYMENT_BILLPAY_LOGPATH_TITLE', 'Logging path');
define('MODULE_PAYMENT_BILLPAY_LOGPATH_DESC', '');

define('MODULE_PAYMENT_BILLPAY_GS_HTTP_X_TITLE', 'X_FORWARDED_FOR allow');
define('MODULE_PAYMENT_BILLPAY_GS_HTTP_X_DESC', 'Activate this function if your shop is using Cloud System');

define('MODULE_PAYMENT_BILLPAY_HTTP_X_TITLE', 'X_FORWARDED_FOR allow');
define('MODULE_PAYMENT_BILLPAY_HTTP_X_DESC', 'Activate this function if your shop is using Cloud System');

// Payment selection texts
define('MODULE_PAYMENT_BILLPAY_TEXT_BIRTHDATE', 'Date of birth');
define('MODULE_PAYMENT_BILLPAY_TEXT_PHONE', 'Phone number');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK',    'I agree with the transfer of the data necessary for the processing of the purchase on account and an identity and credit check to the <a href="https://www.billpay.de/endkunden/" target="blank">BillPay GmbH</a>. The <a href="%s" target="_blank">data protection regulations</a> of BillPay apply.');
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_CH', '<label for="billpay_eula">Here I confirm the <a href="https://www.billpay.de/kunden/agb-ch" target="_blank">AGB</a> and the <a href="https://www.billpay.de/kunden/agb-ch#datenschutz" target="_blank">data protection regulations</a> of BillPay GmbH </label> <br />');

define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_SEPA', "I agree with the transfer of the data required for the processing of the payment and an identity and credit check to the <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a>. The <a href='%s' target='_blank'>data protection regulations</a> of BillPay.<br/><br/> I give BillPay a SEPA direct debit mandate (<a href='#' class='bpy-btn-details'>details</a>) for the collection of due payments and instruct my financial institution to collect the direct debits.");
define('MODULE_PAYMENT_BILLPAY_TEXT_EULA_CHECK_SEPA_AT', "I agree with the transfer of the data required for the processing of the payment and an identity and creditworthiness check to the <a href='https://www.billpay.de/endkunden/' target='_blank'>BillPay GmbH</a>. The <a href='%s' target='_blank'>data protection regulations</a> of BillPay.<br/><br/> I grant BillPay and the <a href='https://www.privatbank1891.com/' target='_blank'>net-m privatbank 1891 AG</a> a SEPA Direct Debit Mandate (<a href='#' class='bpy-btn-details'>details</a>) for the collection of outstanding payments and instruct my financial institution to collect the direct debits.");

define('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE_TITLE', 'Enable UTF8 encoding');
define('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE_DESC', 'Disable this option if you use UTF-8 encoding in your online store');

define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE', 'Please set your date of birth in the account page.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_GENDER', 'Please set your gender in the account page.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_TITLE', 'Please enter your title');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_BIRTHDATE_AND_GENDER', 'Please set your date of birth and gender in the account page.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ENTER_PHONE', 'Please enter your phone number.');
define('MODULE_PAYMENT_BILLPAY_TEXT_NOTE', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_REQ', '');
define('MODULE_PAYMENT_BILLPAY_TEXT_GENDER', 'Gender');
define('MODULE_PAYMENT_BILLPAY_TEXT_SALUTATION', 'Salutation');
define('MODULE_PAYMENT_BILLPAY_TEXT_MALE', 'male');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEMALE', 'female');
define('MODULE_PAYMENT_BILLPAY_TEXT_MR', 'Mr');
define('MODULE_PAYMENT_BILLPAY_TEXT_MRS', 'Mrs');

define('JS_BILLPAY_EULA', '* Please accept EULA to continue.\n\n');
define('JS_BILLPAY_DOBDAY', '* Please set your date of birth in the account page.\n\n');
define('JS_BILLPAY_DOBMONTH', JS_BILLPAY_DOBDAY);
define('JS_BILLPAY_DOBYEAR', JS_BILLPAY_DOBDAY);
define('JS_BILLPAY_GENDER', '* Please set your gender in the account page.\n\n');

define('JS_BILLPAY_CODE', '* Please provide bank account code.\n\n');
define('JS_BILLPAY_NUMBER', '* Please provide bank account number.\n\n');
define('JS_BILLPAY_NAME', '* Please provide name of the holder of selected bank account.\n\n');
define('JS_BILLPAY_PHONE', '* Please provide your telephone number.\n\n');

define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_NUMBER', '* Please correct bank account number.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_CODE', '* Please correct bank account code.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_NAME', '* Please correct the name of the holder of selected bank account.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_PHONE', '* Please provide your telephone number.');

define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_EULA', '* Please accept EULA to continue.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB', 'You have entered an incorrect date of birth!');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DOB_UNDER', 'You must be 18 years of age or older to use BillPay.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DEFAULT', 'Internal error, please pick different payment method.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_SHORT', 'Internal error, please pick different payment method.');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_CREATED_COMMENT', 'Invoice has been created.');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_COMMENT', 'The order has been canceled by BillPay.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_DUEDATE', 'Payment cannot be started because due date is empty.');
define('MODULE_PAYMENT_BILLPAY_TEXT_ERROR_NO_RATEPLAN', 'Bitte fordern Sie einen Ratenplan f&uuml;r die ausgew&auml;lte Anzahl Raten an.');

define('MODULE_PAYMENT_BILLPAY_TEXT_CREATE_INVOICE', 'Create BillPay invoice now?');
define('MODULE_PAYMENT_BILLPAY_TEXT_CANCEL_ORDER', 'Cancel BillPay payment now?');

define('MODULE_PAYMENT_BILLPAY_TEXT_ACCOUNT_HOLDER', 'Account holder');
define('MODULE_PAYMENT_BILLPAY_TEXT_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK_NAME', 'Bank');
define('MODULE_PAYMENT_BILLPAY_TEXT_BIC', 'BIC');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_REFERENCE', 'Invoice number');

define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO',              'Please transfer the total amount with BillPay transaction number (%1$s) within the payment deadline of %2$02s.%3$02s.%4$04s to the following account:');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_NO_DUEDATE',   'Please transfer the total amount with BillPay transaction number (%1$s) within the payment deadline written on invoice to the following account:');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO1', 'Sie haben sich f&uuml;r den Kauf auf Rechnung mit BillPay entschieden. Bitte &uuml;berweisen Sie den Gesamtbetrag bis zum ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO2', ' auf folgendes Konto: ');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO3', 'F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten');
define('MODULE_PAYMENT_BILLPAY_TEXT_INVOICE_INFO_MAIL', '<br/>Bitte &uuml;berweisen Sie den Gesamtbetrag unter Angabe der BillPay-Transaktionsnummer im Verwendungszweck (%s) bis zum F&auml;lligkeitsdatum, das Sie mit der Rechnung erhalten, auf das folgende Konto:');

define('MODULE_PAYMENT_BILLPAY_TEXT_BANKDATA', 'Please provide your bank account details.');

define('MODULE_PAYMENT_BILLPAY_DUEDATE_TITLE', 'Due date');

define('MODULE_PAYMENT_BILLPAY_TEXT_PURPOSE', 'Usage');

define('MODULE_PAYMENT_BILLPAY_TEXT_ADD', 'plus');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE', 'Fee');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE_INFO1', 'F&uuml;r diese Bestellung per Rechnung wird eine Geb&uuml;hr von ');
define('MODULE_PAYMENT_BILLPAY_TEXT_FEE_INFO2', ' erhoben');

define('MODULE_PAYMENT_BILLPAY_TEXT_SANDBOX', 'You are in a sandbox mode:');
define('MODULE_PAYMENT_BILLPAY_TEXT_CHECK', 'You are in the acceptance mode:');
define('MODULE_PAYMENT_BILLPAY_UNLOCK_INFO', 'Information from a live server');

define('MODULE_PAYMENT_BILLPAY_B2BCONFIG_TITLE', 'Type of the customers');
define('MODULE_PAYMENT_BILLPAY_B2BCONFIG_DESC', 'Do you want to offer payment method for private customers (B2C), business (B2B) or both (BOTH)?');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_NAME_TEXT', 'Company name');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_TEXT', 'Legal form');
define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_LEGAL_FORM_SELECT_HTML', "");
define('MODULE_PAYMENT_BILLPAY_B2B_LEGALFORM_VALUES', 'ag:AG (Aktiengesellschaft)|eg:eG (eingetragene Genossenschaft)|ek:EK (eingetragener Kaufmann)|ev:e.V. (eingetragener Verein)|freelancer:Freiberufler/Kleingewerbetreibender/Handelsvertreter|gbr:GbR/BGB (Gesellschaft b&uuml;rgerlichen Rechts)|gmbh:GmbH (Gesellschaft mit beschr&auml;nkter Haftung)|gmbh_ig:GmbH in Gr&uuml;ndung|gmbh_co_kg:GmbH &amp; Co. KG|kg:KG (Kommanditgesellschaft)|ltd:Limited|ltd_co_kg:Limited &amp; Co. KG|ohg:OHG (offene Handelsgesellschaft)|public_inst:&Ouml;ffentliche Einrichtung|misc_capital:Sonstige Kapitalgesellschaft|misc:Sonstige Personengesellschaft|foundation:Stiftung|ug:UG (Unternehmensgesellschaft haftungsbeschr&auml;nkt)');
define('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_TEXT', 'Register number');
define('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_TEXT', 'Tax-ID');
define('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_TEXT', 'Holder name');
define('MODULE_PAYMENT_BILLPAY_B2B_CONTACT_PERSON_TEXT', 'Contact person');

define('MODULE_PAYMENT_BILLPAY_B2B_CHOOSE_CLIENT_TEXT', 'Client type');
define('MODULE_PAYMENT_BILLPAY_B2B_PRIVATE_CLIENT_TEXT', 'Private client');
define('MODULE_PAYMENT_BILLPAY_B2B_BUSINESS_CLIENT_TEXT', 'Business client');

define('MODULE_PAYMENT_BILLPAY_B2B_COMPANY_FIELD_EMPTY', 'Please enter company name');
define('MODULE_PAYMENT_BILLPAY_B2B_LEGAL_FORM_FIELD_EMPTY', 'Please provide legal form');
define('MODULE_PAYMENT_BILLPAY_B2B_HOLDER_NAME_EMPTY', 'Please provide holder\'s name');
define('MODULE_PAYMENT_BILLPAY_B2B_REGISTER_NUMBER_EMPTY', 'Please provide register number');
define('MODULE_PAYMENT_BILLPAY_B2B_TAX_NUMBER_EMPTY', 'Please provide Tax-ID');


defined('MODULE_ORDER_TOTAL_BILLPAY_FEE_FROM_TOTAL') OR define('MODULE_ORDER_TOTAL_BILLPAY_FEE_FROM_TOTAL', 'of the invoice amount');

define('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE_TITLE', 'Locale encoding');
define('MODULE_PAYMENT_BILLPAY_GS_UTF8_ENCODE_DESC', 'Does your site uses locale encoding (other than utf-8)?');


define('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER', 'The order has not been activated by BillPay. Please activate the order immediately prior to dispatch in which you set the appropriate status.');
define('MODULE_PAYMENT_BILLPAY_ACTIVATE_ORDER_WARNING', '<strong style="color:red">Warning: The payment has not yet been started by BillPay!</strong><br/>');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDRESS', 'This address is not allowed on orders with BillPay.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRODUCT', 'This product is not allowed on orders with BillPay.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PAYMENT', 'This payment is not allowed on orders with BillPay.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CURRENCY', 'This currency is not allowed on orders with BillPay.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_HIGHER_QUANTITY', 'You cannot order more products than in original order with BillPay');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_QUANTITY', 'You cannot order a negative quantity of products with BillPay.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_TAX', 'Adjusting the tax rate for order is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_PRICE', 'Adjusting the price for order is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ID', 'Adjusting the product id for order is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ZERO_REDUCTION', 'Zero reduction for order is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_REDUCTION', 'Negative reduction for order is not allowed.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_NEGATIVE_SHIPPING', 'Negative shipping for order is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_INCREASED_SHIPPING', 'Increased shipping for order is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADDED_SHIPPING', 'Added shipping for order is not allowed.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_FORBIDDEN', 'This action is not allowed on orders with BillPay.');
define('MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_NOT_PROCESSED', 'Attention! The adaptation of orders without tax items are not automatically sent to BillPay due to an error in the shop software. Please adjustment the amount manually in BillPay Back Office (https://admin.billpay.de)!');
define('MODULE_PAYMENT_BILLPAY_PARTIAL_CANCEL_ERROR_CUSTOMER_CARE', 'The adaptation of order with BillPay has failed. Please immediately contact our customer service (haendler@billpay.de)!');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADJUST_CHARGEABLE', 'Customizing a paid product option is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_ADD_CHARGEABLE', 'Customizing a paid product option is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_REMOVE_CHARGEABLE', 'Customizing a paid product option is not allowed.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_GENERAL', 'You cannot do this for BillPay payment method.');

define('MODULE_PAYMENT_BILLPAY_HISTORY_ERROR_CONTACT_BILLPAY', 'An error has occurred. Please contact BillPay customer service (haendler@billpay.de).');

define('MODULE_PAYMENT_BILLPAY_HISTORY_INFO_PARTIAL_CANCEL', 'Partial cancellation successfully sent to BillPay.');
define('MODULE_PAYMENT_BILLPAY_HISTORY_INFO_EDIT_CART_CONTENT', 'Edit cart content successfully sent to BillPay.');

define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_TEST' , 'Test-mode');
define('MODULE_PAYMENT_BILLPAY_TRANSACTION_MODE_LIVE' , 'Live-mode');

// -- Order States
// waiting for prepayment or decision
define('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_TITLE_EN' , 'BillPay pending');
define('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_TITLE_DE' , 'BillPay nicht abgeschlossen');

// ready to activate
define('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_TITLE_EN' , 'BillPay approved');
define('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_TITLE_DE' , 'BillPay genehmigt');

// invoice created
define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE_EN' , 'BillPay activated');
define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_TITLE_DE' , 'BillPay aktiviert');

// order cancelled or timed out from pending
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE_EN' , 'BillPay cancelled');
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_TITLE_DE' , 'BillPay storniert');

// error in order
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE_EN' , 'BillPay error!');
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_TITLE_DE' , 'BillPay Fehler!');
// -- end of Order States


define('MODULE_PAYMENT_BILLPAY_STATUS_PENDING_DESC', 'BillPay - waiting for approvement');
define('MODULE_PAYMENT_BILLPAY_STATUS_APPROVED_DESC', 'BillPay - approved');
define('MODULE_PAYMENT_BILLPAY_STATUS_ACTIVATED_DESC', 'BillPay - activated');
define('MODULE_PAYMENT_BILLPAY_STATUS_CANCELLED_DESC', 'BillPay - cancelled');
define('MODULE_PAYMENT_BILLPAY_STATUS_ERROR_DESC', 'BillPay - Due to an error, this order requires a manual correction. Please contact BillPay\'s support');


define('MODULE_PAYMENT_BILLPAY_SALUTATION_MALE', MODULE_PAYMENT_BILLPAY_TEXT_MR);
define('MODULE_PAYMENT_BILLPAY_SALUTATION_FEMALE', MODULE_PAYMENT_BILLPAY_TEXT_MRS);

define('MODULE_PAYMENT_BILLPAY_TEXT_SEPA_INFORMATION', 'The BillPay creditor identification number is DE19ZZ00000237180. The mandate reference number will be communicated to me by email at a later date. Note: I can request reimbursement of the amount debited within eight weeks from the date of debiting. The conditions agreed with my financial institution apply. Please note that the claim due remains valid even in the case of a return debit note. You can find further information on <a href="https://www.billpay.de/sepa" target="_blank">https://www.billpay.de/sepa</a>.');
define('MODULE_PAYMENT_BILLPAY_TEXT_SEPA_INFORMATION_AT', "The creditor identification number of BillPay is DE19ZZZ00000237180, the creditor identification number of net-m privatbank AG is DE62ZZZ00000009232. The mandate reference number will be communicated to me at a later date by email together with a template for a written mandate. I will also sign this written mandate and send it to BillPay. Note: I may request a refund of the amount debited within eight weeks from the date of debiting. The conditions agreed with my financial institution apply. Please note that the claim due remains valid even in the case of a return debit note. You can find further information on <a href='https://www.billpay.de/sepa' target='_blank'>https://www.billpay.de/sepa</a>.");

// Plugin 1.7
define('MODULE_PAYMENT_BILLPAY_THANK_YOU_TEXT', 'Thank you for choosing BillPay Invoice when making your purchase.');
define('MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT', 'Please transfer %1$s %2$s by %3$s, stating the reference, to the following account:');
define('MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_NO_DUE_DATE', 'Bitte &uuml;berweisen Sie den Betrag von %1$s %2$s innerhalb der Zahlungsfrist unter Angabe des Verwendungszwecks auf folgendes Konto.');
define('MODULE_PAYMENT_BILLPAY_PAY_UNTIL_TEXT_ADD_CH', 'Payments at the Post Office counter will incur additional charges. When making a payment via deposit slip, please transfer an additional %1$s %2$s.');
define('MODULE_PAYMENT_BILLPAY_TEXT_PAYEE', 'Payment recipient');
define('MODULE_PAYMENT_BILLPAY_TEXT_PAYEE_CH', 'Zweigniederlassung Schweiz (Regensdorf)');
define('MODULE_PAYMENT_BILLPAY_TEXT_IBAN_CH', 'Account number');
define('MODULE_PAYMENT_BILLPAY_TEXT_BIC_CH', 'BC number');
define('MODULE_PAYMENT_BILLPAY_TEXT_BANK', 'Bank');
define('MODULE_PAYMENT_BILLPAY_TEXT_TOTAL_AMOUNT', 'Amount');
define('MODULE_PAYMENT_BILLPAY_UPDATE_AVAILABLE', 'Version %2$s of the BillPay Payment Plugin is available (currently installed: %1$s). Click <a href="%3$s">here</a> to download.');