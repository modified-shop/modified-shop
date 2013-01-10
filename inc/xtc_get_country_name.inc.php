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
   (c) 2003	 nextcommerce (xtc_get_country_name.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_country_name.inc.php 899 2005-04-29)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  require_once(DIR_FS_INC . 'xtc_get_countries.inc.php');
  
  function xtc_get_country_name($country_id) {
    if ($country_id == 0) return ''; //DokuMan - 2010-08-24 - return when no country set
    $country_array = xtc_get_countriesList($country_id);

    return $country_array['countries_name'];
  }
?>