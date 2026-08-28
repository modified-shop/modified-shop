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
  include(Template::path('source/inc/smarty_default.php'));

  // set cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|csID:'.$_SESSION['customers_status']['customers_status_id']);

  $box_miscellaneous = $box_smarty->fetch(Template::resolve('boxes/box_miscellaneous.html'), $cache_id);

  $smarty->assign('box_MISCELLANEOUS', $box_miscellaneous);
