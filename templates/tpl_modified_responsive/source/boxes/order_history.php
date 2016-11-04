<?php
/* -----------------------------------------------------------------------------------------
   $Id: order_history.php 5581 2013-09-08 21:26:38Z Tomcraft $
   
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(order_history.php,v 1.4 2003/02/10); www.oscommerce.com 
   (c) 2003	nextcommerce (order_history.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2003 xt:Commerce (order_history.php 1262 2005/09/30 mz); www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language'].((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : '0'));

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id) || !$cache) {

  if (isset($_SESSION['customer_id'])) {

    // retreive the last x products purchased
    $orders_query = xtc_db_query("SELECT DISTINCT p.products_id,
                                                  pd.products_name,
                                                  o.orders_id,
                                                  op.orders_products_id
                                             FROM " . TABLE_ORDERS . " o
                                             JOIN " . TABLE_ORDERS_PRODUCTS . " op
                                                  ON o.orders_id = op.orders_id
                                             JOIN " . TABLE_PRODUCTS . " p
                                                  ON op.products_id = p.products_id
                                             JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                                  ON p.products_id = pd.products_id
                                                     AND language_id = '" . (int)$_SESSION['languages_id'] . "'
                                            WHERE o.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                              AND p.products_status = '1' 
                                         GROUP BY p.products_id 
                                         ORDER BY o.date_purchased DESC 
                                            LIMIT " . MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX);

    $customer_orders_array = array();
    if (xtc_db_num_rows($orders_query) > 0) {
      while ($orders = xtc_db_fetch_array($orders_query)) {
        $customer_orders_array[] = array(
          'PRODUCTS_LINK' => '<a href="' . xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$orders['products_id']) . '">' . $orders['products_name'] . '</a>',
          'ORDER_LINK' => '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')) . 'action=add_order_product&order_id='.$orders['orders_id'].'&id='.$orders['orders_products_id']) . '">' . xtc_image_button('templates/' . CURRENT_TEMPLATE . '/img/icon_cart.png' , ICON_CART) . '</a>',
        );
      }
    }

    $box_smarty->assign('BOX_CONTENT', $customer_orders_array);
  }
}

if (!$cache) {
  $box_order_history = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_order_history.html');
} else {
  $box_order_history = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_order_history.html', $cache_id);
}

$smarty->assign('box_HISTORY', $box_order_history);
?>