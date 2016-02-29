<?php
  /* -------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ---------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (account.php,v 1.59 2003/05/19); www.oscommerce.com
   (c) 2003      nextcommerce (account.php,v 1.12 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License
   ------------------------------------------------------------------------------------ */

include ('includes/application_top.php');

// create smarty
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
require_once (DIR_FS_INC.'get_tracking_link.inc.php');
require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');
require_once (DIR_FS_INC.'get_order_total.inc.php');

if (!isset($_SESSION['customer_id']) && GUEST_ACCOUNT_EDIT != 'true') { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// clear session
unset($_SESSION['sendto']);
unset($_SESSION['billto']);
unset($_SESSION['shipping']);
unset($_SESSION['payment']);
unset($_SESSION['delivery_zone']);

$breadcrumb->add(NAVBAR_TITLE_ACCOUNT, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
require (DIR_WS_INCLUDES.'header.php');

if ($messageStack->size('account') > 0) {
	$smarty->assign('error_message', $messageStack->output('account'));
}

$order_content = array();
$products_history = array();
$also_purchased_history = array();

$max = isset($_SESSION['tracking']['products_history']) ? count($_SESSION['tracking']['products_history']) : 0;
for ($i=0; $i<$max; $i++) {
	$product_history_query = xtDBquery("SELECT p.*,
	                                           pd.*,
	                                           cd.categories_name 
	                                      FROM ".TABLE_PRODUCTS." p
	                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
	                                           ON p.products_id=pd.products_id
	                                              AND pd.language_id='".(int) $_SESSION['languages_id']."'
	                                      JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
	                                           ON p.products_id = p2c.products_id
	                                      JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd
	                                           ON cd.categories_id = p2c.categories_id
	                                              AND cd.language_id = '".(int) $_SESSION['languages_id']."'
	                                     WHERE p.products_status = '1'
	                                       AND p.products_id = '".(int) $_SESSION['tracking']['products_history'][$i]."'
	                                  GROUP BY p.products_id
	                                           ".PRODUCTS_CONDITIONS_P);
  if (xtc_db_num_rows($product_history_query, true) > 0) {
	  $history_product = xtc_db_fetch_array($product_history_query, true);
		$history_product['cat_url'] = xtc_href_link(FILENAME_DEFAULT, 'cPath='.xtc_get_product_path($history_product['products_id']));
		$history_product['categories_name'] = $history_product['categories_name'];
		
		$products_history[] = $product->buildDataArray($history_product);
	}
}
$smarty->assign('products_history', $products_history);

if (xtc_count_customer_orders() > 0) {
	$orders_query = xtc_db_query("SELECT o.orders_id,
                                       o.date_purchased,
                                       o.delivery_name,
                                       o.delivery_country,
                                       o.billing_name,
                                       o.billing_country,
                                       o.currency,
                                       s.orders_status_name
	                                FROM ".TABLE_ORDERS." o
	                                JOIN ".TABLE_ORDERS_STATUS." s
	                                     ON o.orders_status = s.orders_status_id
	                                        AND s.language_id = '".(int) $_SESSION['languages_id']."'
	                               WHERE o.customers_id = '".(int) $_SESSION['customer_id']."'
	                            ORDER BY orders_id DESC
	                               LIMIT 3");
  $row = 0;
	while ($orders = xtc_db_fetch_array($orders_query)) {
		if (xtc_not_null($orders['delivery_name'])) {
			$order_name = $orders['delivery_name'];
			$order_country = $orders['delivery_country'];
		} else {
			$order_name = $orders['billing_name'];
			$order_country = $orders['billing_country'];
		}
		$order_content[$row] = array ('ORDER_ID' => $orders['orders_id'], 
                                  'ORDER_DATE' => xtc_date_short($orders['date_purchased']), 
                                  'ORDER_STATUS' => $orders['orders_status_name'], 
                                  'ORDER_TOTAL' => xtc_format_price_order(get_order_total($orders['orders_id']), 1, $orders['currency'], 1), 
                                  'ORDER_LINK' => xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$orders['orders_id'], 'SSL'), 
                                  'ORDER_BUTTON' => '<a href="'.xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$orders['orders_id'], 'SSL').'">'.xtc_image_button('small_view.gif', SMALL_IMAGE_BUTTON_VIEW).'</a>',
                                  'TRACKING' => get_tracking_link($orders['orders_id'], $_SESSION['language_code']),
                                  'BUTTON_CART' => '<a href="'.xtc_href_link(FILENAME_ACCOUNT, 'action=add_order&order_id='.$orders['orders_id'], 'SSL').'">'.xtc_image_button('small_cart.gif', IMAGE_BUTTON_IN_CART).'</a>',
                                  );

	  if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
	    $order_content[$row]['BUTTON_CART_EXPRESS'] = '<a href="'.xtc_href_link(FILENAME_ACCOUNT, 'action=add_order&express=on&order_id='.$orders['orders_id'], 'SSL').'">'.xtc_image_button('small_express.gif', IMAGE_BUTTON_IN_CART).'</a>';
	  }

	  $row ++;
	}
}
$smarty->assign('order_content', $order_content);

if ((isset($_SESSION['customer_id']) && $_SESSION['customers_status']['customers_status_id'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST)) {
  $smarty->assign('LINK_ORDERS', xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
  if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] != '1') {
    $smarty->assign('LINK_DELETE', xtc_href_link(FILENAME_ACCOUNT_DELETE, '', 'SSL'));
  }
  $smarty->assign('LINK_PASSWORD', xtc_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));
  if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
    $smarty->assign('LINK_EXPRESS', xtc_href_link(FILENAME_ACCOUNT_CHECKOUT_EXPRESS, '', 'SSL'));
  }
}

if (isset($_SESSION['customer_id'])) {
  $smarty->assign('LINK_ALL', xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
  $smarty->assign('LINK_EDIT', xtc_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));
  $smarty->assign('LINK_ADDRESS', xtc_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
} else {
	$smarty->assign('LINK_LOGIN', xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}
$smarty->assign('LINK_NEWSLETTER', xtc_href_link(FILENAME_NEWSLETTER, '', 'SSL'));

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