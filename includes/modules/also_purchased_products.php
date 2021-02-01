<?php

/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(also_purchased_products.php,v 1.21 2003/02/12); www.oscommerce.com 
   (c) 2003	 nextcommerce (also_purchased_products.php,v 1.9 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$data = $product->getAlsoPurchased();
if (count($data) > 0
    && count($data) >= MIN_DISPLAY_ALSO_PURCHASED
    )
{
	$module_smarty->assign('language', $_SESSION['language']);
	$module_smarty->assign('module_content', $data);
	
  if (defined('PICTURESET_BOX')) {
    $module_smarty->assign('pictureset_box', get_pictureset_data(PICTURESET_BOX));
  }
  if (defined('PICTURESET_ROW')) {
    $module_smarty->assign('pictureset_row', get_pictureset_data(PICTURESET_ROW));
  }

	// set cache ID
	$module_smarty->caching = 0;
	$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/also_purchased.html');

	$info_smarty->assign('MODULE_also_purchased', $module);
}
?>