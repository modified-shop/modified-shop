<?php
/* -----------------------------------------------------------------------------------------
   $Id: paypal.php 998 2005-07-07 14:18:20Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(paypal.php,v 1.7 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (paypal.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_TITLE', 'PayPal');
define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_DESCRIPTION', 'PayPal IPN');
//define('MODULE_PAYMENT_PAYPAL_IPN_LOGO','<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x50.gif" />');
define('MODULE_PAYMENT_PAYPAL_IPN_LOGO','<img src="https://www.paypal.com/de_DE/DE/i/logo/lockbox_150x47.gif" align="middle" />');
//define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_INFO','Pay easily and safely with PayPal ' . MODULE_PAYMENT_PAYPAL_IPN_LOGO);
define('MODULE_PAYMENT_PAYPAL_IPN_ALLOWED_TITLE' , 'Allowed zones');
define('MODULE_PAYMENT_PAYPAL_IPN_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_PAYPAL_IPN_STATUS_TITLE' , 'Enable PayPal module');
define('MODULE_PAYMENT_PAYPAL_IPN_STATUS_DESC' , 'Do you want to accept PayPal payments?');
define('MODULE_PAYMENT_PAYPAL_IPN_ID_TITLE' , 'E-Mail Address');
define('MODULE_PAYMENT_PAYPAL_IPN_ID_DESC' , 'The E-Mail address to be used for the PayPal service');
define('MODULE_PAYMENT_PAYPAL_IPN_CURRENCY_TITLE' , 'Transaction currency');
define('MODULE_PAYMENT_PAYPAL_IPN_CURRENCY_DESC' , 'The currency to be used for credit card transactions');
define('MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER_TITLE' , 'Order of displayed data');
define('MODULE_PAYMENT_PAYPAL_IPN_SORT_ORDER_DESC' , 'Order in which data are sorted and displayed. Lowest is displayed first.');
define('MODULE_PAYMENT_PAYPAL_IPN_ZONE_TITLE' , 'Payment zone');
define('MODULE_PAYMENT_PAYPAL_IPN_ZONE_DESC' , 'If a zone is selected, this payment method is enabled only for that zone.');
define('MODULE_PAYMENT_PAYPAL_IPN_ORDER_STATUS_ID_TITLE' , 'Order status for successful payments');
define('MODULE_PAYMENT_PAYPAL_IPN_ORDER_STATUS_ID_DESC' , 'Sets the status of orders made with this module to that value');
define('MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID_TITLE','Order status for open payments'); 
define('MODULE_PAYMENT_PAYPAL_IPN_TMP_STATUS_ID_DESC','Sets the status of orders made with this module to that value');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_CURL_TITLE', 'cURL');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_CURL_DESC', 'Use cURL or redirection.');

define('MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT_TITLE', 'PayPal link order:');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_CHECKOUT_DESC', 'Display at the end of ordering process');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_EMAIL_TITLE', 'PayPal link E-Mail:');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_EMAIL_DESC', 'Insert link into order confirmation E-Mail');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT_TITLE', 'PayPal link customer account:');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_ACCOUNT_DESC', 'Display in order section of customer account');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX_TITLE', 'Test mode (Sandbox)');
define('MODULE_PAYMENT_PAYPAL_IPN_USE_SANDBOX_DESC', 'Only for testing purposes for developers');
define('MODULE_PAYMENT_PAYPAL_IPN_SBID_TITLE', 'E-Mail address for testing purposes (Sandbox)');
define('MODULE_PAYMENT_PAYPAL_IPN_SBID_DESC', 'E-Mail address to be used for testing purposes');

//Paypal Seitengestaltung
define('MODULE_PAYMENT_PAYPAL_IPN_IMAGE_TITLE','PayPal shop logo');
define('MODULE_PAYMENT_PAYPAL_IPN_IMAGE_DESC','Logo file to be displayed at PayPal.</ br>Note: will be transferred ONLY when SSL is being used.</ br>Maximum values for logo file: 750px width and 90px height sein.</ br>File is being called from: '.DIR_WS_CATALOG.'lang/LANGUAGE/modules/payment/images/');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BACK_TITLE','PayPal shop logo background colour');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BACK_DESC','Background colour for shop logo to be displayed at PayPal, e.g., FEE8B9');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BORD_TITLE', 'PayPal shop logo frame colour');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_BORD_DESC','Frame colour for shop logo to be displayed at PayPal, e.g., E4C558');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_SITE_TITLE', 'Background colour PayPal website');
define('MODULE_PAYMENT_PAYPAL_IPN_CO_SITE_DESC','Background colour for PayPals website to be displayed, e.g., E4C558');
define('MODULE_PAYMENT_PAYPAL_IPN_CBT_TITLE', 'Text back button');
define('MODULE_PAYMENT_PAYPAL_IPN_CBT_DESC','Text for back button to be displayed at PayPal');

//Weiterleitung URLs
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_TITLE', 'Return URL after payment');
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_DESC','Return URL for transfer after payment, e.g., a specific URL to your website displays "Many thanks for your payment". <br />'.HTTP_SERVER.DIR_WS_CATALOG);
define('MODULE_PAYMENT_PAYPAL_IPN_NOTIFY_TITLE', 'URL for PayPal IPN payment notification');
define('MODULE_PAYMENT_PAYPAL_IPN_NOTIFY_DESC','URL for transfer to notify on PayPal payment (IPN POST MESSAGES).<br />'.HTTP_SERVER.DIR_WS_CATALOG);
define('MODULE_PAYMENT_PAYPAL_IPN_CANCEL_TITLE', 'URL for PayPal error');
define('MODULE_PAYMENT_PAYPAL_IPN_CANCEL_DESC','URL for transfer in case of PayPal error messages<br />'.HTTP_SERVER.DIR_WS_CATALOG);

//Emails
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_PAID_TITLE','E-Mail to shop owners after successful payment process');
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_PAID_DESC','If set to "true" you will be notified by E-Mail after each successfully proceeded payment including adjusted order status.<br /><br /> Please note: in case of incorrect payments (wrong amounts, double payments or payments which cannot be allocated) you will be notified by E-Mail as well.');
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_CUSTOMER_TITLE','E-Mail to customers when order status has changed');
define('MODULE_PAYMENT_PAYPAL_IPN_EMAIL_CUSTOMER_DESC','If set to "true" your customer will be notified by E-Mail automatically when the order status has been changed by the module.');

//###Diese Texte sehen die Kunden###
define('MODULE_PAYMENT_PAYPAL_IPN_TEXT_INFO','Pay easily and safely by PayPal ' . MODULE_PAYMENT_PAYPAL_IPN_LOGO);

//Paypal Linktexte
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT','Pay now with PayPal');
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT2','You will receive the PayPal payment link automatically with your order confirmation email as well! You can use it to make the payment later');
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_EMAIL', "Pay now with Paypal. Please click on the link below:\n");
define('MODULE_PAYMENT_PAYPAL_IPN_TXT_ORDER', " - Order: ");

//PayPal Variablen
define('MODULE_PAYMENT_PAYPAL_IPN_VAR_CBT', "Return to vendor"); //cbt

//Style Schaltfläche
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_LINK', 'style="padding:5px; color:#555555; background: #f8f8f8; border: 1px solid #8c8c8c; text-decoration: none; cursor: pointer;"'); //Tomcraft 2010-06-23 define link color 
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_TOP', '<div style="margin-top:25px;">');
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_LOGO', '<div style="margin-top: 5px; float: left;">' . MODULE_PAYMENT_PAYPAL_IPN_LOGO . '</div>');
define('MODULE_PAYMENT_PAYPAL_IPN_STYLE_TEXT', '<div style="clear: both; color:#496686; font-weight: bold; padding:10px;">' . MODULE_PAYMENT_PAYPAL_IPN_TXT_CHECKOUT2.'</div>');

//PAYPAL NOTIFY: paypal_ipn_notify.php
define('MODULE_PAYMENT_PAYPAL_IPN_COMMENT_STATUS','Automatically through PayPal IPN-ADV module');
define('MODULE_PAYMENT_PAYPAL_IPN_SUBJECT_OK','PayPal payment received and posted');
define('MODULE_PAYMENT_PAYPAL_IPN_UNKNOWN','unknown');

define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1','Incorrect payment - cannot be allocated to a specific order (has been processed manually without using a payment link or order number). ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1A','Order number "%s" could not be found in the database and is invalid. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1B','Amount received is in false currency. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1C','Amount received is too low (outstanding amout: ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1D','Amount received is too high (overpaid amount: ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1E','Double payment received. This order has been paid by PayPal already on %s. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1F','Invalid reception status. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1G','Payment has not been credited to your PayPal account yet (Status: Pending), manual acceptance may be needed. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG1H','Payment was sent to wrong recipient. ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_SUB1','Problem with received PayPal');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_INFO1','Payment was ignored and the respective order status has not been changed.');

define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG2','Amount received is too high (overpaid amount: ');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_SUB2','PayPal payment received is too high');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_INFO2','Payment was nevertheless credited to your account and order status was set to %s .');

define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_MSG3','WARNING! PayPal reports INVALID for this IPN payment confirmation');
define('MODULE_PAYMENT_PAYPAL_IPN_ERROR_SUB3','INVALID for PayPal payment process!');

//PAYPAL RETURN: paypal_ipn_return.php
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_HEADER','Many thanks!');
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_TEXT1','Thank you for your PayPal payment of');
define('MODULE_PAYMENT_PAYPAL_IPN_RETURN_TEXT2','for your order of');
?>