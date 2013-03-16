<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_listing.php 1286 2005-10-07 10:10:18Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_listing.php,v 1.42 2003/05/27); www.oscommerce.com
   (c) 2003  nextcommerce (product_listing.php,v 1.19 2003/08/1); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
$result = true;

// include needed functions
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
$listing_split = new splitPageResults($listing_sql, (isset($_GET['page']) ? (int)$_GET['page'] : 1), MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
$module_content = array ();
$category = array();

if ($listing_split->number_of_rows > 0) {

  $navigation = '
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="smallText">'.$listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS).'</td>
        <td class="smallText" align="right">'.TEXT_RESULT_PAGE.' '.$listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y', 'keywords')).(isset($_GET['keywords'])?'&keywords='. urlencode($_GET['keywords']):'')).'</td>
      </tr>
    </table>';
  $group_check = '';
  if (GROUP_CHECK == 'true') {
    $group_check = "and c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
  }

  $category_query = xtDBquery("SELECT cd.categories_description,
                                      cd.categories_name,
                                      cd.categories_heading_title,
                                      c.listing_template,
                                      c.categories_image
                                 FROM ".TABLE_CATEGORIES." c,
                                      ".TABLE_CATEGORIES_DESCRIPTION." cd
                                WHERE c.categories_id = '".$current_category_id."'
                                  AND cd.categories_id = '".$current_category_id."'
                                      ".$group_check."
                                  AND cd.language_id = '".$_SESSION['languages_id']."'
                                LIMIT 1");
  $category = xtc_db_fetch_array($category_query,true);
  $image = '';
  if ($category['categories_image'] != '') {
    $image = DIR_WS_IMAGES.'categories/'.$category['categories_image'];
    if(!file_exists($image)) $image = DIR_WS_IMAGES.'categories/noimage.gif';
  }

  if (isset ($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    $manu_query = xtDBquery("select manufacturers_image, manufacturers_name from ".TABLE_MANUFACTURERS." where manufacturers_id = '".(int) $_GET['manufacturers_id']."'");
    $manu = xtc_db_fetch_array($manu_query,true);
    $category['categories_name'] = $manu['manufacturers_name'];

    if ($manu['manufacturers_image'] != '') {
      $image = DIR_WS_IMAGES.$manu['manufacturers_image'];
      if(!file_exists($image)) $image = '';
    }

  }
  //EOF -web28- 2010-08-06 - BUGFIX no manufacturers image displayed

  $module_smarty->assign('CATEGORIES_NAME', $category['categories_name']);
  $module_smarty->assign('CATEGORIES_HEADING_TITLE', $category['categories_heading_title']);
  $module_smarty->assign('CATEGORIES_IMAGE', $image);
  $module_smarty->assign('CATEGORIES_DESCRIPTION', $category['categories_description']);
  $rows = 0;
  $listing_query = xtDBquery($listing_split->sql_query);
  while ($listing = xtc_db_fetch_array($listing_query, true)) {
    $rows ++;
    $module_content[] =  $product->buildDataArray($listing);
  }
} else {
  // no product found
  $result = false;
}

if ($result != false) {
  // get default template
  if (!array_key_exists('listing_template', $category) || $category['listing_template'] == '' || $category['listing_template'] == 'default') {
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
    $category['listing_template'] = $files[0];
  }

  $module_smarty->assign('MANUFACTURER_DROPDOWN', (isset($manufacturer_dropdown) ? $manufacturer_dropdown : ''));
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $module_content);
  $module_smarty->assign('NAVIGATION', $navigation);
  // set cache ID
   if (!CacheCheck()) {
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template']);
  } else {
    $module_smarty->caching = 1;
    $module_smarty->cache_lifetime = CACHE_LIFETIME;
    $module_smarty->cache_modified_check = CACHE_CHECK;

    //setting/clearing params
    $get_params = isset($_GET['manufacturers_id']) && xtc_not_null($_GET['manufacturers_id']) ? '_'.(int)$_GET['manufacturers_id'] : '';
    $get_params .= isset($_GET['filter_id']) && xtc_not_null($_GET['filter_id']) ? '_'.(int)$_GET['filter_id'] : '';
    $get_params .= isset($_GET['page']) && $_GET['page'] > 0  ? '_'.(int)$_GET['page'] : '';
    $get_params .= isset($_GET['categories_id']) && xtc_not_null($_GET['categories_id']) ? '_'.(int)$_GET['categories_id'] : '';
    $get_params .= isset($_GET['keywords']) && !empty($_GET['keywords']) ? '_'.stripslashes(trim(urldecode($_GET['keywords']))) : '';
    $get_params .= isset($_GET['pfrom']) && !empty($_GET['pfrom']) ? '_'.stripslashes($_GET['pfrom']) : '';
    $get_params .= isset($_GET['pto']) && !empty($_GET['pto']) ? '_'.stripslashes($_GET['pto']) : '';
    $get_params .= isset($_GET['x']) && $_GET['x'] >= 0 ? '_'.(int)$_GET['x'] : '';
    $get_params .= isset($_GET['y']) && $_GET['y'] >= 0 ? '_'.(int)$_GET['y'] : '';

    $cache_id = $current_category_id.'_'.$_SESSION['language'].'_'.$_SESSION['customers_status']['customers_status_name'].'_'.$_SESSION['currency'].$get_params;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template'], $cache_id);
  }
  $smarty->assign('main_content', $module);
} else {
  $error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
}
?>
