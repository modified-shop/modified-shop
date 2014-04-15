<?php

// -----------------------------------------------------------------------------------------
//	Box Smarty default
// -----------------------------------------------------------------------------------------
$box_smarty = new smarty;
$box_smarty->assign('language', $_SESSION['language']);
$box_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache=false;
  $box_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache=true;
  $box_smarty->caching = 1;
  $box_smarty->cache_lifetime = CACHE_LIFETIME;
  $box_smarty->cache_modified_check = CACHE_CHECK;
}
?>