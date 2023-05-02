<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

// include needed functions
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
require_once (DIR_FS_INC.'get_pictureset_data.inc.php');

$module_smarty = new Smarty;
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$result = true;

$max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_SEARCH_RESULTS);
if (strpos($PHP_SELF, FILENAME_ADVANCED_SEARCH_RESULT) !== false && defined('MAX_DISPLAY_ADVANCED_SEARCH_RESULTS') && MAX_DISPLAY_ADVANCED_SEARCH_RESULTS != '') {
  $max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_ADVANCED_SEARCH_RESULTS);
  $module_smarty->assign('SEARCH_RESULT', true);
} elseif (strpos($PHP_SELF, FILENAME_SPECIALS) !== false && defined('MAX_DISPLAY_SPECIAL_PRODUCTS') && MAX_DISPLAY_SPECIAL_PRODUCTS != '') {
  $max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_SPECIAL_PRODUCTS);
  $module_smarty->assign('SPECIALS', true);
} elseif (strpos($PHP_SELF, FILENAME_PRODUCTS_NEW) !== false && defined('MAX_DISPLAY_PRODUCTS_NEW') && MAX_DISPLAY_PRODUCTS_NEW != '') {
  $max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_PRODUCTS_NEW);
  $module_smarty->assign('WHATS_NEW', true);
}

$list_count_key = 'p.products_id';

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/product_listing_split/','php') as $file) require ($file);

$listing_split = new splitPageResults($listing_sql, (isset($_GET['page']) ? (int)$_GET['page'] : 1), $max_display_results, $list_count_key);

$module_content = $category = array();
$image = '';

