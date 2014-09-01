<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_country_name.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_country_name.inc.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  require_once(DIR_FS_INC . 'xtc_get_countries.inc.php');
  function xtc_get_country_name($country_id) {
    $country_array = xtc_get_countriesList($country_id);

    return $country_array['countries_name'];
  }
 ?>
