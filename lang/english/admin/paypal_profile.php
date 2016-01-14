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
  'TEXT_PAYPAL_PROFILE_HEADING_TITLE' => 'PayPal Profil',

  'TEXT_PAYPAL_PROFILE_STATUS' => 'Standard:',
  'TEXT_PAYPAL_PROFILE_STATUS_INFO' => 'Should this be the default profile?<br/><br/><b>Note:</b> It can be assigned to language-dependent a profile for each module.',
  
  'TEXT_PAYPAL_PROFILE_NAME' => 'Internal name:',
  'TEXT_PAYPAL_PROFILE_NAME_INFO' => '',
  
  'TEXT_PAYPAL_PROFILE_BRAND' => 'Display name:',
  'TEXT_PAYPAL_PROFILE_BRAND_INFO' => 'This name will be displayed to the clients at PayPal',
  
  'TEXT_PAYPAL_PROFILE_LOGO' => 'Logo URL:',
  'TEXT_PAYPAL_PROFILE_LOGO_INFO' => 'Complete URL<br/><br/><b>Note:</b> so that the logo appears, the URL must start with https://',
  
  'TEXT_PAYPAL_PROFILE_LOCALE' => 'Language:',
  'TEXT_PAYPAL_PROFILE_LOCALE_INFO' => '',
  
  'TEXT_PAYPAL_PROFILE_PAGE' => 'Page:',
  'TEXT_PAYPAL_PROFILE_PAGE_INFO' => '<b>Standard:</b> Login<br/><br/>For payment pre-selected page at PayPal is without Account.',

  'TEXT_PAYPAL_PROFILE_ADDRESS' => 'Address override:',
  'TEXT_PAYPAL_PROFILE_ADDRESS_INFO' => 'If the shipping address provided by PayPal are accepted?',

  'TEXT_PAYPAL_PROFILE_INFO' => 'No PayPal Profile available',  
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>