if ($listing_split->number_of_rows > 0) {
  if (!is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/pagination.html')) {
    $pagination = '<div class="smallText" style="clear:both;">
                     <div style="float:left;">'.$listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS).'</div> 
                     <div style="text-align:right;">'.TEXT_RESULT_PAGE.' '.$listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y', 'keywords')).(isset($_GET['keywords'])?'keywords='. urlencode($_GET['keywords']):'')).'</div> 
                   </div>';
  } else {   
    $module_smarty->assign('DISPLAY_COUNT', $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS));
    $module_smarty->assign('DISPLAY_LINKS', $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y', 'keywords')).(isset($_GET['keywords'])?'keywords='. urlencode($_GET['keywords']):'')));
    $module_smarty->caching = 0;
    $pagination = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/pagination.html');
  }
  $module_smarty->assign('NAVIGATION', $pagination);
  $module_smarty->assign('PAGINATION', $pagination);
  
  if ($current_category_id != '0') {
    $category = xtc_get_category_data($current_category_id);
    if (count($category) > 0) {
      $image = $main->getImage($category['categories_image']);
      $image_list = $main->getImage($category['categories_image_list'] != '' ? $category['categories_image_list'] : $category['categories_image']);
      $image_mobile = $main->getImage($category['categories_image_mobile']);
    }
  }

  if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    $manufacturers_id = (int)$_GET['manufacturers_id'];
  } elseif (isset($_GET['filter_id']) && !is_array($_GET['filter_id']) && (int)$_GET['filter_id'] > 0) {
    $manufacturers_id = (int)$_GET['filter_id'];
  }
  
  if (isset($manufacturers_id) && basename($PHP_SELF) != FILENAME_ADVANCED_SEARCH_RESULT) {
    $manufacturers_array = xtc_get_manufacturers();
    if (isset($manufacturers_array[$manufacturers_id])) {
      $manufacturer = $manufacturers_array[$manufacturers_id];
      $manufacturer_image = $main->getImage($manufacturer['manufacturers_image'], 'manufacturers/', MANUFACTURER_IMAGE_SHOW_NO_IMAGE);

      if ($current_category_id != '0') {
        $module_smarty->assign('MANUFACTURER_IMAGE', ((isset($manufacturer_image) && $manufacturer_image != '') ? DIR_WS_BASE . $manufacturer_image : ''));
        $module_smarty->assign('MANUFACTURER_NAME', $manufacturer['manufacturers_name']);
        $module_smarty->assign('MANUFACTURER_TITLE', $manufacturer['manufacturers_title']);
        $module_smarty->assign('MANUFACTURER_DESCRIPTION', $manufacturer['manufacturers_description']);
        $module_smarty->assign('MANUFACTURER_LINK', xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manufacturer['manufacturers_id'], $manufacturer['manufacturers_name']))); 
      } else {
        $category['categories_name'] = $manufacturer['manufacturers_name'];
        $category['categories_heading_title'] = $manufacturer['manufacturers_title'];
        $category['categories_description'] = $manufacturer['manufacturers_description'];
        $category['listing_template'] = $manufacturer['listing_template'];
        $image = ((isset($manufacturer_image) && $manufacturer_image != '') ? $manufacturer_image : '');
      }
    }
  }

  if ($current_category_id == '0' && isset($_GET['keywords'])) {
    $category['categories_name'] = TEXT_SEARCH_TERM . stripslashes(trim(urldecode($_GET['keywords'])));
  }

  if (isset($category['categories_heading_title']) && $category['categories_heading_title'] != '') {
    $list_title = $category['categories_heading_title'];
  } elseif (isset($category['categories_name']) && $category['categories_name'] != '') {
    $list_title = $category['categories_name'];
  } elseif (basename($PHP_SELF) == FILENAME_SPECIALS) {
    $list_title = TITLE_SPECIALS;
  } elseif (basename($PHP_SELF) == FILENAME_PRODUCTS_NEW) {
    $list_title = TITLE_PRODUCTS_NEW;
  }

  $module_smarty->assign('LIST_TITLE',  isset($list_title) ? $list_title : '');
  $module_smarty->assign('CATEGORIES_NAME', isset($category['categories_name']) ? $category['categories_name'] : '');
  $module_smarty->assign('CATEGORIES_HEADING_TITLE', isset($category['categories_heading_title']) ? $category['categories_heading_title'] : '');
  $module_smarty->assign('CATEGORIES_DESCRIPTION', isset($category['categories_description']) ? $category['categories_description'] : '');
  $module_smarty->assign('CATEGORIES_IMAGE', ((isset($image) && $image != '') ? DIR_WS_BASE . $image : ''));
  $module_smarty->assign('CATEGORIES_IMAGE_LIST', ((isset($image_list) && $image_list != '') ? DIR_WS_BASE . $image_list : ''));
  $module_smarty->assign('CATEGORIES_IMAGE_MOBILE', ((isset($image_mobile) && $image_mobile != '') ? DIR_WS_BASE . $image_mobile : ''));

  if ($messageStack->size('product_listing') > 0) {
    $module_smarty->assign('error_message', $messageStack->output('product_listing'));
  }

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/product_listing_begin/','php') as $file) require ($file);

  $listing_query = xtDBquery($listing_split->sql_query);
  while ($listing = xtc_db_fetch_array($listing_query, true)) {
    $module_content[$listing['products_id']] =  $product->buildDataArray($listing);
  }
} else {
  // no product found
  $result = false;
}

