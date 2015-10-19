<?php
/* -----------------------------------------------------------------------------------------
   $Id: set_ids_by_url_parameters.php 6052 2013-11-14 09:06:49Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// product URLS
if (isset($_GET['info'])) {
  $site = explode('_', $_GET['info']);
  $pID = $site[0];
  $actual_products_id = (int) str_replace('p', '', $pID);
  $product = new product($actual_products_id);
} elseif (isset($_GET['products_id'])) { // also check for old 3.0.3 URLS
  $actual_products_id = (int) $_GET['products_id'];
  $product = new product($actual_products_id);
}

// category URLS
if (isset($_GET['cat'])) {
  $site = explode('_', $_GET['cat']);
  $cID = $site[0];
  $cID = str_replace('c', '', $cID);
  $_GET['cPath'] = xtc_get_category_path($cID);
}

// manufacturer URLS
if (isset($_GET['manu'])) {
  $site = explode('_', $_GET['manu']);
  $mID = $site[0];
  $mID = (int)str_replace('m', '', $mID);
  $_GET['manufacturers_id'] = $mID;
}

// calculate category path
define('PRODUCTS_CANONICAL_CAT_ID', false);
if (isset ($_GET['cPath']) && (!isset($product) || !is_object($product))) {
  $cPath = $_GET['cPath'] = xtc_input_validation($_GET['cPath'], 'cPath', '');
} elseif (isset($product) && is_object($product) && !isset($_GET['manufacturers_id'])) {
  if ($product->isProduct() === true) {
    require_once (DIR_FS_INC.'product_redirect.inc.php');
    $cPath = product_redirect($actual_products_id);
  } else {
    $cPath = '';
  }
} else {
  $cPath = '';
}
$products_link_cat_id = 0;

// set default product class
if (!isset($product) || !is_object($product)) {
  $product = new product();
}

// content URLS
if (isset ($_GET['coID']) && function_exists('xtc_get_content_path')) {
  $coPath_array = xtc_get_content_path($_GET['coID']);
  $coPath_array[sizeof($coPath_array)] = xtc_get_content_id($_GET['coID']);  
  $coPath = implode('_', $coPath_array);
}

//set $current_category_id, $_SESSION['CatPath'], verify $cPath
unset($_SESSION['CatPath']);
if (xtc_not_null($cPath)) {
  $cPath_array = xtc_parse_category_path($cPath);
  $current_category_id = end($cPath_array);
  $cPath = xtc_get_category_path($current_category_id); //verify $cPath
  $_SESSION['CatPath'] = $cPath;
} else {
  $current_category_id = 0;
}
