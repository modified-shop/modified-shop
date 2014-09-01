<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_2hp.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_2hp.php
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

define('MODULE_PAYMENT_BILLSAFE_2HP_TEXT_TITLE', 'Hire purchase');
define('MODULE_PAYMENT_BILLSAFE_2HP_CHECKOUT_TEXT_INFO', 'Pay your order the convinient way in %d rates from %s Euro per month. <br />(actual annual interest: %s%%)');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_COMMON', 'Sorry, hire purchase is not possible. Please select another payment method.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_101', 'Hire purchase is not possible right now, please select another payment method.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_102', 'An error occured during data processing. Please contact us.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_215', 'There were missing parameters during data processing. Please contact us.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_216', 'There were invalid parameters during data processing. Please contact us.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_COMPANY', 'Hire purchase is unfortunately only possible for private individuals.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_ADDRESS', 'Hire purchase is unfortunately not possible with an alternate delivery address.');
define('MODULE_PAYMENT_BILLSAFE_2HP_STATUS_TEXT', 'Status');
define('MODULE_PAYMENT_BILLSAFE_2HP_TRANSACTIONID', 'BillSAFE transaction ID');
define('MODULE_PAYMENT_BILLSAFE_2HP_CODE_TEXT', 'Code');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_TEXT', 'Message');
define('MODULE_PAYMENT_BILLSAFE_2HP_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://www.billsafe.de" target="_blank" style="text-decoration: underline; font-weight: bold;" />Visit BillSAFE website</a>');
define('MODULE_PAYMENT_BILLSAFE_2HP_STATUS_TITLE', 'Enable BillSAFE Hire Purchase Module');
define('MODULE_PAYMENT_BILLSAFE_2HP_STATUS_DESC', 'Do you want to accept hire purchase with BillSAFE?');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_ID_TITLE', 'Merchant ID');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_ID_DESC', 'The Merchant ID to use for the BillSAFE API service');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_LICENSE_TITLE', 'Merchant License');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_LICENSE_DESC', 'The Merchant License to use for the BillSAFE API service');
define('MODULE_PAYMENT_BILLSAFE_2HP_MIN_ORDER_TITLE', 'Minimum order value');
define('MODULE_PAYMENT_BILLSAFE_2HP_MIN_ORDER_DESC', 'BillSAFE minimum order value');
define('MODULE_PAYMENT_BILLSAFE_2HP_MAX_ORDER_TITLE', 'Maximim order value');
define('MODULE_PAYMENT_BILLSAFE_2HP_MAX_ORDER_DESC', 'BillSAFE maximum order value');
define('MODULE_PAYMENT_BILLSAFE_2HP_BILLSAFE_LOGO_URL_DESC', 'BillSAFE Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2HP_BILLSAFE_LOGO_URL_TITLE', 'BillSAFE Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2HP_SHOP_LOGO_URL_DESC', 'Shop Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2HP_SHOP_LOGO_URL_TITLE', 'Shop Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2HP_SERVER_TITLE', 'BillSAFE Server');
define('MODULE_PAYMENT_BILLSAFE_2HP_SERVER_DESC', 'Use the live or testing (sandbox) gateway server to process invoices?');
define('MODULE_PAYMENT_BILLSAFE_2HP_ZONE_TITLE', 'Payment Zone');
define('MODULE_PAYMENT_BILLSAFE_2HP_ZONE_DESC', 'If a zone is selected, only enable this payment method for that zone.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ORDER_STATUS_ID_TITLE', 'Set Order Status');
define('MODULE_PAYMENT_BILLSAFE_2HP_ORDER_STATUS_ID_DESC', 'Set the status of orders made with this payment module to this value.');
define('MODULE_PAYMENT_BILLSAFE_2HP_SORT_ORDER_TITLE', 'Sort order of display.');
define('MODULE_PAYMENT_BILLSAFE_2HP_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ALLOWED_TITLE', 'Allowed Zones');
define('MODULE_PAYMENT_BILLSAFE_2HP_ALLOWED_DESC', 'Please enter the zones separately which should be allowed to use this modul (e. g. DE,AT (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_FSHIPMENT', 'Full shippment was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PSHIPMENT', 'Partial shippment was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_FSTORNO', 'Full cancellation was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PSTORNO', 'Partial cancellation was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_FRETOURE', 'Full returns was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PRETOURE', 'Partial returns was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_VOUCHER', 'Credit was successful');
define('MODULE_PAYMENT_BILLSAFE_2HP_DETAILS', 'BillSAFE details');
define('MODULE_PAYMENT_BILLSAFE_2HP_BADDRESS', 'Billing address (BillSAFE)');
define('MODULE_PAYMENT_BILLSAFE_2HP_SADDRESS', 'Shipping address');
define('MODULE_PAYMENT_BILLSAFE_2HP_EMAIL', 'E-mail');
define('MODULE_PAYMENT_BILLSAFE_2HP_PDETAILS', 'Hire purchase details');
define('MODULE_PAYMENT_BILLSAFE_2HP_NOTE', 'Note');
define('MODULE_PAYMENT_BILLSAFE_2HP_PRODUCTS', 'Products');
define('MODULE_PAYMENT_BILLSAFE_2HP_MODEL', 'Model');
define('MODULE_PAYMENT_BILLSAFE_2HP_TAX', 'VAT');
define('MODULE_PAYMENT_BILLSAFE_2HP_PRICE_EX', 'Price (excl.)');
define('MODULE_PAYMENT_BILLSAFE_2HP_PRICE_INC', 'Price (incl.)');
define('MODULE_PAYMENT_BILLSAFE_2HP_CHECK', 'Choose');
define('MODULE_PAYMENT_BILLSAFE_2HP_INC', 'incl. ');
define('MODULE_PAYMENT_BILLSAFE_2HP_FREPORT_SHIPMENT', 'Report full shipment');
define('MODULE_PAYMENT_BILLSAFE_2HP_PREPORT_SHIPMENT', 'Report partial shipment');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTSTORNOFULL', 'Full cancellation');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTSTORNOPART', 'Partial cancellation');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTRETOUREFULL', 'Full returns');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTRETOUREPART', 'Partial returns');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTVOUCHER', 'Credit');
define('MODULE_PAYMENT_BILLSAFE_2HP_PREPORT_METHOD', 'Method');
define('MODULE_PAYMENT_BILLSAFE_2HP_PREPORT_DATE', 'Date');
define('MODULE_PAYMENT_BILLSAFE_2HP_JALERT', 'Please select at least one product for partial shipment.');
define('MODULE_PAYMENT_BILLSAFE_2HP_NO_ORDERID', 'Order ID not found.');
define('MODULE_PAYMENT_BILLSAFE_2HP_VAT', '% VAT');
define('MODULE_PAYMENT_BILLSAFE_2HP_VALUE', 'Subtotal');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_TITLE', 'Activate logging');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_DESC', 'Use BillSAFE server replies for troubleshooting.');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_TYPE_TITLE', 'Logging type: Echo, send log by E-mail or save as file in path "/export".');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_TYPE_DESC', '<b>Note</b>: "Echo" for testing purposes in admin area only. <b>No ordering possible!</b>');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_ADDR_TITLE', 'E-mail address(es) where to send the log');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_ADDR_DESC', 'For more than one E-mail address separate by ",".');
define('MODULE_PAYMENT_BILLSAFE_2HP_MP', 'Merchant site');
define('MODULE_PAYMENT_BILLSAFE_2HP_BUTTON', 'Go to BillSAFE');
define('MODULE_PAYMENT_BILLSAFE_2HP_LAYER_TITLE', 'Payment Layer');
define('MODULE_PAYMENT_BILLSAFE_2HP_LAYER_DESC', 'Would you like to enable the layer mode for payments via BillSAFE? <b>Note: It is absolutely necessary to disable <i>Force Cookie Use</i> in the <i>Sessions</i> settings!</b>');
?>