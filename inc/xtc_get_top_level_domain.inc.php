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
   (c) 2003	 nextcommerce (xtc_get_top_level_domain.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

function xtc_get_top_level_domain($url) {
  // set array
  $return_array = array(
    'old' => '', 
    'new' => get_cookie_domain($url)
  );

  if (strpos($url, '://')) {
    $url = parse_url($url);
    $url = $url['host'];
  }
  $domain_array = explode('.', $url);
  $domain_size = sizeof($domain_array);
  if ($domain_size > 1) {
    if (!is_numeric($domain_array[$domain_size -2]) && !is_numeric($domain_array[$domain_size -1])) {

      // old routine
      $return_array['old'] = $domain_array[$domain_size - 2] . '.' . $domain_array[$domain_size - 1];
      $domain_path = $url;
      if(substr($domain_path, 0, 4) == 'www.') {
          $domain_path = substr($domain_path, 4);
      }
      $return_array['old'] = $domain_path;
      
      // new routine
      if ($return_array['new'] === false) {
        $domain_path = $url;
        if(substr($domain_path, 0, 4) == 'www.') {
            $domain_path = substr($domain_path, 4);
        }
        $return_array['new'] = $domain_path;
      }
    }
  }
  
  return $return_array;
}

function get_cookie_domain($url) {
  $url_array = parse_url($url);
  $domain = $url_array['host'];
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  return false;
}


/*
 * new function from noRiddle - http://www.revilonetz.de
 * use this function for setting cookie to specific domain
 *
 
function xtc_get_top_level_domain($url) {
    if (strpos($url, '://')) {
        $url = parse_url($url);
        $url = $url['host'];
    }
    $domain_array = explode('.', $url);
    $domain_size = sizeof($domain_array);
    if ($domain_size > 1) {
        if (is_numeric($domain_array[$domain_size -2]) && is_numeric($domain_array[$domain_size -1])) {
            return false;
        } else {
            if($domain_size == 4) {
                return $domain_array[$domain_size - 4] . '.' . $domain_array[$domain_size - 3] . '.' . $domain_array[$domain_size - 2] . '.' . $domain_array[$domain_size - 1];
            } elseif($domain_size == 3) {
                return $domain_array[$domain_size - 3] . '.' . $domain_array[$domain_size - 2] . '.' . $domain_array[$domain_size - 1];
            } elseif($domain_size == 2) {
                return $domain_array[$domain_size - 2] . '.' . $domain_array[$domain_size - 1];
            }
            //whole if-else-clause is the same as return $url;
        }
    } else {
        return false;
    }
}
*/
?>