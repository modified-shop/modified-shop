<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
if (DIR_WS_CATALOG == '/') {
  $breadcrumb->add(HEADER_TITLE_TOP, xtc_href_link(FILENAME_DEFAULT));
  $link_index = HEADER_TITLE_TOP;
} else {
  $breadcrumb->add(HEADER_TITLE_TOP, xtc_href_link('../'));
  $breadcrumb->add(HEADER_TITLE_CATALOG, xtc_href_link(FILENAME_DEFAULT));
  $link_index = HEADER_TITLE_CATALOG;
}

// add category names or the manufacturer name to the breadcrumb trail
if (isset ($cPath_array)) {
  for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i ++) {
    $category = xtc_get_category_data($cPath_array[$i]);
    if (count($category) > 0) {
      $breadcrumb->add($category['categories_name'], xtc_href_link(FILENAME_DEFAULT, xtc_category_link($cPath_array[$i], $category['categories_name'])));
    } else {
      break;
    }
  }
} elseif (isset($_GET['manufacturers_id']) && xtc_not_null($_GET['manufacturers_id'])) { 
  $_GET['manufacturers_id'] = (int) $_GET['manufacturers_id'];
  $manufacturers_array = xtc_get_manufacturers();
  if (isset($manufacturers_array[(int)$_GET['manufacturers_id']])) {
    $manufacturers = $manufacturers_array[(int)$_GET['manufacturers_id']];
    $breadcrumb->add($manufacturers['manufacturers_name'], xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link((int) $_GET['manufacturers_id'], $manufacturers['manufacturers_name'])));
  }
}

// add the products model/name to the breadcrumb trail
if ($product->isProduct() === true) {
  $breadcrumb->add($product->getBreadcrumbModel(), xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($product->data['products_id'], $product->data['products_name'])));
}

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/create_breadcrumb/','php') as $file) require_once ($file);
