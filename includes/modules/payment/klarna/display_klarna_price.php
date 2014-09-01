<?php
/**
 * Display ppbox on product page
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
require_once DIR_WS_INCLUDES . 'application_top.php';

$totalSum = 0;
$productRate = 0;

$productRate = xtc_get_tax_rate($product->data['products_tax_class_id']);
$totalSum = $xtPrice->xtcAddTax($product->data['products_price'], $productRate);

try{
$ppbox = new klarna_ppbox();
$html = $ppbox->showPPBox($totalSum, KlarnaFlags::PRODUCT_PAGE);
}catch(Exception $e){
	return false;
}

$info_smarty->assign('KLARNA_PPBOX', $html);
