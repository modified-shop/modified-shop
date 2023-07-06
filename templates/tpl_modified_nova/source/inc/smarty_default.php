<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

//include needed functions
require_once (DIR_FS_INC.'xtc_date_short.inc.php');
require_once (DIR_FS_INC.'get_pictureset_data.inc.php');

$box_smarty = new Smarty();
$box_smarty->assign('language', $_SESSION['language']);
$box_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

if (defined('PICTURESET_BOX')) {
  $box_smarty->assign('pictureset_box', get_pictureset_data(PICTURESET_BOX));
}
if (defined('PICTURESET_ROW')) {
  $box_smarty->assign('pictureset_row', get_pictureset_data(PICTURESET_ROW));
}

// set cache ID
if (!CacheCheck()) {
  $cache = false;
  $box_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache = true;
  $box_smarty->caching = 1;
  $box_smarty->cache_lifetime = CACHE_LIFETIME;
  $box_smarty->cache_modified_check = CACHE_CHECK == 'true';
}
