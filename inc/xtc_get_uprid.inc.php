<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_get_uprid.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_uprid.inc.php 899)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Return a product ID with attributes

  function xtc_get_uprid($prid, $params) {
  if (is_numeric($prid)) {
    $uprid = (int)$prid; //int-typecasting

    if (is_array($params) && (sizeof($params) > 0)) {
      $attributes_check = true;
      $attributes_ids = '';

      reset($params);
      foreach($params as $option => $value) { //Dokuman - 2011-07-26 - Change while with foreach for performance
        if (is_numeric($option) && is_numeric($value)) {
          $attributes_ids .= '{' . (int)$option . '}' . (int)$value;
        } else {
          $attributes_check = false;
          break;
        }
      }

      if ($attributes_check == true) {
        $uprid .= $attributes_ids;
      }
    }
  } else {
    $uprid = xtc_get_prid($prid);

    if (is_numeric($uprid)) {
      if (strpos($prid, '{') !== false) {
        $attributes_check = true;
        $attributes_ids = '';

        $attributes = explode('{', substr($prid, strpos($prid, '{')+1));

        //BOF - DokuMan - 2011-12-19 - precount for performance
        //for ($i=0, $n=sizeof($attributes); $i<$n; $i++) {
        $n=sizeof($attributes);
        for ($i=0; $i<$n; $i++) {
        //EOF - DokuMan - 2011-12-19 - precount for performance
          $pair = explode('}', $attributes[$i]);

          if (is_numeric($pair[0]) && is_numeric($pair[1])) {
            $attributes_ids .= '{' . (int)$pair[0] . '}' . (int)$pair[1];
          } else {
            $attributes_check = false;
            break;
          }
        }

        if ($attributes_check == true) {
          $uprid .= $attributes_ids;
        }
      }
    } else {
      return false;
    }
  }

  return $uprid;
}
?>