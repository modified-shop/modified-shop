<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


$lang_array = array(
  'TEXT_PAYPAL_PAYMENT_HEADING_TITLE' => 'PayPal Transactions',
  
  'TABLE_HEADING_DATE' => 'Date',
  'TABLE_HEADING_NAME' => 'Customer name',
  'TABLE_HEADING_EMAIL' => 'Customer E-Mail Address',
  'TABLE_HEADING_INTENT' => 'Intent',
  'TABLE_HEADING_STATUS' => 'Status',
  'TABLE_HEADING_ID' => 'Transaction ID',
  'TABLE_HEADING_TOTAL' => 'Amount',
  'TABLE_HEADING_ORDER' => 'Order ID',

  'TEXT_PAYPAL_PAYMENT_INFO' => 'No Transactions available',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>