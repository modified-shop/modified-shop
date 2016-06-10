<?php
  /* -----------------------------------------------------------------------------------------
   $Id: whats_new.php 4583 2013-04-05 15:25:22Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(whats_new.php,v 1.31 2003/02/10); www.oscommerce.com
   (c) 2003  nextcommerce (whats_new.php,v 1.12 2003/08/21); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3 Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// reset cache id
$cache_id = '';

$days = '';
if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
  $days = "AND p.products_date_added > '".date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")))."'";
}

$current_prd =  (isset($_GET['products_id']) && (int)$_GET['products_id'] > 0) ? 'AND p.products_id != ' . (int)$_GET['products_id'] : '';

// get random product data
$whats_new_query = xtc_db_query("SELECT DISTINCT p.products_id,
                                                 p.products_image,                                              
                                                 p.products_tax_class_id,
                                                 p.products_vpe,
                                                 p.products_vpe_status,
                                                 p.products_vpe_value,
                                                 p.products_price,
                                                 pd.products_name
                                            FROM ".TABLE_PRODUCTS." p
                                            JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                                 ON p.products_id = pd.products_id 
                                                    AND pd.language_id = ".(int)$_SESSION['languages_id']."
                                                    AND trim(pd.products_name) != ''
                                            JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                                 ON p.products_id = p2c.products_id
                                            JOIN ".TABLE_CATEGORIES." c
                                                 ON c.categories_id = p2c.categories_id 
                                                    AND c.categories_status = 1 
                                                        ".CATEGORIES_CONDITIONS_C."
                                           WHERE p.products_status = 1
                                                 " . PRODUCTS_CONDITIONS_P . "
                                                 " . $current_prd . "
                                                 " . $days . "                                           
                                        ORDER BY MD5(CONCAT(p.products_id, CURRENT_TIMESTAMP)) 
                                           LIMIT 1");

if (xtc_db_num_rows($whats_new_query) > 0) {
  $whats_new = xtc_db_fetch_array($whats_new_query);
  
  // set cache id
  $cache_id = md5($_SESSION['language'].$whats_new['products_id']);
  
  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html', $cache_id) || !$cache) {
    $box_smarty->assign('box_content', $product->buildDataArray($whats_new));
    $box_smarty->assign('LINK_NEW_PRODUCTS', xtc_href_link(FILENAME_PRODUCTS_NEW));
  }
}

if (!$cache) {
  $box_whats_new = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html');
} else {
  $box_whats_new = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html', $cache_id);
}

$smarty->assign('box_WHATSNEW', $box_whats_new);
?>