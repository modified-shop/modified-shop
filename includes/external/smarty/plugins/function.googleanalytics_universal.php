<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2011 WEB-Shop Software (function.googleAnalytics.php 1871) http://www.webs.de/

   Add the Google Analytics tracking code (and the possibility to track the order details as well)

   Usage: Put one of the following tags into the templates\yourtemplate\index.html at the bottom
   {googleanalytics_universal account=UA-XXXXXXX-X} or
   {googleanalytics_universal account=UA-XXXXXXX-X trackOrders=true}
   where "UA-XXXXXXX-X" is your Google Analytics ID

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function smarty_function_googleanalytics_universal($params, &$smarty) {
  global $PHP_SELF;
  
  if (!isset($params['account'])) {
    return false;
  }
  $account = strtoupper($params['account']);

  $trackorders = false;
  if (isset($params['trackorders']) && ($params['trackorders'] == true)) {
    $trackorders = true;
  }

  $beginCode = '<script type="text/javascript">
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');
  ga(\'create\', \''.$account.'\');
  ga(\'send\', \'pageview\', {
     \'anonymizeIp\': true
  });';
  
  $endCode = '</script>';
  
  $orderCode = null;
  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false) && $trackorders) {
    $orderCode = getOrderDetailsAnalyticsUniversal();
  }

  return $beginCode . $orderCode . $endCode;
}

/**
 * Get the details of the order
 *
 * @global <type> $last_order
 * @return string Code for the eCommerce tracking
 */
function getOrderDetailsAnalyticsUniversal() {
  global $last_order;

  $shipping_query = xtc_db_query("SELECT value
                           FROM " . TABLE_ORDERS_TOTAL . "
                          WHERE orders_id = '" . (int)$last_order . "' 
                            AND class='ot_shipping'");
  $shipping = xtc_db_fetch_array($shipping_query);

  $tax_query = xtc_db_query("SELECT value
                           FROM " . TABLE_ORDERS_TOTAL . "
                          WHERE orders_id = '" . (int)$last_order . "' 
                            AND class='ot_tax'");
  $tax = xtc_db_fetch_array($tax_query);

  $total_query = xtc_db_query("SELECT value
                           FROM " . TABLE_ORDERS_TOTAL . "
                          WHERE orders_id = '" . (int)$last_order . "' 
                            AND class='ot_total'");
  $total = xtc_db_fetch_array($total_query);

  $currency_query = xtc_db_query("SELECT currency
                           FROM " . TABLE_ORDERS . "
                          WHERE orders_id = '" . (int)$last_order . "'");
  $currency = xtc_db_fetch_array($currency_query);

  $trackCommerce = "ga('require', 'ecommerce', 'ecommerce.js');\n";

  /* 
  ga('ecommerce:addTransaction', {
     'id': '1234',                     // Transaction ID. Required.
     'affiliation': 'Acme Clothing',   // Affiliation or store name.
     'revenue': '11.99',               // Grand Total.
     'shipping': '5',                  // Shipping.
     'tax': '1.29'                     // Tax.
  }); 
  */
  $addTrans = sprintf("ga('ecommerce:addTransaction', {'id': '%s', 'affiliation': '%s', 'revenue': '%s', 'shipping': '%s', 'tax': '%s', 'currency': '%s'});\n",
    $last_order,
    STORE_NAME,
    $total['value'],
    $shipping['value'],
    $tax["value"],
    $currency['currency']
  );

  $item_query = xtc_db_query("SELECT cd.categories_name,
                                     op.products_id,
                                     op.orders_products_id,
                                     op.products_model,
                                     op.products_name,
                                     op.products_price,
                                     op.products_quantity
                                FROM " . TABLE_ORDERS_PRODUCTS . " op
                                JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                     ON op.products_id = p2c.products_id
                                JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                     ON p2c.categories_id = cd.categories_id
                                        AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                               WHERE op.orders_id='" . (int)$last_order . "'
                            GROUP BY op.products_id");

  $addItem = array();
  while ($item = xtc_db_fetch_array($item_query)) {
   /*
   ga('ecommerce:addItem', {
      'id': '1234',
      'name': 'Fluffy Pink Bunnies',
      'sku': 'DD23444',
      'category': 'Party Toys',
      'price': '11.99',
      'quantity': '1',
      'currency': 'GBP' // local currency code.
    });
    */
    $addItem[] = sprintf("ga('ecommerce:addItem', {'id': '%s', 'name': '%s', 'sku': '%s', 'category': '%s', 'price': '%s', 'quantity': '%s', 'currency': '%s'});\n",
      $last_order,
      $item['products_name'],
      $item['products_id'],
      $item['categories_name'],
      $item['products_price'],
      $item['products_quantity'],
      $currency['currency']
    );
  }
  $trackTrans = "ga('ecommerce:send');\n";

  return $trackCommerce . $addTrans . implode('', $addItem) . $trackTrans;
}
?>