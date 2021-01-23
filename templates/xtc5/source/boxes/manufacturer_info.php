<?php
  /* -----------------------------------------------------------------------------------------
   $Id: manufacturer_info.php 2853 2012-05-10 08:48:39Z gtb-modified $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(manufacturer_info.php,v 1.10 2003/02/12); www.oscommerce.com
   (c) 2003   nextcommerce (manufacturer_info.php,v 1.6 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language'].((isset($product->data['manufacturers_id']) && $product->data['manufacturers_id'] != '') ? $product->data['manufacturers_id'] : '0'));

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_manufacturers_info.html', $cache_id) || !$cache) {
  $manufacturers_array = xtc_get_manufacturers();
  if (isset($manufacturers_array[$product->data['manufacturers_id']])) {
    $manufacturer = $manufacturers_array[$product->data['manufacturers_id']];
    $image = $main->getImage($manufacturer['manufacturers_image'], '', MANUFACTURER_IMAGE_SHOW_NO_IMAGE, 'manufacturers/noimage.gif');

    $info_smarty->assign('IMAGE', (($image != '') ? DIR_WS_BASE . $image : ''));
    $info_smarty->assign('NAME', $manufacturer['manufacturers_name']);
    if ($manufacturer['manufacturers_url'] != '') {
      $box_smarty->assign('URL','<a href="' . xtc_href_link(FILENAME_REDIRECT, 'action=manufacturer&'.xtc_manufacturer_link($manufacturer['manufacturers_id'],$manufacturer['manufacturers_name'])) . '" onclick="window.open(this.href); return false;">' . sprintf(BOX_MANUFACTURER_INFO_HOMEPAGE, $manufacturer['manufacturers_name']) . '</a>');
    }
    $box_smarty->assign('LINK_MORE','<a href="' . xtc_href_link(FILENAME_DEFAULT, xtc_manufacturer_link($manufacturer['manufacturers_id'],$manufacturer['manufacturers_name'])) . '">' . BOX_MANUFACTURER_INFO_OTHER_PRODUCTS . '</a>');
  }
}

if (!$cache) {
  $box_manufacturers_info = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers_info.html');
} else {
  $box_manufacturers_info = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_manufacturers_info.html', $cache_id);
}

$smarty->assign('box_MANUFACTURERS_INFO',$box_manufacturers_info);
?>