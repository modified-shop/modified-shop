<?php
/* -----------------------------------------------------------------------------------------
   $Id: products_new.php 4221 2013-01-11 10:18:52Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce 
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(products_new.php,v 1.25 2003/05/27); www.oscommerce.com 
   (c) 2003	 nextcommerce (products_new.php,v 1.16 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3        	Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed function
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');

$breadcrumb->add(NAVBAR_TITLE_PRODUCTS_NEW, xtc_href_link(FILENAME_PRODUCTS_NEW));

require (DIR_WS_INCLUDES.'header.php');

$products_new_array = array ();
$fsk_lock = '';
if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
	$fsk_lock = ' and p.products_fsk18!=1';
}
$group_check = '';
if (GROUP_CHECK == 'true') {
	$group_check = " and p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
}
if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
	$date_new_products = date("Y.m.d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
	$days = " and p.products_date_added > '".$date_new_products."' ";
}
//BOF - web28 - 2010.06.09 - added p.products_shippingtime
$products_new_query_raw = "select distinct
                                    p.products_id,
                                    p.products_fsk18,
                                    pd.products_name,
                                    pd.products_short_description,
                                    p.products_image,
                                    p.products_price,
                               	    p.products_vpe,
                               	    p.products_vpe_status,
                               	    p.products_vpe_value,
                                    p.products_tax_class_id,
									p.products_shippingtime,
                                    p.products_date_added,
                                    m.manufacturers_name
                           from ".TABLE_PRODUCTS." p
                           left join ".TABLE_MANUFACTURERS." m
                                    on p.manufacturers_id = m.manufacturers_id
                           left join ".TABLE_PRODUCTS_DESCRIPTION." pd
                                    on p.products_id = pd.products_id,
                                    ".TABLE_CATEGORIES." c,
                                    ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                           where pd.language_id = '".(int) $_SESSION['languages_id']."'
                           and c.categories_status=1
                           and p.products_id = p2c.products_id
                           and c.categories_id = p2c.categories_id
                           and products_status = '1'
                                    ".$group_check."
                                    ".$fsk_lock."                                    
                                    ".$days."
                           order by p.products_date_added DESC ";

//EOF - web28 - 2010.06.09 - added p.products_shippingtime

$products_new_split = new splitPageResults($products_new_query_raw, $_GET['page'], MAX_DISPLAY_PRODUCTS_NEW, 'p.products_id');
//BOF - Hetfield - 2009-08-11 - no longer empty site products_new.php
if (($products_new_split->number_of_rows > 0)) {
$module_content = '';
//BOF - Hetfield - 2009-08-11 - replace table with div
	$smarty->assign('NAVIGATION_BAR', '
		   <div style="width:100%;font-size:smaller">
		          <div style="float:left">'.$products_new_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW).'</div>
		          <div style="float:right">'.TEXT_RESULT_PAGE.' '.$products_new_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div>
		          <br style="clear:both" />
		   </div>		
		   ');
//EOF - Hetfield - 2009-08-11 - replace table with div
	$products_new_query = xtc_db_query($products_new_split->sql_query);
	while ($products_new = xtc_db_fetch_array($products_new_query)) {
		//BOF - Hetfield - 2009-08-11 - products_new uses now the product class
		$module_content[] = $product->buildDataArray($products_new);
		//EOF - Hetfield - 2009-08-11 - products_new uses now the product class
	}
} else {
    //BOF - web28 - 2010.06.09 - added p.products_shippingtime
	$new_products_query = "select distinct
                                    p.products_id,
                                    p.products_fsk18,
                                    pd.products_name,
                                    pd.products_short_description,
                                    p.products_image,
                                    p.products_price,
                               	    p.products_vpe,
                               	    p.products_vpe_status,
                               	    p.products_vpe_value,
                                    p.products_tax_class_id,
									p.products_shippingtime,
                                    p.products_date_added,
									m.manufacturers_name
                           from ".TABLE_PRODUCTS." p
                           left join ".TABLE_MANUFACTURERS." m
                                    on p.manufacturers_id = m.manufacturers_id
                           left join ".TABLE_PRODUCTS_DESCRIPTION." pd
                                    on p.products_id = pd.products_id,
                                    ".TABLE_CATEGORIES." c,
                                    ".TABLE_PRODUCTS_TO_CATEGORIES." p2c 
                           where pd.language_id = '".(int) $_SESSION['languages_id']."'
                           and c.categories_status=1
                           and p.products_id = p2c.products_id
                           and c.categories_id = p2c.categories_id
                           and products_status = '1'
                                    ".$group_check."
                                    ".$fsk_lock."
	                       order by p.products_date_added DESC limit 10";
	
	//EOF - web28 - 2010.06.09 - added p.products_shippingtime
	
	$module_content = array ();
	$new_products_query = xtDBquery($new_products_query);
	while ($new_products = xtc_db_fetch_array($new_products_query, true)) {
		$module_content[] = $product->buildDataArray($new_products);
	
	}
	$smarty->assign('NO_NEW_PRODUCTS', TEXT_NO_NEW_PRODUCTS);
}
//EOF - Hetfield - 2009-08-11 - no longer empty site products_new.php
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