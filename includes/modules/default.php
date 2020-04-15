<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------------------
  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommerce(default.php,v 1.84 2003/05/07); www.oscommerce.com
  (c) 2003 nextcommerce (default.php,v 1.11 2003/08/22); www.nextcommerce.org
  (c) 2006 xt:Commerce (cross_selling.php 1243 2005-09-25); www.xt-commerce.de

  Released under the GNU General Public License
  -----------------------------------------------------------------------------------------
  Third Party contributions:
  Enable_Disable_Categories 1.3        Autor: Mikel Williams | mikel@ladykatcostumes.com
  Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/
  | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs...by=date#dirlist

  Released under the GNU General Public License
  ---------------------------------------------------------------------------------------*/

// todo: move to configuration ?
defined('CATEGORIES_IMAGE_SHOW_NO_IMAGE') OR define('CATEGORIES_IMAGE_SHOW_NO_IMAGE', 'true');
defined('CATEGORIES_SHOW_PRODUCTS_SUBCATS') OR define('CATEGORIES_SHOW_PRODUCTS_SUBCATS', 'false');

$default_smarty = new smarty;
$default_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
$default_smarty->assign('session', xtc_session_id());

// define defaults
$main_content = '';

// include needed functions
require_once (DIR_FS_INC.'xtc_get_path.inc.php');
require_once (DIR_FS_INC.'xtc_check_categories_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_subcategories.inc.php');
require_once (DIR_FS_INC.'xtc_parse_search_string.inc.php');
require_once (DIR_FS_INC.'xtc_get_currencies_values.inc.php');
require_once (DIR_FS_INC.'check_whatsnew.inc.php');

$category_depth = 'top';
if (isset ($cPath) && xtc_not_null($cPath)) {

  // check categorie exist
  if (xtc_check_categories_status($current_category_id) === false) {
    $site_error = CATEGORIE_NOT_FOUND;
    include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
    return;
  }
  
  $subcat_list = (int)$current_category_id;
  if (CATEGORIES_SHOW_PRODUCTS_SUBCATS == 'true') {
    $subcategories_array = array ();
    xtc_get_subcategories($subcategories_array, $subcat_list);
    $subcategories_array[] = $subcat_list;
    $subcat_list = implode("', '", $subcategories_array);
  }
  
  $categories_products_query = "SELECT p2c.products_id
                                  FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                             LEFT JOIN ".TABLE_PRODUCTS." p
                                       ON p2c.products_id = p.products_id
                                          AND p2c.categories_id IN ('".$subcat_list."')
                                WHERE p.products_status = '1'
                                      ".PRODUCTS_CONDITIONS_P;
  $categories_products_result = xtDBquery($categories_products_query);
  if (xtc_db_num_rows($categories_products_result, true) > 0) {
    $category_depth = 'products'; // display products
  } else {
    $category_parent_query = "SELECT parent_id 
                                FROM ".TABLE_CATEGORIES." 
                               WHERE parent_id = ".(int)$current_category_id." 
                                 AND categories_status = '1'
                                     ".CATEGORIES_CONDITIONS;
    $category_parent_result = xtDBquery($category_parent_query);
    if (xtc_db_num_rows($category_parent_result, true) > 0) {
      $category_depth = 'nested'; // navigate through the categories
    } else {
      $category_depth = 'products'; // category has no products, but display the 'no products' message
    }
  }
} elseif (isset($_GET['manufacturers_id']) && (int)$_GET['manufacturers_id'] > 0) {
  $category_depth = 'products';
}

if (isset($language_not_found) && $language_not_found === true) {
  if ($category_depth != 'top') {
    $site_error = CATEGORIE_NOT_FOUND;
    include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
    return;
  } else {
    header("HTTP/1.0 410 Gone"); 
    header("Status: 410 Gone");
  }
}

$category_depth_array = array(
  FILENAME_ADVANCED_SEARCH_RESULT,
  FILENAME_PRODUCTS_NEW,
  FILENAME_SPECIALS,
);
if (in_array(basename($PHP_SELF), $category_depth_array)
    || (isset($_GET['manufacturers_id']) && (int)$_GET['manufacturers_id'] > 0)
    )
{
  $category_depth = 'products';
}
foreach(auto_include(DIR_FS_CATALOG.'includes/extra/default/category_depth/','php') as $file) require ($file);

