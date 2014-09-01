<?php
/* -----------------------------------------------------------------------------------------
   $Id: default.php 2774 2012-04-20 18:30:22Z web28 $

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

$default_smarty = new smarty;
$default_smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
$default_smarty->assign('session', session_id());

// define defaults
$main_content = '';
$group_check = '';
$fsk_lock = '';

// include needed functions
require_once (DIR_FS_INC.'xtc_customer_greeting.inc.php');
require_once (DIR_FS_INC.'xtc_get_path.inc.php');
require_once (DIR_FS_INC.'xtc_check_categories_status.inc.php');

// check categorie exist
if (xtc_check_categories_status($current_category_id) >= 1) {
  $error = CATEGORIE_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
  return;
}

// the following cPath references come from application_top.php
$category_depth = 'top';
if (isset ($cPath) && xtc_not_null($cPath)) {
  $categories_products_query = "select p2c.products_id
                                  from ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                  left join ".TABLE_PRODUCTS." p
                                   on p2c.products_id = p.products_id
                                  where p2c.categories_id = ".(int)$current_category_id."
                                  and p.products_status = 1";
  $categories_products_result = xtDBquery($categories_products_query);
  if (xtc_db_num_rows($categories_products_result, true) > 0) {
    $category_depth = 'products'; // display products
  } else {
    $category_parent_query = "select parent_id from ".TABLE_CATEGORIES." where parent_id = ".(int)$current_category_id." AND categories_status = 1";
    $category_parent_result = xtDBquery($category_parent_query);
    $category_parent = xtc_db_fetch_array($category_parent_result, true);
    if (xtc_db_num_rows($category_parent_result, true) > 0) {
      $category_depth = 'nested'; // navigate through the categories
    } else {
      $category_depth = 'products'; // category has no products, but display the 'no products' message
    }
  }
}

/**
 * list of categories
 *
 */
