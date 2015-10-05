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
                          
$tags_query = xtDBquery("SELECT pto.options_id,
                                pto.options_name,
                                pto.options_description,
                                pto.options_content_group,
                                ptv.values_id,
                                ptv.values_name,
                                ptv.values_description,
                                ptv.values_image,
                                ptv.values_content_group
                           FROM ".TABLE_PRODUCTS_TAGS." pt
                           JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                ON pt.options_id = pto.options_id
                                   AND pto.status = '1'
                                   AND pto.languages_id = '".(int)$_SESSION['languages_id']."'
                           JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                ON ptv.values_id = pt.values_id
                                   AND ptv.status = '1'
                                   AND ptv.languages_id = '".(int)$_SESSION['languages_id']."'
                          WHERE pt.products_id = '".$product->data['products_id']."'
                       ORDER BY pto.sort_order, ptv.sort_order");

if (xtc_db_num_rows($tags_query, true) > 0) {
  while ($tags = xtc_db_fetch_array($tags_query, true)) {
    if (!isset($module_content[$tags['options_id']])) {
      $module_content[$tags['options_id']] = array('OPTIONS_NAME' => $tags['options_name'],
                                                   'OPTIONS_DESCRIPTION' => $tags['options_description'],
                                                   'OPTIONS_CONTENT_LINK' => (($tags['options_content_group'] != '') ? xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.$tags['options_content_group'], 'NONSSL') : ''),
                                                   'DATA' => array());
    }
    $module_content[$tags['options_id']]['DATA'][] = array('VALUES_NAME' => $tags['values_name'],
                                                           'VALUES_DESCRIPTION' => $tags['values_description'],
                                                           'VALUES_IMAGE' => (($tags['values_image'] != '' && is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.$tags['values_image'])) ? DIR_WS_BASE.DIR_WS_IMAGES.$tags['values_image'] : ''),
                                                           'VALUES_CONTENT_LINK' => (($tags['values_content_group'] != '') ? xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.$tags['values_content_group'], 'NONSSL') : ''),
                                                           );
  } 
}
                     

if (sizeof($module_content) >= 1) {
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $module_content);

  // set cache ID
   if (!CacheCheck()) {
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_tags.html');
  } else {
    $module_smarty->caching = 1;
    $module_smarty->cache_lifetime = CACHE_LIFETIME;
    $module_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $product->data['products_id'].$_SESSION['language'].$_SESSION['customers_status']['customers_status_name'].$_SESSION['currency'];
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_tags.html', $cache_id);
  }
  $info_smarty->assign('MODULE_product_tags', $module);
}
?>