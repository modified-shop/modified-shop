<?php
  /* --------------------------------------------------------------
   $Id: cookie_consent.js.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

if (defined('MODULE_COOKIE_CONSENT_STATUS')) {
  $add_contents[BOX_HEADING_CONFIGURATION][] = array( 
    'admin_access_name' => 'cookie_consent',
    'filename' => 'cookie_consent.php',
    'boxname' => 'Cookie Consent', 
    'parameters' => '',
    'ssl' => ''
  );
}
