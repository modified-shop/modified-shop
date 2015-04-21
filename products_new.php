<?php
/* -----------------------------------------------------------------------------------------
   $Id: products_new.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(products_new.php,v 1.25 2003/05/27); www.oscommerce.com
   (c) 2003  nextcommerce (products_new.php,v 1.16 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (products_new.php 1292 2005-10-07)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3          Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed function
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');

$breadcrumb->add(NAVBAR_TITLE_PRODUCTS_NEW, xtc_href_link(FILENAME_PRODUCTS_NEW));

require (DIR_WS_INCLUDES.'header.php');

$days = '';
if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
	$date_new_products = date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
	$days = " AND p.products_date_added > '".$date_new_products."' ";
}

$products_new_query_raw = "SELECT DISTINCT p.*,
                                           pd.products_name,
                                           pd.products_short_description,
                                           m.manufacturers_name
                                      FROM ".TABLE_PRODUCTS." p 
                                 LEFT JOIN ".TABLE_MANUFACTURERS." m
                                           ON p.manufacturers_id = m.manufacturers_id
                                 LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                           ON p.products_id = pd.products_id
                                              AND pd.language_id = '".$_SESSION['languages_id']."'
                                              AND trim(pd.products_name) != ''
                                      JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                           ON p.products_id = p2c.products_id
                                      JOIN ".TABLE_CATEGORIES." c
                                           ON c.categories_id = p2c.categories_id
                                              AND c.categories_status=1
                                     WHERE p.products_status = '1'
                                           ".PRODUCTS_CONDITIONS_P."
                                           ".$days."
                                  ORDER BY p.products_date_added DESC";

$products_new_split = new splitPageResults($products_new_query_raw, (isset($_GET['page']) ? (int)$_GET['page'] : 1), MAX_DISPLAY_PRODUCTS_NEW, 'p.products_id');

$module_content = array();
if (($products_new_split->number_of_rows > 0)) {

  if (USE_PAGINATION_LIST == 'false') {
    $smarty->assign('NAVIGATION_BAR', '<div class="smallText" style="clear:both;">
                                         <div style="float:left;">'.$products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW).'</div> 
                                         <div align="right">'.TEXT_RESULT_PAGE.' '.$products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div> 
                                         <br style="clear:both" />
                                       </div>'); 
  } else {
    $smarty->assign('DISPLAY_COUNT', $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW));
    $smarty->assign('DISPLAY_LINKS', $products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))));
    $smarty->caching = 0;
    $pagination = $smarty->fetch(CURRENT_TEMPLATE.'/module/pagination.html');
    $smarty->assign('NAVIGATION_BAR', $pagination);  
    $smarty->assign('PAGINATION', $pagination);  
  }
  
	$products_new_query = xtc_db_query($products_new_split->sql_query);
	while ($products_new = xtc_db_fetch_array($products_new_query)) {
		$module_content[] = $product->buildDataArray($products_new);
	}
	
} else {

	$new_products_query = "SELECT DISTINCT p.*,
                                         pd.products_name,
                                         pd.products_short_description,
                                         m.manufacturers_name
                                    FROM ".TABLE_PRODUCTS." p
                               LEFT JOIN ".TABLE_MANUFACTURERS." m
                                         ON p.manufacturers_id = m.manufacturers_id
                               LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                         ON p.products_id = pd.products_id
                                            AND pd.language_id = '".(int) $_SESSION['languages_id']."'
                                    JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                                         ON p.products_id = p2c.products_id
                                    JOIN ".TABLE_CATEGORIES." c
                                         ON c.categories_id = p2c.categories_id
                                            AND c.categories_status=1
                                   WHERE p.products_status = '1'
                                         ".PRODUCTS_CONDITIONS_P."
                                ORDER BY p.products_date_added DESC";

  $products_new_split = new splitPageResults($new_products_query, (isset($_GET['page']) ? (int)$_GET['page'] : 1), MAX_DISPLAY_PRODUCTS_NEW, 'p.products_id');

  if (($products_new_split->number_of_rows > 0)) {
    if (USE_PAGINATION_LIST == 'false') {
      $smarty->assign('NAVIGATION_BAR', '<div class="smallText" style="clear:both;">
                                           <div style="float:left;">'.$products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW).'</div> 
                                           <div align="right">'.TEXT_RESULT_PAGE.' '.$products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div> 
                                           <br style="clear:both" />
                                         </div>'); 
    } else {
      $smarty->assign('DISPLAY_COUNT', $products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW));
      $smarty->assign('DISPLAY_LINKS', $products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))));
      $smarty->caching = 0;
      $pagination = $smarty->fetch(CURRENT_TEMPLATE.'/module/pagination.html');
      $smarty->assign('NAVIGATION_BAR', $pagination);  
      $smarty->assign('PAGINATION', $pagination);  
    }
  
    $products_new_query = xtc_db_query($products_new_split->sql_query);
    while ($products_new = xtc_db_fetch_array($products_new_query)) {
      $module_content[] = $product->buildDataArray($products_new);
    }
	}	

	$smarty->assign('NO_NEW_PRODUCTS', TEXT_NO_NEW_PRODUCTS);
}

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$smarty->assign('module_content', $module_content);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/new_products_overview.html');
$smarty->assign('main_content', $main_content);

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>