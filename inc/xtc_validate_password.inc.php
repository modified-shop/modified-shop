<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(password_funcs.php,v 1.10 2003/02/11); www.oscommerce.com 
   (c) 2003	nextcommerce (xtc_validate_password.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');

  // include needed class
  require_once (DIR_FS_CATALOG.'includes/classes/validpass.php');
  
  // This funstion validates a plain text password with an encrpyted password
  function xtc_validate_password($plain, $encrypted, $customers_id = false) {
    if (xtc_not_null($plain) && xtc_not_null($encrypted)) {
      
      $check = xtc_validate_password_collation($plain, $encrypted, $customers_id);
      if ($check === false) {
        $plain = mb_convert_encoding($plain, 'ISO-8859-15', 'UTF-8');
        $check = xtc_validate_password_collation($plain, $encrypted, $customers_id);
      }
      
      return $check;
    }
  }

  function xtc_validate_password_collation($plain, $encrypted, $customers_id) {
    if (xtc_not_null($plain) && xtc_not_null($encrypted)) {

      $password_check = false;
      foreach(auto_include(DIR_FS_CATALOG.'includes/extra/validate_password/','php') as $file) require ($file);
      if ($password_check === true) {
        return true;
      }

      // check for old passwords
      if (preg_match('#^[a-z0-9]{32}$#i', $encrypted)) {
        if ($encrypted != md5($plain)) {
          return false;
        } elseif ($customers_id) {
          xtc_password_rehash($plain, $customers_id);
        }
        return true;
      } else {
        // check for old passwords
        $validpass = new validpass();
        $valid = $validpass->validate_password($plain, $encrypted);
        if ($valid === true) {
          xtc_password_rehash($plain, $customers_id);
          return true;
        }
        
        if (password_verify($plain, $encrypted)) {
          if (password_needs_rehash($encrypted, PASSWORD_DEFAULT) || (defined('PASSWORD_HMAC') && PASSWORD_HMAC != '')) {
            xtc_password_rehash($plain, $customers_id);
          }
          return true;
        }
        
        if (defined('PASSWORD_HMAC') && PASSWORD_HMAC != '') {
          $sha256 = hash_hmac("sha256", $plain, PASSWORD_HMAC);
          if (password_verify($sha256, $encrypted)) {
            if (password_needs_rehash($encrypted, PASSWORD_DEFAULT)) {
              xtc_password_rehash($plain, $customers_id);
            }
            return true;
          }        
        }
      }
    }
    return false;
  }
  
  function xtc_password_rehash($plain, $customers_id) {
    $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_CUSTOMERS." WHERE Field = 'customers_password'");
    $check = xtc_db_fetch_array($check_query);
    
    if ((int)preg_replace('/[^\d]/', '', $check['Type']) < 255) {
      xtc_db_query("ALTER TABLE ".TABLE_CUSTOMERS." MODIFY `customers_password` VARCHAR(255) NOT NULL");
    }
    
    xtc_db_query("UPDATE ".TABLE_CUSTOMERS."
                     SET customers_password = '".xtc_encrypt_password($plain)."'
                   WHERE customers_id = '".(int)$customers_id."'");
  }
