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
   (c) 2003	 nextcommerce (xtc_get_address_format_id.inc.php,v 1.3 2003/08/13); www.nextcommerce.org 
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_address_format_id($country_id) {
    $address_format_query = xtc_db_query("select address_format_id as format_id from " . TABLE_COUNTRIES . " where countries_id = '" . $country_id . "'");
    if (xtc_db_num_rows($address_format_query)) {
      $address_format = xtc_db_fetch_array($address_format_query);
      return $address_format['format_id'];
    } else {
      return '1';
    }
  }
 ?>