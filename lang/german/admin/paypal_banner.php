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

  'TEXT_PAYPAL_BANNER_HEADING_PRODUCT' => 'Artikeldetails',
  'TEXT_PAYPAL_BANNER_HEADING_CART' => 'Warenkorb',
  'TEXT_PAYPAL_BANNER_HEADING_CHECKOUT' => 'Checkout',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY' => 'Ratenzahlung Banner:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_DISPLAY_INFO' => 'Soll das Banner f&uuml;r Ratenzahlung angezeigt werden?<br/><br/><b>Hinweis:</b> PayPal Ratenzahlung ist nur verf&uuml;gbar, sofern Ihr PayPal Konto daf&uuml;r freigeschaltet und PayPal Vault inaktiv ist.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR' => 'Farbe:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_COLOR_INFO' => 'W&auml;hlen Sie die Farbe f&uuml;r das Banner aus.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTCOLOR' => 'Textfarbe:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTCOLOR_INFO' => 'W&auml;hlen Sie die Textfarbe f&uuml;r das Banner aus.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE' => 'Gr&ouml;&szlig;e:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_SIZE_INFO' => 'W&auml;hlen Sie die Gr&ouml;&szlig;e f&uuml;r das Banner aus.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTSIZE' => 'Textgr&ouml;&szlig;e:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_TEXTSIZE_INFO' => 'W&auml;hlen Sie die Textgr&ouml;&szlig;e f&uuml;r das Banner aus.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOTYPE' => 'Logo Typ:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOTYPE_INFO' => 'W&auml;hlen Sie dien Logo Typ f&uuml;r das Banner aus.',

  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOPOSITION' => 'Logo Position:',
  'TEXT_PAYPAL_INSTALLMENT_BANNER_LOGOPOSITION_INFO' => 'W&auml;hlen Sie die Logo Position f&uuml;r das Banner aus.',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
