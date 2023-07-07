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

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_greeting.html', $cache_id) || !$cache) {
    $shop_content_data = $main->getContentData(5, '', '', false, ADD_SELECT_CONTENT);
    if (!empty($shop_content_data['content_heading'])) {
      $box_smarty->assign('TITLE', $shop_content_data['content_heading']);
    }

    if (!empty($shop_content_data['content_text'])) {
      $shop_content_data['content_text'] = str_replace('{$greeting}', xtc_customer_greeting(), $shop_content_data['content_text']);
      $box_smarty->assign('BOX_CONTENT', $shop_content_data['content_text']);
    }
  }
  
  $box_greeting = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_greeting.html', $cache_id);

  $smarty->assign('box_GREETING', $box_greeting);
