<?php
/* -----------------------------------------------------------------------------------------
   $Id: stylesheet.css 4246 2013-01-11 14:36:07Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$filter_smarty = new Smarty;
$filter_smarty->caching = false;
$filter_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$filter_set_dropdown = '';
$filter_sort_dropdown = '';
$manufacturer_dropdown = '';

// optional Product List Filter
if (PRODUCT_LIST_FILTER == 'true') {
  $filter_set_const = strtoupper(substr(basename($PHP_SELF), 0, -4));
    
  if (defined('DISPLAY_FILTER_'.$filter_set_const)) {
    $filter_vars_array = explode(',', constant('DISPLAY_FILTER_'.$filter_set_const));

    $filter_set_array = array(
      array('id' => '',  'text' => TEXT_FILTER_SETTING_DEFAULT),
    );

    for ($i=0, $n=count($filter_vars_array); $i<$n; $i++) {
      if (trim($filter_vars_array[$i]) != 'all') {
        $filter_set_array[] = array('id' => $filter_vars_array[$i], 'text' => sprintf(TEXT_FILTER_SETTING, trim($filter_vars_array[$i])));
      } else {
        $filter_set_array[] = array('id' => '999999', 'text' => TEXT_FILTER_SETTING_ALL);
      }
    }

    $filter_set_dropdown  = xtc_draw_form('set', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('show'))), 'post').PHP_EOL;
    $filter_set_dropdown .= xtc_draw_pull_down_menu('filter_set', $filter_set_array, ((isset($_SESSION['filter_set'])) ? (int)$_SESSION['filter_set'] : ''), 'onchange="this.form.submit()"').PHP_EOL;
    $filter_set_dropdown .= '<noscript><input type="submit" value="'.SMALL_IMAGE_BUTTON_VIEW.'" id="filter_set_submit" /></noscript>'.PHP_EOL;
    $filter_set_dropdown .= '</form>'.PHP_EOL;
  }
  
  $filter_sort_array = array(
    array ('id' => '',  'text' => TEXT_FILTER_SORTING_DEFAULT),
    array ('id' => '1', 'text' => TEXT_FILTER_SORTING_ABC_ASC),
    array ('id' => '2', 'text' => TEXT_FILTER_SORTING_ABC_DESC),
    array ('id' => '3', 'text' => TEXT_FILTER_SORTING_PRICE_ASC),
    array ('id' => '4', 'text' => TEXT_FILTER_SORTING_PRICE_DESC),
    array ('id' => '5', 'text' => TEXT_FILTER_SORTING_DATE_DESC),
    array ('id' => '6', 'text' => TEXT_FILTER_SORTING_DATE_ASC),
    array ('id' => '7', 'text' => TEXT_FILTER_SORTING_ORDER_DESC),
  );

  $filter_sort_dropdown  = xtc_draw_form('sort', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('show'))), 'post').PHP_EOL;
  $filter_sort_dropdown .= xtc_draw_pull_down_menu('filter_sort', $filter_sort_array, ((isset($_SESSION['filter_sort'])) ? (int)$_SESSION['filter_sort'] : ''), 'onchange="this.form.submit()"').PHP_EOL;
  $filter_sort_dropdown .= '<noscript><input type="submit" value="'.SMALL_IMAGE_BUTTON_VIEW.'" id="filter_sort_submit" /></noscript>'.PHP_EOL;
  $filter_sort_dropdown .= '</form>'.PHP_EOL;

  
  // manufacturers
  if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    $filterlist_sql = "SELECT DISTINCT c.categories_id as id,
                                       cd.categories_name as name
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON p.products_id = pd.products_id
                                          AND pd.language_id = '".$_SESSION['languages_id']."'
                                          AND trim(pd.products_name) != ''
                                  JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                       ON p2c.products_id = p.products_id
                                  JOIN ".TABLE_CATEGORIES." c 
                                       ON c.categories_id = p2c.categories_id 
                                          ".CATEGORIES_CONDITIONS_C."
                                  JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                                       ON cd.categories_id = p2c.categories_id
                                          AND cd.language_id = '".(int) $_SESSION['languages_id']."'
                                 WHERE p.products_status = '1'
                                   AND p.manufacturers_id = '".(int) $_GET['manufacturers_id']."'
                                       ".PRODUCTS_CONDITIONS_P."
                              ORDER BY cd.categories_name";
  } elseif (isset($current_category_id) && $current_category_id > 0) {
    $filterlist_sql = "SELECT DISTINCT m.manufacturers_id as id,
                                       m.manufacturers_name as name
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON p.products_id = pd.products_id
                                          AND pd.language_id = '".$_SESSION['languages_id']."'
                                          AND trim(pd.products_name) != ''
                                  JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                       ON p2c.products_id = p.products_id
                                          AND p2c.categories_id = '".$current_category_id."'
                                  JOIN ".TABLE_MANUFACTURERS." m on m.manufacturers_id = p.manufacturers_id
                                 WHERE p.products_status = '1'
                                       ".PRODUCTS_CONDITIONS_P."
                              ORDER BY m.manufacturers_name";
  } elseif (basename($PHP_SELF) == FILENAME_SPECIALS) {
    $filterlist_sql = "SELECT DISTINCT m.manufacturers_id as id,
                                       m.manufacturers_name as name
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON p.products_id = pd.products_id
                                          AND pd.language_id = '".$_SESSION['languages_id']."'
                                          AND trim(pd.products_name) != ''
                                  JOIN ".TABLE_SPECIALS." s 
                                       ON p.products_id = s.products_id
                                          AND s.status = '1'
                                  JOIN ".TABLE_MANUFACTURERS." m on m.manufacturers_id = p.manufacturers_id
                                 WHERE p.products_status = '1'
                                       ".PRODUCTS_CONDITIONS_P."
                              ORDER BY m.manufacturers_name";
  } elseif (basename($PHP_SELF) == FILENAME_PRODUCTS_NEW) {
    $days = '';
    if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
      $date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
      $days = " AND p.products_date_added > '".$date_new_products."' ";
    }
    $filterlist_sql = "SELECT DISTINCT m.manufacturers_id as id,
                                       m.manufacturers_name as name
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON p.products_id = pd.products_id
                                          AND pd.language_id = '".$_SESSION['languages_id']."'
                                          AND trim(pd.products_name) != ''
                                  JOIN ".TABLE_MANUFACTURERS." m on m.manufacturers_id = p.manufacturers_id
                                 WHERE p.products_status = '1'
                                       ".$days."
                                       ".PRODUCTS_CONDITIONS_P."
                              ORDER BY m.manufacturers_name";
  } elseif (basename($PHP_SELF) == FILENAME_ADVANCED_SEARCH_RESULT) {
    $filterlist_sql = "SELECT DISTINCT m.manufacturers_id as id,
                                       m.manufacturers_name as name
                                  FROM ".TABLE_PRODUCTS." p
                                  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                       ON p.products_id = pd.products_id
                                          AND pd.language_id = '".$_SESSION['languages_id']."'
                                          AND trim(pd.products_name) != ''
                                  JOIN ".TABLE_MANUFACTURERS." m on m.manufacturers_id = p.manufacturers_id
                                 WHERE p.products_status = '1'
                                   AND p.products_id IN ('".implode("', '", $products_search_array)."')
                                       ".PRODUCTS_CONDITIONS_P."
                              ORDER BY m.manufacturers_name";
  }
  
  $filterlist_query = xtDBquery($filterlist_sql);
  if (xtc_db_num_rows($filterlist_query, true) > 1) {
    $manufacturer_dropdown = xtc_draw_form('filter', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('page', 'show', 'cat'))), 'get');
    if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
      $manufacturer_dropdown .= xtc_draw_hidden_field('manufacturers_id', (int)$_GET['manufacturers_id']).PHP_EOL;
      $options = array (array ('id' => '', 'text' => TEXT_ALL_CATEGORIES));
    } else {
      $manufacturer_dropdown .= xtc_draw_hidden_field('cat', $current_category_id).PHP_EOL;
      $options = array (array ('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
    }
    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
      $manufacturer_dropdown .= xtc_draw_hidden_field('sort', $_GET['sort']).PHP_EOL;
    }
    if (isset($_GET['keywords']) && !empty($_GET['keywords'])) {
      $manufacturer_dropdown .= xtc_draw_hidden_field('keywords', $_GET['keywords']).PHP_EOL;
    }
    while ($filterlist = xtc_db_fetch_array($filterlist_query, true)) {
      $options[] = array ('id' => $filterlist['id'], 'text' => $filterlist['name']);
    }
    $manufacturer_dropdown .= xtc_draw_pull_down_menu('filter_id', $options, isset($_GET['filter_id']) ? (int)$_GET['filter_id'] : '', 'onchange="this.form.submit()"').PHP_EOL;
    $manufacturer_dropdown .= '<noscript><input type="submit" value="'.SMALL_IMAGE_BUTTON_VIEW.'" id="filter_submit" /></noscript>'.PHP_EOL;
    $manufacturer_dropdown .= xtc_hide_session_id() .PHP_EOL; //Session ID nur anh‰ngen, wenn Cookies deaktiviert sind
    $manufacturer_dropdown .= '</form>'.PHP_EOL;
  }
}

$filter_smarty->assign('language', $_SESSION['language']);
$filter_smarty->assign('FILTER_MANUFACTURER', $manufacturer_dropdown);
$filter_smarty->assign('FILTER_SORT', $filter_sort_dropdown);
$filter_smarty->assign('FILTER_SET', $filter_set_dropdown);
$filter_smarty->assign('LINK_DISPLAY_LIST', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('show')).'show=list', 'NONSSL'));
$filter_smarty->assign('LINK_DISPLAY_BOX', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('show')).'show=box', 'NONSSL'));

$filter_smarty->caching = 0;
if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/listing_filter.html')) {
  $module_filter = $filter_smarty->fetch(CURRENT_TEMPLATE.'/module/listing_filter.html');
}

if (is_object($smarty)) {
  $smarty->assign('LISTING_FILTER', $module_filter);
}

if (is_object($module_smarty)) {
  $module_smarty->assign('LISTING_FILTER', $module_filter);
}
?>