<?php
/**
 * landingpage to wait for "APPROVED" status response from billpay
 *
 * @category   Billpay
 * @package    Billpay\Giropay
 * @version    @TODO
 * @link       https://www.billpay.de/
 */
include ('includes/application_top.php');
require (DIR_WS_CLASSES . 'order.php');
require (DIR_FS_CATALOG . 'includes/external/billpay/base/Config.php');

// check if we got the data we need
if (isset($_SESSION['billpay_data']['order_id']) === false
    || isset ($_SESSION['customer_id']) === false
) {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
}

// to prevent loading the cart in order object
if (defined('RUN_MODE_ADMIN') === false) define('RUN_MODE_ADMIN', true);
$order = new order((int)$_SESSION['billpay_data']['order_id']);

$redirectTarget = false;

// security check -> the order must be owned by the visitor of this page
if ($order->customer['id'] != $_SESSION['customer_id']) {
    $redirectTarget = xtc_href_link(FILENAME_DEFAULT);

// the order is not in waiting status so we redirect to checkout success page
// status is 101 in xtcmodified and null in commerceseo
} elseif ($order->info['orders_status'] != '101' && $order->info['orders_status'] !== null) {
    unset($_SESSION['billpay_data']['order_id']);
    $redirectTarget = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
}

if (isset($_GET['ajaxCall']) === true && $_GET['ajaxCall'] == true) {
    echo json_encode(array(
        'redirectTarget' => $redirectTarget,
    ));

} else {
    if ($redirectTarget !== false) {
        xtc_redirect($redirectTarget);
    }

    // loading the shop system specific configs
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

    $campaignText = 'Vielen Dank, dass Sie sich für die Anzahlung des Rechnungsbetrages entschieden haben.<br/>
                     Bitte gedulden Sie sich einen Moment bis wir die Bestätigung ihrer Bank erhalten haben.';
    if (defined('MODULE_PAYMENT_BILLPAY_UTF8_ENCODE') && MODULE_PAYMENT_BILLPAY_UTF8_ENCODE == 'True') {
        $campaignText = utf8_decode($campaignText);
    }
    $billpaySmarty->assign('campaignText', $campaignText);
    $billpaySmarty->assign('image_loading', $oBillpayConfig->get('template.waiting-for-approve.img-loading'));
    $billpaySmarty->assign('image_ok', $oBillpayConfig->get('template.waiting-for-approve.img-loading-ok'));

    $billpaySmarty->assign('refresh_url', xtc_href_link('checkout_billpay_waiting_for_approve.php'));
    $billpaySmarty->assign('checkout_success_url', xtc_href_link(FILENAME_CHECKOUT_SUCCESS));

    $smarty->assign('language', $_SESSION['language']); // intended duplicate
    $smarty->assign('main_content', $billpaySmarty->fetch('../includes/external/billpay/templates/checkout_billpay_waiting_for_approve.tpl'));
    $smarty->display(CURRENT_TEMPLATE . '/index.html');

    include ('includes/application_bottom.php');
}