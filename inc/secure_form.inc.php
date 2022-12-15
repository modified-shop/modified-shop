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

function secure_form($case = '') {
  // create CSRF Token
  if (!isset($_SESSION['SFName'])
      || !isset($_SESSION['SFToken'])
      )
  {
    if (!isset($_SESSION['SFUsed'])) {
      $_SESSION['SFUsed'] = 0;
    }
    
    $gap = 4;
    if (isset($_SESSION['customer_id']) || $_SESSION['SFUsed'] > 0) {
      $gap = 2;
    }
    
    switch ($case) {
      case 'newsletter':
      case 'password_double_opt':
        $gap = 2;
        break;
    }
    
    $data = array(
      'n' => xtc_RandomString(6),
      'k' => xtc_RandomString(32),
      'f' => time() + $gap,
      't' => time() + SESSION_LIFE_CUSTOMERS,
    );
    
    $_SESSION['SFName'] = $data['n'];
    $_SESSION['SFToken'] = base64_encode(json_encode($data));
    $_SESSION['SFUsed'] ++;
  }
  
  return xtc_draw_hidden_field($_SESSION['SFName'], $_SESSION['SFToken']);
}

function check_secure_form($params) {
  $valid = true;
  
  if (!isset($_SESSION['SFName'])) {
    $valid = false;
  }

  if (!isset($_SESSION['SFToken'])) {
    $valid = false;
  }

  if (!isset($_SESSION['SFUsed'])) {
    $valid = false;
  }
  
  if (!isset($params[$_SESSION['SFName']])) {
    $valid = false;
  }

  if (isset($params[$_SESSION['SFName']]) 
      && $params[$_SESSION['SFName']] != $_SESSION['SFToken']
      )
  {
    $valid = false;
  }
  
  if (xtc_check_agent() == 1) {
    $valid = false;
  }
  
  if ($valid === true) {
    $data = json_decode(base64_decode($_SESSION['SFToken']), true);
    
    if ($data['f'] > time()) {
      $valid = false;
    }

    if ($data['t'] < time()) {
      $valid = false;
    }
  }
  
  if ($valid === true) {
    unset($_SESSION['SFUsed']);
  }
  
  unset($_SESSION['SFName']);
  unset($_SESSION['SFToken']);

  return $valid;
}
?>