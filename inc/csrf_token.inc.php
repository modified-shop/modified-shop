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
  $user_exclusions  = preg_replace("'[\r\n\s]+'", '', CSRF_TOKEN_EXCLUSIONS);
  $user_exclusions = explode(',', $user_exclusions);
}

if (!isset($module_exclusions) || !is_array($module_exclusions)) {
  $module_exclusions = array();
}

// keep Token for popups, user_exclusions, module_exclusions
$CSRFKeep = false;
if (defined('RUN_MODE_ADMIN')) {
  foreach(auto_include(DIR_FS_ADMIN.'includes/extra/csrf_exclusion/','php') as $file) require_once ($file);
  
  $exclusions = array(
    'bill', 
    'haendlerbund', 
    'magnalister', 
    'popup', 
    'popup_memo',
    'print_order', 
    'print_packingslip', 
    'products_attributes', 
    'products_tags', 
    'validproducts', 
    'validcategories',
  );
  if (isset($user_exclusions) && is_array($user_exclusions)) {
    $exclusions = array_merge($exclusions, $user_exclusions);
  }
  if (isset($module_exclusions) && is_array($module_exclusions)) {
    $exclusions = array_merge($exclusions, $module_exclusions);
  }
  foreach ($exclusions as $filename) {
    if (strpos(basename($PHP_SELF), $filename) !== false) {
      $CSRFKeep = true;
    }
  }
}

// verfiy CSRF Token
if (is_array($_POST) && count($_POST) > 0) {
  $error = false;
  if (isset($_POST[$_SESSION['CSRFName']])) {
    if ($_POST[$_SESSION['CSRFName']] != $_SESSION['CSRFToken']) {
      $error = CSRF_TOKEN_MANIPULATION;
    }
  } elseif ($CSRFKeep !== true) {
    $error = CSRF_TOKEN_NOT_DEFINED;
  }
  
  if ($error !== false) {
    trigger_error($error."\n".print_r($_POST, true), E_USER_WARNING);
    unset($_POST);
    unset($_GET['action']);
    unset($_GET['saction']);
    
    // create CSRF Token
    $_SESSION['CSRFName'] = xtc_RandomString(6);
    $_SESSION['CSRFToken'] = xtc_RandomString(32);

    if (defined('RUN_MODE_ADMIN')) {
      $messageStack->add($error, 'warning');
    }
  }
} elseif ($CSRFKeep === false) {
  // create CSRF Token
  $_SESSION['CSRFName'] = xtc_RandomString(6);
  $_SESSION['CSRFToken'] = xtc_RandomString(32);
}
?>