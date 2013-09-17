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
$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
$result = true;

// include needed functions
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
require_once (DIR_FS_INC . 'xtc_hide_session_id.inc.php');

// fsk18 lock
$fsk_lock = '';
if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
  $fsk_lock = ' AND p.products_fsk18!=1';
}
// group check
$group_check = '';
if (GROUP_CHECK == 'true') {
  $group_check = " AND p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
}

$listing_sql = "SELECT p.*,
                       pd.products_name,
                       pd.products_description,
                       pd.products_short_description
                  FROM ".TABLE_PRODUCTS." p
                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                    ON p.products_id = pd.products_id AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                 WHERE p.products_status = '1'
                   AND p.products_id IN ('".$products_id."')   
                       ".$group_check."
                       ".$fsk_lock;

$listing_query = xtc_db_query($listing_sql);
$module_content = array ();
$category = array();

if (xtc_db_num_rows($listing_query) > 0) {

  $rows = 0;
  while ($listing = xtc_db_fetch_array($listing_query, true)) {
    $rows ++;
    $module_content[] =  $product->buildDataArray($listing);
  }
} else {
  // no product found
  $result = false;
}

if ($result != false) {
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
  $error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
}
?>
