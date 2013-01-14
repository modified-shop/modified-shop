<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_vorkasse.php 3773 2012-10-10 14:51:44Z gtb-modified $
 */
 
require_once(DIR_FS_CATALOG.'callback/sofort/helperFunctions.php');
include(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/sofort_sofortvorkasse.php');

// create module_smarty elements
$module_smarty = new smarty;

// include boxes
require_once(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$module_smarty->assign('TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_TEXT);
$module_smarty->assign('HOLDER', HelperFunctions::htmlMask($_GET['holder']));
$module_smarty->assign('HOLDER_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT);
$module_smarty->assign('ACCOUNT_NUMBER', HelperFunctions::htmlMask($_GET['account_number']));
$module_smarty->assign('ACCOUNT_NUMBER_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT);
$module_smarty->assign('IBAN', HelperFunctions::htmlMask($_GET['iban']));
$module_smarty->assign('IBAN_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT);
$module_smarty->assign('BANK_CODE', HelperFunctions::htmlMask($_GET['bank_code']));
$module_smarty->assign('BANK_CODE_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT);
$module_smarty->assign('BIC', HelperFunctions::htmlMask($_GET['bic']));
$module_smarty->assign('BIC_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT);
$module_smarty->assign('AMOUNT', number_format(HelperFunctions::htmlMask($_GET['amount']),2,',','.'). ' &euro;');
$module_smarty->assign('AMOUNT_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT);
$module_smarty->assign('REASON_1', HelperFunctions::htmlMask($_GET['reason_1']));
$module_smarty->assign('REASON_1_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT);
$module_smarty->assign('REASON_2', HelperFunctions::htmlMask($_GET['reason_2']));
$module_smarty->assign('REASON_2_TEXT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT);
$module_smarty->assign('REASONS_HINT', MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_HINT);

$module_smarty->assign('language', $_SESSION['language']);

$module_smarty->caching = 0;
$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/sofort_vorkasse.html');

$smarty->assign('MODULE_sofort_vorkasse', $module);
unset($_SESSION['sofort']['sofort_transaction']);
?>