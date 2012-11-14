<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
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
  //BOF - GTB - 2010-11-29 - show categories when no active product
  //$categories_products_query = "select count(*) as total from ".TABLE_PRODUCTS_TO_CATEGORIES." where categories_id = '".$current_category_id."'";
  //BOF - DokuMan - 2010-01-26 - use Join on TABLE_PRODUCTS
  $categories_products_query = "select p2c.products_id
                                  from ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                  left join ".TABLE_PRODUCTS." p
                                   on p2c.products_id = p.products_id
                                  where p2c.categories_id = ".(int)$current_category_id."
                                  and p.products_status = 1";
  //EOF - DokuMan - 2010-01-26 - use Join on TABLE_PRODUCTS
  $categories_products_result = xtDBquery($categories_products_query);
  //$cateqories_products = xtc_db_fetch_array($categories_products_query, true);
  //if ($cateqories_products['total'] > 0) {
  if (xtc_db_num_rows($categories_products_result) > 0) {
  //EOF - GTB - 2010-11-29 - show categories when no active product
    $category_depth = 'products'; // display products
  } else {
    //BOF - GTB - 2010-11-29 - only count avtive categories
    //$category_parent_query = "select count(*) as total from ".TABLE_CATEGORIES." where parent_id = '".$current_category_id."'";
    $category_parent_query = "select count(*) as total from ".TABLE_CATEGORIES." where parent_id = ".(int)$current_category_id." AND categories_status = 1";
    //EOF - GTB - 2010-11-29 - only count avtive categories
    $category_parent_result = xtDBquery($category_parent_query);
    $category_parent = xtc_db_fetch_array($category_parent_result, true);
    if ($category_parent['total'] > 0) {
      $category_depth = 'nested'; // navigate through the categories
    } else {
      $category_depth = 'products'; // category has no products, but display the 'no products' message
    }
  }
}

require (DIR_WS_INCLUDES.'header.php');
include (DIR_WS_MODULES.'default.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
?>