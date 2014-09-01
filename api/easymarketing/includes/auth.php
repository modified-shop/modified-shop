<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

@ini_set('display_errors', false);
error_reporting(0);

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
  die('Direct Access to this location is not allowed.');
}

// set request parameters shop_token
$shop_token = (isset($_GET['shop_token']) ? $_GET['shop_token'] : NULL);

if (isset($shop_token) && $shop_token == MODULE_EASYMARKETING_SHOP_TOKEN && MODULE_EASYMARKETING_STATUS == 'True') {

  // set request parameters products
  $offset = (isset($_GET['offset']) ? (int) $_GET['offset'] : NULL);
  $limit = (isset($_GET['limit']) ? (int) $_GET['limit'] : NULL);
  $id = (isset($_GET['id']) ? (int) $_GET['id'] : NULL);
  $newer_than = (isset($_GET['newer_than']) ? (int) $_GET['newer_than'] : NULL);

  // set request parameters bestseller
  $most_sold_since = (isset($_GET['most_sold_since']) ? (int) $_GET['most_sold_since'] : NULL);
  $limit = (isset($_GET['limit']) ? (int) $_GET['limit'] : NULL);

  // set request parameters categories
  $parent_id = (isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : NULL);

  // set sql limit
  $sql_limit = '';
  $sql_sort = '';
  if (isset($offset) && isset($limit)) {
    $sql_limit = " LIMIT ".(int) $offset.", ".(int) $limit;
    $sql_sort = " ORDER BY p.products_id ASC ";
  } elseif (isset($limit)) {
    $sql_limit = " LIMIT ".(int) $limit;
  }

  // set sql where
  $sql_where = '';
  if (isset($id)) {
    $sql_where = " AND p.products_id = '".$id."' ";
  } elseif (isset($newer_than)) {
    $sql_where = " AND p.products_date_added >= '".date("Y-m-d H:i:s", $newer_than)."' ";
    $sql_sort  = " ORDER BY p.products_id ASC ";
  }

} else {

  // wrong authentification
  die('Direct Access to this location is not allowed.');
}