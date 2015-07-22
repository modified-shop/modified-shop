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

$plain_content = array();
$module_content = array();

$listing_sql = "SELECT p.*,
                       pd.products_name,
                       pd.products_description,
                       pd.products_short_description
                  FROM ".TABLE_PRODUCTS." p
                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                       ON p.products_id = pd.products_id 
                          AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                 WHERE p.products_status = '1'
                   AND p.products_id IN ('".$products_id."')   
                       ".PRODUCTS_CONDITIONS_P;

$listing_query = xtc_db_query($listing_sql);

$result = false;
if (xtc_db_num_rows($listing_query) > 0) {
  while ($listing = xtc_db_fetch_array($listing_query, true)) {
    $plain_content[$listing['products_id']] =  $product->buildDataArray($listing);
  }
  $result = true;

  // SORT
  $sorting_id = explode("', '", $products_id);
  foreach ($sorting_id as $key) {
    $module_content[] = $plain_content[$key];
  }
  unset($plain_content);
}

if (($count_module = count($module_content)) != ($count_result = count($product_id_array))) {
  $empty = array('products_image' => 'no_image.gif',
                 'products_name' => 'Artikel existiert nicht mehr');
  for ($i=$count_module; $i<$count_result; $i++) {
    $module_content[] = $product->buildDataArray($empty);
  }
  $result = true;
}


if ($result === true) {
  $files = array ();
  if ($dir = opendir(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_listing/')) {
    while (($file = readdir($dir)) !== false) {
      if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_listing/'.$file) and (substr($file, -5) == ".html") and ($file != "index.html") and (substr($file, 0, 1) !=".")) {
        $files[] = $file;
      }
    }
    closedir($dir);
  }
  sort($files);

  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $module_content);
  $module_smarty->assign('CATEGORIES_NAME', TEXT_SEARCH_TERM . strip_tags($_GET['keywords']));
  
  $module_smarty->caching = 0;
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_listing/'.$files[0]);
  $smarty->assign('main_content', $module);
} else {
  $site_error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
}
?>