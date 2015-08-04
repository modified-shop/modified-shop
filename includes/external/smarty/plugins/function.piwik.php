<?php
/* -----------------------------------------------------------------------------------------
   $Id: function.piwik.php 2147 2011-09-01 07:15:14Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2011 WEB-Shop Software (function.piwik.php 1871) http://www.webs.de/

   Add the Piwik tracking code (and the possibility to track the order details as well)

   Usage: Put one of the following tags into the templates\yourtemplate\index.html at the bottom
   {piwik url=piwik.example.com id=1} or
   {piwik url=piwik.example.com id=1 goal=1}
   where "id=1" is the domain-ID you want to track (see your Piwik configuration for details)

   Asynchronous Piwik tracking is possible from Piwik version 1.1 and higher
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   extended version to track
   - viewed products
   - categories
   - abandoned shopping carts
   - placed orders
   noRiddle 05-2013

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_INC.'get_order_total.inc.php');

function smarty_function_piwik($params, &$smarty) {
  global $PHP_SELF;
  
  $url = isset($params['url']) ? $params['url'] : false;
  $id = isset($params['id']) ? (int)$params['id'] : false;
  $goal = isset($params['goal']) ? (int)$params['goal'] : false;

  if (!$url || !$id) {
    return false;
  }

  $url = str_replace(array('http://', 'https://'), '', $url);
  $url = trim($url, '/');

  $beginCode = '
    <script type="text/javascript">
      var _paq = _paq || [];
      (function(){
        var u=(("https:" == document.location.protocol) ? "https://'.$url.'/" : "http://'.$url.'/");
        _paq.push([\'setSiteId\', '.$id.']);
        _paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
  '."\n";

  $endCode = '
        _paq.push([\'trackPageView\']);
        _paq.push([\'enableLinkTracking\']);

        var d=document,
        g=d.createElement(\'script\'),
        s=d.getElementsByTagName(\'script\')[0];
        g.type=\'text/javascript\';
        g.defer=true;
        g.async=true;
        g.src=u+\'piwik.js\';
        s.parentNode.insertBefore(g,s);
      })();
    </script>
    <noscript><p><img src="http://'.$url.'/piwik.php?idsite='.$id.'&rec=1" style="border:0" alt="" /></p></noscript>
  ';

  $orderCode = null;
  if ((basename($PHP_SELF) == FILENAME_DEFAULT) && (isset($_GET['cPath'])) && ($_GET['cPath'] != '')) {
    $orderCode .= getCategoryName();
  }
  if ((strpos($PHP_SELF, FILENAME_PRODUCT_INFO) != false) && (isset($_GET['products_id'])) && ($_GET['products_id'] != '')) {
    $orderCode .= getProductsName();
  }
  if (strpos($PHP_SELF, FILENAME_SHOPPING_CART) != false) {
    $orderCode .= getShoppingCartContents();
  }
  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) != false) && isset($_SESSION['customer_id'])) {
    $orderCode .= getOrders();
  }
  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false) && ($goal > 0)) {
    $orderCode .= getOrderDetailsPiwik($goal);
  }
  return $beginCode . $orderCode . $endCode;
}


/*** Functions ***/

/* get category name */
function getCategoryName() {
  $categories_query = xtc_db_query("SELECT categories_name
                                      FROM " . TABLE_CATEGORIES_DESCRIPTION . "
                                     WHERE categories_id = '" . (int)$_GET['cPath'] . "'
                                       AND language_id = '" . (int)$_SESSION['languages_id'] . "'"
                                  );
  $categories = xtc_db_fetch_array($categories_query);
  if ($categories['categories_name'] != '') {
    return "        "."_paq.push(['setEcommerceView', productSku = false, productName = false, category = '".encode_htmlspecialchars($categories['categories_name'])."']);\n";
  }
}

/* get products name */
function getProductsName() {
  $products_query = xtc_db_query("SELECT p.products_id,
                                         pd.products_name,
                                         cd.categories_name
                                    FROM " . TABLE_PRODUCTS . " p,
                                         " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                         " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c,
                                         " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                   WHERE p.products_id = pd.products_id
                                     AND p2c.categories_id = cd.categories_id
                                     AND p2c.products_id = '" . (int)$_GET['products_id'] . "'
                                     AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                     AND cd.language_id = '". (int)$_SESSION['languages_id'] . "'"
                                );
  $products = xtc_db_fetch_array($products_query);  
  return "        "."_paq.push(['setEcommerceView', '".(int)$products['products_id']."', '".encode_htmlspecialchars($products['products_name'])."', '".encode_htmlspecialchars($products['categories_name'])."']);\n";
}

