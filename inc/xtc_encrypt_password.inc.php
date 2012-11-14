<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(password_funcs.php,v 1.10 2003/02/11); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_encrypt_password.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_encrypt_password.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// This function makes a new password from a plaintext password.

function xtc_encrypt_password($plain, $salt = false) {
  if (!$salt) {
    $salt = generateSalt();
  }
  $password = "\${$salt}$" . sha1($salt . $plain);
  return $password;
}

/**
 * This function generates a password salt as a string of x (default = 8) characters
 * ranging from a-zA-Z0-9.
 * @param $max integer The number of characters in the string
 * @author AfroSoft <scripts@afrosoft.co.cc>
 */
function generateSalt($max = 8) {
  $characterList = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  $i = 0;
  $salt = "";
  do {
    $salt .= $characterList{mt_rand(0,strlen($characterList)-1)}; // DokuMan - 2012-06-12 - added "-1" thx to Christian Rothe
    $i++;
  } while ($i < $max);
  return $salt;
}
?>