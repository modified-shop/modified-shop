<?php
chdir('../../');
include('includes/application_top.php');

require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/ipl_xml_api.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_xml_request.php');
require_once(DIR_FS_CATALOG . 'includes/external/billpay/api/php4/ipl_preauthorize_request.php');
require_once(DIR_FS_CATALOG . 'lang/' . $_SESSION["language"] . '/modules/payment/billpaytransactioncredit.php');

if (!isset ($_SESSION['customer_id'])) {
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART));
}

if (isset ($_GET['action']) && ($_GET['action'] == 'update')) {

    if ($_SESSION['account_type'] != 1) {
        xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
    } else {
        xtc_redirect(xtc_href_link(FILENAME_LOGOFF));
    }
}

// we check for the status provided by giropay
$gpCode = $_GET['gpCode'];
if ($gpCode != '4000') {
    if (defined('FILENAME_CHECKOUT')) {
        $redirectPage = FILENAME_CHECKOUT;
    } else {
        $redirectPage = FILENAME_CHECKOUT_PAYMENT;
    }
    xtc_redirect(xtc_href_link(
        $redirectPage,
        'error_message=' . urlencode(MODULE_PAYMENT_BILLPAYTRANSACTIONCREDIT_TEXT_ERROR_DEFAULT),
        'SSL'
    ));

} else {

    // unset billpay session data
    unset($_SESSION['billpay_transaction_id']);
    unset($_SESSION['billpay_total_amount']);
    unset($_SESSION['billpay_preselect']);
    unset($_SESSION['bp_rate_result']);
    unset($_SESSION['rr_data']);

    // should not happen but better check than run into a fatal error
    if (isset($_SESSION['cart'])) {
        // we take the reference since it is done like this in xtc
        $oShoppingCart =& $_SESSION['cart'];

        if (is_object($oShoppingCart) && $oShoppingCart instanceof shoppingCart) {
            $oShoppingCart->reset(true);
        }
    }

    $redirectPage = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
    if (empty($_SESSION['tmp_oID']) === false) {
        // to prevent loading the cart in order object
        if (defined('RUN_MODE_ADMIN') === false) define('RUN_MODE_ADMIN', true);

        require (DIR_WS_CLASSES . 'order.php');
        $order = new order((int)$_SESSION['tmp_oID']);

        // we still have our temporary order status so we wait for the approving
        // status is 101 in xtcmodified and null in commerceseo
        if ($order->info['orders_status'] == '101' || $order->info['orders_status'] === null) {
            $redirectPage = xtc_href_link('checkout_billpay_waiting_for_approve.php', '', 'SSL');
            $_SESSION['billpay_data']['order_id'] = $_SESSION['tmp_oID'];
        }
    }

    // unset session variables used during checkout
    unset($_SESSION['sendto']);
    unset($_SESSION['billto']);
    unset($_SESSION['shipping']);
    unset($_SESSION['payment']);
    unset($_SESSION['comments']);
    unset($_SESSION['last_order']);
    unset($_SESSION['tmp_oID']);
    unset($_SESSION['cc']);

    require(DIR_WS_CLASSES . 'order_total.php');
    $order_total_modules = new order_total();
    $order_total_modules->clear_posts(); //ICW ADDED FOR CREDIT CLASS SYSTEM

    xtc_redirect($redirectPage);
}


