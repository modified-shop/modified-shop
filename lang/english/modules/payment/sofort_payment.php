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
    (c) 2010 Payment Network AG - http://www.payment-network.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_TITLE', 'Online bank transfer.<br /><img src="https://cdn.klarna.com/1.0/shared/image/generic/badge/en_gb/pay_now/standard/pink.svg" alt="Logo SOFORT Banking"/>');
define('MODULE_PAYMENT_'.$sofort_code.'_KS_TEXT_TITLE', 'Online bank transfer. with customer protection<br /><img src="https://cdn.klarna.com/1.0/shared/image/generic/badge/en_gb/pay_now/standard/pink.svg" alt="Logo SOFORT Banking"/>');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION', 'SOFORT Banking is the free of charge, T&Uuml;V certified payment method by SOFORT GmbH.');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_INFO', 'You can pay with the T&Uuml;V certified online banking system SOFORT Banking of SOFORT GmbH.');

// checkout
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="bottom">
	      <a onclick="javascript:window.open(\'https://images.sofort.com/en/su/landing.php\',\'Customerinformationen\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">{{image}}</a>
	    </td>
	  </tr>
	  <tr>
	    <td class="main">{{text}}</td>
	  </tr>
	</table>');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', '
  <ul>
    <li>Payment system with data protection certified by T&Uuml;V</li>
    <li>No registration required</li>
    <li>Immediate shipping of stock goods</li>
    <li>Please keep your online banking login data ready</li>
  </ul>');
define('MODULE_PAYMENT_'.$sofort_code.'_KS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', '
  <ul>
    <li>If paying with SOFORT Banking you enjoy [[link_beginn]]buyer protection![[link_end]]</li>
    <li>Payment system with T&Uuml;V-certified privacy policy</li>
    <li>No registration needed</li>
    <li>Goods/service will be shipped immediately, if available</li>
    <li>Please keep your online banking data ready</li>
  </ul>');

// admin
define('MODULE_PAYMENT_'.$sofort_code.'_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_PAYMENT_'.$sofort_code.'_ALLOWED_DESC', 'Please enter <b>einzeln</b> the zones, which should be allowed for this module. (eg allow AT, DE (if empty, all zones))');
define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_TITLE', 'Activate SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_DESC', 'Activates/deactivates SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_ORDER_TITLE', 'Temporary Order');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_ORDER_DESC', 'Do you want to create a temporary order?');
define('MODULE_PAYMENT_'.$sofort_code.'_LOGGING_TITLE', 'activate Logging');
define('MODULE_PAYMENT_'.$sofort_code.'_LOGGING_DESC', 'Activates/deactivates Logging<br/>Logfiles are saved in /log');
define('MODULE_PAYMENT_'.$sofort_code.'_KEY_TITLE', 'Configurationkey');
define('MODULE_PAYMENT_'.$sofort_code.'_KEY_DESC', 'Configurationkey can be found in SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_SORT_ORDER_TITLE', 'Display order');
define('MODULE_PAYMENT_'.$sofort_code.'_SORT_ORDER_DESC', 'Order of display. Smallest number is displayed first');
define('MODULE_PAYMENT_'.$sofort_code.'_ZONE_TITLE', 'Payment Zone');
define('MODULE_PAYMENT_'.$sofort_code.'_ZONE_DESC', 'If a zone is selected, the payment method is valid only for that zone.');
define('MODULE_PAYMENT_'.$sofort_code.'_CURRENCY_TITLE', 'Transaction currency');
define('MODULE_PAYMENT_'.$sofort_code.'_CURRENCY_DESC', 'Receiving currency SOFORT Banking setting');
define('MODULE_PAYMENT_'.$sofort_code.'_ORDER_STATUS_ID_TITLE', 'Confirmed Order');
define('MODULE_PAYMENT_'.$sofort_code.'_ORDER_STATUS_ID_DESC', 'Order status after receipt of an order, was sent in advance of a successful payment confirmation');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_STATUS_ID_TITLE', 'Temporary Order Status');
define('MODULE_PAYMENT_'.$sofort_code.'_TMP_STATUS_ID_DESC', 'Order for not yet completed transactions');
define('MODULE_PAYMENT_'.$sofort_code.'_UNC_STATUS_ID_TITLE', 'To Check Order Status');
define('MODULE_PAYMENT_'.$sofort_code.'_UNC_STATUS_ID_DESC', 'Order Status has been received in an incorrect payment confirmation after receipt of an order');
define('MODULE_PAYMENT_'.$sofort_code.'_REC_STATUS_ID_TITLE', 'Order Status after payment');
define('MODULE_PAYMENT_'.$sofort_code.'_REC_STATUS_ID_DESC', 'Order Status after the money has been credited to your account.');
define('MODULE_PAYMENT_'.$sofort_code.'_REF_STATUS_ID_TITLE', 'Order Status after chargeback');
define('MODULE_PAYMENT_'.$sofort_code.'_REF_STATUS_ID_DESC', 'Order Status after a chargeback has occurred.');
define('MODULE_PAYMENT_'.$sofort_code.'_LOSS_STATUS_ID_TITLE', 'Order Status if no money has arrived');
define('MODULE_PAYMENT_'.$sofort_code.'_LOSS_STATUS_ID_DESC', 'Order Status if no money is credited to your account.');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_1_TITLE', 'Usage Line 1');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_1_DESC', 'If no temporary order is created, the order number is not available. Therefore, it should be then placed on -TRANSACTION-');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_2_TITLE', 'Usage Line 2');
define('MODULE_PAYMENT_'.$sofort_code.'_REASON_2_DESC', 'In use (maximum 27 characters) to be replaced following placeholders:<br /> {{order_id}}<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');
define('MODULE_PAYMENT_'.$sofort_code.'_IMAGE_TITLE', 'Payment Grafic / Text');
define('MODULE_PAYMENT_'.$sofort_code.'_IMAGE_DESC', 'Grafic / Text on Payment Checkout');
define('MODULE_PAYMENT_'.$sofort_code.'_KS_STATUS_TITLE', 'Customer protection activated');
define('MODULE_PAYMENT_'.$sofort_code.'_KS_STATUS_DESC', 'Activate customer protection for SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_USER_ID_TITLE', 'Customer Number');
define('MODULE_PAYMENT_'.$sofort_code.'_USER_ID_DESC', 'Customer Number at SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_ID_TITLE', 'Project Nummer');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_ID_DESC', 'The responsible project number in the immediate navigation use SOFORT Banking to the payment belongs');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_PASS_TITLE', 'Project Password');
define('MODULE_PAYMENT_'.$sofort_code.'_PROJECT_PASS_DESC', 'Find this under Settings at SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_NOTIFY_PASS_TITLE', 'Notification Password');
define('MODULE_PAYMENT_'.$sofort_code.'_NOTIFY_PASS_DESC', 'Find this under Settings at SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_HASH_ALGORITHM_TITLE', 'Hash-Algorithmus:');
define('MODULE_PAYMENT_'.$sofort_code.'_HASH_ALGORITHM_DESC', 'Find this under Settings at SOFORT Banking');
define('MODULE_PAYMENT_'.$sofort_code.'_DESCRIPTION_INSTALL', '<br/><br/>Do you want to install proper order status?<br/>The currently set statuses are overwritten.');

