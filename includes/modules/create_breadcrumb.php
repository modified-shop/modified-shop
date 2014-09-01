<?php
/* -----------------------------------------------------------------------------------------
   $Id: create_breadcrumb.php 3851 2012-11-06 10:33:23Z web28 $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 Modified
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
    $group_check = '';
    if (GROUP_CHECK == 'true') {
      $group_check = "AND c.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
    }
    $categories_query = xtDBquery("-- /includes/application_top.php
                                   SELECT cd.categories_name
                                     FROM ".TABLE_CATEGORIES_DESCRIPTION." cd,
                                          ".TABLE_CATEGORIES." c
                                    WHERE cd.categories_id = '".$cPath_array[$i]."'
                                      AND c.categories_id=cd.categories_id
                                          ".$group_check."
                                      AND cd.language_id='".(int) $_SESSION['languages_id']."'");
    if (xtc_db_num_rows($categories_query,true) > 0) {
      $categories = xtc_db_fetch_array($categories_query,true);
      $breadcrumb->add($categories['categories_name'], xtc_href_link(FILENAME_DEFAULT, xtc_category_link($cPath_array[$i], $categories['categories_name'])));
    } else {
      break;
    }
  }
} elseif (isset($_GET['manufacturers_id']) && xtc_not_null($_GET['manufacturers_id'])) { 
  $_GET['manufacturers_id'] = (int) $_GET['manufacturers_id'];
  $manufacturers_query = xtDBquery("-- /includes/application_top.php
                                    SELECT manufacturers_name 
                                      FROM ".TABLE_MANUFACTURERS." 
                                     WHERE manufacturers_id = '".(int) $_GET['manufacturers_id']."'");
  $manufacturers = xtc_db_fetch_array($manufacturers_query, true);
  $breadcrumb->add($manufacturers['manufacturers_name'], xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link((int) $_GET['manufacturers_id'], $manufacturers['manufacturers_name'])));
}

// add the products model/name to the breadcrumb trail
if ($product->isProduct()) {
  $breadcrumb->add($product->data['products_name'], xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($product->data['products_id'], $product->data['products_name'])));
}