<?php
/* -----------------------------------------------------------------------------------------
   $Id: set_ids_by_url_parameters.php 3851 2012-11-06 10:33:23Z web28 $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 Modified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// product URLS
if (isset ($_GET['info'])) {
  $site = explode('_', $_GET['info']);
  $pID = $site[0];
  $actual_products_id = (int) str_replace('p', '', $pID);
  $product = new product($actual_products_id);
} elseif (isset($_GET['products_id'])) { // also check for old 3.0.3 URLS
  $actual_products_id = (int) $_GET['products_id'];
  $product = new product($actual_products_id);
}
if (!isset($product) || !is_object($product)) {
  $product = new product();
}
// category URLS
if (isset ($_GET['cat'])) {
  $site = explode('_', $_GET['cat']);
  $cID = $site[0];
  $cID = str_replace('c', '', $cID);
  $_GET['cPath'] = xtc_get_category_path($cID);
}
// manufacturer URLS
if (isset ($_GET['manu'])) {
  $site = explode('_', $_GET['manu']);
  $mID = $site[0];
  $mID = (int)str_replace('m', '', $mID);
  $_GET['manufacturers_id'] = $mID;
}

// calculate category path
if (isset ($_GET['cPath'])) {
  $cPath = $_GET['cPath'] = xtc_input_validation($_GET['cPath'], 'cPath', '');
} elseif (is_object($product) && !isset ($_GET['manufacturers_id'])) {
  if ($product->isProduct()) {
    $cPath = xtc_get_product_path($actual_products_id);
  } else {
    $cPath = '';
  }
} else {
  $cPath = '';
}

if (xtc_not_null($cPath)) {
  $cPath_array = xtc_parse_category_path($cPath);
  $cPath = implode('_', $cPath_array);
  $current_category_id = $cPath_array[(sizeof($cPath_array) - 1)];
} else {
  $current_category_id = 0;
}