<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_create_random_value.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
// include needed functions

  function xtc_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
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
  }
?>