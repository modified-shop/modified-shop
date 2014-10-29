<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_2.php
* location = /lang/english/modules/payment
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 Bernd Blazynski
* @license GPLv2
*/

define('MODULE_PAYMENT_BILLSAFE_2_TEXT_TITLE', 'Purchase on account');
define('MODULE_PAYMENT_BILLSAFE_2_CHECKOUT_TEXT_INFO', 'Pay your order the convinient way via invoice. Easily and efficiently with BillSAFE, a service of PayPal.');
define('MODULE_PAYMENT_BILLSAFE_2_SCHG_TEXT_INFO', 'For this payment method we raise a surcharge of: ');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMMON', 'Sorry, the payment via BillSAFE is not possible. Please select another payment method.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_101', 'Payment via BillSAFE is not possible for this transaction, please select another payment method.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_102', 'An error occured during data processing. Please contact us.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_215', 'There were missing parameters during data processing. Please contact us.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_216', 'There were invalid parameters during data processing. Please contact us.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_INLINE', 'You have not entered all necessary information. Please check your information for the payment method!');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMPANY', 'Payment via BillSAFE is unfortunately only possible for private individuals.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_ADDRESS', 'Payment via BillSAFE is unfortunately not possible with an alternate delivery address.');
define('MODULE_PAYMENT_BILLSAFE_2_STATUS_TEXT', 'Status');
define('MODULE_PAYMENT_BILLSAFE_2_TRANSACTIONID', 'BillSAFE transaction ID');
define('MODULE_PAYMENT_BILLSAFE_2_CODE_TEXT', 'Code');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_TEXT', 'Message');
define('MODULE_PAYMENT_BILLSAFE_2_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.billsafe.de" target="_blank" style="text-decoration: underline; font-weight: bold;" />Visit BillSAFE website</a>');
define('MODULE_PAYMENT_BILLSAFE_2_STATUS_TITLE', 'Enable BillSAFE Module');
define('MODULE_PAYMENT_BILLSAFE_2_STATUS_DESC', 'Do you want to accept BillSAFE invoices?');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID_TITLE', 'Merchant ID');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID_DESC', 'The Merchant ID to use for the BillSAFE API service');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE_TITLE', 'Merchant License');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE_DESC', 'The Merchant License to use for the BillSAFE API service');
define('MODULE_PAYMENT_BILLSAFE_2_PKEY_TITLE', 'Public Key');
define('MODULE_PAYMENT_BILLSAFE_2_PKEY_DESC', 'Security code provided by BillSAFE. You will find your code at your personal BillSAFE website.');
define('MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER_TITLE', 'Minimum order value');
define('MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER_DESC', 'BillSAFE minimum order value');
define('MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER_TITLE', 'Maximim order value');
define('MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER_DESC', 'BillSAFE maximum order value');
define('MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL_DESC', 'BillSAFE Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL_TITLE', 'BillSAFE Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL_DESC', 'Shop Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL_TITLE', 'Shop Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2_SERVER_TITLE', 'BillSAFE Server');
define('MODULE_PAYMENT_BILLSAFE_2_SERVER_DESC', 'Use the live or testing (sandbox) gateway server to process invoices?');
define('MODULE_PAYMENT_BILLSAFE_2_ZONE_TITLE', 'Payment Zone');
define('MODULE_PAYMENT_BILLSAFE_2_ZONE_DESC', 'If a zone is selected, only enable this payment method for that zone.');
define('MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID_TITLE', 'Set Order Status');
define('MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID_DESC', 'Set the status of orders made with this payment module to this value.');
define('MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER_TITLE', 'Sort order of display.');
define('MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_BILLSAFE_2_ALLOWED_TITLE', 'Allowed Zones');
define('MODULE_PAYMENT_BILLSAFE_2_ALLOWED_DESC', 'Please enter the zones separately which should be allowed to use this modul (e. g. DE,AT (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FSHIPMENT', 'Full shippment was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PSHIPMENT', 'Partial shippment was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FSTORNO', 'Full cancellation was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PSTORNO', 'Partial cancellation was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FRETOURE', 'Full returns was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PRETOURE', 'Partial returns was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_VOUCHER', 'Credit was successful');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PAUSETRANSACTION', 'Pause transaction was successful');
define('MODULE_PAYMENT_BILLSAFE_2_DETAILS', 'BillSAFE details');
define('MODULE_PAYMENT_BILLSAFE_2_BADDRESS', 'Billing address (BillSAFE)');
define('MODULE_PAYMENT_BILLSAFE_2_SADDRESS', 'Shipping address');
define('MODULE_PAYMENT_BILLSAFE_2_EMAIL', 'E-mail');
define('MODULE_PAYMENT_BILLSAFE_2_BANK_DETAILS', 'Bank details');
define('MODULE_PAYMENT_BILLSAFE_2_BANK_CODE', 'Bank code');
define('MODULE_PAYMENT_BILLSAFE_2_BANK_NAME', 'Bank name');
define('MODULE_PAYMENT_BILLSAFE_2_ACCOUNT_NUMBER', 'Account no.');
define('MODULE_PAYMENT_BILLSAFE_2_RECIPIENT', 'Recipient');
define('MODULE_PAYMENT_BILLSAFE_2_BIC', 'BIC');
define('MODULE_PAYMENT_BILLSAFE_2_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLSAFE_2_REFERENCE', 'Reference');
define('MODULE_PAYMENT_BILLSAFE_2_REFERENCE2', 'Reference 2');
define('MODULE_PAYMENT_BILLSAFE_2_NOTE', 'Note');
define('MODULE_PAYMENT_BILLSAFE_2_AMOUNT', 'Payable amount');
define('MODULE_PAYMENT_BILLSAFE_2_PRODUCTS', 'Products');
define('MODULE_PAYMENT_BILLSAFE_2_MODEL', 'Model');
define('MODULE_PAYMENT_BILLSAFE_2_TAX', 'VAT');
define('MODULE_PAYMENT_BILLSAFE_2_PRICE_EX', 'Price (excl.)');
define('MODULE_PAYMENT_BILLSAFE_2_PRICE_INC', 'Price (incl.)');
define('MODULE_PAYMENT_BILLSAFE_2_CHECK', 'Choose');
define('MODULE_PAYMENT_BILLSAFE_2_INC', 'incl. ');
define('MODULE_PAYMENT_BILLSAFE_2_TOTAL', 'Total');
define('MODULE_PAYMENT_BILLSAFE_2_FREPORT_SHIPMENT', 'Report full shipment');
define('MODULE_PAYMENT_BILLSAFE_2_PREPORT_SHIPMENT', 'Report partial shipment');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOFULL', 'Full cancellation');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOPART', 'Partial cancellation');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREFULL', 'Full returns');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREPART', 'Partial returns');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTVOUCHER', 'Credit');
define('MODULE_PAYMENT_BILLSAFE_2_PREPORT_METHOD', 'Method');
define('MODULE_PAYMENT_BILLSAFE_2_PREPORT_DATE', 'Date');
define('MODULE_PAYMENT_BILLSAFE_2_JALERT', 'Please select at least one product for partial shipment.');
define('MODULE_PAYMENT_BILLSAFE_2_NO_ORDERID', 'Order ID not found.');
define('MODULE_PAYMENT_BILLSAFE_2_VAT', '% VAT');
define('MODULE_PAYMENT_BILLSAFE_2_VALUE', 'Subtotal');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_TITLE', 'Activate logging');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_DESC', 'Use BillSAFE server replies for troubleshooting.');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE_TITLE', 'Logging type: Echo, send log by E-mail or save as file in path "/export".');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE_DESC', '<b>Note</b>: "Echo" for testing purposes in admin area only. <b>No ordering possible!</b>');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR_TITLE', 'E-mail address(es) where to send the log');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR_DESC', 'For more than one E-mail address separate by ",".');
define('MODULE_PAYMENT_BILLSAFE_2_PAUSETRANSACTION', 'pause Transaction');
define('MODULE_PAYMENT_BILLSAFE_2_PAUSEDAYS', 'days');
define('MODULE_PAYMENT_BILLSAFE_2_SCHG_TITLE', 'Payment surcharge');
define('MODULE_PAYMENT_BILLSAFE_2_SCHG_DESC', 'Surcharge for payment via BillSAFE. Leave empty for none, fix surcharge amount as net, percentage surcharge with "%" (e. g. 3%). <b>Note: The fee must be agreed with BillSAFE and must not exceed the agreed value!</b>');
define('MODULE_PAYMENT_BILLSAFE_2_SCHGTAX_TITLE', 'Tax rate for payment surcharge');
define('MODULE_PAYMENT_BILLSAFE_2_SCHGTAX_DESC', 'Choose the desired tax rate');
define('MODULE_PAYMENT_BILLSAFE_2_MP', 'Merchant site');
define('MODULE_PAYMENT_BILLSAFE_2_BUTTON', 'Go to BillSAFE');
define('MODULE_PAYMENT_BILLSAFE_2_DPAYMENT', 'Direct payment by customer');
define('MODULE_PAYMENT_BILLSAFE_2_REPORT_DPAYMENT', 'Transmit direct payment now');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_DPAYMENT', 'Transmitting direct payment was successful');
define('MODULE_PAYMENT_BILLSAFE_2_DAY', 'Day');
define('MODULE_PAYMENT_BILLSAFE_2_MONTH', 'Month');
define('MODULE_PAYMENT_BILLSAFE_2_YEAR', 'Year');
define('MODULE_PAYMENT_BILLSAFE_2_LAYER_TITLE', 'Payment Layer');
define('MODULE_PAYMENT_BILLSAFE_2_LAYER_DESC', 'Would you like to enable the layer mode for payments via BillSAFE? <b>Note: Does not function with Onsite Checkout activated! It is absolutely necessary to disable <i>Force Cookie Use</i> in the <i>Sessions</i> settings!</b>');
define('MODULE_PAYMENT_BILLSAFE_2_INLINE_TITLE', 'Onsite Checkout');
define('MODULE_PAYMENT_BILLSAFE_2_INLINE_DESC', 'Would you like to enable Onsite Checkout for payments via BillSAFE? <b>Note: Does not function with Payment Layer activated! This function must be agreed with BillSAFE.</b>');
define('MODULE_PAYMENT_BILLSAFE_2_DOB', 'Please enter your birthdate (e.g. 21.05.1970):');
define('MODULE_PAYMENT_BILLSAFE_2_TC', '<b>I agree to the <a class="info" href="https://www.billsafe.de/privacy-policy/buyer" target="_blank">privacy policy</a> and the <a class="info" href="https://www.billsafe.de/privacy-policy/credit-check" target="_blank">credit investigation</a> of <a class="info" href="https://www.billsafe.de/imprint" target="_blank">PayPal</a>. The <a class="info" href="https://www.billsafe.de/resources/docs/pdf/Kaeufer_AGB.pdf" target="_blank">general terms and conditions</a> for invoice purchase apply.</b>');
?>