<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(password_funcs.php,v 1.10 2003/02/11); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_validate_password.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_validate_password.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// This funstion validates a plain text password with an
// encrpyted password

//BOF - DokuMan - 2011-02-02 - added support for passwort+salt (SHA1)
/*
  function xtc_validate_password($plain, $encrypted) {
    if (xtc_not_null($plain) && xtc_not_null($encrypted)) {
      // split apart the hash / salt
      if ($encrypted!= md5($plain)){
            return false;
      } else {
             return true;
      }
    }
    return false;
  }
*/
function xtc_validate_password($plain, $encrypted, $email = false) {
  if (xtc_not_null($plain) && xtc_not_null($encrypted)) {
    // Is it SHA1?
    if (preg_match('#^\$[a-z0-9]{8}\$[a-z0-9]{40}$#i', $encrypted)) {
      $chunks = explode('$', $encrypted);

      if ($encrypted == "\${$chunks[1]}$" . sha1($chunks[1] . $plain)) {
        return true;
      }
      return false;
    }
    // Or MD5?
    elseif (preg_match('#^[a-z0-9]{32}$#i', $encrypted)) {
      if ($encrypted != md5($plain)) {
        return false;
      }
      elseif ($email) {
        // The EMail adress is provided we can change the MD5 hash to SHA1
        require_once (DIR_FS_INC . 'xtc_encrypt_password.inc.php');
        xtc_db_query("update " . TABLE_CUSTOMERS . "
                      set customers_password = '" . xtc_encrypt_password($plain) . "'
                      where customers_email_address = '" . xtc_db_input($email) . "'");
      }
      return true;
    }
  }
  return false;
}
//EOF - DokuMan - 2011-02-02 - added support for passwort+salt (SHA1)
?>