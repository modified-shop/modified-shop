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

$box_smarty = new smarty;
$box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// include needed functions
require_once (DIR_FS_INC.'xtc_random_select.inc.php');

// query restrictions
$fsk_lock = ($_SESSION['customers_status']['customers_fsk18_display'] == '0') ? 'AND p.products_fsk18 != 1': '';

$group_check = (GROUP_CHECK == 'true') ? 'AND p.group_permission_'.$_SESSION['customers_status']['customers_status_id'].' = 1' : '';

$current_prd =  (isset($_GET['products_id']) && (int)$_GET['products_id'] > 0) ? 'AND p.products_id != ' . (int)$_GET['products_id'] : '';

if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
  $days = "AND p.products_date_added > '".date("Y.m.d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")))."'";
} else {
  $days = '';
}

// get random product data
if ($random_product = xtc_random_select("-- templates/xtc5/source/boxes/whats_new.php
                                        SELECT distinct
                                              p.products_id,
                                              p.products_image,                                              
                                              p.products_tax_class_id,
                                              p.products_vpe,
                                              p.products_vpe_status,
                                              p.products_vpe_value,
                                              p.products_price,
                                              pd.products_name
                                         FROM ".TABLE_PRODUCTS." p
                                    LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                           ON (p.products_id = pd.products_id AND pd.language_id = '".(int) $_SESSION['languages_id']."' AND pd.products_name != '')
                                    LEFT JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                           ON p.products_id = p2c.products_id
                                    LEFT JOIN ".TABLE_CATEGORIES." c
                                           ON c.categories_id = p2c.categories_id AND c.categories_status = 1
                                        WHERE p.products_status = 1
                                          " . $fsk_lock . "
                                          " . $group_check . "
                                          " . $current_prd . "
                                          " . $days . "                                           
                                     ORDER BY p.products_date_added desc
                                        LIMIT ".MAX_RANDOM_SELECT_NEW))
{
  $whats_new_price = $xtPrice->xtcGetPrice($random_product['products_id'], $format = true, 1, $random_product['products_tax_class_id'], $random_product['products_price']);
  $box_smarty->assign('box_content',$product->buildDataArray($random_product));
  $box_smarty->assign('LINK_NEW_PRODUCTS',xtc_href_link(FILENAME_PRODUCTS_NEW));
  $box_smarty->assign('language', $_SESSION['language']);

  // set cache ID
  if (!CacheCheck()) {
    $box_smarty->caching = 0;
    $box_whats_new = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html');
  } else {
    $box_smarty->caching = 1;
    $box_smarty->cache_lifetime = CACHE_LIFETIME;
    $box_smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $_SESSION['language'].$random_product['products_id'].$_SESSION['customers_status']['customers_status_name'];
    $box_whats_new = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_whatsnew.html', $cache_id);
  }
  $smarty->assign('box_WHATSNEW', $box_whats_new);
}
?>