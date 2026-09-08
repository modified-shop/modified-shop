<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

// include smarty
include(Template::path('source/inc/smarty_default.php'));

// set cache id
$cache_id = md5('lID:'.$_SESSION['language']);

if (!$box_smarty->is_cached(Template::resolve('boxes/box_trustedshops.html'), $cache_id) || !$cache) {
  $box_smarty->assign('STICKER_CODE', MODULE_TS_REVIEW_STICKER);
}

if (!$cache) {
  $box_trustedshops = $box_smarty->fetch(Template::resolve('boxes/box_trustedshops.html'));
} else {
  $box_trustedshops = $box_smarty->fetch(Template::resolve('boxes/box_trustedshops.html'), $cache_id);
}

$smarty->assign('box_TRUSTEDSHOPS', $box_trustedshops);
?>