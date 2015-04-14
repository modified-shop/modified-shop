<?php

/* -----------------------------------------------------------------------------------------
   $Id: graduated_prices.php 1243 2005-09-25 09:33:02Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercebased on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35 www.oscommerce.com
   (c) 2003	 nextcommerce (graduated_prices.php,v 1.11 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
$module_content = array ();

$staffel_data = $product->getGraduated();

if (sizeof($staffel_data) > 1) {
	$module_smarty->assign('language', $_SESSION['language']);
	$module_smarty->assign('module_content', $staffel_data);
	// set cache ID

	$module_smarty->caching = 0;
	$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/graduated_price.html');

	$info_smarty->assign('MODULE_graduated_price', $module);
}
?>