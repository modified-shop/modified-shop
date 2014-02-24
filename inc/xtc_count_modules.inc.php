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

  $modules_array = explode(';', $modules);

  for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
    $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));
    if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
      if ($GLOBALS[$class]->enabled) {
        $count++;
      }
    }
  }

  return $count;
}
?>