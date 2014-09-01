<?php

/* -----------------------------------------------------------------------------------------
   $Id: account.php 4221 2013-01-11 10:18:52Z gtb-modified $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (account.php,v 1.59 2003/05/19); www.oscommerce.com
   (c) 2003      nextcommerce (account.php,v 1.12 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'xtc_count_customer_orders.inc.php');
require_once (DIR_FS_INC.'xtc_date_short.inc.php');
require_once (DIR_FS_INC.'xtc_get_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_product_path.inc.php');
require_once (DIR_FS_INC.'xtc_get_products_name.inc.php');
require_once (DIR_FS_INC.'xtc_get_products_image.inc.php');

//BOF - DokuMan - 2010-03-02 - redirect not logged in users to login page instead
if (!isset ($_SESSION['customer_id'])) { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}
//EOF - DokuMan - 2010-03-02 - redirect not logged in users to login page instead

$breadcrumb->add(NAVBAR_TITLE_ACCOUNT, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

if ($messageStack->size('account') > 0)
	$smarty->assign('error_message', $messageStack->output('account'));

$i = 0;
//BOF - DokuMan - 2010-02-28 - set undefined Undefined variables
//$max = count($_SESSION['tracking']['products_history']);
$max = isset($_SESSION['tracking']['products_history']) ? count($_SESSION['tracking']['products_history']) : 0;
$products_history = array();
$also_purchased_history = array();
//EOF - DokuMan - 2010-02-28 - set undefined Undefined variables

while ($i < $max) {

	
	$product_history_query = xtDBquery("select * from ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd where p.products_id=pd.products_id and pd.language_id='".(int) $_SESSION['languages_id']."' and p.products_status = '1' and p.products_id = '".$_SESSION['tracking']['products_history'][$i]."'");
	$history_product = xtc_db_fetch_array($product_history_query, true);
$cpath = xtc_get_product_path($_SESSION['tracking']['products_history'][$i]);
	if ($history_product['products_status'] != 0) {

		$history_product = array_merge($history_product,array('cat_url' => xtc_href_link(FILENAME_DEFAULT, 'cPath='.$cpath)));
		$products_history[] = $product->buildDataArray($history_product);
	}
	$i ++;
}

$order_content = '';
if (xtc_count_customer_orders() > 0) {

	$orders_query = xtc_db_query("select
	                                  o.orders_id,
	                                  o.date_purchased,
	                                  o.delivery_name,
	                                  o.delivery_country,
	                                  o.billing_name,
	                                  o.billing_country,
	                                  ot.text as order_total,
	                                  s.orders_status_name
	                              from ".TABLE_ORDERS." o, ".TABLE_ORDERS_TOTAL."
	                                  ot, ".TABLE_ORDERS_STATUS." s
	                              where o.customers_id = '".(int) $_SESSION['customer_id']."'
	                              and o.orders_id = ot.orders_id
	                              and ot.class = 'ot_total'
	                              and o.orders_status = s.orders_status_id
	                              and s.language_id = '".(int) $_SESSION['languages_id']."'
	                              order by orders_id desc limit 3");

	while ($orders = xtc_db_fetch_array($orders_query)) {
		if (xtc_not_null($orders['delivery_name'])) {
			$order_name = $orders['delivery_name'];
			$order_country = $orders['delivery_country'];
		} else {
			$order_name = $orders['billing_name'];
			$order_country = $orders['billing_country'];
		}
		$order_content[] = array ('ORDER_ID' => $orders['orders_id'], 'ORDER_DATE' => xtc_date_short($orders['date_purchased']), 'ORDER_STATUS' => $orders['orders_status_name'], 'ORDER_TOTAL' => $orders['order_total'], 'ORDER_LINK' => xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$orders['orders_id'], 'SSL'), 'ORDER_BUTTON' => '<a href="'.xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$orders['orders_id'], 'SSL').'">'.xtc_image_button('small_view.gif', SMALL_IMAGE_BUTTON_VIEW).'</a>');
	}

}
$smarty->assign('LINK_EDIT', xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));
$smarty->assign('LINK_ADDRESS', xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
$smarty->assign('LINK_PASSWORD', xtc_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));
//BOF - Dokuman - 2009-08-21 - Added 'delete account' functionality for customers
//Link_Delete button will not work for Admin (ID1) or not logged in users
if(isset($_SESSION['customer_id']) && ($_SESSION['customer_id']!= '1')) {
  $smarty->assign('LINK_DELETE', xtc_href_link(FILENAME_ACCOUNT_DELETE, '', 'SSL'));
}
//EOF - Dokuman - 2009-08-21 - Added 'delete account' functionality for customers
if (!isset ($_SESSION['customer_id']))
	$smarty->assign('LINK_LOGIN', xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
$smarty->assign('LINK_ORDERS', xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$smarty->assign('LINK_NEWSLETTER', xtc_href_link(FILENAME_NEWSLETTER, '', 'SSL'));
$smarty->assign('LINK_ALL', xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$smarty->assign('order_content', $order_content);
$smarty->assign('products_history', $products_history);
$smarty->assign('also_purchased_history', $also_purchased_history);
$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>