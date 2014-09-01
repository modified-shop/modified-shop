<?php
require_once('mcp_prepay.php');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_TITLE','(global) Status');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_DESC','(global) Enable the micropayment&trade; module');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_TITLE','Positioning');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_DESC','Position in the liste');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_TITLE','(global) Account-ID');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_DESC','Account-ID from micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_TITLE','Access-Key');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_DESC','Access-Key from micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_TITLE','Project code');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_DESC','Project code from micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_TITLE','Payment text');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_DESC','Text used for identification.');

define('MODULE_PAYMENT_MCP_SERVICE_THEME_TITLE','Theme');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_DESC','Theme for the payment windows, default is x1');

define('MODULE_PAYMENT_MCP_SERVICE_GFX_TITLE','Logo-Code');
define('MODULE_PAYMENT_MCP_SERVICE_GFX_DESC','Please insert your Logo-Code here');

define('MODULE_PAYMENT_MCP_SERVICE_BGGFX_TITLE','Background image parameter');
define('MODULE_PAYMENT_MCP_SERVICE_BGGFX_DESC','Please insert your Background image parameter here.');

define('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR_TITLE','Background Color');
define('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR_DESC','Please insert your Background color in HEX here.');

define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_TITLE','Security field name');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_DESC','For more security in server-to-server communication, please enter a name only you know.');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE_TITLE','Security field value');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE_DESC','Please enter a private security code which should not be passed on to customers. The micropayment&trade; server will process this code with each notification for improved security.');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_TITLE','Order status: in process');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_DESC','Customor is paying the order');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_TITLE','Order status: paid');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_DESC','Customer has successfully paid.');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_TITLE','Order status: Canceled / Error');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_DESC','If a back posting occurs, this status is set');

define('MODULE_PAYMENT_MCP_SERVICE_ALLOWED_IP_ADDRESSES','193.159.183.234;193.159.183.235;193.159.183.236');

define('MODULE_PAYMENT_MCP_SERVICE_SUCCESS_TRANSACTION','The order has been paid. The Auth-Code is: %s');
define('MODULE_PAYMENT_MCP_SERVICE_IP_NOT_ALLOWED','The IP-Adress is invalid.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_TERMINATED','The request is invalid.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_UNKNOWN_ORDER_ID','This order does not exist');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_SECRET_FIELD_MISSMATCH','Security field wrong!');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_AMOUNT_MISSMATCH','The sum does not correspond with paid amount! Actual: %s  Balance due: %s');
define('MODULE_PAYMENT_MCP_SERVICE_PAYIN_MESSAGE','%s %s has been paid.');

define('MODULE_PAYMENT_MCP_SERVICE_PENDING_PAYMENT','Payment pending. Automatic cancellation %s');
define('MODULE_PAYMENT_MCP_PREPAY_EXPIRED','No receipt of payment, automatic cancellation');

define('MODULE_PAYMENT_MCP_SERVICE_NO_ACCOUNT','%s<div class="mcp_notice_register">In order to ensure functionality of the micropayment&trade; Payment modules, please first register an account and create a project.<a href="%s" target="blank">Click here to register.</a></div>');
define('MODULE_PAYMENT_MCP_SERVICE_CSS','
<style type="text/css">
.mcp_notice_register {
    margin-bottom: 5px;
    background-image: url("../images/micropayment/logo_small.png");
	background-position: 10px 10px;
	background-color: #ffdede;
    background-repeat: no-repeat;
    background-size: 100px;
    height: 40px;
	padding-left:130px;
	padding-top: 18px;
	border: 1px #cdcdcd solid;
}
</style>
');
?>