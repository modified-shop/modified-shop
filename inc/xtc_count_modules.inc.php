<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_count_modules.inc.php 2531 2011-12-19 15:02:34Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_count_modules.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_count_modules.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_count_modules($modules = '') {
  $count = 0;
  if (empty($modules)) return $count;

  $modules_array = explode(';', $modules); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3

  //BOF - DokuMan - 2011-12-19 - precount for performance
  //for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
  $n=sizeof($modules_array);
  for ($i=0; $i<$n; $i++) {
  //EOF - DokuMan - 2011-12-19 - precount for performance
    $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

    //BOF - DokuMan - 2010-08-24 - set undefined index
    //if (is_object($GLOBALS[$class])) {
    if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
    //EOF - DokuMan - 2010-08-24 - set undefined index
      if ($GLOBALS[$class]->enabled) {
        $count++;
      }
    }
  }

  return $count;
}
?>