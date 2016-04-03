<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$module_content = array ();
if (isset($current_category_id)) {

  $products_category_query = "SELECT * 
                                FROM ".TABLE_PRODUCTS." p
                           LEFT JOIN ".TABLE_MANUFACTURERS." m
                                     ON p.manufacturers_id = m.manufacturers_id
                                JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                     ON p.products_id = pd.products_id
                                        AND trim(pd.products_name) != ''
                                        AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                     ON p.products_id = p2c.products_id
                                        AND p2c.categories_id = '".(int)$current_category_id."'
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

if (sizeof($module_content) >= 1) {
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $module_content);

  // set cache ID
   if (!CacheCheck()) {
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_category.html');
  } else {
    $module_smarty->caching = 1;
    $module_smarty->cache_lifetime = CACHE_LIFETIME;
    $module_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = md5($product->data['products_id'].$current_category_id.$_SESSION['language'].$_SESSION['customers_status']['customers_status_name'].$_SESSION['currency']);
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_category.html', $cache_id);
  }
  $info_smarty->assign('MODULE_products_category', $module);
}
?>