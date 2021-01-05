<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_rand.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_rand($min = null, $max = null) {
    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      }
    } else {
      $min = 0;
      $max = mt_getrandmax();
    }
    return crypto_rand_secure($min, $max);
  }

  function crypto_rand_secure($min, $max) {
    if (function_exists("random_int")) {
      return random_int($min, $max);
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
      $range = 1 + $max - $min;
      if ($range <= 0) return $min; // not so random...
      $log = log($range, 2);
      $bytes = (int) ($log / 8) + 1; // length in bytes
      $bits = (int) $log + 1; // length in bits
      $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
      do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
      } while ($rnd >= $range);
      
      return $min + $rnd;
    } else {
      return mt_rand($min, $max);
    }
  }
?>