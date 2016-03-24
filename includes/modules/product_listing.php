<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_listing.php 5861 2013-10-01 12:50:02Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_listing.php,v 1.42 2003/05/27); www.oscommerce.com
   (c) 2003 nextcommerce (product_listing.php,v 1.19 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce (product_listing.php 1286 2005-10-07); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// todo: move to configuration ?
defined('CATEGORIES_IMAGE_SHOW_NO_IMAGE') OR define('CATEGORIES_IMAGE_SHOW_NO_IMAGE', 'true');
defined('MANUFACTURER_IMAGE_SHOW_NO_IMAGE') OR define('MANUFACTURER_IMAGE_SHOW_NO_IMAGE', 'false');

$module_smarty = new Smarty;
$module_smarty->caching = false;
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$result = true;

// include needed functions
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');

$max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_SEARCH_RESULTS);
if (strpos($PHP_SELF, FILENAME_ADVANCED_SEARCH_RESULT) !== false && defined('MAX_DISPLAY_ADVANCED_SEARCH_RESULTS') && MAX_DISPLAY_ADVANCED_SEARCH_RESULTS != '') {
  $max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_ADVANCED_SEARCH_RESULTS);
  $module_smarty->assign('SEARCH_RESULT', true);
}

$listing_split = new splitPageResults($listing_sql, (isset($_GET['page']) ? (int)$_GET['page'] : 1), $max_display_results, 'p.products_id');

$module_content = $category = array();
$image = '';

if ($listing_split->number_of_rows > 0) {
  if (USE_PAGINATION_LIST == 'false') {
    $module_smarty->assign('NAVIGATION', '<div class="smallText" style="clear:both;">
                                            <div style="float:left;">'.$listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS).'</div> 
                                            <div align="right">'.TEXT_RESULT_PAGE.' '.$listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y', 'keywords')).(isset($_GET['keywords'])?'keywords='. urlencode($_GET['keywords']):'')).'</div> 
                                          </div>');
  } else {   
    $module_smarty->assign('DISPLAY_COUNT', $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS));
    $module_smarty->assign('DISPLAY_LINKS', $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y', 'keywords')).(isset($_GET['keywords'])?'keywords='. urlencode($_GET['keywords']):'')));
    $module_smarty->caching = 0;
    $pagination = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/pagination.html');
    $module_smarty->assign('NAVIGATION', $pagination);
    $module_smarty->assign('PAGINATION', $pagination);
  }
  
  if ($current_category_id != '0') {

    $category_query = xtDBquery("SELECT cd.categories_description,
                                        cd.categories_name,
                                        cd.categories_heading_title,
                                        c.listing_template,
                                        c.categories_image
                                   FROM ".TABLE_CATEGORIES." c
                                   JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
                                     ON (c.categories_id = cd.categories_id AND cd.language_id = '".(int)$_SESSION['languages_id']."')
                                  WHERE c.categories_id = '".(int)$current_category_id."'
                                        ".CATEGORIES_CONDITIONS_C."
                                  LIMIT 1");
    $category = xtc_db_fetch_array($category_query, true);
    if ($category['categories_image'] != '') {
      $image = DIR_WS_IMAGES.'categories/'.$category['categories_image'];
      if (!file_exists(DIR_FS_CATALOG.$image)) {
        if (CATEGORIES_IMAGE_SHOW_NO_IMAGE == 'true') {
          $image = DIR_WS_IMAGES.'categories/noimage.gif';
        } else {
          $image = '';
        }
      }
    }
  }

  if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    $manufacturers_id = (int)$_GET['manufacturers_id'];
  } elseif (isset($_GET['filter_id']) && $_GET['filter_id'] > 0) {
    $manufacturers_id = (int)$_GET['filter_id'];
  }
  
  if (isset($manufacturers_id) && basename($PHP_SELF) != FILENAME_ADVANCED_SEARCH_RESULT) {
    $manu_query = xtDBquery("SELECT m.manufacturers_image, 
                                    m.manufacturers_name,
                                    mi.manufacturers_description 
                               FROM ".TABLE_MANUFACTURERS." m
                               JOIN " . TABLE_MANUFACTURERS_INFO . " mi
                                    ON (m.manufacturers_id = mi.manufacturers_id
                                        AND mi.languages_id = '" . (int)$_SESSION['languages_id'] . "')
                              WHERE m.manufacturers_id = '".$manufacturers_id."'");
    $manu = xtc_db_fetch_array($manu_query, true);
    if ($manu['manufacturers_image'] != '') {
      $manu_image = DIR_WS_IMAGES.$manu['manufacturers_image'];
      if (!file_exists(DIR_FS_CATALOG.$manu_image)) {
        if (MANUFACTURER_IMAGE_SHOW_NO_IMAGE == 'true') {
          $manu_image = DIR_WS_IMAGES.'manufacturers/noimage.gif';
        } else {
          $manu_image = '';
        }
      }
    }
    if ($current_category_id != '0') {
      $module_smarty->assign('MANUFACTURER_IMAGE', ((isset($manu_image) && $manu_image != '') ? DIR_WS_BASE . $manu_image : ''));
      $module_smarty->assign('MANUFACTURER_NAME', $manu['manufacturers_name']);
      $module_smarty->assign('MANUFACTURER_DESCRIPTION', $manu['manufacturers_description']);
      $module_smarty->assign('MANUFACTURER_LINK', xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manu['manufacturers_id'], $manu['manufacturers_name']))); 
    } else {
      $category['categories_name'] = $manu['manufacturers_name'];
      $category['categories_description'] = $manu['manufacturers_description'];
      $image = ((isset($manu_image) && $manu_image != '') ? $manu_image : '');
    }
  }

  if ($current_category_id == '0' && isset($_GET['keywords'])) {
    $category['categories_name'] = TEXT_SEARCH_TERM . stripslashes(trim(urldecode($_GET['keywords'])));
  }

  if (isset($category['categories_heading_title']) && $category['categories_heading_title'] != '') {
    $list_title = $category['categories_heading_title'];
  } elseif (isset($category['categories_name']) && $category['categories_name'] != '') {
    $list_title = $category['categories_name'];
  }

  $module_smarty->assign('LIST_TITLE',  isset($list_title) ? $list_title : '');
  $module_smarty->assign('CATEGORIES_NAME', isset($category['categories_name']) ? $category['categories_name'] : '');
  $module_smarty->assign('CATEGORIES_HEADING_TITLE', isset($category['categories_heading_title']) ? $category['categories_heading_title'] : '');
  $module_smarty->assign('CATEGORIES_DESCRIPTION', isset($category['categories_description']) ? $category['categories_description'] : '');
  $module_smarty->assign('CATEGORIES_IMAGE', ((isset($image) && $image != '') ? DIR_WS_BASE . $image : ''));

  $listing_query = xtDBquery($listing_split->sql_query);
  while ($listing = xtc_db_fetch_array($listing_query, true)) {
    $module_content[] =  $product->buildDataArray($listing);
  }
} else {
  // no product found
  $result = false;
}

