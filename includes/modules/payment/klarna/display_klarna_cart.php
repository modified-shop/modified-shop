<?php
/**
 *  Display box in cart
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
require_once DIR_FS_DOCUMENT_ROOT . 'includes/external/klarna/class.KlarnaCore.php';
require_once DIR_KLARNA . 'class.klarnappbox.php';

$totalSum = $_SESSION['cart']->show_total();

$ppbox = new klarna_ppbox($order->delivery['country']);
$html = $ppbox -> showPPBox($totalSum, KlarnaFlags::CHECKOUT_PAGE);
$module_smarty->assign('KLARNA_PPBOX', $html);
