<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');

// needed define
define('SESSION_FORCE_COOKIE_USE', 'False');

$json = file_get_contents('php://input');
$klarna_data = json_decode($json, true);

include('includes/application_top.php');

// include needed language
require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/klarna_checkout.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_count_shipping_modules.inc.php');
require_once (DIR_FS_INC.'xtc_create_password.inc.php');
require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');
require_once (DIR_FS_INC.'get_country_id.inc.php');

// include needed classes
require_once(DIR_WS_MODULES.'payment/klarna_checkout.php');
require_once(DIR_WS_CLASSES.'order.php');

include(DIR_FS_EXTERNAL.'klarna/modules/address_update.php');
include(DIR_FS_EXTERNAL.'klarna/modules/shipping_option_update.php');

$backup_shipping = $_SESSION['shipping'];
$backup_delivery_zone = $_SESSION['delivery_zone'];
$_SESSION['country'] = $_SESSION['customer_country'];

// create smarty elements
$smarty = $module_smarty = new Smarty;

include(DIR_WS_INCLUDES.'shipping_estimate.php');
$_SESSION['shipping'] = $backup_shipping;
$_SESSION['delivery_zone'] = $backup_delivery_zone;

if ((!isset($_SESSION['shipping']) && CHECK_CHEAPEST_SHIPPING_MODUL == 'true') || (isset($_SESSION['shipping']) && ($_SESSION['shipping'] == false) && (xtc_count_shipping_modules() > 1))) {
	$_SESSION['shipping'] = $shipping->cheapest();
}

$klarna = new klarna_checkout();
$response = $klarna->getOrderData(true);
$response['shipping_options'] = $klarna->getShippingData($shipping_content);

//file_put_contents(DIR_FS_CATALOG.'log/klarna.log', print_r($response, true), FILE_APPEND);

$response = json_encode($response);

// response headers
header('Content-Type: application/json');
header("Expires: Sun, 19 Nov 1978 05:00:00 GMT");
header("Last-Modified: " . gmdate('D, d M Y H:i:s') . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// output
echo $response;

// close MySQL connection
session_write_close();
xtc_db_close();
