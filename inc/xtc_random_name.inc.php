<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (xtc_random_name.inc.php,v 1.1 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_random_name.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // Returns a random name, 16 to 20 characters long
  // There are more than 10^28 combinations
  // The directory is "hidden", i.e. starts with '.'
  function xtc_random_name() {
    $letters = 'abcdefghijklmnopqrstuvwxyz';
    $dirname = '.';
    $length = floor(mt_rand(16,20));
    for ($i = 1; $i <= $length; $i++) {
      $q = floor(mt_rand(1,26));
      $dirname .= $letters[$q];
    }
    return $dirname;
  }
 ?>