switch ($category_depth) {
  case 'nested':
    $category_query = "SELECT ".ADD_SELECT_CATEGORIES."
                              c.categories_image,
                              c.categories_template,
                              cd.categories_name,
                              cd.categories_heading_title,
                              cd.categories_description
                         FROM ".TABLE_CATEGORIES." c
                         JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                              ON cd.categories_id = c.categories_id
                                 AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                 AND trim(cd.categories_name) != ''
                        WHERE c.categories_status = '1'
                          AND c.categories_id = '".(int)$current_category_id."'
                              ".CATEGORIES_CONDITIONS_C;
    $category_query = xtDBquery($category_query);
    $category = xtc_db_fetch_array($category_query, true);

    if (MAX_DISPLAY_CATEGORIES_PER_ROW > 0) {
      // check to see if there are deeper categories within the current category
      $categories_query = "SELECT ".ADD_SELECT_CATEGORIES."
                                  c.categories_id,
                                  c.categories_image,
                                  c.parent_id,
                                  cd.categories_name,
                                  cd.categories_heading_title,
                                  cd.categories_description
                             FROM ".TABLE_CATEGORIES." c
                             JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                                  ON cd.categories_id = c.categories_id
                                     AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                     AND trim(cd.categories_name) != ''
                            WHERE c.categories_status = '1'
                              AND c.parent_id = '".(int)$current_category_id."'
                                  ".CATEGORIES_CONDITIONS_C."
                         ORDER BY c.sort_order, cd.categories_name";
      $categories_query = xtDBquery($categories_query);
      $categories_content = array();
      $cindex = 0;
      while ($categories = xtc_db_fetch_array($categories_query, true)) {
        $cPath_new = xtc_category_link($categories['categories_id'],$categories['categories_name']);
      
        $image = $main->getImage($categories['categories_image']);

        $categories_content[$cindex] = array ('CATEGORIES_NAME' => $categories['categories_name'],
                                       'CATEGORIES_HEADING_TITLE' => $categories['categories_heading_title'],
                                       'CATEGORIES_IMAGE' => (($image != '') ? DIR_WS_BASE . $image : ''),
                                       'CATEGORIES_LINK' => xtc_href_link(FILENAME_DEFAULT, $cPath_new),
                                       'CATEGORIES_DESCRIPTION' => $categories['categories_description']);
                                     
        foreach(auto_include(DIR_FS_CATALOG.'includes/extra/default/categories_content/','php') as $file) require ($file);
      
        $cindex++;
      }
    }

    $new_products_category_id = $current_category_id;
    include (DIR_WS_MODULES.FILENAME_NEW_PRODUCTS);

    $image = $main->getImage($category['categories_image']);

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

    $max_per_row = MAX_DISPLAY_CATEGORIES_PER_ROW;
    $width = $max_per_row ? intval(100 / $max_per_row).'%' : '';
    $default_smarty->assign('TR_COLS', $max_per_row);
    $default_smarty->assign('TD_WIDTH', $width);
    $default_smarty->assign('CATEGORIES_NAME', $category['categories_name']);
    $default_smarty->assign('CATEGORIES_HEADING_TITLE', $category['categories_heading_title']);
    $default_smarty->assign('CATEGORIES_IMAGE', (($image != '') ? DIR_WS_BASE . $image : ''));
    $default_smarty->assign('CATEGORIES_DESCRIPTION', $category['categories_description']);
    $default_smarty->assign('language', $_SESSION['language']);
    $default_smarty->assign('module_content', $categories_content);
    $default_smarty->caching = 0;
  
    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/default/categories_smarty/','php') as $file) require_once ($file);
    $main_content = $default_smarty->fetch(CURRENT_TEMPLATE.'/module/categorie_listing/'.$category['categories_template']);
    $smarty->assign('main_content', $main_content);
    break;

  case 'products':
    $select = '';
    $from = '';
    $filter_join = '';
    $where = '';

    // sorting query
    if (isset($_GET['manufacturers_id']) && isset($_GET['filter_id'])) {
      $categories_id = implode("', '", (array)$_GET['filter_id']);
    } else {
      $categories_id = (int)$current_category_id;
    }
    $sorting_query = xtDBquery("SELECT products_sorting,
                                       products_sorting2
                                  FROM ".TABLE_CATEGORIES."
                                 WHERE categories_id IN ('".$categories_id ."')");
    $sorting_data = xtc_db_fetch_array($sorting_query,true);
  
    //Fallback for products_sorting to products_name
    if (empty($sorting_data['products_sorting'])) { 
      $sorting_data['products_sorting'] = 'pd.products_name';
    }
  
    //Fallback for products_sorting2 to ascending
    if (empty($sorting_data['products_sorting2'])) { 
      $sorting_data['products_sorting2'] = 'ASC';
    }
    $sorting = ' ORDER BY '.$sorting_data['products_sorting'].' '.$sorting_data['products_sorting2'].' ';

    if (basename($PHP_SELF) == FILENAME_SPECIALS) {
      $select .= "s.expires_date,
                  s.specials_new_products_price,
                  s.specials_new_products_price AS price, ";
      $from   .= "JOIN ".TABLE_SPECIALS." s
                    ON p.products_id = s.products_id 
                       ".SPECIALS_CONDITIONS_S." ";
      $sorting = ' ORDER BY s.specials_date_added DESC ';
    } else {
      $select .= "IFNULL(s.specials_new_products_price, p.products_price) AS price, ";
      $from   .= "LEFT JOIN ".TABLE_SPECIALS." s
                         ON p.products_id = s.products_id 
                            ".SPECIALS_CONDITIONS_S." ";
    }
  
    if (basename($PHP_SELF) == FILENAME_PRODUCTS_NEW) {
      if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0'
          && check_whatsnew() === true
          )
      {
        $date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
        $where .= " AND p.products_date_added > '".$date_new_products."' ";
        $daysfound = true;
      }
      $sorting = ' ORDER BY p.products_date_added DESC ';
    }

    if (isset($_GET['manufacturers_id'])) {
      if (basename($PHP_SELF) == FILENAME_DEFAULT) {
        // show the products of a specified manufacturer
        $select .= "m.manufacturers_name, ";
        $from   .= "JOIN ".TABLE_MANUFACTURERS." m 
                         ON p.manufacturers_id = m.manufacturers_id
                            AND m.manufacturers_id = '".(int) $_GET['manufacturers_id']."' ";
      }
      
      // We are asked to show only a specific category
      if (isset($_GET['filter_id']) && xtc_not_null($_GET['filter_id'])) {
        $filter_join .= "JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                           ON p2c.products_id = pd.products_id 
                              AND p2c.categories_id = '".(int)$_GET['filter_id']."' ";
      }
    } else {
      if (basename($PHP_SELF) == FILENAME_DEFAULT && $subcat_list != '') {
        // show the products in a given categorie
        $from   .= "JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                         ON p2c.products_id = pd.products_id
                            AND p2c.categories_id IN ('".$subcat_list."') ";
      }
      // We are asked to show only specific manufacturer                    
      if (isset($_GET['filter_id']) && xtc_not_null($_GET['filter_id'])) {
        $select .= "m.manufacturers_name, ";
        $filter_join .= "JOIN ".TABLE_MANUFACTURERS." m 
                           ON p.manufacturers_id = m.manufacturers_id
                              AND m.manufacturers_id = '".(int)$_GET['filter_id']."' ";
      }
    }
  
    // filter
    if (isset($_GET['filter']) && is_array($_GET['filter'])) {
      $fi = 1;
      foreach ($_GET['filter'] as $options_id => $values_id) {
        if ($values_id != '') {
          $filter_join .= "JOIN ".TABLE_PRODUCTS_TAGS." pt".$fi." 
                             ON pt".$fi.".products_id = p.products_id
                                AND pt".$fi.".options_id = '".(int)$options_id."'
                                AND pt".$fi.".values_id = '".(int)$values_id."' ";
          $fi ++;
        }
      }
    }
     
    $listing_sql = "SELECT ".$select."
                           ".ADD_SELECT_DEFAULT."
                           p.products_id,
                           p.products_ean,
                           p.products_quantity,
                           p.products_shippingtime,
                           p.products_model,
                           p.products_image,
                           p.products_price,
                           p.products_discount_allowed,
                           p.products_weight,
                           p.products_tax_class_id,
                           p.manufacturers_id,
                           p.products_fsk18,
                           p.products_vpe,
                           p.products_vpe_status,
                           p.products_vpe_value,
                           pd.products_name,
                           pd.products_heading_title,
                           pd.products_description,
                           pd.products_short_description
                      FROM ".TABLE_PRODUCTS." p
                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                           ON p.products_id = pd.products_id 
                              AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                              AND trim(pd.products_name) != '' 
                           ".$from."
                           ".$filter_join."
                     WHERE p.products_status = '1'
                           ".PRODUCTS_CONDITIONS_P."
                           ".$where."
                  GROUP BY p.products_id
                           ".((isset($_SESSION['filter_sorting'])) ? $_SESSION['filter_sorting'] : $sorting);
  
    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/default/listing_sql/','php') as $file) require ($file);

    include (DIR_WS_MODULES.FILENAME_PRODUCT_LISTING);
    break;
    
  case 'top':
    $shop_content_data = $main->getContentData(5, '', '', false, ADD_SELECT_CONTENT);
  
    $content_main_template = 'main_content.html';

    $default_smarty->assign('title', $shop_content_data['content_heading']);

    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/default/center_modules/','php') as $file) require_once ($file);

    $default_smarty->assign('language', $_SESSION['language']);

    // set cache ID
    if (!CacheCheck()) {
      $default_smarty->caching = 0;
      $main_content = $default_smarty->fetch(CURRENT_TEMPLATE.'/module/'.$content_main_template);
    } else {
      $default_smarty->caching = 1;
      $default_smarty->cache_lifetime = CACHE_LIFETIME;
      $default_smarty->cache_modified_check = CACHE_CHECK;
      $cache_id = md5($_SESSION['language'].$_SESSION['currency'].((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : ''));
      $main_content = $default_smarty->fetch(CURRENT_TEMPLATE.'/module/'.$content_main_template, $cache_id);
    }
    $smarty->assign('main_content', $main_content);
    break;
}
?>