//include Categorie Listing
include (DIR_WS_MODULES. 'categories_listing.php');

if ($result != false) {
  // get default template
  if (empty($category['listing_template']) || $category['listing_template'] == 'default') {
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

  include (DIR_WS_MODULES.'listing_filter.php');
  
  $module_smarty->assign('MANUFACTURER_DROPDOWN', (isset($manufacturer_dropdown) ? $manufacturer_dropdown : ''));
  
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $module_content);
  // support for own manufacturers template
  $template = CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template'];
  if (isset ($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0 && strpos($PHP_SELF, FILENAME_ADVANCED_SEARCH_RESULT) === false) {
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/manufacturers_listing.html')) {
      $template = CURRENT_TEMPLATE.'/module/manufacturers_listing.html';
    }
  }
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

    $cache_id = md5($current_category_id.'_'.$_SESSION['language'].'_'.$_SESSION['customers_status']['customers_status_name'].'_'.$_SESSION['currency'].$get_params);
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template'], $cache_id);
  }
  $smarty->assign('main_content', $module);
} elseif (isset($current_category_id) && $current_category_id > 0) {
  $category_query = xtDBquery("SELECT c.categories_image,
                                      c.categories_template,
                                      cd.categories_name,
                                      cd.categories_heading_title,
                                      cd.categories_description
                                 FROM ".TABLE_CATEGORIES." c
                                 JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                                      ON cd.categories_id = c.categories_id
                                         AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                         AND trim(cd.categories_name) != ''
                                         AND trim(cd.categories_description) != ''
                                WHERE c.categories_status = '1'
                                  AND c.categories_id = '".(int)$current_category_id."'
                                      ".CATEGORIES_CONDITIONS_C);
  if (xtc_db_num_rows($category_query, true) > 0) {
    $category = xtc_db_fetch_array($category_query, true);

    $image = '';
    if ($category['categories_image'] != '') {
      $image = DIR_WS_IMAGES.'categories/'.$category['categories_image'];
      if (!file_exists(DIR_FS_CATALOG.$image)) {
        if (CATEGORIES_IMAGE_SHOW_NO_IMAGE == 'true') {
          $image = DIR_WS_IMAGES.'categories/noimage.gif';
        } else {
          $image = '';
        }
      }
    }

    // get default template
    if ($category['categories_template'] == '' || $category['categories_template'] == 'default') {
      $files = array_filter(auto_include(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/categorie_listing/','html'), function($file) {
        return false === strpos($file, 'index.html');
      });
      $category['categories_template'] = basename($files[0]);
    }

    $module_smarty->assign('CATEGORIES_NAME', $category['categories_name']);
    $module_smarty->assign('CATEGORIES_HEADING_TITLE', $category['categories_heading_title']);
    $module_smarty->assign('CATEGORIES_IMAGE', (($image != '') ? DIR_WS_BASE . $image : ''));
    $module_smarty->assign('CATEGORIES_DESCRIPTION', $category['categories_description']);
    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->caching = 0;
    $main_content = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/categorie_listing/'.$category['categories_template']);
    $smarty->assign('main_content', $main_content);
  } else {
    $site_error = CATEGORIE_NOT_FOUND;
    include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
  }
} elseif (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
  $site_error = MANUFACTURER_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
} else {
  $site_error = CATEGORIE_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
}
?>