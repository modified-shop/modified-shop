<?php

/* -----------------------------------------------------------------------------------------
   $Id: infobox.php 1262 2005-09-30 10:00:32Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercebased on original files from OSCommerce CVS 2.2 2002/08/28 02:14:35 www.oscommerce.com 
   (c) 2003	 nextcommerce (infobox.php,v 1.7 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Loginbox V1.0        	Aubrey Kilian <aubrey@mycon.co.za>

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
$box_smarty = new smarty;
$box_smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
$box_content = '';

if ($_SESSION['customers_status']['customers_status_image'] != '') {
	$loginboxcontent = xtc_image('admin/images/icons/' . $_SESSION['customers_status']['customers_status_image']) . '<br />';
}
$loginboxcontent .= BOX_LOGINBOX_STATUS . ' <strong>' . $_SESSION['customers_status']['customers_status_name'] . '</strong><br />';
if ($_SESSION['customers_status']['customers_status_show_price'] == 0) {
	$loginboxcontent .= NOT_ALLOWED_TO_SEE_PRICES_TEXT;
} else {
	if ($_SESSION['customers_status']['customers_status_discount'] != '0.00') {
		$loginboxcontent .= BOX_LOGINBOX_DISCOUNT . ' ' . $_SESSION['customers_status']['customers_status_discount'] . '%<br />';
	}
	if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == 1 && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00') {
		$loginboxcontent .= BOX_LOGINBOX_DISCOUNT_TEXT . ' ' . $_SESSION['customers_status']['customers_status_ot_discount'] . ' % ' . BOX_LOGINBOX_DISCOUNT_OT . '<br />';
	}
}

$box_smarty->assign('BOX_CONTENT', $loginboxcontent);
$box_smarty->assign('language', $_SESSION['language']);

$box_smarty->caching = 0;
$box_infobox = $box_smarty->fetch(CURRENT_TEMPLATE . '/boxes/box_infobox.html');

$smarty->assign('box_INFOBOX', $box_infobox);
?>