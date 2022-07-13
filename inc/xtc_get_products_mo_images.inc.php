<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2004 XT-Commerce
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
	function xtc_get_products_mo_images($products_id = '', $language_id = '') {
	  if ($language_id == '') $language_id = $_SESSION['languages_id'];
	  
   	$products_mo_images_query = xtDBquery("SELECT * 
                                             FROM ".TABLE_PRODUCTS_IMAGES." pi
                                        LEFT JOIN ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pid
                                                  ON pi.image_id = pid.image_id
                                                     AND language_id = '".(int)$language_id ."'
                                            WHERE pi.products_id = '".(int)$products_id."' 
                                         ORDER BY pi.image_nr");
   	$more_images_array = array();
		while ($products_mo_images = xtc_db_fetch_array($products_mo_images_query,true)) {
			$more_images_array[($products_mo_images['image_nr'] - 1)] = $products_mo_images;
		}
		
		if (count($more_images_array) > 0) {
			return $more_images_array;
		} else {
		  return false;
		}
	}
