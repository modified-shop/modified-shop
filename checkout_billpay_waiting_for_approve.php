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

    $redirectTarget = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
    xtc_redirect($redirectTarget);
    return true;
}

// payment pending - waiting for user

// some hacking to fit the normal checkout layout (commerce::SEO):
$_SERVER['REQUEST_URI'] = FILENAME_CHECKOUT_PAYMENT;

$billpaySmarty = new Smarty();
$billpaySmarty->caching = 0;

$billpaySmarty->assign('refresh_url', xtc_href_link('checkout_billpay_waiting_for_approve.php', 'orderId='.$orderId));
$billpaySmarty->assign('checkout_success_url', xtc_href_link(FILENAME_CHECKOUT_SUCCESS));
$billpaySmarty->assign('language', $_SESSION['language']);

if($billpay->isGambio()) {

    // include boxes and header
    $mainContent = $billpaySmarty->fetch('../includes/external/billpay/templates/checkout_billpay_waiting_for_approve.tpl');

    /** @var LayoutContentControl $coo_layout_control */
    $coo_layout_control = MainFactory::create_object('LayoutContentControl');
    $coo_layout_control->set_data('GET', $_GET);
    $coo_layout_control->set_data('POST', $_POST);
    $t_category_id = 0;

    $coo_layout_control->set_('coo_breadcrumb', $GLOBALS['breadcrumb']);
    $coo_layout_control->set_('coo_product', $GLOBALS['product']);
    $coo_layout_control->set_('coo_xtc_price', $GLOBALS['xtPrice']);
    $coo_layout_control->set_('c_path', $GLOBALS['cPath']);
    $coo_layout_control->set_('main_content', $mainContent);
    $coo_layout_control->set_('request_type', $GLOBALS['request_type']);
    $coo_layout_control->proceed();

    echo $coo_layout_control->get_response();
} else {

    // create smarty elements
    $smarty = new Smarty;
    $smarty->caching = 0;
    $smarty->assign('language', $_SESSION['language']);


    require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
    require (DIR_WS_INCLUDES.'header.php');

    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('main_content', $billpaySmarty->fetch('../includes/external/billpay/templates/checkout_billpay_waiting_for_approve.tpl'));
    $smarty->display(CURRENT_TEMPLATE . '/index.html');

    include ('includes/application_bottom.php');
}