<?php
/* ----------------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 xtCommerce - www.xt-commerce.de

   Released under the GNU General Public License
   --------------------------------------------------------------------------------------------*/

$module_smarty = new Smarty();
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
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id'].'|pID:'.$product->data['products_id'].'|cID:'.$current_category_id);
}

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/product_navigator.html', $cache_id) || !$cache) {  
  if (isset($current_category_id) && (int)$current_category_id > 0) {
    $actual_key = 0;
    $sorting_data = array(
      'products_sorting' => '',
      'products_sorting2' => '',
    );
  
    $sorting_query = xtDBquery("SELECT products_sorting,
                                       products_sorting2
                                  FROM ".TABLE_CATEGORIES."
                                 WHERE categories_id = '".$current_category_id."'");
    if (xtc_db_num_rows($sorting_query, true) > 0) {
      $sorting_data = xtc_db_fetch_array($sorting_query, true);
    }

    //Fallback for products_sorting to products_name
    if (empty($sorting_data['products_sorting'])) { 
      $sorting_data['products_sorting'] = 'pd.products_name';
    }

    //Fallback for products_sorting2 to ascending
    if (empty($sorting_data['products_sorting2'])) { 
      $sorting_data['products_sorting2'] = 'ASC';
    }
    $sorting = ' ORDER BY '.$sorting_data['products_sorting'].' '.$sorting_data['products_sorting2'].' ';

    $products_query = xtDBquery("SELECT p2c.products_id,
                                        pd.products_name,
                                        p.products_image
                                   FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                   JOIN ".TABLE_PRODUCTS." p
                                        ON p.products_id = p2c.products_id
                                           AND p.products_status = '1'
                                   JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                        ON p.products_id = pd.products_id
                                           AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                           AND trim(pd.products_name) != ''
                                  WHERE p2c.categories_id='".(int)$current_category_id."'
                                        ".PRODUCTS_CONDITIONS_P."
                                        ".$sorting);

    $i = 0;
    $p_data = array();
    while ($products_data = xtc_db_fetch_array($products_query, true)) {
      $p_data[$i] = array (
        'ID' => $products_data['products_id'], 
        'NAME' => $products_data['products_name'], 
        'IMAGE' => $products_data['products_image'],
        'LINK' => '',
      );
      if ($products_data['products_id'] == $product->data['products_id']) {
        $actual_key = $i;
      }
      $i ++;
    }

    $navigator_array = array(
      'overview' => array('LINK' => xtc_href_link(FILENAME_DEFAULT, xtc_category_link($current_category_id))),
      'first' => array('LINK' => ''),
      'prev' => array('LINK' => ''),
      'actual' => $p_data[$actual_key],
      'next' => array('LINK' => ''),
      'last' => array('LINK' => ''),
    );
    if ($actual_key != 0 && ($actual_key - 1) != 0) {
      $navigator_array['first'] = $p_data[0];
      $navigator_array['first']['LINK'] = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($navigator_array['first']['ID'], $navigator_array['first']['NAME']));
      $navigator_array['first']['IMAGE'] = $product->productImage($navigator_array['first']['IMAGE'], 'thumbnail');
    }
    if (isset($p_data[$actual_key - 1])) {
      $navigator_array['prev'] = $p_data[$actual_key - 1];
      $navigator_array['prev']['LINK'] = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($navigator_array['prev']['ID'], $navigator_array['prev']['NAME']));
      $navigator_array['prev']['IMAGE'] = $product->productImage($navigator_array['prev']['IMAGE'], 'thumbnail');
    }
    if (isset($p_data[$actual_key + 1])) {
      $navigator_array['next'] = $p_data[$actual_key + 1];
      $navigator_array['next']['LINK'] = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($navigator_array['next']['ID'], $navigator_array['next']['NAME']));
      $navigator_array['next']['IMAGE'] = $product->productImage($navigator_array['next']['IMAGE'], 'thumbnail');
    }
    if ($actual_key != (count($p_data) - 1) && $actual_key != (count($p_data) - 2)) {
      $navigator_array['last'] = $p_data[count($p_data) - 1];
      $navigator_array['last']['LINK'] = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($navigator_array['last']['ID'], $navigator_array['last']['NAME']));
      $navigator_array['last']['IMAGE'] = $product->productImage($navigator_array['last']['IMAGE'], 'thumbnail');
    }

    $module_smarty->assign('module_content', $navigator_array);
    $module_smarty->assign('FIRST', $navigator_array['first']['LINK']);
    $module_smarty->assign('PREVIOUS', $navigator_array['prev']['LINK']);
    $module_smarty->assign('OVERVIEW', $navigator_array['overview']['LINK']);
    $module_smarty->assign('NEXT', $navigator_array['next']['LINK']);
    $module_smarty->assign('LAST', $navigator_array['last']['LINK']);
    $module_smarty->assign('ACTUAL_PRODUCT', $actual_key +1);
    $module_smarty->assign('PRODUCTS_COUNT', count($p_data));
  }
}

$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_navigator.html', $cache_id);
$info_smarty->assign('PRODUCT_NAVIGATOR', !empty($module) ? trim($module) : $module);
