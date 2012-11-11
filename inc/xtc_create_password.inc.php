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
   (c) 2003 nextcommerce (xtc_create_password.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_create_password.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_RandomString($length) {
    $chars = array( 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J', 'k', 'K', 'l', 'L', 'm', 'M', 'n','N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v','V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

    $max_chars = count($chars) - 1;
    srand((double) microtime()*1000000);

    $rand_str = '';
    for($i=0;$i<$length;$i++){
      $rand_str = ( $i == 0 ) ? $chars[rand(0, $max_chars)] : $rand_str . $chars[rand(0, $max_chars)];
    }

    return $rand_str;
  }

  function xtc_create_password($length) {
    $pass=xtc_RandomString($length); //DokuMan - 2011-02-10 - corrected typo $lenght -> $length
    //BOF - DokuMan - 2011-02-02 - added support for passwort+salt (SHA1)
    //return md5($pass);
    require_once (DIR_FS_INC . 'xtc_encrypt_password.inc.php');

    return xtc_encrypt_password($pass);
    //EOF - DokuMan - 2011-02-02 - added support for passwort+salt (SHA1)
  }
?>