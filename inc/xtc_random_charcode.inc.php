<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2004 XT-Commerce
   -----------------------------------------------------------------------------------------
   by Guido Winger for XT:Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // include needed functions
  require_once(DIR_FS_INC . 'xtc_rand.inc.php');

  // build to generate a random charcode
  function xtc_random_charcode($length, $strict = false) {
    $chars = array('A','B','C','D','E','F','G','H','K','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','2','3','4','5','6','8','9');

    $code = '';
    if (function_exists("random_bytes") && $strict === false) {
      $bytes = random_bytes($length);
      $code = substr(bin2hex($bytes), 0, $length);
    } elseif (function_exists("openssl_random_pseudo_bytes") && $strict === false) {
      $bytes = openssl_random_pseudo_bytes($length);
      $code = substr(bin2hex($bytes), 0, $length);
    } else {
      for ($i = 1; $i <= $length; $i++) {
        $j = floor(xtc_rand(0, count($chars)));
        $code .= $chars[$j];
      }
    }
    
    return  $code;
  }
?>