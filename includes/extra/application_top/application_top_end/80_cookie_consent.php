<?php
  /* --------------------------------------------------------------
   $Id: cookie_consent.js.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (defined('MODULE_COOKIE_CONSENT_STATUS') && MODULE_COOKIE_CONSENT_STATUS == 'true') {
    if (basename($PHP_SELF) == FILENAME_POPUP_CONTENT && !empty($_GET['coID'])) {
      if (in_array($_GET['coID'], array(2,4))) {
        define('COOKIE_CONSENT_NO_TRACKING', true);
      }
    }
  }
