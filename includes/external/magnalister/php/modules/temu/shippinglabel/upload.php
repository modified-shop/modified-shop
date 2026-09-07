<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

global $_url;

if (!defined('DIR_MAGNALISTER_MODULES_TEMU_ORDERLIST')) {
    define('DIR_MAGNALISTER_MODULES_TEMU_ORDERLIST', DIR_MAGNALISTER_MODULES.'temu/shippinglabel/classes/Orderlist/');
}

// Define Temu template override path
if (!defined('DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES')) {
    define('DIR_MAGNALISTER_MODULES_TEMU_SHIPPINGLABEL_TEMPLATES', DIR_MAGNALISTER_MODULES.'temu/shippinglabel/templates/');
}

$aAllowedSubviews = array('orderlist', 'form', 'shippingmethod', 'summary');
if (!array_key_exists('subview', $_GET) || !in_array($_GET['subview'], $aAllowedSubviews)) {
    $_url['subview'] = 'orderlist';
} else {
    $_url['subview'] = $_GET['subview'];
}

$sClassName = 'TemuShippingLabelUpload' . ucfirst($_url['subview']);
$sFileName = DIR_MAGNALISTER_MODULES . 'temu/shippinglabel/upload/' . $sClassName . '.php';

if (file_exists($sFileName)) {
    require_once($sFileName);
    $oOrderlist = new $sClassName();
    echo $oOrderlist->render();
} else {
    echo '<p class="errorBox">Subview not found: ' . htmlspecialchars($_url['subview']) . '</p>';
}