if ($result != false) {

  // get default template
  if (!isset($category['listing_template'])
      || $category['listing_template'] == '' 
      || $category['listing_template'] == 'default'
      || !is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template'])
      )
  {
    $files = array_filter(auto_include(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_listing/','html'), function($file) {
      return false === strpos($file, 'index.html');
    });
    $category['listing_template'] = basename($files[0]);
  }

  //include Categorie Listing
  include (DIR_WS_MODULES. 'categories_listing.php');

  include (DIR_WS_MODULES.'listing_filter.php');
  
  $module_smarty->assign('MANUFACTURER_DROPDOWN', (isset($manufacturer_dropdown) ? $manufacturer_dropdown : ''));  
  $module_smarty->assign('module_content', $module_content);

  if (defined('PICTURESET_BOX')) {
    $module_smarty->assign('pictureset_box', get_pictureset_data(PICTURESET_BOX));
  }
  if (defined('PICTURESET_ROW')) {
    $module_smarty->assign('pictureset_row', get_pictureset_data(PICTURESET_ROW));
  }

  $module_smarty->caching = 0;
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/product_listing/'.$category['listing_template']);

  $smarty->assign('main_content', $module);
} elseif (isset($current_category_id) && $current_category_id > 0) {
  $category = xtc_get_category_data($current_category_id);
  if (count($category) > 0 
      && $category['categories_name'] != ''
      && $category['categories_description'] != ''
      )
  {  
    //include Categorie Listing
    include (DIR_WS_MODULES. 'categories_listing.php');

    $image = $main->getImage($category['categories_image']);

    $module_smarty->assign('CATEGORIES_NAME', $category['categories_name']);
    $module_smarty->assign('CATEGORIES_HEADING_TITLE', $category['categories_heading_title']);
    $module_smarty->assign('CATEGORIES_IMAGE', (($image != '') ? DIR_WS_BASE . $image : ''));
    $module_smarty->assign('CATEGORIES_DESCRIPTION', $category['categories_description']);

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/product_listing_end/','php') as $file) require ($file);

    // get default template
    if ($category['categories_template'] == '' 
        || $category['categories_template'] == 'default'
        || !is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/categorie_listing/'.$category['categories_template'])
        )
    {
      $files = array_filter(auto_include(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/categorie_listing/','html'), function($file) {
        return false === strpos($file, 'index.html');
      });
      $category['categories_template'] = basename($files[0]);
    }
    
    $module_smarty->caching = 0;
    $main_content = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/categorie_listing/'.$category['categories_template']);
    
    $smarty->assign('main_content', $main_content);
  } elseif (isset($_GET['filter_id']) && !is_array($_GET['filter_id']) && (int)$_GET['filter_id'] > 0) {
    $site_error = MANUFACTURER_NOT_FOUND;
    include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
  } else {
    $site_error = CATEGORIE_NOT_FOUND;
    include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
  }
} elseif (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
  $manufacturers_array = xtc_get_manufacturers();
  if (isset($manufacturers_array[(int)$_GET['manufacturers_id']])
      && $manufacturers_array[(int)$_GET['manufacturers_id']]['manufacturers_name'] != ''
      && $manufacturers_array[(int)$_GET['manufacturers_id']]['manufacturers_description'] != ''
      )
  {
    $manufacturer = $manufacturers_array[(int)$_GET['manufacturers_id']];
    $manufacturer_image = $main->getImage($manufacturer['manufacturers_image'], 'manufacturers/', MANUFACTURER_IMAGE_SHOW_NO_IMAGE, 'manufacturers/noimage.gif');

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('CATEGORIES_NAME', $manufacturer['manufacturers_name']);
    $module_smarty->assign('CATEGORIES_HEADING_TITLE', $manufacturer['manufacturers_title']);
    $module_smarty->assign('CATEGORIES_DESCRIPTION', $manufacturer['manufacturers_description']);
    $module_smarty->assign('CATEGORIES_IMAGE', ((isset($manufacturer_image) && $manufacturer_image != '') ? DIR_WS_BASE . $manufacturer_image : ''));

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/product_listing_end/','php') as $file) require ($file);

    // get default template
    if ($manufacturer['categories_template'] == '' 
        || $manufacturer['categories_template'] == 'default'
        || !is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/categorie_listing/'.$manufacturer['categories_template'])
        )
    {
      $files = array_filter(auto_include(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/categorie_listing/','html'), function($file) {
        return false === strpos($file, 'index.html');
      });
      $manufacturer['categories_template'] = basename($files[0]);
    }
  
    $module_smarty->caching = 0;
    $main_content = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/categorie_listing/'.$manufacturer['categories_template']);
    
    $smarty->assign('main_content', $main_content);
  } else {
    $site_error = MANUFACTURER_NOT_FOUND;
    if (isset($_GET['filter_id']) && !is_array($_GET['filter_id']) && (int)$_GET['filter_id'] > 0) {
      $site_error = CATEGORIE_NOT_FOUND;  
    }
    include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
  }
} elseif (basename($PHP_SELF) == FILENAME_SPECIALS) {
  $site_error = TEXT_SPECIALS_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
} elseif (basename($PHP_SELF) == FILENAME_PRODUCTS_NEW) {
  $site_error = sprintf(TEXT_PRODUCTS_NEW_NOT_FOUND, MAX_DISPLAY_NEW_PRODUCTS_DAYS);
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
} elseif ($current_category_id == '0' && isset($_GET['keywords'])) {
  $site_error = TEXT_PRODUCT_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
} else {
  $site_error = CATEGORIE_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
}
