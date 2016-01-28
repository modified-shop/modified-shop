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
  'TEXT_PAYPAL_WEBHOOK_HEADING_TITLE' => 'PayPal Webhooks',

  'TEXT_PAYPAL_WEBHOOK_STATUS_NOT_DEFINED' => 'not configured',
  'TEXT_PAYPAL_WEBHOOK_INFO' => '<ul><li>No Webhooks available.</li><li>With Webhhoks you receive updates for Payments and orders status</li><li>To use webhook, it is imperative that you have a valid SSL certificate.</li></ul>',
  'TEXT_PAYPAL_WEBHOOK_CREDENTIAL_INFO' => 'No Webhooks available. Please check your credentials.',
  
  'TABLE_HEADING_URL' => 'URL',
  'TABLE_HEADING_WEBHOOK' => 'Webhook',
  'TABLE_HEADING_STATUS' => 'Status',
  'TABLE_HEADING_DESCRIPTION' => 'Description',
  
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>