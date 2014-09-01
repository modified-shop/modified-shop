<?php

/* -----------------------------------------------------------------------------------------
   $Id: wpcallback.php,v 1.0
   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   Anpassung Worldpay by XTC-Webservice.de, Matthias Hinsche
   -----------------------------------------------------------------------------------------
   based on:

  $Id: wpcallback.php,v MS1a 2003/04/06 21:30
  Author : 	Graeme Conkie (graeme@conkie.net)
  Title: WorldPay Payment Callback Module V4.0 Version 1.4

  Revisions:

    Version 1.8 - cleaned up coding errors in wpcallback.php
                - reduced "refresh" to 2 seconds (less chance of callback failing)

            Gary Burton - www.oscbooks.com

	Version MS1a Cleaned up code, moved static English to language file to allow for bi-lingual use,
	        Now posting language code to WP, Redirect on failure now to Checkout Payment,
			Reduced re-direct time to 8 seconds, added MD5, made callback dynamic
			NOTE: YOU MUST CHANGE THE CALLBACK URL IN WP ADMIN TO <wpdisplay item="MC_callback">
	Version 1.4 Removes boxes to prevent users from clicking away before update, 
			Fixes currency for Yen,
			Redirects to Checkout_Process after 10 seconds or click by user
	Version 1.3 Fixes problem with Multi Currency
	Version 1.2 Added Sort Order and Default order status to work with snapshots after 14 Jan 2003
	Version 1.1 Added Worldpay Pre-Authorisation ability
	Version 1.0 Initial Payment Module

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$breadcrumb->add(NAVBAR_TITLE_SPECIALS, xtc_href_link(FILENAME_SPECIALS));

require (DIR_WS_INCLUDES.'header.php');

//if(isset($transStatus) && $transStatus == "Y") {
if($_POST['transStatus'] == "Y") {
	$url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, $cartId, 'NONSSL', false);
	$meta = "<meta http-equiv='Refresh' content='2; Url=\"$url\"'>";
	$text = WP_TEXT_SUCCESS;
} else {
	$url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $cartId, 'NONSSL', false);
	$meta = "<meta http-equiv='Refresh' content='2; Url=\"$url\"'>";
	$link = '<a href="'.xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', false).'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>';
}

$smarty->assign('URL', $url);
$smarty->assign('META', $meta);
$smarty->assign('TEXT', $text);
$smarty->assign('LINK', $link);

$smarty->assign('language', $_SESSION['language']);
//  $smarty->assign('module_content',$module_content);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/wpcallback.html');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined(RM))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>
