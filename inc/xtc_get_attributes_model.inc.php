<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_get_attributes_model.inc.php,v 1.1 2003/08/19); www.nextcommerce.org
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
	function xtc_get_attributes_model($product_id, $attribute_name,$options_name,$language='') {
    if ($language=='') $language=$_SESSION['languages_id'];
    //BOF - DokuMan - 2010-08-10 - removed double entry of pa.products_id and added (int)  
    //BOF - Tomcraft - 2009-10-06 - ADDED addslashes by $option_name, $attribute_name
    //BOF - Hetfield - 2009-08-11 - BUGFIX: #0000211 wrong modelnumbers on atrributes
    $options_value_id_query=xtc_db_query("
    SELECT pa.attributes_model
      FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
      INNER JOIN ".TABLE_PRODUCTS_OPTIONS." po ON po.products_options_id = pa.options_id
      INNER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." pov ON pa.options_values_id = pov.products_options_values_id
      WHERE pa.products_id=" . (int)$product_id . "
      AND po.language_id = ".(int)$language."
      AND po.products_options_name = '".addslashes($options_name)."'
      AND pov.language_id = ".(int)$language."
      AND pov.products_options_values_name = '".addslashes($attribute_name)."'");
    //EOF - Hetfield - 2009-08-11 - BUGFIX: #0000211 wrong modelnumbers on atrributes
    //EOF - Tomcraft - 2009-10-06 - ADDED addslashes by $option_name, $attribute_name
    //EOF - DokuMan - 2010-08-10 - removed double entry of pa.products_id and added (int)   
    $options_attr_data = xtc_db_fetch_array($options_value_id_query);
    return $options_attr_data['attributes_model'];	
}
?>