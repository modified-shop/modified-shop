<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(default.php,v 1.84 2003/05/07); www.oscommerce.com
   (c) 2003 nextcommerce (default.php,v 1.13 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (index.php 1215 2010-08-26)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3          Autor: Mikel Williams | mikel@ladykatcostumes.com
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// the following cPath references come from application_top.php
$category_depth = 'top';
if (isset($cPath) && xtc_not_null($cPath)) {
  $categories_products_query = "select p2c.products_id
                                  from ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                  left join ".TABLE_PRODUCTS." p
                                   on p2c.products_id = p.products_id
                                  where p2c.categories_id = ".(int)$current_category_id."
                                  and p.products_status = 1";
  $categories_products_result = xtDBquery($categories_products_query);
  if (xtc_db_num_rows($categories_products_result, true) > 0) {
    $category_depth = 'products'; // display products
  } else {
    $category_parent_query = "select parent_id from ".TABLE_CATEGORIES." where parent_id = ".(int)$current_category_id." AND categories_status = 1";
    $category_parent_result = xtDBquery($category_parent_query);
    $category_parent = xtc_db_fetch_array($category_parent_result, true);
    if (xtc_db_num_rows($category_parent_result, true) > 0) {
      $category_depth = 'nested'; // navigate through the categories
    } else {
      $category_depth = 'products'; // category has no products, but display the 'no products' message
    }
  }
}

include (DIR_WS_MODULES.'default.php');
require (DIR_WS_INCLUDES.'header.php'); //web28 - 2013-01-04 - load header.php after default.php because of error handling status code

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
?>