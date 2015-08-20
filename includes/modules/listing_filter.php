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
$filter_dropdown = array();

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

  // filter
  $filter_join = '';
  if (isset($_GET['filter']) && is_array($_GET['filter'])) {
    $fi = 1;
    foreach ($_GET['filter'] as $options_id => $values_id) {
      if ($values_id != '') {
        $filter_join .= "JOIN ".TABLE_PRODUCTS_TAGS." pt".$fi." 
                              ON pt".$fi.".products_id = p.products_id
                                 AND pt".$fi.".options_id = '".$options_id."'
                                 AND pt".$fi.".values_id = '".$values_id."' ";
        $fi ++;
      }
    }
  }
  
  // manufacturers
  $join = '';
  $where = '';
  $select = "m.manufacturers_id as id,
             m.manufacturers_name as name ";
  if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
    $select = "c.categories_id as id,
               cd.categories_name as name ";
    $join = " JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                   ON p2c.products_id = p.products_id
              JOIN ".TABLE_CATEGORIES." c 
                   ON c.categories_id = p2c.categories_id 
                      ".CATEGORIES_CONDITIONS_C."
              JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd 
                   ON cd.categories_id = p2c.categories_id
                      AND cd.language_id = '".(int) $_SESSION['languages_id']."' ";
    $where = " AND manufacturers_id = '".(int)$_GET['manufacturers_id']."' ";
  } elseif (isset($current_category_id) && $current_category_id > 0) {
    $join = " JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                   ON p2c.products_id = p.products_id
                      AND p2c.categories_id = '".$current_category_id."' ";
  } elseif (basename($PHP_SELF) == FILENAME_SPECIALS) {
    $join = " JOIN ".TABLE_SPECIALS." s 
                   ON p.products_id = s.products_id
                      AND s.status = '1' ";
  } elseif (basename($PHP_SELF) == FILENAME_PRODUCTS_NEW) {
    if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0' && $daysfound == true) {
      $date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
      $where = " AND p.products_date_added > '".$date_new_products."' ";
    }
  } elseif (basename($PHP_SELF) == FILENAME_ADVANCED_SEARCH_RESULT) {
    $where = " AND p.products_id IN ('".implode("', '", $products_search_array)."') ";
  }

  $filterlist_sql = "SELECT DISTINCT ".$select."
                                FROM ".TABLE_PRODUCTS." p
                                JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                     ON p.products_id = pd.products_id
                                        AND pd.language_id = '".$_SESSION['languages_id']."'
                                        AND trim(pd.products_name) != ''
                                JOIN ".TABLE_MANUFACTURERS." m 
                                     ON m.manufacturers_id = p.manufacturers_id
                                     ".$join."
                                     ".$filter_join."
                               WHERE p.products_status = '1'
                                     ".$where."
                                     ".PRODUCTS_CONDITIONS_P."
                            ORDER BY name";
  
  
  $filterlist_query = xtDBquery($filterlist_sql);
  if (xtc_db_num_rows($filterlist_query, true) > 0) {
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
    if (isset($_GET['filter']) && is_array($_GET['filter'])) {
      foreach ($_GET['filter'] as $key => $val) {
       $manufacturer_dropdown .= xtc_draw_hidden_field('filter['.$key.']', $val).PHP_EOL;
      }
    }
    while ($filterlist = xtc_db_fetch_array($filterlist_query, true)) {
      $options[] = array ('id' => $filterlist['id'], 'text' => $filterlist['name']);
    }
    $manufacturer_dropdown .= xtc_draw_pull_down_menu('filter_id', $options, isset($_GET['filter_id']) ? (int)$_GET['filter_id'] : '', 'onchange="this.form.submit()"').PHP_EOL;
    $manufacturer_dropdown .= '<noscript><input type="submit" value="'.SMALL_IMAGE_BUTTON_VIEW.'" id="filter_submit" /></noscript>'.PHP_EOL;
    $manufacturer_dropdown .= xtc_hide_session_id() .PHP_EOL; //Session ID nur anh‰ngen, wenn Cookies deaktiviert sind
    $manufacturer_dropdown .= '</form>'.PHP_EOL;
  }


  // filter
  $join = '';  
  $where = '';
  $filterlist_sql = '';
  if ((isset($_GET['filter_id']) && $_GET['filter_id'] > 0)
      || (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0)
      )
  {
    $where .= " AND p.manufacturers_id = '".(int)((isset($_GET['filter_id'])) ? $_GET['filter_id'] : $_GET['manufacturers_id'])."' ";
  }
  if (isset($current_category_id) && $current_category_id > 0) {
    $join .= " JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                    ON p2c.products_id = p.products_id
                       AND p2c.categories_id = '".$current_category_id."' ";
  }
  if (basename($PHP_SELF) == FILENAME_SPECIALS) {
    $join .= " JOIN ".TABLE_SPECIALS." s 
                    ON p.products_id = s.products_id
                       AND s.status = '1' ";
  } elseif (basename($PHP_SELF) == FILENAME_PRODUCTS_NEW) {
    if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0' && $daysfound == true) {
      $date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
      $where .= " AND p.products_date_added > '".$date_new_products."' ";
    }
  } elseif (basename($PHP_SELF) == FILENAME_ADVANCED_SEARCH_RESULT) {
    $where .= " AND p.products_id IN ('".implode("', '", $products_search_array)."') ";
  }
  
  $filterlist_sql = "SELECT DISTINCT pto.options_id,
                                     pto.options_name,
                                     ptv.values_id,
                                     ptv.values_name
                                FROM ".TABLE_PRODUCTS." p
                                JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                     ON p.products_id = pd.products_id
                                        AND pd.language_id = '".$_SESSION['languages_id']."'
                                        AND trim(pd.products_name) != ''
                                JOIN ".TABLE_PRODUCTS_TAGS." pt
                                     ON pt.products_id = p.products_id
                                JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                     ON pt.options_id = pto.options_id
                                        AND pto.languages_id = '".$_SESSION['languages_id']."'
                                        AND pto.filter = '1'
                                JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                     ON pto.options_id = ptv.options_id
                                        AND pt.values_id = ptv.values_id
                                        AND ptv.languages_id = '".$_SESSION['languages_id']."'
                                        AND ptv.filter = '1'
                                     ".$join."
                                     ".$filter_join."
                               WHERE p.products_status = '1'
                                     ".$where."
                                     ".PRODUCTS_CONDITIONS_P."
                            ORDER BY pto.sort_order, ptv.sort_order";                           

  $filterlist_query = xtDBquery($filterlist_sql);
  if (xtc_db_num_rows($filterlist_query, true) > 0) {
    $options = array();
    while ($filterlist = xtc_db_fetch_array($filterlist_query, true)) {
      $options[$filterlist['options_id']]['NAME'] = $filterlist['options_name'];
      $options[$filterlist['options_id']][] = array ('id' => $filterlist['values_id'], 'text' => $filterlist['values_name']);
    }
        
    foreach ($options as $options_id => $values) {
      
      $options_array = array (array ('id' => '', 'text' => $values['NAME']));
      unset($values['NAME']);
      $options_array = array_merge($options_array, $values);
            
      $filter_dropdown[$options_id] .= xtc_draw_form('filter', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('page', 'show', 'cat'))), 'get');
      if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] > 0) {
        $filter_dropdown[$options_id] .= xtc_draw_hidden_field('manufacturers_id', (int)$_GET['manufacturers_id']).PHP_EOL;
      } else {
        $filter_dropdown[$options_id] .= xtc_draw_hidden_field('cat', $current_category_id).PHP_EOL;
      }
      if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        $filter_dropdown[$options_id] .= xtc_draw_hidden_field('sort', $_GET['sort']).PHP_EOL;
      }
      if (isset($_GET['keywords']) && !empty($_GET['keywords'])) {
        $filter_dropdown[$options_id] .= xtc_draw_hidden_field('keywords', $_GET['keywords']).PHP_EOL;
      }
      if (isset($_GET['filter_id']) && !empty($_GET['filter_id'])) {
        $filter_dropdown[$options_id] .= xtc_draw_hidden_field('filter_id', $_GET['filter_id']).PHP_EOL;
      }
      if (isset($_GET['filter']) && is_array($_GET['filter'])) {
        foreach ($_GET['filter'] as $key => $val) {
          if ($key != $options_id) {
            $filter_dropdown[$options_id] .= xtc_draw_hidden_field('filter['.$key.']', $val).PHP_EOL;
          }
        }
      }
      $filter_dropdown[$options_id] .= xtc_draw_pull_down_menu('filter['.$options_id.']', $options_array, isset($_GET['filter'][$options_id]) ? (int)$_GET['filter'][$options_id] : '', 'onchange="this.form.submit()"').PHP_EOL;
      $filter_dropdown[$options_id] .= '<noscript><input type="submit" value="'.SMALL_IMAGE_BUTTON_VIEW.'" id="filter_submit" /></noscript>'.PHP_EOL;
      $filter_dropdown[$options_id] .= xtc_hide_session_id() .PHP_EOL; //Session ID nur anh‰ngen, wenn Cookies deaktiviert sind
      $filter_dropdown[$options_id] .= '</form>'.PHP_EOL;
      
    }
  }
}

$filter_smarty->assign('language', $_SESSION['language']);
$filter_smarty->assign('FILTER_MANUFACTURER', $manufacturer_dropdown);
$filter_smarty->assign('FILTER_SORT', $filter_sort_dropdown);
$filter_smarty->assign('FILTER_SET', $filter_set_dropdown);
$filter_smarty->assign('FILTER_TAG', $filter_dropdown);
$filter_smarty->assign('LINK_DISPLAY_LIST', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('show')).'show=list', 'NONSSL'));
$filter_smarty->assign('LINK_DISPLAY_BOX', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('show')).'show=box', 'NONSSL'));
$filter_smarty->assign('LINK_FILTER_RESET', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('filter', 'show', 'filter_id')), 'NONSSL'));

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