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
   
	function xtc_get_attributes_model($product_id, $attribute_name,$options_name,$language='')
    {
	if ($language=='') $language=(int)$_SESSION['languages_id'];
	//BOF - Hetfield - 2009-08-11 - BUGFIX: #0000211 wrong modelnumbers on atrributes
	$options_value_id_query=xtc_db_query("SELECT
		pa.attributes_model
		FROM
		".TABLE_PRODUCTS_ATTRIBUTES." pa
		INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON po.products_options_id = pa.options_id
		INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON pa.options_values_id = pov.products_options_values_id
		WHERE
		po.language_id = '".xtc_db_input((int)$language)."' AND
		pa.products_id = '".xtc_db_input((int)$product_id)."' AND		
		po.products_options_name = '".xtc_db_input($options_name)."' AND
		pov.language_id = '".xtc_db_input((int)$language)."' AND
		pov.products_options_values_name = '".xtc_db_input($attribute_name)."' AND 
		pa.products_id = '".xtc_db_input((int)$product_id)."'");
	//EOF - Hetfield - 2009-08-11 - BUGFIX: #0000211 wrong modelnumbers on atrributes
    $options_attr_data = xtc_db_fetch_array($options_value_id_query);
    return $options_attr_data['attributes_model'];	
    	
    }
?>