/* get shopping cart contents */
function getShoppingCartContents() {
  $products = $_SESSION['cart']->get_products();
  if ($_SESSION['cart']->count_contents() > 0) {
    $return_string = '';
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $categories_query = xtc_db_query("SELECT cd.categories_name
                                          FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd,
                                               " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                         WHERE cd.categories_id = p2c.categories_id
                                           AND p2c.products_id = '" . (int)$products[$i]['id'] . "'
                                           AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'"
                                      );
      $categories = xtc_db_fetch_array($categories_query);
      $return_string .= "        "."_paq.push(['addEcommerceItem', '".(int)$products[$i]['id']."', '".encode_htmlspecialchars($products[$i]['name'])."', '".encode_htmlspecialchars($categories['categories_name'])."', '".format_price($products[$i]['final_price'])."', '". (int)$products[$i]['quantity']."']);\n";
    }
    $return_string .= "        "."_paq.push(['trackEcommerceCartUpdate', '".format_price($_SESSION['cart']->show_total())."']);\n";
  }
  return $return_string;
}

/* get orders */
function getOrders () {
  $orders_query = xtc_db_query("SELECT orders_id
                                  FROM " . TABLE_ORDERS . "
                                 WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'
                              ORDER BY date_purchased DESC limit 1"
                              );
  if (xtc_db_num_rows($orders_query) == 1) {
    $order = xtc_db_fetch_array($orders_query);
    $total = array();
    $return_string = '';
    $order_total_query = xtc_db_query("SELECT value,
                                              class
                                         FROM " . TABLE_ORDERS_TOTAL . "
                                        WHERE orders_id = '" . (int)$order['orders_id'] . "'"
                                     );
    while ($order_total = xtc_db_fetch_array($order_total_query)) {
      $total[$order_total['class']] = $order_total['value'];
    }
    $order_products_query = xtc_db_query("SELECT op.products_id,
                                                 pd.products_name,
                                                 op.final_price,
                                                 op.products_quantity
                                            FROM " . TABLE_ORDERS_PRODUCTS . " op,
                                                 " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                                 " . TABLE_LANGUAGES . " l
                                           WHERE op.orders_id = '" . (int)$order['orders_id'] . "'
                                             AND op.products_id = pd.products_id
                                             AND l.code = '" . xtc_db_input(DEFAULT_LANGUAGE) . "'
                                             AND l.languages_id = pd.language_id"
                                        );
    while ($order_products = xtc_db_fetch_array($order_products_query)) {
      $category_query = xtc_db_query("SELECT cd.categories_name
                                        FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd,
                                             " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c,
                                             " . TABLE_LANGUAGES . " l
                                       WHERE p2c.products_id = '" . (int)$order_products['products_id'] . "'
                                         AND p2c.categories_id = cd.categories_id
                                         AND l.code = '" . xtc_db_input(DEFAULT_LANGUAGE) . "'
                                         AND l.languages_id = cd.language_id limit 1"
                                    );
      $category = xtc_db_fetch_array($category_query);
      $return_string .= "        "."_paq.push(['addEcommerceItem', '".(int)$order_products['products_id']."', '".encode_htmlspecialchars($order_products['products_name'])."', '".encode_htmlspecialchars($category['categories_name'])."', '".format_price($order_products['final_price'])."', '".(int)$order_products['products_quantity']."']);\n";
    }
    $return_string .= "        "."_paq.push(['trackEcommerceOrder', '".(int)$order['orders_id']."', '".(isset($total['ot_total']) ? format_price($total['ot_total']) : 0)."', '".(isset($total['ot_subtotal']) ? format_price($total['ot_subtotal']) : 0)."', '".(isset($total['ot_tax']) ? format_price($total['ot_tax']) : 0)."', '".(isset($total['ot_shipping']) ? format_price($total['ot_shipping']) : 0)."', '".(isset($total['ot_payment']) ? format_price($total['ot_payment']) : 0)."']);\n";
  }
  return $return_string;
}

/**
 * Get the order details
 *
 * @global <type> $last_order
 * @param mixed $goal
 * @return string Code for the eCommerce tracking
 */
function getOrderDetailsPiwik($goal) {
  global $last_order; // from checkout_success.php

  $total = get_order_total($last_order);

  return "        "."_paq.push(['trackGoal', '" . $goal . "', '" . $total . "' ]);\n";
}

/* format price */
function format_price($price) {      
  return number_format($price, 2, '.', '');
}
