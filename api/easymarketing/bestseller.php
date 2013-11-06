<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
require_once('includes/application_top.php');

// include easymarketing configuration
require_once(DIR_FS_CATALOG.'api/easymarketing/includes/config.php');

// include easymarketing authentification
require_once(DIR_FS_EASYMARKETING_INCLUDES.'auth.php');

// include easymarketing functions
require_once(DIR_FS_EASYMARKETING_INCLUDES.'functions.php');

// process request
if (isset($most_sold_since) && isset($limit)) {

  // sql query for best sellers
  $products_query_raw = "SELECT op.products_id,
                                SUM(op.products_quantity) AS quantity
                           FROM ".TABLE_ORDERS." o
                           JOIN ".TABLE_ORDERS_PRODUCTS." op
                                ON o.orders_id = op.orders_id
                          WHERE o.date_purchased >= '".date("Y-m-d H:i:s", $most_sold_since)."'
                       GROUP BY op.products_id
                       ORDER BY quantity DESC
                          LIMIT ".$limit;

  // make sql query
  $products_query = xtc_db_query($products_query_raw);
  
  // check for result
  if (xtc_db_num_rows($products_query) > 0) {

    // init products array
    $products_array = array();

    while ($products = xtc_db_fetch_array($products_query)) {
    
      // build products array
      $products_array[] = array('id' => $products['products_id'],
                                'sales' => intval($products['quantity'])
                                );
    }

    // build response array
    $response = array('limit' => $limit,
                      'most_sold_since' => $most_sold_since,
                      'products' => $products_array
                      );
    
    // output products
    mod_stream_response($response);
  }
}
