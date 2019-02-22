<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2003 nextcommerce; www.nextcommerce.org
   (c) 2005 xtCommerce (sitemap.php 1278 2005-10-02); www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new smarty;
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache=false;
  $module_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache=true;
  $module_smarty->caching = 1;
  $module_smarty->cache_lifetime = CACHE_LIFETIME;
  $module_smarty->cache_modified_check = CACHE_CHECK;
}

// include needed function
require_once(DIR_FS_INC . 'xtc_get_category_tree.inc.php');
require_once(DIR_FS_INC . 'xtc_count_products_in_category.inc.php');

$cache_id = md5($_SESSION['language'].$_SESSION['customers_status']['customers_status'].((isset($_REQUEST['error'])) ? $_REQUEST['error'] : ''));

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/sitemap.html', $cache_id) || !$cache) {
  
  $module_content = array();

  if (function_exists('xtc_get_category_tree_array')) {
    define('SITEMAP_CASE', 3);
    $module_content = xtc_get_category_tree_array();  
  } else {
    $categories_query = xtDBquery("SELECT c.categories_image, 
                                          c.categories_id, 
                                          cd.categories_name
                                     FROM " . TABLE_CATEGORIES . " c
                                     JOIN " . TABLE_CATEGORIES_DESCRIPTION ." cd 
                                          ON c.categories_id = cd.categories_id
                                             AND cd.language_id = ".(int)$_SESSION['languages_id']."
                                             AND trim(cd.categories_name) != ''
                                    WHERE c.categories_status = 1
                                      AND c.parent_id = '0'
                                          ".CATEGORIES_CONDITIONS_C."
                                 ORDER BY c.sort_order, cd.categories_name");

    while ($categories = xtc_db_fetch_array($categories_query,true)) {
      $module_content[] = array(
        'ID' => $categories['categories_id'],
        'CAT_NAME' => $categories['categories_name'],
        'CAT_IMAGE' => DIR_WS_IMAGES . 'categories/' . $categories['categories_image'],
        'CAT_LINK' => xtc_href_link(FILENAME_DEFAULT, xtc_category_link($categories['categories_id'], $categories['categories_name'])),
        'SCATS' => xtc_get_category_tree($categories['categories_id'], '', 0),
      );
    }
  }
    
  if (count($module_content) >= 1) {

    if (defined('SITEMAP_CASE')) {
      $categories_string = '';
      xtc_show_category(0, '', $module_content);
      $module_content = $categories_string; 
    }

    $module_smarty->assign('module_content', $module_content);
    
    $error = array(
      '400' => SITEMAP_ERROR_400,
      '401' => SITEMAP_ERROR_401,
      '403' => SITEMAP_ERROR_403,
      '404' => SITEMAP_ERROR_404,
      '500' => SITEMAP_ERROR_500,
    );    
    
    if (isset($_REQUEST['error']) && isset($error[$_REQUEST['error']])) {
      $module_smarty->assign('herror', $error[$_REQUEST['error']]);
      if ($_REQUEST['error'] == '404') {
        header('HTTP/1.1 404 Not Found');
      }
    }
  }
}

if (!$cache) {
  $module_smarty->display(CURRENT_TEMPLATE.'/module/sitemap.html');
} else {
  $module_smarty->display(CURRENT_TEMPLATE.'/module/sitemap.html', $cache_id);
}
?>