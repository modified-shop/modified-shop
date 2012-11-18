<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
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
  require_once (DIR_FS_INC.'xtc_get_products_name.inc.php');

  // query restrictions
  if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
    $fsk_lock = 'AND p.products_fsk18 != 1';
  } else {
    $fsk_lock = '';
  }
  if (GROUP_CHECK == 'true') {
    $group_check = 'AND p.group_permission_'.$_SESSION['customers_status']['customers_status_id'].' = 1';
  } else {
    $group_check = '';
  }
  if (isset($_GET['products_id']) && (int)$_GET['products_id'] > 0) {
    $current_prd = 'AND p.products_id != ' . (int)$_GET['products_id'];
  } else {
    $current_prd = '';
  }
  if (MAX_DISPLAY_NEW_PRODUCTS_DAYS != '0') {
    $days = "AND p.products_date_added > '".date("Y.m.d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")))."'";
  } else {
    $days = '';
  }

  // get random product data
  $random_product = xtc_random_select("-- templates/tpl_modified/source/boxes/whats_new.php
                                       SELECT distinct
                                              p.products_id,
                                              p.products_image,
                                              p.products_tax_class_id,
                                              p.products_vpe,
                                              p.products_vpe_status,
                                              p.products_vpe_value,
                                              p.products_price
                                         FROM ".TABLE_PRODUCTS." p,
                                              ".TABLE_PRODUCTS_TO_CATEGORIES." p2c,
                                              ".TABLE_CATEGORIES." c
                                        WHERE p.products_status=1
                                          AND p.products_id = p2c.products_id
                                          AND c.categories_id = p2c.categories_id
                                          " . $fsk_lock . "
                                          " . $group_check . "
                                          " . $current_prd . "
                                          " . $days . "
                                          AND c.categories_status=1
                                     ORDER BY p.products_date_added desc
                                        LIMIT ".MAX_RANDOM_SELECT_NEW);
  if (!empty($random_product)) {
    $whats_new_price = $xtPrice->xtcGetPrice($random_product['products_id'], $format = true, 1, $random_product['products_tax_class_id'], $random_product['products_price']);
  }

  if(!empty($random_product['products_id'])) {
    $random_product['products_name'] = xtc_get_products_name($random_product['products_id']);
  }

  if (!empty($random_product['products_name'])) {
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