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

$breadcrumb->add(NAVBAR_TITLE_SPECIALS, xtc_href_link(FILENAME_SPECIALS));

$specials_query_raw = "SELECT p.*,
                              pd.products_name,
                              pd.products_short_description,
                              m.manufacturers_name,
                              s.expires_date,
                              s.specials_new_products_price
                         FROM ".TABLE_PRODUCTS." p
                    LEFT JOIN ".TABLE_MANUFACTURERS." m
                              ON p.manufacturers_id = m.manufacturers_id
                         JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                              ON p.products_id = pd.products_id
                                 AND pd.language_id = ".$_SESSION['languages_id']."
                        JOIN ".TABLE_SPECIALS." s
                              ON p.products_id = s.products_id
                                 AND s.status = '1'
                        WHERE p.products_status = '1'
                              ".PRODUCTS_CONDITIONS_P."
                     ORDER BY s.specials_date_added DESC";

$specials_split = new splitPageResults($specials_query_raw, (isset($_GET['page']) ? (int)$_GET['page'] : 1), MAX_DISPLAY_SPECIAL_PRODUCTS);

if ($specials_split->number_of_rows==0) {
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
}

require (DIR_WS_INCLUDES.'header.php');

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

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('module_content', $module_content);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/specials.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>