<?php
/* -----------------------------------------------------------------------------------------
   $Id: specials.php 4221 2013-01-11 10:18:52Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.47 2003/05/27); www.oscommerce.com 
   (c) 2003	 nextcommerce (specials.php,v 1.12 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

require_once (DIR_FS_INC.'xtc_get_short_description.inc.php');

$breadcrumb->add(NAVBAR_TITLE_SPECIALS, xtc_href_link(FILENAME_SPECIALS));

//fsk18 lock
$fsk_lock = '';
if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
	$fsk_lock = ' and p.products_fsk18!=1';
}
if (GROUP_CHECK == 'true') {
	$group_check = " and p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
}
$specials_query_raw = "select p.products_id,
                                pd.products_name,
                                p.products_price,
                                p.products_tax_class_id,p.products_shippingtime,
                                p.products_image,p.products_vpe_status,p.products_vpe_value,p.products_vpe,p.products_fsk18,
                                s.specials_new_products_price from ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd, ".TABLE_SPECIALS." s
                                where p.products_status = '1'
                                and s.products_id = p.products_id
                                and p.products_id = pd.products_id
                                ".$group_check."
                                ".$fsk_lock."
                                and pd.language_id = '".(int) $_SESSION['languages_id']."'
                                and s.status = '1' order by s.specials_date_added DESC";
$specials_split = new splitPageResults($specials_query_raw, $_GET['page'], MAX_DISPLAY_SPECIAL_PRODUCTS);

$module_content = '';
$row = 0;
if ($specials_split->number_of_rows==0) xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
require (DIR_WS_INCLUDES.'header.php');
$specials_query = xtc_db_query($specials_split->sql_query);



while ($specials = xtc_db_fetch_array($specials_query)) {
	$module_content[] = $product->buildDataArray($specials);
}

if (($specials_split->number_of_rows > 0)) {
//BOF - Dokuman - 2009-06-05 - replace table with div
/*
	$smarty->assign('NAVBAR', '
	<table border="0" width="100%" cellspacing="0" cellpadding="2">
	          <tr>
	            <td class="smallText">'.$specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS).'</td>
	            <td align="right" class="smallText">'.TEXT_RESULT_PAGE.' '.$specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</td>
	          </tr>
	        </table>
  ');	        
*/
	$smarty->assign('NAVBAR', '
	<div style="width:100%;font-size:smaller">
		<div style="float:left">'.$specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS).'</div>
		<div style="float:right">'.TEXT_RESULT_PAGE.' '.$specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div>
		<br style="clear:both" />
	</div>
	');
//EOF - Dokuman - 2009-06-05 - replace table with div
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('module_content', $module_content);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/specials.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>