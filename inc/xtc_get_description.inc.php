<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_products_name.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_get_description($product_id, $language = '', $shorten = false) {

    if (empty($language)) $language = $_SESSION['languages_id'];

    $product_query = "SELECT products_description 
                        FROM " . TABLE_PRODUCTS_DESCRIPTION . " 
                       WHERE products_id = '" . (int)$product_id . "' 
                         AND language_id = '" . (int)$language . "'";
    $product_query  = xtDBquery($product_query);
    $product = xtc_db_fetch_array($product_query,true);
    
    if ($shorten === true 
        && defined('CHECKOUT_USE_PRODUCTS_DESCRIPTION_FALLBACK_LENGTH') 
        && CHECKOUT_USE_PRODUCTS_DESCRIPTION_FALLBACK_LENGTH > 0
        ) 
    {
      return preg_replace("/[^ ]*$/", '', substr(strip_tags($product['products_description']), 0, CHECKOUT_USE_PRODUCTS_DESCRIPTION_FALLBACK_LENGTH)) . ' [...]';
    }
    
    return $product['products_description'];
  }
 ?>