<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_random_select.inc.php 1108 2005-07-24 20:24:08Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_random_select.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_random_select($query) {
    $random_product = '';
    $random_query = xtc_db_query($query);
    $num_rows = xtc_db_num_rows($random_query);
    if ($num_rows > 0) {
      $random_row = xtc_rand(0, ($num_rows - 1));
      xtc_db_data_seek($random_query, $random_row);
      $random_product = xtc_db_fetch_array($random_query);
    }

    return $random_product;
  }
 ?>