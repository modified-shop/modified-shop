<?php
/* -----------------------------------------------------------------------------------------
   $Id: function.googleanalytics.php 2147 2011-09-01 07:15:14Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2011 WEB-Shop Software (function.googleAnalytics.php 1871) http://www.webs.de/

   Add the Google Analytics tracking code (and the possibility to track the order details as well)

   Usage: Put one of the following tags into the templates\yourtemplate\index.html at the bottom
   {googleanalytics account=UA-XXXXXXX-X} or
   {googleanalytics account=UA-XXXXXXX-X trackOrders=true}
   where "UA-XXXXXXX-X" is your Google Analytics ID

   Third party contributions:
   Snippets from http://webanalyse-news.de/xtcommerce-tracking-mit-google-analytics-tutorial/
   and http://code.google.com/intl/de-DE/apis/analytics/docs/tracking/gaTrackingEcommerce.html

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
function smarty_function_googleanalytics($params, &$smarty) {
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
  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \''.$account.'\']);
  _gaq.push([\'_gat._anonymizeIp\']);
  _gaq.push([\'_trackPageview\']);
  ';
  $endCode ='(function() {
    var ga = document . createElement(\'script\');
    ga.type = \'text/javascript\';
    ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0];
    s . parentNode . insertBefore(ga, s);
  })();
  </script>
  ';

  $orderCode = null;
  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false) && $trackorders) {
    $orderCode = getOrderDetailsAnalytics();
  }

  return $beginCode . $orderCode . $endCode;
}

/**
 * Get the details of the order
 *
 * @global <type> $last_order
 * @return string Code for the eCommerce tracking
 */
function getOrderDetailsAnalytics() {
  global $last_order; // from checkout_success.php

  $query = xtc_db_query("-- function.googleanalytics.php
    SELECT value
    FROM " . TABLE_ORDERS_TOTAL . "
    WHERE orders_id = '" . $last_order . "' AND class='ot_shipping'");
  $orders_total_shipping = xtc_db_fetch_array($query);

  $query = xtc_db_query("-- function.googleanalytics.php
    SELECT value
    FROM " . TABLE_ORDERS_TOTAL . "
    WHERE orders_id = '" . $last_order . "' AND class='ot_tax'");
  $orders_total_tax = xtc_db_fetch_array($query);

  $query = xtc_db_query("-- function.googleanalytics.php
    SELECT value
    FROM " . TABLE_ORDERS_TOTAL . "
    WHERE orders_id = '" . $last_order . "' AND class='ot_total'");
  $orders_total = xtc_db_fetch_array($query);

  $query = xtc_db_query("-- function.googleanalytics.php
    SELECT customers_city, customers_state, customers_country
    FROM " . TABLE_ORDERS . "
    WHERE orders_id = '" . $last_order . "'");
  $orders = xtc_db_fetch_array($query);

  /**
   * _gaq.push(['_addTrans',
   *    '1234',           // order ID - required
   *    'Acme Clothing',  // affiliation or store name
   *    '11.99',          // total - required
   *    '1.29',           // tax
   *    '5',              // shipping
   *    'San Jose',       // city
   *    'California',     // state or province
   *    'USA'             // country
   *  ]);
   *
   */
  $addTrans = sprintf("_gaq.push(['_addTrans','%s','%s','%s','%s','%s','%s','%s','%s']);\n",
          $last_order,
          STORE_NAME,
          $orders_total['value'],
          $orders_total_tax["value"],
          $orders_total_shipping['value'],
          $orders['customers_city'],
          $orders['customers_state'],
          $orders['customers_country']);

  $query = xtc_db_query("-- function.googleanalytics.php
    SELECT
      categories_name,
      p.products_id,
      orders_products_id,
      products_model,
      products_name,
      products_price,
      products_quantity
    FROM " . TABLE_ORDERS_PRODUCTS . " p,
         " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc,
         " . TABLE_CATEGORIES_DESCRIPTION . " cd
    WHERE p.products_id = ptc.products_id
    AND ptc.categories_id=cd.categories_id
    AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "'
    AND orders_id='" . $last_order . "'
    GROUP BY p.products_id");

  $addItem = array();
  while ($order = xtc_db_fetch_array($query)) {
    /**
     * _gaq.push(['_addItem',
     *    '1234', // order ID - required
     *    'DD44', // SKU/code - required
     *    'T-Shirt', // product name
     *    'Green Medium', // category or variation
     *    '11.99', // unit price - required
     *    '1'         // quantity - required
     *  ]);
     *
     */
    $addItem[] = sprintf("_gaq.push(['_addItem','%s','%s','%s','%s','%s','%s']);\n",
            $last_order,
            $order['products_id'],
            $order['products_name'],
            $order['categories_name'],
            $order['products_price'],
            $order['products_quantity']);
  }
  $trackTrans = "_gaq.push(['_trackTrans']);\n";

  return $addTrans . implode('', $addItem) . $trackTrans;
}