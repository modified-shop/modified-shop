<?php
/**
 * landingpage to wait for "APPROVED" status response from billpay
 *
 * @category   Billpay
 * @package    Billpay\Giropay
 * @link       https://www.billpay.de/
 */
include ('includes/application_top.php');
$orderId = (int)$_GET['orderId'];

// check if we got the data we need
if (empty($orderId)) {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
}

require_once (DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');
$orderStatusId = BillpayDB::DBFetchValue("SELECT orders_status FROM ".TABLE_ORDERS." WHERE orders_id = '".$orderId."'");
// TODO: we should use order's payment method, but currently only PayLater uses prepayment
/** @var BillpayPayLater $billpay */
$billpay = billpayBase::PaymentInstance(billpayBase::PAYMENT_METHOD_PAY_LATER);
if ($orderStatusId === $billpay->getOrderStatusFromBillpayState(billpayBase::STATE_APPROVED)) {
    // payment complete
    $redirectTarget = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
    xtc_redirect($redirectTarget);
    return true;
}

// payment pending - waiting for user


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

$billpaySmarty->assign('refresh_url', xtc_href_link('checkout_billpay_waiting_for_approve.php', 'orderId='.$orderId));
$billpaySmarty->assign('checkout_success_url', xtc_href_link(FILENAME_CHECKOUT_SUCCESS));
$billpaySmarty->assign('language', $_SESSION['language']);

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $billpaySmarty->fetch('../includes/external/billpay/templates/checkout_billpay_waiting_for_approve.tpl'));
$smarty->display(CURRENT_TEMPLATE . '/index.html');

include ('includes/application_bottom.php');