// status
define('TEXT_NO_STATUSUPDATE', 'no status update');

// error
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_ERROR_HEADING', 'The following error was reported during the process:');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_ERROR_MESSAGE', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');

// callback
define('TEXT_SOFORT_NOT_CREDITED_YET', 'Successfully completed Transfer');
define('TEXT_SOFORT_NOT_CREDITED', 'Not received money on account');
define('TEXT_SOFORT_LOSS', 'verify the order');
define('TEXT_SOFORT_RECEIVED', 'Received money on account');
define('TEXT_SOFORT_CREDITED', TEXT_SOFORT_RECEIVED);
define('TEXT_SOFORT_REFUNDED', 'Money was refunded in full');
define('TEXT_SOFORT_CANCELED', 'canceled');
define('TEXT_SOFORT_WAIT_FOR_MONEY', 'Waiting for Payment');
define('TEXT_SOFORT_CONFIRMATION_PERIOD_EXPIRED', 'Timeout');
define('TEXT_SOFORT_REJECTED', 'rejected');
define('TEXT_SOFORT_SOFORT_BANK_ACCOUNT_NEEDED', TEXT_SOFORT_NOT_CREDITED_YET);

define('MODULE_PAYMENT_'.$sofort_code.'_ERROR_TRANSACTION', "Error during HTTP notification\nPlease check transaction and notification\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_ERROR_PAYMENT', "Money NOT received yet\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_ERROR_UNEXPECTED_STATUS', "Error (SU204): Unexpected Status\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_SUCCESS_TRANSACTION', "Payment successful\nTransaction-ID: %s");
define('MODULE_PAYMENT_'.$sofort_code.'_SUCCESS_PAYMENT', "Money received\nTransaction-ID: %s");

// order status
$SOFORT_INST_ORDER_STATUS_TMP_NAME = 'Temp';
$SOFORT_INST_ORDER_STATUS_UNC_NAME = 'Waiting';
$SOFORT_INST_ORDER_STATUS_LOSS_NAME = 'Waiting';
$SOFORT_INST_ORDER_STATUS_REC_NAME = 'Received Payment';
$SOFORT_INST_ORDER_STATUS_REF_NAME = 'Refunded';
$SOFORT_INST_ORDER_STATUS_ORDER_NAME = 'Payment';
?>