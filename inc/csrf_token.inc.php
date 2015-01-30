<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed function
require_once (DIR_FS_INC . 'xtc_create_password.inc.php');

// verfiy CSRF Token
if (is_array($_POST) && count($_POST) > 0) {

  if (isset($_POST[$_SESSION['CSRFName']])) {
    if ($_POST[$_SESSION['CSRFName']] != $_SESSION['CSRFToken']) {
      trigger_error("CSRFToken manipulation.\n".print_r($_POST, true), E_USER_WARNING);
      unset($_POST);
      unset($_GET['action']);
      unset($_GET['saction']);
      
      // create CSRF Token
      $_SESSION['CSRFName'] = xtc_RandomString(6);
      $_SESSION['CSRFToken'] = xtc_RandomString(32);
      if (defined('RUN_MODE_ADMIN')) {
        $messageStack->add('CSRFToken manipulation', 'warning');
        $messageStack->add_session('CSRFToken manipulation', 'warning');
      }
    }
  } else {
    trigger_error("CSRFToken not defined.\n".print_r($_POST, true), E_USER_WARNING);
    unset($_POST);
    unset($_GET['action']);
    unset($_GET['saction']);
    
    // create CSRF Token
    $_SESSION['CSRFName'] = xtc_RandomString(6);
    $_SESSION['CSRFToken'] = xtc_RandomString(32);
    if (defined('RUN_MODE_ADMIN')) {
      $messageStack->add('CSRFToken not defined', 'warning');
      $messageStack->add_session('CSRFToken not defined', 'warning');
    }
  }
}

// keep Token for popups
$CSRFKeep = false;
if (defined('RUN_MODE_ADMIN') 
    && (strpos(basename($PHP_SELF), 'print_order') !== false  
        || strpos(basename($PHP_SELF), 'print_packingslip') !== false
        || strpos(basename($PHP_SELF), 'bill') !== false
        || strpos(basename($PHP_SELF), 'popup') !== false
        || strpos(basename($PHP_SELF), 'fck_wrapper') !== false
        || strpos(basename($PHP_SELF), 'new_attributes') !== false
        )
    ) 
{
  $CSRFKeep = true;
}

// create CSRF Token
if ($CSRFKeep === false) {
  $_SESSION['CSRFName'] = xtc_RandomString(6);
  $_SESSION['CSRFToken'] = xtc_RandomString(32);
}
?>