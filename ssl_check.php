<?php

/* -----------------------------------------------------------------------------------------
   $Id: ssl_check.php 4221 2013-01-11 10:18:52Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ssl_check.php,v 1.1 2003/03/10); www.oscommerce.com 
   (c) 2003	 nextcommerce (ssl_check.php,v 1.9 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');


$breadcrumb->add(NAVBAR_TITLE_SSL_CHECK, xtc_href_link(FILENAME_SSL_CHECK));

require (DIR_WS_INCLUDES.'header.php');
$smarty->assign('BUTTON_CONTINUE', '<a href="'.xtc_href_link(FILENAME_DEFAULT).'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
$smarty->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$smarty->caching = 0;
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/ssl_check.html');
} else {
	$smarty->caching = 1;
	$smarty->cache_lifetime = CACHE_LIFETIME;
	$smarty->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'];
	$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/ssl_check.html', $cache_id);
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>