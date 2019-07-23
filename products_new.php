<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(products_new.php,v 1.25 2003/05/27); www.oscommerce.com
   (c) 2003  nextcommerce (products_new.php,v 1.16 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (products_new.php 1292 2005-10-07)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Enable_Disable_Categories 1.3          Autor: Mikel Williams | mikel@ladykatcostumes.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

$smarty = new Smarty;

// include needed function
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if ($language_not_found === true) {
  $site_error = TEXT_SITE_NOT_FOUND;
  include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);

} else {
  $breadcrumb->add(NAVBAR_TITLE_PRODUCTS_NEW, xtc_href_link(FILENAME_PRODUCTS_NEW));

  include (DIR_WS_MODULES.'default.php');
}

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>