if ($category_depth == 'nested') {

  if (GROUP_CHECK == 'true') {
    $group_check = "AND c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
  }

  $category_query = "-- /includes/modules/default.php
                     SELECT c.categories_image,
                            c.categories_template,
                            cd.categories_name,
                            cd.categories_heading_title,
                            cd.categories_description
                          FROM ".TABLE_CATEGORIES." c
                          JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd on cd.categories_id = c.categories_id
                          WHERE c.categories_id = '".$current_category_id."'
                            ".$group_check."
                            AND cd.language_id = '".(int) $_SESSION['languages_id']."'";
  $category_query = xtDBquery($category_query);
  $category = xtc_db_fetch_array($category_query, true);

  if (MAX_DISPLAY_CATEGORIES_PER_ROW > 0) {
    // check to see if there are deeper categories within the current category
    $categories_query = "-- /includes/modules/default.php
                         SELECT c.categories_id,
                                c.categories_image,
                                c.parent_id,
                                cd.categories_name,
                                cd.categories_heading_title,
                                cd.categories_description
                              FROM ".TABLE_CATEGORIES." c
                              JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd on cd.categories_id = c.categories_id
                              WHERE c.categories_status = '1'
                                ".$group_check."
                                AND c.parent_id = '".$current_category_id."'
                                AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                              ORDER BY sort_order, cd.categories_name";
    $categories_query = xtDBquery($categories_query);
    $categories_content = array();
    while ($categories = xtc_db_fetch_array($categories_query, true)) {
      $cPath_new = xtc_category_link($categories['categories_id'],$categories['categories_name']);
      $image = '';
      if ($categories['categories_image'] != '') {
        $image = DIR_WS_IMAGES.'categories/'.$categories['categories_image'];
        if(!file_exists($image)) $image = DIR_WS_IMAGES.'categories/noimage.gif';
        $image = $image;
      }
      $categories_content[] = array ('CATEGORIES_NAME' => $categories['categories_name'],
                                     'CATEGORIES_HEADING_TITLE' => $categories['categories_heading_title'],
                                     'CATEGORIES_IMAGE' => $image,
                                     'CATEGORIES_LINK' => xtc_href_link(FILENAME_DEFAULT, $cPath_new),
                                     'CATEGORIES_DESCRIPTION' => $categories['categories_description']);
    }
  }

  $new_products_category_id = $current_category_id;
  include (DIR_WS_MODULES.FILENAME_NEW_PRODUCTS);

  $image = '';
  if ($category['categories_image'] != '') {
    $image = DIR_WS_IMAGES.'categories/'.$category['categories_image'];
    if(!file_exists($image)) $image = DIR_WS_IMAGES.'categories/noimage.gif';
    $image = $image;
  }
  // get default template
  if ($category['categories_template'] == '' || $category['categories_template'] == 'default') {
    $files = array ();
    $cl_dir = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/categorie_listing/';
    if ($dir = opendir($cl_dir)) {
      while (($file = readdir($dir)) !== false) {
        if (is_file($cl_dir.$file) && (substr($file, 0, 1) != '.') && (substr($file, -5) == '.html') && ($file != 'index.html')) {
          $files[] = $file;
        }
      }
      closedir($dir);
    }
    sort($files);
    $category['categories_template'] = $files[0];
  }

  $max_per_row = MAX_DISPLAY_CATEGORIES_PER_ROW;
  $width = $max_per_row ? intval(100 / $max_per_row).'%' : '';
  $default_smarty->assign('TR_COLS', $max_per_row);
  $default_smarty->assign('TD_WIDTH', $width);
  $default_smarty->assign('CATEGORIES_NAME', $category['categories_name']);
  $default_smarty->assign('CATEGORIES_HEADING_TITLE', $category['categories_heading_title']);
  $default_smarty->assign('CATEGORIES_IMAGE', $image);
  $default_smarty->assign('CATEGORIES_DESCRIPTION', $category['categories_description']);
  $default_smarty->assign('language', $_SESSION['language']);
  $default_smarty->assign('module_content', $categories_content);
  $default_smarty->caching = 0;
  $main_content = $default_smarty->fetch(CURRENT_TEMPLATE.'/module/categorie_listing/'.$category['categories_template']);
  $smarty->assign('main_content', $main_content);


/**
  * list of products
  *
  */
} elseif ($category_depth == 'products' || (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0)) {

  $select = '';
  $from = '';
  $where = '';

  // fsk18 lock
  if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
    $fsk_lock = ' AND p.products_fsk18!=1';
  }
  // group check
  if (GROUP_CHECK == 'true') {
    $group_check = " AND p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
  }
  // sorting query
  if (isset($_GET['manufacturers_id']) && isset($_GET['filter_id'])) {
    $categories_id = (int)$_GET['filter_id'];
  } else {
    $categories_id = $current_category_id;
  }
  $sorting_query = xtDBquery("-- /includes/modules/default.php
                              SELECT products_sorting,
                                     products_sorting2
                                FROM ".TABLE_CATEGORIES."
                               WHERE categories_id='".$categories_id ."'");
  $sorting_data = xtc_db_fetch_array($sorting_query,true);
  if (empty($sorting_data['products_sorting'])) { //Fallback für products_sorting auf products_name
    $sorting_data['products_sorting'] = 'pd.products_name';
  }
  if (empty($sorting_data['products_sorting2'])) { //Fallback für products_sorting2 auf ascending
    $sorting_data['products_sorting2'] = 'ASC';
  }
  $sorting = ' ORDER BY '.$sorting_data['products_sorting'].' '.$sorting_data['products_sorting2'].' ';

  if (isset($_GET['manufacturers_id'])) {
    // show the products of a specified manufacturer
    $select .= "m.manufacturers_name, ";
    $from   .= "LEFT JOIN ".TABLE_MANUFACTURERS." m on p.manufacturers_id = m.manufacturers_id ";
    $where  .= " AND m.manufacturers_id = '".(int) $_GET['manufacturers_id']."' ";
    if (isset($_GET['filter_id']) && xtc_not_null($_GET['filter_id'])) {
      // We are asked to show only a specific category
      $from   .= "JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c on p2c.products_id = pd.products_id ";
      $where  .= "AND p2c.categories_id = '".(int)$_GET['filter_id']."' ";
    } else {
      // We show them all
    }
  } else {
    // show the products in a given categorie
    $from   .= "JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c on p2c.products_id = pd.products_id ";
    $where  .= "AND p2c.categories_id = '".$current_category_id."' ";
    if (isset($_GET['filter_id']) && xtc_not_null($_GET['filter_id'])) {
      // We are asked to show only specific catgeory
      $select .= "m.manufacturers_name, ";
      $from   .= "LEFT JOIN ".TABLE_MANUFACTURERS." m on p.manufacturers_id = m.manufacturers_id ";
      $where  .= "AND m.manufacturers_id = '".(int)$_GET['filter_id']."' ";
    } else {
      // We show them all
    }
  }
  $select .= 'p.products_manufacturers_model, ';
  $listing_sql = "-- /includes/modules/default.php
                  SELECT ".$select."
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
                         pd.products_description,
                         pd.products_short_description
                    FROM ".TABLE_PRODUCTS_DESCRIPTION." pd
                    JOIN ".TABLE_PRODUCTS." p
                         ".$from."
                   WHERE p.products_status = '1'
                     AND p.products_id = pd.products_id
                     AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                         ".$group_check."
                         ".$fsk_lock."
                         ".$where."
                         ".$sorting;

    // optional Product List Filter
  if (PRODUCT_LIST_FILTER == 'true') {
    if (isset($_GET['manufacturers_id'])) {
      $filterlist_sql = "-- /includes/modules/default.php
                         SELECT distinct c.categories_id as id,
                                         cd.categories_name as name
                                       FROM ".TABLE_PRODUCTS." p
                                       JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c on p2c.products_id = p.products_id
                                       JOIN ".TABLE_CATEGORIES." c on c.categories_id = p2c.categories_id
                                       JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd on cd.categories_id = p2c.categories_id
                                       WHERE p.products_status = '1'
                                         AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                         AND p.manufacturers_id = '".(int) $_GET['manufacturers_id']."'
                                         ORDER BY cd.categories_name";
    } else {
      $filterlist_sql = "-- /includes/modules/default.php
                         SELECT distinct m.manufacturers_id as id,
                                         m.manufacturers_name as name
                                       FROM ".TABLE_PRODUCTS." p
                                       JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c on p2c.products_id = p.products_id
                                       JOIN ".TABLE_MANUFACTURERS." m on m.manufacturers_id = p.manufacturers_id
                                       WHERE p.products_status = '1'
                                         AND p2c.categories_id = '".$current_category_id."'
                                         ORDER BY m.manufacturers_name";
    }
    $filterlist_query = xtDBquery($filterlist_sql);
    if (xtc_db_num_rows($filterlist_query, true) > 1) {
      $manufacturer_dropdown = xtc_draw_form('filter', DIR_WS_CATALOG . FILENAME_DEFAULT, 'get');
      if (isset($_GET['manufacturers_id'])) {
        $manufacturer_dropdown .= xtc_draw_hidden_field('manufacturers_id', (int)$_GET['manufacturers_id']).PHP_EOL;
        $options = array (array ('id' => '', 'text' => TEXT_ALL_CATEGORIES)); // DokuMan - 2012-03-27 - added missing "id" for xtc_draw_pull_down_menu
      } else {
        $manufacturer_dropdown .= xtc_draw_hidden_field('cat', $current_category_id).PHP_EOL;
        $options = array (array ('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)); // DokuMan - 2012-03-27 - added missing "id" for xtc_draw_pull_down_menu
      }
      if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        $manufacturer_dropdown .= xtc_draw_hidden_field('sort', $_GET['sort']).PHP_EOL;
      }
      while ($filterlist = xtc_db_fetch_array($filterlist_query, true)) {
        $options[] = array ('id' => $filterlist['id'], 'text' => $filterlist['name']);
      }
      $manufacturer_dropdown .= xtc_draw_pull_down_menu('filter_id', $options, isset($_GET['filter_id']) ? (int)$_GET['filter_id'] : '', 'onchange="this.form.submit()"').PHP_EOL;
      $manufacturer_dropdown .= '<noscript><input type="submit" value="'.SMALL_IMAGE_BUTTON_VIEW.'" id="filter_submit" /></noscript>'.PHP_EOL;
      $manufacturer_dropdown .= xtc_hide_session_id() .PHP_EOL; //Session ID nur anhängen, wenn Cookies deaktiviert sind
      $manufacturer_dropdown .= '</form>'.PHP_EOL;
    }
  }

  include (DIR_WS_MODULES.FILENAME_PRODUCT_LISTING);


/**
  * default content page
  *
  */
} else {

  $shop_content_data = $main->getContentData(5);

  $default_smarty->assign('title', $shop_content_data['content_heading']);

  include (DIR_WS_INCLUDES.FILENAME_CENTER_MODULES);

  $default_smarty->assign('text', str_replace('{$greeting}', xtc_customer_greeting(), $shop_content_data['content_text']));
  $default_smarty->assign('language', $_SESSION['language']);

  // set cache ID
  if (!CacheCheck()) {
    $default_smarty->caching = 0;
    $main_content = $default_smarty->fetch(CURRENT_TEMPLATE.'/module/main_content.html');
  } else {
    $default_smarty->caching = 1;
    $default_smarty->cache_lifetime = CACHE_LIFETIME;
    $default_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $_SESSION['language'].$_SESSION['currency'].$_SESSION['customer_id'];
    $main_content = $default_smarty->fetch(CURRENT_TEMPLATE.'/module/main_content.html', $cache_id);
  }
  $smarty->assign('main_content', $main_content);
}
?>