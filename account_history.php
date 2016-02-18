<?php
/* -----------------------------------------------------------------------------------------
   $Id: account_history.php 5581 2013-09-08 21:26:38Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(account_history.php,v 1.60 2003/05/27); www.oscommerce.com
   (c) 2003 nextcommerce (account_history.php,v 1.13 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (account_history.php 1309 2005-10-17)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_count_customer_orders.inc.php');
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_image_button.inc.php');
require_once (DIR_FS_INC.'xtc_format_price_order.inc.php');
require_once (DIR_FS_INC.'get_tracking_link.inc.php');
require_once (DIR_FS_INC.'get_order_total.inc.php');

if (!isset($_SESSION['customer_id'])) { 
  xtc_redirect(xtc_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (isset($_SESSION['customer_id']) 
    && $_SESSION['customers_status']['customers_status_id'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST
    && GUEST_ACCOUNT_EDIT != 'true'
    )
{ 
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_1_ACCOUNT_HISTORY, xtc_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_ACCOUNT_HISTORY, xtc_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

$module_content = array ();
if (xtc_count_customer_orders() > 0) {
  $history_query_raw = "SELECT o.orders_id,
                               o.currency,
                               o.date_purchased,
                               o.delivery_name,
                               o.billing_name,
                               s.orders_status_name
                          FROM ".TABLE_ORDERS." o
                          JOIN ".TABLE_ORDERS_STATUS." s
                               ON o.orders_status = s.orders_status_id
                                  AND s.language_id = '".(int) $_SESSION['languages_id']."'
                         WHERE o.customers_id = '".(int) $_SESSION['customer_id']."'
                      ORDER BY o.orders_id DESC";
                      
  $history_split = new splitPageResults($history_query_raw, (isset($_GET['page']) ? (int)$_GET['page'] : 1), MAX_DISPLAY_ORDER_HISTORY);
  
  if (USE_PAGINATION_LIST == 'false') {
    $smarty->assign('SPLIT_BAR', '<div class="smallText" style="clear:both;">
                                    <div style="float:left;">'.$history_split->display_count(TEXT_DISPLAY_NUMBER_OF_ORDERS).'</div>
                                    <div align="right">'.TEXT_RESULT_PAGE.' '.$history_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))).'</div>
                                    <br style="clear:both" />
                                  </div>');
  } else {
    $smarty->assign('DISPLAY_COUNT', $history_split->display_count(TEXT_DISPLAY_NUMBER_OF_ORDERS));
    $smarty->assign('DISPLAY_LINKS', $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, xtc_get_all_get_params(array ('page', 'info', 'x', 'y'))));
    $smarty->caching = 0;
    $pagination = $smarty->fetch(CURRENT_TEMPLATE.'/module/pagination.html');
    $smarty->assign('SPLIT_BAR', $pagination);
    $smarty->assign('PAGINATION', $pagination);
  }
  
  $row = 0;
  $history_query = xtc_db_query($history_split->sql_query);
  while ($history = xtc_db_fetch_array($history_query)) {
    // count products in order
    $products_query = xtc_db_query("SELECT count(*) as count 
                                      FROM ".TABLE_ORDERS_PRODUCTS." 
                                     WHERE orders_id = '".$history['orders_id']."'");
    $products = xtc_db_fetch_array($products_query);
    
    $module_content[$row] = array ('ORDER_ID' => $history['orders_id'],
                                   'ORDER_STATUS' => $history['orders_status_name'],
                                   'ORDER_DATE' => xtc_date_long($history['date_purchased']),
                                   'ORDER_PRODUCTS' => $products['count'],
                                   'ORDER_TOTAL' => xtc_format_price_order(get_order_total($history['orders_id']), 1, $history['currency'], 1),
                                   'ORDER_BUTTON' => '<a href="'.xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, xtc_get_all_get_params().'order_id='.$history['orders_id'],'SSL').'">'.xtc_image_button('small_view.gif', SMALL_IMAGE_BUTTON_VIEW).'</a>',
                                   'ORDER_TRACKING' => get_tracking_link($history['orders_id'], $_SESSION['language_code']),
                                   'BUTTON_CART' => '<a href="'.xtc_href_link(FILENAME_ACCOUNT_HISTORY, 'action=add_order&order_id='.$history['orders_id'], 'SSL').'">'.xtc_image_button('small_cart.gif', IMAGE_BUTTON_IN_CART).'</a>',
                                   'ORDER_LINK' => xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.$history['orders_id'], 'SSL'),
                                   );

    if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
      $module_content[$row]['BUTTON_CART_EXPRESS'] = '<a href="'.xtc_href_link(FILENAME_ACCOUNT_HISTORY, 'action=add_order&express=on&order_id='.$history['orders_id'], 'SSL').'">'.xtc_image_button('small_express.gif', IMAGE_BUTTON_IN_CART).'</a>';
    }

    $row ++;
  }
}

$smarty->assign('order_content', $module_content);
$smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(FILENAME_ACCOUNT, '', 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$smarty->assign('language', $_SESSION['language']);

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/account_history.html');

$smarty->assign('main_content', $main_content);
$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>