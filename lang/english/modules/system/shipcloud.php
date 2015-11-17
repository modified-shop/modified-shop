<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (defined('_VALID_XTC')) {
  define('MODULE_SHIPCLOUD_TEXT_TITLE', 'shipcloud - the new generation of parcel shipment');
  define('MODULE_SHIPCLOUD_TEXT_DESCRIPTION', 'Print parcel labels directly out of the Shop.');
  define('MODULE_SHIPCLOUD_STATUS_TITLE', 'Status');
  define('MODULE_SHIPCLOUD_STATUS_DESC', 'Module activate?');
  define('MODULE_SHIPCLOUD_API_TITLE', '<hr noshade>API');
  define('MODULE_SHIPCLOUD_API_DESC', 'API Key von shipcloud');
  define('MODULE_SHIPCLOUD_PARCEL_TITLE', '<hr noshade>Packagesize');
  define('MODULE_SHIPCLOUD_PARCEL_DESC', 'Please define the Packagesize like this: length,width,height;<br/>You can define more sizes separated by semicolon (;). Exapmple: 20,40,30;15,20,20;');
  define('MODULE_SHIPCLOUD_COMPANY_TITLE', '<hr noshade>Customer details<br/>');
  define('MODULE_SHIPCLOUD_COMPANY_DESC', 'Company:');
  define('MODULE_SHIPCLOUD_FIRSTNAME_TITLE', '');
  define('MODULE_SHIPCLOUD_FIRSTNAME_DESC', 'Firstname:');
  define('MODULE_SHIPCLOUD_LASTNAME_TITLE', '');
  define('MODULE_SHIPCLOUD_LASTNAME_DESC', 'Lastname:');
  define('MODULE_SHIPCLOUD_ADDRESS_TITLE', '');
  define('MODULE_SHIPCLOUD_ADDRESS_DESC', 'Address:');
  define('MODULE_SHIPCLOUD_POSTCODE_TITLE', '');
  define('MODULE_SHIPCLOUD_POSTCODE_DESC', 'Zip:');
  define('MODULE_SHIPCLOUD_CITY_TITLE', '');
  define('MODULE_SHIPCLOUD_CITY_DESC', 'City:');
  define('MODULE_SHIPCLOUD_TELEPHONE_TITLE', '');
  define('MODULE_SHIPCLOUD_TELEPHONE_DESC', 'Telephone:');
  define('MODULE_SHIPCLOUD_ACCOUNT_IBAN_TITLE', '');
  define('MODULE_SHIPCLOUD_ACCOUNT_IBAN_DESC', 'IBAN:');
  define('MODULE_SHIPCLOUD_ACCOUNT_BIC_TITLE', '');
  define('MODULE_SHIPCLOUD_ACCOUNT_BIC_DESC', 'BIC:');
  define('MODULE_SHIPCLOUD_BANK_NAME_TITLE', '<hr noshade>Bank details<br/>');
  define('MODULE_SHIPCLOUD_BANK_NAME_DESC', 'Bank:');
  define('MODULE_SHIPCLOUD_BANK_HOLDER_TITLE', '');
  define('MODULE_SHIPCLOUD_BANK_HOLDER_DESC', 'Holder:');
  define('MODULE_SHIPCLOUD_LOG_TITLE', '<hr noshade>Log');
  define('MODULE_SHIPCLOUD_LOG_DESC', 'the log file is stored in the folder / log.');
  define('MODULE_SHIPCLOUD_EMAIL_TITLE', '<hr noshade>E-Mail notification');
  define('MODULE_SHIPCLOUD_EMAIL_DESC', 'Notify customer by E-Mail?');
  define('MODULE_SHIPCLOUD_EMAIL_TYPE_TITLE', '<hr noshade>Notification');
  define('MODULE_SHIPCLOUD_EMAIL_TYPE_DESC', 'Should the customer be notifyed by the Shop or shipcloud?<br><Note:</b>For a notification from the Shop must set a Webhook to this URL: '.xtc_catalog_href_link('callback/shipcloud/callback.php', '', 'SSL', false).' in shipcloud.');
}

define('SHIPMENT.TRACKING.SHIPCLOUD_LABEL_CREATED', 'Shipment created at shipcloud');
define('SHIPMENT.TRACKING.LABEL_CREATED', 'A label has been created');
define('SHIPMENT.TRACKING.PICKED_UP', 'Shipment was picked up by carrier');
define('SHIPMENT.TRACKING.TRANSIT', 'Shipment is in transit');
define('SHIPMENT.TRACKING.OUT_FOR_DELIVERY', 'Out for delivery');
define('SHIPMENT.TRACKING.DELIVERED', 'Delivered');
define('SHIPMENT.TRACKING.AWAITS_PICKUP_BY_RECEIVER', 'Awaiting pickup by the receiver');
define('SHIPMENT.TRACKING.CANCELED', 'label has been deleted');
define('SHIPMENT.TRACKING.DELAYED', 'Delivery will be delayed');
define('SHIPMENT.TRACKING.EXCEPTION', 'There is a problem with the shipment');
define('SHIPMENT.TRACKING.NOT_DELIVERED', 'Not delivered');
define('SHIPMENT.TRACKING.NOTIFICATION', 'Carrier internal notification: Tracking events within the shipment will carry more elaborate information.');
define('SHIPMENT.TRACKING.UNKNOWN', 'Status unknown');
?>