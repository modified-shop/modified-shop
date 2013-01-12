<?php
/* --------------------------------------------------------------
   $Id: customers.php 2666 2012-02-23 11:38:17Z dokuman $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(customers.php,v 1.13 2002/06/15); www.oscommerce.com 
   (c) 2003 nextcommerce (customers.php,v 1.8 2003/08/15); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Customers');
define('HEADING_TITLE_SEARCH', 'Search:');

define('TABLE_HEADING_CUSTOMERSCID','Customer ID');
define('TABLE_HEADING_FIRSTNAME', 'First Name');
define('TABLE_HEADING_LASTNAME', 'Last Name');
define('TABLE_HEADING_ACCOUNT_CREATED', 'Account Created');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_DATE_ACCOUNT_CREATED', 'Account Created:');
define('TEXT_DATE_ACCOUNT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_DATE_LAST_LOGON', 'Last Logon:');
define('TEXT_INFO_NUMBER_OF_LOGONS', 'Number of Logons:');
define('TEXT_INFO_COUNTRY', 'Country:');
define('TEXT_INFO_NUMBER_OF_REVIEWS', 'Number of Reviews:');
define('TEXT_DELETE_INTRO', 'Are you sure you want to delete this customer?');
define('TEXT_DELETE_REVIEWS', 'Delete %s review(s)');
define('TEXT_INFO_HEADING_DELETE_CUSTOMER', 'Delete Customer');
define('TYPE_BELOW', 'Type below');
define('PLEASE_SELECT', 'Select One');
define('HEADING_TITLE_STATUS','Customers Group');
define('TEXT_ALL_CUSTOMERS','All Groups');
define('TEXT_INFO_HEADING_STATUS_CUSTOMER','Customers Group');
define('TABLE_HEADING_NEW_VALUE','New Status');
define('TABLE_HEADING_DATE_ADDED','Date');
define('TEXT_NO_CUSTOMER_HISTORY','--no modification yet--');
define('TABLE_HEADING_GROUPIMAGE','Icon');
define('ENTRY_MEMO','Memo');
define('TEXT_DATE','Date');
define('TEXT_TITLE','Title');
define('TEXT_POSTER','Poster');
define('ENTRY_PASSWORD_CUSTOMER','Password:');
define('TABLE_HEADING_ACCOUNT_TYPE','Account');
define('TEXT_ACCOUNT','Yes');
define('TEXT_GUEST','No');
define('NEW_ORDER','New order ?');
define('ENTRY_PAYMENT_UNALLOWED','Unallowed paymentmodules:');
define('ENTRY_SHIPPING_UNALLOWED','Unallowed shippingmodules:');
define('ENTRY_NEW_PASSWORD','New Password:');

// NEU HINZUGEFUEGT 04.12.2008 - UMSATZANZEIGE BEI KUNDEN 03.12.2008
define('TABLE_HEADING_UMSATZ','total revenue');

// BOF - web28 - 2010-05-28 - added  customers_email_address
define('TABLE_HEADING_EMAIL','Email');
// EOF - web28 - 2010-05-28 - added  customers_email_address

define('TEXT_INFO_HEADING_ADRESS_BOOK', 'Addressbook');
define('TEXT_INFO_DELETE', '<b>Delete this address book entry?</b>');
define('TEXT_INFO_DELETE_DEFAULT', '<b>The address book entry can not be deleted!</b>'); 
?>