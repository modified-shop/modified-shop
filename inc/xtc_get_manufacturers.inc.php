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
   (c) 2003	 nextcommerce (xtc_get_manufacturers.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_manufacturers($manufacturers_array = '', $manufacturers_id = null) {
    static $manufacturers_cache = array();

    // the complete list is kept under key 0, a single manufacturer under its own id
    if ($manufacturers_id !== null) {
      $manufacturers_id = (int)$manufacturers_id;
      if ($manufacturers_id < 1) return array();
    } else {
      $manufacturers_id = 0;
    }

    if ($manufacturers_array == '') {
      if (isset($manufacturers_cache[$manufacturers_id])) return $manufacturers_cache[$manufacturers_id];

      // no second query when the complete list has already been loaded
      if ($manufacturers_id > 0 && isset($manufacturers_cache[0])) {
        return (isset($manufacturers_cache[0][$manufacturers_id])
                ? array($manufacturers_id => $manufacturers_cache[0][$manufacturers_id])
                : array());
      }
    }

    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $conditions = array();
    if (!defined('RUN_MODE_ADMIN')) {
      $conditions[] = "m.manufacturers_status = 1";
    }
    if ($manufacturers_id > 0) {
      $conditions[] = "m.manufacturers_id = '" . $manufacturers_id . "'";
    }

    $manufacturers_query = xtDBquery("SELECT *
                                        FROM " . TABLE_MANUFACTURERS . " m
                                   LEFT JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                                             ON m.manufacturers_id = mi.manufacturers_id
                                                AND mi.languages_id = '" . (int)$_SESSION['languages_id'] . "'
                                             " . ((count($conditions) > 0) ? " WHERE " . implode(" AND ", $conditions) . " " : "") . "
                                    ORDER BY m.sort_order, m.manufacturers_name");
    while ($manufacturers = xtc_db_fetch_array($manufacturers_query, true)) {
      $manufacturers['manufacturers_image'] = str_replace('manufacturers/', '', $manufacturers['manufacturers_image']);
      $manufacturers_array[$manufacturers['manufacturers_id']] = $manufacturers;
      
      // dropdown
      $manufacturers_array[$manufacturers['manufacturers_id']]['id'] = $manufacturers['manufacturers_id'];
      $manufacturers_array[$manufacturers['manufacturers_id']]['text'] = $manufacturers['manufacturers_name'];
    }
    $manufacturers_cache[$manufacturers_id] = $manufacturers_array;
    
    return $manufacturers_array;
  }
