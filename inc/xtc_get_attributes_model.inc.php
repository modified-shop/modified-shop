<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_get_attributes_model.inc.php,v 1.1 2003/08/19); www.nextcommerce.org
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
	function xtc_get_attributes_model($products_id, $options_values_name, $options_name, $language_id = '') {
	  if ($language_id == '') $language_id = $_SESSION['languages_id'];

  	$attributes_query = xtc_db_query("SELECT pa.attributes_model
                                        FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                  INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po 
                                             ON po.products_options_id = pa.options_id
                                                AND po.language_id = '".(int)$language_id."'
                                                AND po.products_options_name = '".xtc_db_input($options_name)."'
                                  INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov 
                                             ON pa.options_values_id = pov.products_options_values_id
                                                AND pov.language_id = '".(int)$language_id."'
                                                AND pov.products_options_values_name = '".xtc_db_input($options_values_name)."'
                                       WHERE pa.products_id = '".(int)$products_id."'");
   
    if (xtc_db_num_rows($attributes_query) > 0) {
      $attributes = xtc_db_fetch_array($attributes_query);
    
      return $attributes['attributes_model'];
    }
  }
