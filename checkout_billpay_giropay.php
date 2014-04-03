<?php
include ('includes/application_top.php');

require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_xml_request.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_preauthorize_request.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/Config.php');

$_SESSION['billpay_state'] = "YELOW";
// if NO BP session is found go home :)
if(!isset($_SESSION['billpay_state']) || $_SESSION['billpay_state'] != "YELOW"){
    xtc_redirect(xtc_href_link("", null, 'SSL'));
}
unset($_SESSION['billpay_state']);

$req = false;
if (isset($_SESSION['billpay_preauth_req'])) {
    $req = unserialize($_SESSION['billpay_preauth_req']);

    if (is_object($req) === false || $req instanceof ipl_preauthorize_request === false) {
        $req = false;
    }
}

$oBillpayConfig = new Billpay_Base_Config();

// create smarty elements
$smarty = new Smarty;
$smarty->caching = 0;
$smarty->assign('language', $_SESSION['language']);

// some hacking to fit the normal checkout layout (commerce::SEO):
$_SERVER['REQUEST_URI'] = FILENAME_CHECKOUT_PAYMENT;

// include boxes and header
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
require (DIR_WS_INCLUDES.'header.php');



$billpaySmarty = new Smarty();
$billpaySmarty->caching = 0;

if ($req !== false) {
    /** @var $req ipl_preauthorize_request */
    if (defined('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') && MODULE_PAYMENT_BILLPAY_UTF8_ENCODE == 'True') {
        $billpaySmarty->assign('campaignText', utf8_decode($req->get_campaign_dispay_text()));
    } else {
        $billpaySmarty->assign('campaignText', $req->get_campaign_dispay_text());
    }

    $billpaySmarty->assign('externalRedirect', $req->get_external_redirect_url() );
    $billpaySmarty->assign('campaignImg', $req->get_campaign_dispay_image_url() );
    $billpaySmarty->assign('rateLink', $req->get_rate_plan_url() );
} else {
    $billpaySmarty->assign('campaignText', 'BLUBBLA');
    $billpaySmarty->assign('externalRedirect', 'https://www.billpay.de/external');
    $billpaySmarty->assign('campaignImg', 'https://www.billpay.de/wp-content/themes/billpay/images/billpay_logo.jpg');
    $billpaySmarty->assign('rateLink', 'https://www.billpay.de/ratelink');
}

// do we use image buttons?
if ($oBillpayConfig->get('template.giropay.image-buttons', false) === true) {

    $billpaySmarty->assign('button_container_class', 'bpy-image-buttons');

    $billpaySmarty->assign('button_back_content', $oBillpayConfig->get('template.giropay.btn-back.text'));
    $billpaySmarty->assign('button_back_image', $oBillpayConfig->get('template.giropay.btn-back.image'));
    $billpaySmarty->assign('button_continue_content', $oBillpayConfig->get('template.giropay.btn-continue.text'));
    $billpaySmarty->assign('button_continue_image', $oBillpayConfig->get('template.giropay.btn-continue.image'));

} else {
    $billpaySmarty->assign('button_container_class', 'bpy-text-buttons');

    $billpaySmarty->assign('button_back_content', $oBillpayConfig->get('template.giropay.btn-back.text'));
    $billpaySmarty->assign('button_back_image', '');
    $billpaySmarty->assign('button_continue_content', $oBillpayConfig->get('template.giropay.btn-continue.text'));
    $billpaySmarty->assign('button_continue_image', '');
}

$billpaySmarty->assign('button_back_width', $oBillpayConfig->get('template.giropay.btn-back.width'));
$billpaySmarty->assign('button_back_height', $oBillpayConfig->get('template.giropay.btn-back.height'));
$billpaySmarty->assign('button_continue_width', $oBillpayConfig->get('template.giropay.btn-continue.width'));
$billpaySmarty->assign('button_continue_height', $oBillpayConfig->get('template.giropay.btn-continue.height'));

if (defined('RM') === false) {
    if (isset($billpaySmarty->_version) === true && version_compare($billpaySmarty->_version, '3.0.0', '<')) {
        $billpaySmarty->load_filter('output', 'note');
    } else {
        $billpaySmarty->loadFilter('output', 'note');
    }
}

$smarty->assign('language', $_SESSION['language']); // intended duplicate
$smarty->assign('main_content', $billpaySmarty->fetch('../includes/external/billpay/templates/checkout_billpay_giropay.tpl'));
$smarty->display(CURRENT_TEMPLATE . '/index.html');

include ('includes/application_bottom.php');
