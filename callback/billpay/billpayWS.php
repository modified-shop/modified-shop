<?php
chdir('../../');
include('includes/application_top.php');
require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_api.php');
require_once(DIR_WS_INCLUDES . 'external/billpay/api/ipl_xml_ws.php');
require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_update_order_request.php');
require_once(DIR_WS_INCLUDES . 'external/billpay/api/php4/ipl_xml_request.php');
require_once(DIR_WS_INCLUDES . 'external/billpay/base/billpayBase.php');


$billpay = new billpayBase(billpayBase::PAYMENT_METHOD_TRANSACTION_CREDIT);
$billpay->billpayAsyncWS();
