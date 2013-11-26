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
   (c) 2003	nextcommerce (xtc_encrypt_password.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
 
  // include needed class
  require_once (DIR_FS_CATALOG.'includes/classes/validpass.php');

  // This function makes a new password from a plaintext password. 
  function xtc_encrypt_password($plain) {

    // init class
    $validpass = new validpass();
    // encrypt password
    $encrypted = $validpass->encrypt_password($plain);

    return $encrypted;
  }
?>