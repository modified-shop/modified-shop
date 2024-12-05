<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
include_once('mcp_service.php');
define('MODULE_PAYMENT_MCP_GIROPAY_TEXT_DESCRIPTION', 'micropayment&trade; giropay Module
<br /><br />
Links<br />
<b>Tools</b><br />
<a target="_new" href="../callback/micropayment/cleanup.php">
  <input class="button" type="button" value="clear old orders">
</a><br />
<br />
<b>Extern</b><br />
<a href="https://www.micropayment.de/products/plugins/modified/?_r=gst&_src=ctor">
  <input class="button" type="button" value="Manual">
</a>&nbsp;
<a target="_new" href="https://r120.micropayment.de">
  <input class="button" type="button" value="Micropayment register">
</a>');
define('MODULE_PAYMENT_MCP_GIROPAY_TEXT_TITLE', 'micropayment&trade; Giropay');
define('MODULE_PAYMENT_MCP_GIROPAY_TEXT_TITLE_EXTERN', 'Giropay');
define('MODULE_PAYMENT_MCP_GIROPAY_TEXT_INFO', '
<div style="margin:10px; height:140px;">
  <div style="float:right;"><img src="./images/micropayment/logo_small.png" width="150"/></div>
  <div style="float:left;">
    <b>Please have your giropay details at hand.</b><br />
    To conclude your order, you will now be forwarded to our payment service provider, micropayment&trade;.<br /><br />
    &#10004; secure &nbsp; &#10004; simple &nbsp; &#10004; no registration needed
  </div>
</div>');
define('MODULE_PAYMENT_MCP_GIROPAY_STATUS_TITLE','Giropay');
define('MODULE_PAYMENT_MCP_GIROPAY_STATUS_DESC','The following configuration settings are used globally for all micropayment&trade; payment modules and only need to be configured once<br />Giropay module by micropayment&trade;');
define('MODULE_PAYMENT_MCP_GIROPAY_MINIMUM_AMOUNT_TITLE','Minimum amount');
define('MODULE_PAYMENT_MCP_GIROPAY_MINIMUM_AMOUNT_DESC','Minimum amount');
define('MODULE_PAYMENT_MCP_GIROPAY_MAXIMUM_AMOUNT_TITLE','Maximum amount');
define('MODULE_PAYMENT_MCP_GIROPAY_MAXIMUM_AMOUNT_DESC','Maximum amount');
define('MODULE_PAYMENT_MCP_GIROPAY_SORT_ORDER_TITLE','Positioning');
define('MODULE_PAYMENT_MCP_GIROPAY_SORT_ORDER_DESC','Positioning in the payment method selection');
define('MODULE_PAYMENT_MCP_GIROPAY_ALLOWED_TITLE','Country selection');
define('MODULE_PAYMENT_MCP_GIROPAY_ALLOWED_DESC','Allow orders only from these countries (Comma seperated list DE,EN)');
