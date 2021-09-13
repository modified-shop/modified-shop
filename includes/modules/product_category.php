<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once (DIR_FS_INC.'get_pictureset_data.inc.php');

$module_smarty = new Smarty;
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache = false;
  $module_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache = true;
  $module_smarty->caching = 1;
  $module_smarty->cache_lifetime = CACHE_LIFETIME;
  $module_smarty->cache_modified_check = CACHE_CHECK == 'true';
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|pID:'.$product->data['products_id'].'|cID:'.$current_category_id.'|curr:'.$_SESSION['currency'].'|country:'.((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)));
}

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/products_category.html', $cache_id) || !$cache) {
  $module_content = array();
  if (isset($current_category_id)) {
    $products_category_query = "SELECT * 
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON p.products_id = pd.products_id
                                          AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                          AND trim(pd.products_name) != ''
                                  JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                       ON p.products_id = p2c.products_id
                                          AND p2c.categories_id = '".(int)$current_category_id."'
                                  JOIN ".TABLE_CATEGORIES." c
                                       ON p2c.categories_id = c.categories_id
                                          AND c.categories_status = 1
                                              ".CATEGORIES_CONDITIONS_C."
                             LEFT JOIN ".TABLE_MANUFACTURERS." m
                                       ON p.manufacturers_id = m.manufacturers_id
                                 WHERE p.products_status = '1'
                                   AND p.products_id != '".$product->data['products_id']."'
                                       ".PRODUCTS_CONDITIONS_P."
                              GROUP BY p.products_id
                              ORDER BY MD5(CONCAT(p.products_id, CURRENT_TIMESTAMP)) 
                                 LIMIT ".MAX_DISPLAY_PRODUCTS_CATEGORY;

    $products_category_query = xtDBquery($products_category_query);
    while ($products_category = xtc_db_fetch_array($products_category_query, true)) {
      $module_content[] = $product->buildDataArray($products_category);
    }
  }

  if (count($module_content) > 0) {
    $module_smarty->assign('module_content', $module_content);

    if (defined('PICTURESET_BOX')) {
      $module_smarty->assign('pictureset_box', get_pictureset_data(PICTURESET_BOX));
    }
    if (defined('PICTURESET_ROW')) {
      $module_smarty->assign('pictureset_row', get_pictureset_data(PICTURESET_ROW));
    }
  }
}

$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_category.html', $cache_id);
$info_smarty->assign('MODULE_products_category', $module);
