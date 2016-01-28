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
  'TEXT_PAYPAL_PROFILE_STATUS_INFO' => 'Soll dieses Profil als Standard verwendet werden?<br/><br/><b>Hinweis:</b> Es kann f&uuml;r jedes Modul ein eigenes Profil sprachabh&auml;ngig zugewiesen werden.',
  
  'TEXT_PAYPAL_PROFILE_NAME' => 'Interner Name:',
  'TEXT_PAYPAL_PROFILE_NAME_INFO' => '',
  
  'TEXT_PAYPAL_PROFILE_BRAND' => 'Angezeigter Name:',
  'TEXT_PAYPAL_PROFILE_BRAND_INFO' => 'Dieser Name wird dem Kunden bei PayPal angezeigt',
  
  'TEXT_PAYPAL_PROFILE_LOGO' => 'Logo URL:',
  'TEXT_PAYPAL_PROFILE_LOGO_INFO' => 'Vollst&auml;ndige URL<br/><br/><b>Hinweis:</b> Damit das Logo angezeigt wird, muss die URL zwingend mit https:// angegeben werden',
  
  'TEXT_PAYPAL_PROFILE_LOCALE' => 'Sprache:',
  'TEXT_PAYPAL_PROFILE_LOCALE_INFO' => '',
  
  'TEXT_PAYPAL_PROFILE_PAGE' => 'Seite:',
  'TEXT_PAYPAL_PROFILE_PAGE_INFO' => '<b>Standard:</b> Login<br/><br/>Bei Payment ist die Zahlung ohne Kundenkonto vorausgew&auml;hlt.',

  'TEXT_PAYPAL_PROFILE_ADDRESS' => 'Adresse &uuml;berschreiben:',
  'TEXT_PAYPAL_PROFILE_ADDRESS_INFO' => 'Soll die Versandadresse von PayPal &uuml;bernommen werden?',
  
  'TEXT_PAYPAL_PROFILE_INFO' => 'Es ist kein PayPal Profil vorhanden.<br/><br/>Mit einem PayPal Profil k&ouml;nnen sie:<ul><li>die PayPal Seite mit einem Logo versehen</li><li>Adresse von PayPal f&uuml;r den K&auml;uferschutz &uuml;bernehmen</li></ul>',
);


foreach ($lang_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>