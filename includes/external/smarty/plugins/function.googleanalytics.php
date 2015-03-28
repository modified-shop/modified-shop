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
   {googleanalytics account=UA-XXXXXXX-X trackorders=true}
   where "UA-XXXXXXX-X" is your Google Analytics ID

   Third party contributions:
   Snippets from http://webanalyse-news.de/xtcommerce-tracking-mit-google-analytics-tutorial/
   and https://developers.google.com/analytics/devguides/collection/gajs/

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

  // chache ga.js
  $cache_gs = DIR_FS_CATALOG.'cache/ga.js';
  if (!is_file($cache_gs) || (filemtime($cache_gs) > (time() - 3600))) {
    require_once(DIR_FS_INC.'get_external_content.inc.php');
    $source_gs = get_external_content('http://www.google-analytics.com/ga.js', 2, false);
    if (file_put_contents($cache_gs, $source_gs, LOCK_EX) !== false) {
      $gs = xtc_href_link('cache/ga.js', '', $request_type, false);
    }
  } elseif (is_file($cache_gs)) {
    $gs = xtc_href_link('cache/ga.js', '', $request_type, false);
  }

  $endCode ='(function() {
    var ga = document . createElement(\'script\');
    ga.type = \'text/javascript\';
    ga.async = true;
    ga.src = '.((isset($gs)) ? '\''.$gs.'\'' : '(\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\'').';
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

  $shipping_query = xtc_db_query("-- function.googleanalytics.php
                         SELECT value
                           FROM " . TABLE_ORDERS_TOTAL . "
                          WHERE orders_id = '" . (int)$last_order . "' 
                            AND class='ot_shipping'");
  $shipping = xtc_db_fetch_array($shipping_query);

  $tax_query = xtc_db_query("-- function.googleanalytics.php
                         SELECT value
                           FROM " . TABLE_ORDERS_TOTAL . "
                          WHERE orders_id = '" . (int)$last_order . "' 
                            AND class='ot_tax'");
  $tax = xtc_db_fetch_array($tax_query);

  $total_query = xtc_db_query("-- function.googleanalytics.php
                         SELECT value
                           FROM " . TABLE_ORDERS_TOTAL . "
                          WHERE orders_id = '" . (int)$last_order . "' 
                            AND class='ot_total'");
  $total = xtc_db_fetch_array($total_query);

  $location_query = xtc_db_query("-- function.googleanalytics.php
                         SELECT customers_city,
                                customers_state,
                                customers_country
                           FROM " . TABLE_ORDERS . "
                          WHERE orders_id = '" . (int)$last_order . "'");
  $location = xtc_db_fetch_array($location_query);

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
    $total['value'],
    $tax["value"],
    $shipping['value'],
    $location['customers_city'],
    $location['customers_state'],
    $location['customers_country']
  );

  $item_query = xtc_db_query("-- function.googleanalytics_universal.php
                              SELECT cd.categories_name,
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
      $item['products_id'],
      $item['products_name'],
      $item['categories_name'],
      $item['products_price'],
      $item['products_quantity']
    );
  }
  $trackTrans = "_gaq.push(['_trackTrans']);\n";

  return $addTrans . implode('', $addItem) . $trackTrans;
}
?>