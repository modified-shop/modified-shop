<?php
/* -----------------------------------------------------------------------------------------
   $Id: specials.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.47 2003/05/27); www.oscommerce.com
   (c) 2003 nextcommerce (specials.php,v 1.12 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (specials.php 1292 2005-10-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if ($language_not_found === true) {
  $site_error = TEXT_SITE_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
  require (DIR_WS_INCLUDES.'header.php');

} else {

  $max_display_results = (isset($_SESSION['filter_set']) ? (int)$_SESSION['filter_set'] : MAX_DISPLAY_SPECIAL_PRODUCTS);

  $where = '';
  if (isset($_GET['filter_id']) && $_GET['filter_id'] != '') {
    $where = " AND p.manufacturers_id = '".(int)$_GET['filter_id']."' ";
  }

  $filter_join = '';
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

  $specials_query_raw = "SELECT p.*,
                                pd.products_name,
                                pd.products_short_description,
                                m.manufacturers_name,
                                s.expires_date,
                                s.specials_new_products_price,
                                s.specials_new_products_price AS price
                           FROM ".TABLE_PRODUCTS." p
                      LEFT JOIN ".TABLE_MANUFACTURERS." m
                                ON p.manufacturers_id = m.manufacturers_id
                           JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                ON p.products_id = pd.products_id
                                   AND trim(pd.products_name) != ''
                                   AND pd.language_id = ".(int)$_SESSION['languages_id']."
                          JOIN ".TABLE_SPECIALS." s
                                ON p.products_id = s.products_id
                                   AND s.status = '1'
                                ".$filter_join."
                          WHERE p.products_status = '1'
                                ".PRODUCTS_CONDITIONS_P."
                                ".$where."
                                ".((isset($_SESSION['filter_sorting'])) ? $_SESSION['filter_sorting'] : 'ORDER BY s.specials_date_added DESC');

  $specials_split = new splitPageResults($specials_query_raw, (isset($_GET['page']) ? (int)$_GET['page'] : 1), $max_display_results);

  if ($specials_split->number_of_rows == 0 || $_SESSION['customers_status']['customers_status_specials'] != '1') {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
  }

  $module_content = array();
  if (($specials_split->number_of_rows > 0)) {

    if (USE_PAGINATION_LIST == 'false') {
      $smarty->assign('NAVBAR', '<div style="width:100%;font-size:smaller">
                                   <div style="float:left">'.$specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS).'</div>
                                   <div style="float:right">'.TEXT_RESULT_PAGE.' '.$specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div>
                                   <br style="clear:both" />
                                 </div>');
    } else {
      $smarty->assign('DISPLAY_COUNT', $specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS));
      $smarty->assign('DISPLAY_LINKS', $specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))));
      $smarty->caching = 0;
      $pagination = $smarty->fetch(CURRENT_TEMPLATE.'/module/pagination.html');
      $smarty->assign('NAVBAR', $pagination);
      $smarty->assign('PAGINATION', $pagination);
    }

    $specials_query = xtc_db_query($specials_split->sql_query);
    while ($specials = xtc_db_fetch_array($specials_query)) {
      $module_content[] = $product->buildDataArray($specials);
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_SPECIALS, xtc_href_link(FILENAME_SPECIALS));

  require (DIR_WS_INCLUDES.'header.php');

  include (DIR_WS_MODULES.'listing_filter.php');

  $smarty->assign('language', $_SESSION['language']);
  $smarty->assign('module_content', $module_content);
  $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/specials.html');
  $smarty->assign('main_content', $main_content);
}

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>