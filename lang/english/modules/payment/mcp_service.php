<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
define('MODULE_PAYMENT_MCP_SERVICE_TEXT_TITLE','micropayment - Gateway');
define('MODULE_PAYMENT_MCP_SERVICE_TEXT_DESCRIPTION','Bla Blub ding dong');
define('MODULE_PAYMENT_MCP_SERVICE_TEXT_INFO','INFOTEXT von MCP_SERVICE');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_TITLE','Status');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_DESC','Enable the micropayment&trade; module');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_TITLE','Positioning');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_DESC','Position in the list');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_TITLE','<div style="color:#850000;font-style: italic;">The following configuration settings are used globally for all micropayment&trade; payment modules and only need to be configured once</div><br />Account-ID');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_DESC','Account-ID from micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_TITLE','Access-Key');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_DESC','Access-Key from micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_TITLE','Project code');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_DESC','Project code from micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_TITLE','Payment text');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_DESC','This option is shown on the invoice and page title of the payment window. With the place holder #ORDER# it is possible to automatically integrate the OrderID in to the invoice or payment window. e.g. "Order: #ORDER#" would show "Order: 0000023"');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_TITLE','Theme');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_DESC','Theme for the payment windows, default is x1');
define('MODULE_PAYMENT_MCP_SERVICE_TESTMODE_TITLE',' Test Mode');
define('MODULE_PAYMENT_MCP_SERVICE_TESTMODE_DESC','Enable Test Mode');

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

define('MODULE_PAYMENT_MCP_SERVICE_SUCCESS_TRANSACTION','The order has been paid. The Auth-Code is: %s');
define('MODULE_PAYMENT_MCP_SERVICE_IP_NOT_ALLOWED','The IP-Adress is invalid.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_TERMINATED','The request is invalid.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_UNKNOWN_ORDER_ID','This order does not exist');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_SECRET_FIELD_MISSMATCH','Security field wrong!');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_AMOUNT_MISSMATCH','The sum does not correspond with paid amount! Actual: %s  Balance due: %s');
define('MODULE_PAYMENT_MCP_SERVICE_PAYIN_MESSAGE','%s %s has been paid.');
define('MODULE_PAYMENT_MCP_SERVICE_UNKNOWN_FUNCTION','unknown function');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_INVALID_AUTH_CODE','invalid auth code');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_INVALID_AMOUNT_VALUE','invalid amount');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_TITLE','Order status: in process');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_DESC','Customer is paying the order');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_TITLE','Order status: paid');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_DESC','Customer has successfully paid.');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_TITLE','Order status: Canceled / Error');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_DESC','If a back posting occurs, this status is set');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_REFUNDED_ID_TITLE','Order status: Refunded');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_REFUNDED_ID_DESC','If a refund raised, this status is set.');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID_TITLE','Order status: Payment review');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID_DESC','This status is set if a problem has occurred and the payment needs to be reviewed');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID_TITLE','Order status: conflict');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID_DESC','logical payment flow has been interrupted. Please review this order.');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID_TITLE',' Order status: Prepay - partial payment');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID_DESC','this order has a partial payin yet has not been fully paid');

define('MODULE_PAYMENT_MCP_SERVICE_REFUND_COMMENT','Refund is raised.');
define('MODULE_PAYMENT_MCP_SERVICE_PENDING_PAYMENT','Payment pending. Automatic cancellation %s');
define('MODULE_PAYMENT_MCP_PREPAY_EXPIRED','No receipt of payment, automatic cancellation');

define('MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS_TITLE','Deletion of unpaid orders');
define('MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS_DESC','How many days old can an order be with the status "pending payment" before being deleted by the "clear old orders" button. Important: Prepayment orders will not be deleted.');

define('MODULE_PAYMENT_MCP_SERVICE_NEW_VERSION','%s<div class="mcp_notice_register">New version of micropayment&trade; Payment modules are avaiable. <a href="http://ecommerce.micropayment.de/modifiedshop/?lang=EN" target="_new">Click here for Download.</a></div>');

define('MODULE_PAYMENT_MCP_SERVICE_NO_ACCOUNT','%s<div class="mcp_notice_register">In order to ensure functionality of the micropayment&trade; Payment modules, please first register an account and create a project. <a href="https://%s.micropayment.de" target="blank">Click here to register.</a></div>');
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
	padding-top: 24px;
	border: 1px #cdcdcd solid;
	font-family: verdana, tahoma, sans-serif;
	font-size: 12px;
}
.mcp_notice_register a {
  font-family: verdana, tahoma, sans-serif;
  font-size: 12px;
  font-weight: bold;
  color: #8d005d;
}
</style>
');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_GERMAN_TITLE','pending payment');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_GERMAN_TITLE','prepay, partpay');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_GERMAN_TITLE','processing');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_GERMAN_TITLE','cancelled');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_GERMAN_TITLE','payment review');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_GERMAN_TITLE','event-conflict!');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ENGLISH_TITLE','pending payment');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ENGLISH_TITLE','prepay, partpay');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ENGLISH_TITLE','processing');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ENGLISH_TITLE','cancelled');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ENGLISH_TITLE','payment review');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ENGLISH_TITLE','event-conflict!');
