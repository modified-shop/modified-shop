<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_attributes_model.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_get_attributes_model.inc.php,v 1.1 2003/08/19); www.nextcommerce.org
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
	function xtc_get_attributes_model($product_id, $attribute_name, $options_name, $language='') {
	  if ($language == '') $language = $_SESSION['languages_id'];

  	$options_value_id_query=xtc_db_query("SELECT pa.attributes_model
		                                        FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
		                                  INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po 
		                                             ON po.products_options_id = pa.options_id
		                                                AND po.language_id = '".(int)$language."'
		                                                AND po.products_options_name = '".xtc_db_input($options_name)."'
		                                  INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov 
		                                             ON pa.options_values_id = pov.products_options_values_id
		                                                AND pov.language_id = '".(int)$language."'
		                                                AND pov.products_options_values_name = '".xtc_db_input($attribute_name)."'
		                                       WHERE pa.products_id = '".(int)$product_id."' 
		                                         AND pa.products_id='".(int)$product_id."'");
    $options_attr_data = xtc_db_fetch_array($options_value_id_query);
    
    return $options_attr_data['attributes_model'];
  }
?>