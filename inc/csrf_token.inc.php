<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('CSFR_ADMIN_MSG', 'CSRFToken nicht erkannt - Bitte informieren sie ihren Administrator');

// include needed function
require_once (DIR_FS_INC . 'xtc_create_password.inc.php');

// verfiy CSRF Token
if (is_array($_POST) && count($_POST) > 0) {

  if (isset($_POST[$_SESSION['CSRFName']])) {
    if ($_POST[$_SESSION['CSRFName']] != $_SESSION['CSRFToken']) {
      unset($_POST);
      // create CSRF Token
      $_SESSION['CSRFName'] = xtc_RandomString(6);
      $_SESSION['CSRFToken'] = xtc_RandomString(32);
      if (defined('RUN_MODE_ADMIN')) {
        $messageStack->add_session(CSFR_ADMIN_MSG, 'warning');
      }
    }
  } else {
    unset($_POST);
    // create CSRF Token
    $_SESSION['CSRFName'] = xtc_RandomString(6);
    $_SESSION['CSRFToken'] = xtc_RandomString(32);
    if (defined('RUN_MODE_ADMIN')) {
      $messageStack->add_session(CSFR_ADMIN_MSG, 'warning');
    }
  }
}

// create CSRF Token
if ((!isset($_SESSION['CSRFKeep']) && !isset($CSRFKeep)) || ((isset($_SESSION['CSRFKeep']) && $_SESSION['CSRFKeep'] == '') || (isset($CSRFKeep) && $CSRFKeep == ''))) {
  $_SESSION['CSRFName'] = xtc_RandomString(6);
  $_SESSION['CSRFToken'] = xtc_RandomString(32);
} else {
  unset($_SESSION['CSRFKeep']);
  unset($CSRFKeep);
}

// keep Token for orders.php
if (defined('RUN_MODE_ADMIN') 
    && strpos(basename($PHP_SELF), 'print_order') !== false  
    && strpos(basename($PHP_SELF), 'print_packingslip') !== false
    && strpos(basename($PHP_SELF), 'bill') !== false
    ) 
{
  $_SESSION['CSRFKeep'] = true;  
}
?>