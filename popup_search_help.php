<?php

/* -----------------------------------------------------------------------------------------
   $Id: popup_search_help.php 1238 2005-09-24 10:51:19Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(popup_search_help.php,v 1.3 2003/02/13); www.oscommerce.com
   (c) 2003	 nextcommerce (popup_search_help.php,v 1.6 2003/08/17); www.nextcommerce.org 

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

$smarty = new Smarty;

include ('includes/header.php');

$smarty->assign('link_close', 'javascript:window.close()');
$smarty->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$smarty->caching = 0;
	$smarty->display(CURRENT_TEMPLATE.'/module/popup_search_help.html');
} else {
	$smarty->caching = 1;
	$smarty->cache_lifetime = CACHE_LIFETIME;
	$smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'];
	$smarty->display(CURRENT_TEMPLATE.'/module/popup_search_help.html', $cache_id);
}
?>