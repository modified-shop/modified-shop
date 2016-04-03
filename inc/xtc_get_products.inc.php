<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_products.inc.php 3072 2012-06-18 15:01:13Z hhacker $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_address_format.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_CATALOG.'includes/classes/xtcPrice.php');   

function xtc_get_products($session) {
  if (!is_array($session)) return false;
  $products_array = array();
  reset($session);
  if (is_array($session['cart']->contents)) {     
      while (list($products_id, ) = each($session['cart']->contents)) {
        $products_query = xtc_db_query("SELECT p.products_id, 
                                               pd.products_name,
                                               p.products_image, 
                                               p.products_model, 
                                               p.products_price, 
                                               p.products_discount_allowed, 
                                               p.products_weight, 
                                               p.products_tax_class_id 
                                          FROM " . TABLE_PRODUCTS . " p
                                          JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd 
                                               ON pd.products_id = p.products_id 
                                                  AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                         WHERE p.products_id='" . xtc_get_prid($products_id) . "'");
        if ($products = xtc_db_fetch_array($products_query)) {

          // dirty workaround
          $xtPrice = new xtcPrice($session['currency'], $session['customers_status']['customers_status_id']);
          $products_price=$xtPrice->xtcGetPrice($products['products_id'], false, $session['cart']->contents[$products_id]['qty'], $products['products_tax_class_id'], $products['products_price']);

          $products_array[] = array('id' => $products['products_id'],
                                    'name' => $products['products_name'],
                                    'model' => $products['products_model'],
                                    'image' => $products['products_image'],
                                    'price' => $products_price+attributes_price($products['products_id'], $session),
                                    'quantity' => $session['cart']->contents[$products['products_id']]['qty'],
                                    'weight' => $products['products_weight'],
                                    'final_price' => ($products_price+attributes_price($products['products_id'], $session)),
                                    'tax_class_id' => $products['products_tax_class_id'],
                                    'attributes' => $session['contents'][$products['products_id']]['attributes']);
        }
      }

      return $products_array;
  }
  return false;
}
    
function attributes_price($products_id,$session) {
  $attributes_price = 0;

  $xtPrice = new xtcPrice($session['currency'],$session['customers_status']['customers_status_id']);
  if (isset($session['contents'][$products_id]['attributes'])) {
    reset($session['contents'][$products_id]['attributes']);
    while (list($option, $value) = each($session['contents'][$products_id]['attributes'])) {
      $attribute_price_query = xtc_db_query("SELECT pd.products_tax_class_id, 
                                                    p.options_values_price, 
                                                    p.price_prefix 
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . " p
                                               JOIN " . TABLE_PRODUCTS . " pd 
                                                    ON pd.products_id = p.products_id
                                              where p.products_id = '" . (int)$products_id . "' 
                                                AND p.options_id = '" . (int)$option . "' 
                                                AND p.options_values_id = '" . (int)$value . "'");
      $attribute_price = xtc_db_fetch_array($attribute_price_query);
      if ($attribute_price['price_prefix'] == '+') {
        $attributes_price += $xtPrice->xtcFormat($attribute_price['options_values_price'],false,$attribute_price['products_tax_class_id']);
      } else {
        $attributes_price -= $xtPrice->xtcFormat($attribute_price['options_values_price'],false,$attribute_price['products_tax_class_id']);
      }
    }
  }
  return $attributes_price;
}
?>