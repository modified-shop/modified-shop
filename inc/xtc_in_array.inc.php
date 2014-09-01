<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_in_array.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.3 2002/08/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_in_array.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
 function xtc_in_array($value, $array) {
    if (!$array) $array = array();

    if (function_exists('in_array')) {
      if (is_array($value)) {
        for ($i=0; $i<sizeof($value); $i++) {
          if (in_array($value[$i], $array)) return true;
        }
        return false;
      } else {
        return in_array($value, $array);
      }
    } else {
      reset($array);
      while (list(,$key_value) = each($array)) {
        if (is_array($value)) {
          for ($i=0; $i<sizeof($value); $i++) {
            if ($key_value == $value[$i]) return true;
          }
          return false;
        } else {
          if ($key_value == $value) return true;
        }
      }
    }

    return false;
  }
 ?>