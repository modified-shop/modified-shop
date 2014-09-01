<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_general.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

define('MODULE_PAYMENT_SOFORT_MULTIPAY_JS_LIBS', '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="'.DIR_WS_CATALOG.'callback/sofort/ressources/javascript/sofortbox.js"></script>');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_STATUS_TITLE', 'Activate sofort.de module');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_STATUS_DESC', 'Activates/deactivates the complete module');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY_TITLE', 'configuration key');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY_DESC', 'Assigned configuration key by SOFORT AG');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH_TITLE', 'test configuration key');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH_DESC', '<noscript>Please activate Javascript</noscript><script src="../callback/sofort/ressources/javascript/testAuth.js"></script>');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE', 'Payment zone');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC', 'When a zone is selected, the payment method applies only to this zone.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1_TITLE', 'Reason 1');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1_DESC', 'For purpose 1 the following options can be selected');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2_TITLE', 'Reason 2');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2_DESC', 'Following placeholders will be replaced inside the reason (max 27 characters):<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_ERROR_HEADING', 'Following error occurred during the process:');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_ERROR_MESSAGE', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE_TITLE', 'Banner or text in the selection of payment methods');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE_DESC', 'Banner or text in the selection of payment methods');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID_TITLE', 'Order status for manual review');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID_DESC', 'Order status for orders with unusual payment performance such as invalid amounts, missing payment, debit note returns, etc. These orders require manual review.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_ORDER_CANCELED', 'The order was canceled.'); //Die Bestellung wurde abgebrochen.

define('MODULE_PAYMENT_SOFORT_STATUS_NOT_CREDITED_YET', 'Order with {{paymentMethodStr}} successfully submitted. Transaction-ID: {{tId}} {{time}}');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_CREDITED_TO_SELLER', 'Payment to the merchant account has been completed.');
define('MODULE_PAYMENT_SOFORT_STATUS_WAIT_FOR_MONEY', 'Waiting for payment. Transaction ID: {{tId}} {{time}}');

define('MODULE_PAYMENT_SOFORT_STATUS_PARTIALLY_CREDITED', '{{paymentMethodStr}} - Only a partial amount of the initially claimed amount was received. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_OVERPAYMENT', '{{paymentMethodStr}} - An higher amount than initially claimed was received. Amount: {{received_amount}}. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_SV_COMPENSATION', 'The invoice amount will be partially refunded. Total amount being refunded: {{refunded_amount}}. {{time}}');

define('MODULE_PAYMENT_SOFORT_STATUS_RECEIVED', '{{paymentMethodStr}} - Money received. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_DEFAULT', '{{paymentMethod}} {{status}} {{statusReason}} {{time}}');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_TRANSACTION_ID', 'transaction ID');

define('MODULE_PAYMENT_SOFORT_ERROR_ORDER_NOT_FOUND', 'Error: Order not found.\n');
define('MODULE_PAYMENT_SOFORT_SUCCESS_CALLBACK', 'Order status successfully updated.');
define('MODULE_PAYMENT_SOFORT_ERROR_UNEXPECTED_STATUS', 'Error: Unknown payment status.');
define('MODULE_PAYMENT_SOFORT_ERROR_TERMINATED', 'Script terminated.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_FORWARDING', 'Your request is being checked, please wait a moment and do not cancel.<br />The process can take up to 30 seconds.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER', 'Version number');

define('MODULE_PAYMENT_SOFORT_KEYTEST_SUCCESS', 'API-Key succesully validated');
define('MODULE_PAYMENT_SOFORT_KEYTEST_SUCCESS_DESC', 'Test OK on');
define('MODULE_PAYMENT_SOFORT_KEYTEST_ERROR', 'Could not validate API key!');
define('MODULE_PAYMENT_SOFORT_KEYTEST_ERROR_DESC', 'Note: API key error');
define('MODULE_PAYMENT_SOFORT_KEYTEST_DEFAULT', 'API key not tested yet');

define('MODULE_PAYMENT_SOFORT_REFRESH_INFO', 'If you have just confirmed, adjusted, canceled or credited this order, you may need to {{refresh}} this page to see all changes.');
define('MODULE_PAYMENT_SOFORT_REFRESH_PAGE', 'Click here to reload the page');

//definition of error-codes that can resolve by calling the SOFORT-API
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_0',		'An unknown error occured.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8002',		'Validation error occurred.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010',		'The data is incomplete oder incorrect. Please correct or contact the online merchant.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8011',		'Not in the range of valid values.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8012',		'Value must be positive.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8013',		'Only orders in Euro are suppported at the moment. Please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8015',		'The total amount is too large or too small, please correct this or contact the shop owner.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8017',		'Unknown characters.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8018',		'Maximum number of characters exceeded (max. 27).');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8019',		'The order cannot be completed due to an incorrect e-mail address. Please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8020',		'The order cannot be completed due to an incorrect telephone number. Please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8021',		'The county code ist not supported, please contact your merchant.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8022',		'The provided BIC is invalid.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8023',		'The order cannot be completed due to an incorrect BIC (Bank Identifier Code). Please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8024',		'The order cannot be completed due to an incorrect country code. The delivery / billing address must be in Germany. please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8029',		'We can only support German accounts. Please correct this or try an alternative method of payment.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8033',		'The total amount is too high. please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8034',		'The total amount is too low. Please correct this and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8041',		'Value for VAT is incorrect. Valid values: 0, 7,19.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8046',		'The validation of bank account and bank routing number failed.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8047',		'The maximum number of 255 characters has been exceeded.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8051',		'The query contained invalid cart positions. Please correct this or contact the shop owner.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8058',		'Please enter, at least, the account holder and try again.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8061',		'A transaction with the information you have submitted already exists.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8068',		'Buy on account is only available to private customers at the moment.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10001', 	'Please complete the fields account number, sort code and account holder in full.'); //LS: holder and bankdata missing
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10002',	'Please accept the privacy policy.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10003',	'Unfortunately, the chosen payment method can not be used for payment with items such as downloads or gift vouchers.');  //RBS and virtual content is not allowed
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10004',	'An unknown error occured.');  //order could not be saved in table sofort_orders
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10005',	'An unknown error occured.');  //saving of order (after successful payment-process) MAYBE failed, seller informed
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10006',	'An unknown error occured.');  //saving of order (after successful payment-process) REALLY failed, seller informed

//check for empty fields failed (code 8010 = 'must not be empty')
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.EMAIL_CUSTOMER',				'The e-mail address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.PHONE_CUSTOMER',				'The telephone number can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.FIRSTNAME',	'The firstname of the invoice address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.FIRSTNAME',	'The firstname of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.LASTNAME',	'The lastname of the invoice address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.LASTNAME',	'The lastname of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.STREET',		'Street and house number must be separated by a space.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.STREET',		'Street and house number of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.STREET_NUMBER',	'Street and house number of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.STREET_NUMBER',	'Street and house number of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.ZIPCODE',		'The zipcode of the invoice address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.ZIPCODE',	'The zipcode of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.CITY',		'The city name of the invoice address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.CITY',		'The city name of the shipping address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.COUNTRY_CODE',	'The country code of the invoice address can not be empty.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.COUNTRY_CODE',	'The country code of the shipping address can not be emtpy.');