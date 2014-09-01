<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_products_mo_images.inc.php 4217 2013-01-11 09:38:42Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2004 XT-Commerce
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
	function xtc_get_products_mo_images($products_id = '') {
   	$mo_query = "select * from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . $products_id ."' ORDER BY image_nr";
   	$products_mo_images_query = xtDBquery($mo_query);
   
   	$results = array();
		while ($row = xtc_db_fetch_array($products_mo_images_query,true)) {
			$results[($row['image_nr']-1)] = $row;
		}
		if (sizeof($results)>0) {
			 return $results;
		} else {
			 return false;
		}
	}
?>