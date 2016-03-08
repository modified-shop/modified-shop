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

  $box_order_history = '';

  if (isset($_SESSION['customer_id'])) {

    $box_smarty = new smarty;
    $customer_orders_array = array();

    // retreive the last x products purchased
    $orders_query = xtc_db_query("
        SELECT DISTINCT op.products_id
                   FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS . " p
                  WHERE o.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                    AND o.orders_id = op.orders_id 
                    AND op.products_id = p.products_id
                    AND p.products_status = '1' 
               GROUP BY products_id 
               ORDER BY o.date_purchased DESC 
                  LIMIT " . MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX);
    if (xtc_db_num_rows($orders_query)) {
      $product_ids = '';
      while ($orders = xtc_db_fetch_array($orders_query)) {
        $product_ids .= $orders['products_id'] . ',';
      }
      $product_ids = substr($product_ids, 0, -1);
      $products_query = xtc_db_query("
          SELECT products_id, products_name 
            FROM " . TABLE_PRODUCTS_DESCRIPTION . "
           WHERE products_id IN (" . $product_ids . ")
             AND language_id = '" . (int)$_SESSION['languages_id'] . "'
        ORDER BY products_name");
      while ($products = xtc_db_fetch_array($products_query)) {
        $customer_orders_array[] = array(
          'PRODUCTS_LINK' => '<a href="' . xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($products['products_id'],$products['products_name'])) . '">' . $products['products_name'] . '</a>',
          'ORDER_LINK' => '<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $products['products_id']) . '">' . xtc_image_button('templates/' . CURRENT_TEMPLATE . '/img/icon_cart.png' , ICON_CART) . '</a>',
        );
      }
    }

    $box_smarty->assign('BOX_CONTENT', $customer_orders_array);

    $box_smarty->caching = 0;
    $box_smarty->assign('language', $_SESSION['language']);
    $box_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/'); 
    $box_order_history = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_order_history.html');
  }

  $smarty->assign('box_HISTORY', $box_order_history);

?>