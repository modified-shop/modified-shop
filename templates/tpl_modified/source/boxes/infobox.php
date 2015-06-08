<?php
/* -----------------------------------------------------------------------------------------
   $Id: infobox.php 1262 2005-09-30 10:00:32Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercebased on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35 www.oscommerce.com 
   (c) 2003	 nextcommerce (infobox.php,v 1.7 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Loginbox V1.0        	Aubrey Kilian <aubrey@mycon.co.za>

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

// set cache id
$cache_id = md5($_SESSION['language'] . $_SESSION['customers_status']['customers_status']);

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_infobox.html', $cache_id) || !$cache) {

  $box_content = '';
  if ($_SESSION['customers_status']['customers_status_image'] != '') {
    //$box_content .= xtc_image('images/icons/' . $_SESSION['customers_status']['customers_status_image']) . '<br />';
  }

  $box_content .= BOX_LOGINBOX_STATUS . ' <strong>' . $_SESSION['customers_status']['customers_status_name'] . '</strong>';

  if ($_SESSION['customers_status']['customers_status_show_price'] == 0) {
    $box_content .= ' | ' . NOT_ALLOWED_TO_SEE_PRICES_TEXT;
  } else {
    if ($_SESSION['customers_status']['customers_status_discount'] != '0.00') {
      $box_content .= ' | ' . BOX_LOGINBOX_DISCOUNT . ' ' . $_SESSION['customers_status']['customers_status_discount'] . '%';
    }
    if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1 && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00') {
      $box_content .= ' | ' . BOX_LOGINBOX_DISCOUNT_TEXT . ' ' . $_SESSION['customers_status']['customers_status_ot_discount'] . ' % ' . BOX_LOGINBOX_DISCOUNT_OT . '';
    }
  }

  $box_smarty->assign('BOX_CONTENT', $box_content);
}

if (!$cache) {
  $box_infobox = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_infobox.html');
} else {
  $box_infobox = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_infobox.html', $cache_id);
}

$smarty->assign('box_INFOBOX', $box_infobox);
?>