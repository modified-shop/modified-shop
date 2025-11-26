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
  'TEXT_PAYPAL_BANNER_HEADING_TITLE' => 'PayPal Banner',

  'TEXT_PAYPAL_BANNER_HEADING_PRODUCT' => 'Product details',
  'TEXT_PAYPAL_BANNER_HEADING_CART' => 'Shopping cart',
  'TEXT_PAYPAL_BANNER_HEADING_CHECKOUT' => 'Checkout',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY' => 'Credit banner:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY_INFO' => 'Display credit banner?<br/><br/><b>Note:</b> PayPal Installment is only available if your PayPal account is approved and PayPal Vault disabled.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR' => 'Color:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR_INFO' => 'Choose the color for the banner.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTCOLOR' => 'Text color:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTCOLOR_INFO' => 'Choose the text color for the banner.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE' => 'Size',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE_INFO' => 'Choose the size for the banner.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTSIZE' => 'Text size',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTSIZE_INFO' => 'Choose the text size for the banner.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOTYPE' => 'Logo type:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOTYPE_INFO' => 'Choose the logo type for the banner.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOPOSITION' => 'Logo position:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOPOSITION_INFO' => 'Choose the logo position for the banner.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
