<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  // set cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id']);

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_infobox.html', $cache_id) || !$cache) {

    $box_content = '';
    if ($_SESSION['customers_status']['customers_status_image'] != '') {
      //$box_content .= xtc_image('images/icons/' . $_SESSION['customers_status']['customers_status_image']) . '<br />';
    }

    $box_content .= BOX_LOGINBOX_STATUS . ' <strong>' . $_SESSION['customers_status']['customers_status_name'] . '</strong>';

    if ($_SESSION['customers_status']['customers_status_show_price'] == 0) {
      $box_content .= '<br />' . NOT_ALLOWED_TO_SEE_PRICES_TEXT;
    } else {
      if ($_SESSION['customers_status']['customers_status_discount'] != '0.00') {
        $box_content .= '<br />' . BOX_LOGINBOX_DISCOUNT . ' ' . $_SESSION['customers_status']['customers_status_discount'] . '%';
      }
      if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1 && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00') {
        $box_content .= '<br />' . BOX_LOGINBOX_DISCOUNT_TEXT . ' ' . $_SESSION['customers_status']['customers_status_ot_discount'] . ' % ' . BOX_LOGINBOX_DISCOUNT_OT . '';
      }
    }

    $box_smarty->assign('BOX_CONTENT', $box_content);
  }

  $box_infobox = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_infobox.html', $cache_id);

  $smarty->assign('box_INFOBOX', $box_infobox);
