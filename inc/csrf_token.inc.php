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

if (defined('CSRF_TOKEN_EXCLUSIONS') && CSRF_TOKEN_EXCLUSIONS != '') {
  $user_exclusions  = preg_replace("'[\r\n\s]+'",'',CSRF_TOKEN_EXCLUSIONS);
  $user_exclusions = explode(',',$user_exclusions);
}

if ( !isset($module_exclusions) || !is_array($module_exclusions) ) {
  $module_exclusions = array();
}

// keep Token for popups, user_exclusions, module_exclusions
$CSRFKeep = false;
if (defined('RUN_MODE_ADMIN')) {
  $exclusions = array('print_order', 'print_packingslip', 'bill', 'popup', 'haendlerbund', 'new_attributes');
  if (isset($user_exclusions) && is_array($user_exclusions)) {
    $exclusion = array_merge($exclusions,$user_exclusions,$module_exclusions);
  }
  foreach ($exclusions as $filename) {
    if (strpos(basename($PHP_SELF), $filename) !== false) {
      $CSRFKeep = true;
    }
  }
}

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
        $messageStack->add(CSRF_TOKEN_MANIPULATION, 'warning');
        $messageStack->add_session(CSRF_TOKEN_MANIPULATION, 'warning');
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
      $messageStack->add(CSRF_TOKEN_NOT_DEFINED, 'warning');
      $messageStack->add_session(CSRF_TOKEN_NOT_DEFINED, 'warning');
    }
  }
} elseif ($CSRFKeep === false) {
  $_SESSION['CSRFName'] = xtc_RandomString(6);
  $_SESSION['CSRFToken'] = xtc_RandomString(32);
}
?>