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
  'TEXT_PAYPAL_MODULE_HEADING_TITLE' => 'PayPal Module',
  
  'TABLE_HEADING_MODULES' => 'Modul',
  'TABLE_HEADING_SORT_ORDER' => 'Sortierung',
  'TABLE_HEADING_STATUS' => 'Status',
  'TABLE_HEADING_ACTION' => 'Aktion',

  'TABLE_HEADING_WALL_STATUS' => 'Auf der Paymentwall anzeigen',
  'TABLE_HEADING_WALL_DESCRIPTION' => 'Beschreibung',
  
  'TEXT_PAYPAL_MODULE_PROFILE' => 'Profil',
  'TEXT_PAYPAL_NO_PROFILE' => 'kein Webprofil',
  'TEXT_PAYPAL_STANDARD_PROFILE' => 'Standard Webprofil',
  
  'TEXT_PAYPAL_MODULE_LINK_SUCCESS' => 'Link im Checkout',
  'TEXT_PAYPAL_MODULE_LINK_SUCCESS_INFO' => 'Soll der Zahllink im Checkout angezeigt werden?',

  'TEXT_PAYPAL_MODULE_LINK_ACCOUNT' => 'Link im Account',
  'TEXT_PAYPAL_MODULE_LINK_ACCOUNT_INFO' => 'Soll der Zahllink im Account angezeigt werden?',

  'TEXT_PAYPAL_MODULE_LINK_PRODUCT' => 'Button beim Artikel',
  'TEXT_PAYPAL_MODULE_LINK_PRODUCT_INFO' => 'Soll der PayPal Button in den Artikel Infos angezeigt werden?',

  'TEXT_PAYPAL_MODULE_USE_TABS' => 'Accordion / Tabs',
  'TEXT_PAYPAL_MODULE_USE_TABS_INFO' => 'Verwendet das Template Accordion oder Tabs im Checkout?',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>