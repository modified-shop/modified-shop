<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_manufacturers.inc.php 899 2005-04-29 02:40:57Z hhgag $   

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
   
  function xtc_get_manufacturers($manufacturers_array = '') {
    //BOF - web28 - 2011-01-20 - set manufacturers cache for better performance - thanks to DocBobo
    static $manufacturers_cache;
    
    if (isset ($manufacturers_cache) && $manufacturers_array == '') return $manufacturers_cache;
    //BOF - web28 - 2011-01-20 - set manufacturers cache for better performance - thanks to DocBobo
    
    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $manufacturers_query = xtc_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
    while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
    }
    
    //BOF - web28 - 2011-01-20 - set manufacturers cache for better performance - thanks to DocBobo
    $manufacturers_cache = $manufacturers_array;
    //BOF - web28 - 2011-01-20 - set manufacturers cache for better performance - thanks to DocBobo
    
    return $manufacturers_array;
  }
 ?>