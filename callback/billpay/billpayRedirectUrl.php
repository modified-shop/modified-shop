<?php
chdir('../../');

include('includes/application_top.php');

$orderId = $_SESSION['tmp_oID'];
$success = false;
$error   = "";

// Warning: if client uses old account number, we will get different error codes here!
if (!empty($_GET['gpCode']))
{
    // OLD API
    // http://oldapi.giroconnect.de/GiroConnect/tutorial_GiroConnect.errorcodes.pkg.html

    if ($_GET['gpCode'] === '4000') {
        $success = true;
    }
    elseif ($_GET['gpCode'] === '3100')
    {
        // Giropayment aborted by user, deny order, redirect to cart
        $error = 'Giropay payment aborted by user. Please pick another payment method.';
    } else {
        $error = 'Unhandled Old Giropay API error. Please contact BillPay Support. Error code: '.$_GET['gpCode'];
    }
}

if (!empty($_GET['gcReference']))
{
    // NEW API
    // http://api.girocheckout.de/girocheckout:resultcodes#zahlungsausgang
    if ($_GET['gcResultPayment'] === '4000')
    {
        $success = true;
    } else {
        $error = constant('MODULE_PAYMENT_BILLPAY_GIROPAY_CANCELED');
    }
}

if ($success)
{
    foreach ($_SESSION as $key => $val)
    {
        if (strpos($key, 'billpay') === 0)
        {
            unset($_SESSION[$key]);
        }
    }
    if ($_SESSION['cart'])
    {
        /** @var shoppingCart $cart */
        $cart = $_SESSION['cart'];
        $cart->reset(true);
    }
    $redirectPage = xtc_href_link('checkout_billpay_waiting_for_approve.php', 'orderId='.$orderId, 'SSL');
    xtc_redirect($redirectPage);
} else {
    if (empty($error))
    {
        $error = constant('MODULE_PAYMENT_BILLPAY_GIROPAY_CANCELED');
    }
    $messageStack->add_session('checkout_payment', $error);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}