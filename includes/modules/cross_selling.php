<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(also_purchased_products.php,v 1.21 2003/02/12); www.oscommerce.com
   (c) 2003 nextcommerce (also_purchased_products.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 xt:Commerce (cross_selling.php 1243 2005-09-25); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$module_smarty = new Smarty;
//BOF - GTB - 2010-08-03 - Security Fix - Base
$module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
//$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
//EOF - GTB - 2010-08-03 - Security Fix - Base
$data = $product->getCrossSells();

if (count($data) > 0) {
  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('module_content', $data);
  // set cache ID
  $module_smarty->caching = 0;
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/cross_selling.html');
  $info_smarty->assign('MODULE_cross_selling', $module);
}

// reverse cross selling
if (defined('ACTIVATE_REVERSE_CROSS_SELLING') && ACTIVATE_REVERSE_CROSS_SELLING=='true') {
  $module_smarty = new Smarty;
  //BOF - Dokuman - 2010-01-20: set template path also on activated cross selling
  //BOF - GTB - 2010-08-03 - Security Fix - Base
  $module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  //$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
  //EOF - GTB - 2010-08-03 - Security Fix - Base
  //EOF - Dokuman - 2010-01-20: set template path also on activated cross selling

  $data = $product->getReverseCrossSells();

  if (count($data) > 0) {
    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('module_content', $data);
    // set cache ID
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/reverse_cross_selling.html');
    $info_smarty->assign('MODULE_reverse_cross_selling', $module);
  }

}
?>