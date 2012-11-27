<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_create_random_value.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   portions from
   (c) 2004-2006 Portable PHP password hashing framework, http://www.openwall.com/phpass/

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
// include needed functions

  function xtc_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) $type = 'mixed'; //use "mixed" instead of returning nothing

    $rand_value = '';

//BOF - DokuMan - 2012-11-27 - the usage of mt_rand() is replaced with Phpass' better random bytes generator function
/*
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = mt_rand(0,9);
      } else {
        $char = chr(mt_rand(0,255));
      }
      if ($type == 'mixed') {
        //if (preg_match('/^[a-z0-9]$/i', $char)) $rand_value .= $char; // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3
        if (ctype_alnum($char)) $rand_value .= $char; // DokuMan - 2011-07-26 - Change regex to faster ctype functions
      } elseif ($type == 'chars') {
        //if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char; // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3
        if (ctype_alpha($char)) $rand_value .= $char; // DokuMan - 2011-07-26 - Change regex to faster ctype functions
      } elseif ($type == 'digits') {
        if (preg_match('/^[0-9]$/', $char)) $rand_value .= $char; // Hetfield - 2009-08-19 - replaced deprecated function ereg with preg_match to be ready for PHP >= 5.3
      }
    }
    return $rand_value;
*/

    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $digits = '0123456789';
    $base = '';

    if ( ($type == 'mixed') || ($type == 'chars') ) {
      $base .= $chars;
    }

    if ( ($type == 'mixed') || ($type == 'digits') ) {
      $base .= $digits;
    }

    do {
      $random = base64_encode(get_random_bytes($length));

      for ($i = 0, $n = strlen($random); $i < $n; $i++) {
        $char = substr($random, $i, 1);
        if ( strpos($base, $char) !== false ) {
          $rand_value .= $char;
        }
      }
    } while ( strlen($rand_value) < $length );

    if ( strlen($rand_value) > $length ) {
      $rand_value = substr($rand_value, 0, $length);
    }

    return $rand_value;
  }


  function get_random_bytes($count) {
    $output = '';
    if (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
      if (function_exists('stream_set_read_buffer')) {
        stream_set_read_buffer($fh, 0);
      }
      $output = fread($fh, $count);
      fclose($fh);

    } elseif ( function_exists('openssl_random_pseudo_bytes') ) {
      $output = openssl_random_pseudo_bytes($count, $orpb_secure);
      if ( $orpb_secure != true ) {
        $output = '';
      }

    } elseif (defined('MCRYPT_DEV_URANDOM')) {
      $output = mcrypt_create_iv($count, MCRYPT_DEV_URANDOM);
    }

    //fallback scenario is none of the above is working or generated string is too small
    if (strlen($output) < $count) {
      $output = '';
      for ($i = 0; $i < $count; $i += 16) {
        $this->random_state = md5(microtime() . $this->random_state);
        $output .= pack('H*', md5($this->random_state));
      }
      $output = substr($output, 0, $count);
    }

    return $output;
  }
?>