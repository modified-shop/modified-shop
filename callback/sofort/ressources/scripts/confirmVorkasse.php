<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: confirmVorkasse.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

require_once('../../library/sofortLib.php');
chdir('../../../..');
include ('includes/application_top.php');
require_once(DIR_FS_CATALOG.'callback/sofort/helperFunctions.php');

$language = HelperFunctions::getSofortLanguage($_SESSION['language']);
include(DIR_WS_LANGUAGES.$language.'/modules/payment/sofort_sofortvorkasse.php');

// create smarty elements
$smarty = new Smarty;
// include boxes
require_once(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// if the customer is not logged on, redirect them to the shopping cart page
if (!isset ($_SESSION['customer_id'])) {
	xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_CONFIRMATION);
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_CONFIRMATION);
require_once(DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('order', xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL')));
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('BUTTON_PRINT', '<img src="'.'templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/button_print.gif" style="cursor:hand"
				onclick="window.open(\''.xtc_href_link(FILENAME_PRINT_ORDER, 'oID='.$orders['orders_id']).'\', \'popup\', \'toolbar=0, width=640, height=600\')" />');
$smarty->assign('FORM_END', '</form>');
$smarty->assign('HEADING', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HEADING_TEXT);
$smarty->assign('TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_TEXT);
$smarty->assign('HOLDER', HelperFunctions::htmlMask($_GET['holder']));
$smarty->assign('HOLDER_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT);
$smarty->assign('ACCOUNT_NUMBER', HelperFunctions::htmlMask($_GET['account_number']));
$smarty->assign('ACCOUNT_NUMBER_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT);
$smarty->assign('IBAN', HelperFunctions::htmlMask($_GET['iban']));
$smarty->assign('IBAN_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT);
$smarty->assign('BANK_CODE', HelperFunctions::htmlMask($_GET['bank_code']));
$smarty->assign('BANK_CODE_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT);
$smarty->assign('BIC', HelperFunctions::htmlMask($_GET['bic']));
$smarty->assign('BIC_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT);
$smarty->assign('AMOUNT', number_format(HelperFunctions::htmlMask($_GET['amount']),2,',','.'). ' &euro;');
$smarty->assign('AMOUNT_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT);
$smarty->assign('REASON_1', HelperFunctions::htmlMask($_GET['reason_1']));
$smarty->assign('REASON_1_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT);
$smarty->assign('REASON_2', HelperFunctions::htmlMask($_GET['reason_2']));
$smarty->assign('REASON_2_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT);
$smarty->assign('REASONS_HINT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_HINT);

// Google Conversion tracking
if (GOOGLE_CONVERSION == 'true') {
	$smarty->assign('google_tracking', 'false');
	$smarty->assign('tracking_code', '');
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('PAYMENT_BLOCK', $payment_block);
$smarty->caching = 0;
$main_content = $smarty->fetch('../callback/sofort/ressources/scripts/checkoutVorkasse.html');
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;

if (!defined(RM)) {
	$smarty->load_filter('output', 'note');
}

$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
unset($_SESSION['sofort']['sofort_transaction']);

?>