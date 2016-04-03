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
  'TEXT_PAYPAL_PROFILE_HEADING_TITLE' => 'PayPal Profile',

  'TEXT_PAYPAL_PROFILE_STATUS' => 'Default:',
  'TEXT_PAYPAL_PROFILE_STATUS_INFO' => 'Should this be the default profile?<br/><br/><b>Note:</b> You can be assign a language-dependent profile for each single module.',
  
  'TEXT_PAYPAL_PROFILE_NAME' => 'Internal name:',
  'TEXT_PAYPAL_PROFILE_NAME_INFO' => '',
  
  'TEXT_PAYPAL_PROFILE_BRAND' => 'Display name:',
  'TEXT_PAYPAL_PROFILE_BRAND_INFO' => 'This name will be displayed to the clients at PayPal',
  
  'TEXT_PAYPAL_PROFILE_LOGO' => 'Logo URL:',
  'TEXT_PAYPAL_PROFILE_LOGO_INFO' => 'Complete URL<br/><br/><b>Note:</b> For the logo to appear, the URL must start with https://',
  
  'TEXT_PAYPAL_PROFILE_LOCALE' => 'Language:',
  'TEXT_PAYPAL_PROFILE_LOCALE_INFO' => '',
  
  'TEXT_PAYPAL_PROFILE_PAGE' => 'Page:',
  'TEXT_PAYPAL_PROFILE_PAGE_INFO' => '<b>Default:</b> Login<br/><br/>For Payment the act of transfer without customer account is pre-selected.',

  'TEXT_PAYPAL_PROFILE_ADDRESS' => 'Address override:',
  'TEXT_PAYPAL_PROFILE_ADDRESS_INFO' => 'Shall the shipping address provided by PayPal be taken over?',

  'TEXT_PAYPAL_PROFILE_INFO' => 'No PayPal Profile available.<br/><br/>Use a PayPal Profile to:<ul><li>use an Image at PayPal</li><li>get Address from PayPal for Buyer Protection</li></ul>',  
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>