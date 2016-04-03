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
  'TEXT_PAYPAL_PAYMENT_HEADING_TITLE' => 'PayPal Transaktionen',
  
  'TABLE_HEADING_DATE' => 'Datum',
  'TABLE_HEADING_NAME' => 'Kunden Name',
  'TABLE_HEADING_EMAIL' => 'Kunden E-Mail Adresse',
  'TABLE_HEADING_INTENT' => 'Intent',
  'TABLE_HEADING_STATUS' => 'Status',
  'TABLE_HEADING_ID' => 'Transaktions ID',
  'TABLE_HEADING_TOTAL' => 'Betrag',
  'TABLE_HEADING_ORDER' => 'Bestellnummer',

  'TEXT_PAYPAL_PAYMENT_INFO' => 'Es sind keine Transaktionen vorhanden',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>