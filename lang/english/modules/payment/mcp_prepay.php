<?php
require_once('mcp_service.php');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_DESCRIPTION', 'micropayment&trade; Prepay Module');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_TITLE', 'micropayment&trade; Prepay<br /><img src="http://www.micropayment.de/resources/?what=img&group=pp&show=type-h.4" />');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_TITLE_EXTERN', 'Prepay');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_INFO', '
<div style="margin:10px;">
<div style="float:right;"><img src="./images/micropayment/logo_small.png" width="150"/></div><div style="float:left;">
Your advantages:<br />
- Certified Payment Provider<br />
- Secure data transfer (128-Bit SSL)<br />
- No registration required<br /><br />
</div>
<div style="clear:both;"></div>
You are being forwarded to micropayment&trade;. Your order will be processed immediately after the payment process has been successfully completed!
</div>
');

define('MODULE_PAYMENT_MCP_PREPAY_STATUS_TITLE','Prepay');
define('MODULE_PAYMENT_MCP_PREPAY_STATUS_DESC','Prepay-Module by micropayment&trade;');
define('MODULE_PAYMENT_MCP_PREPAY_MINIMUM_AMOUNT_TITLE','Minimum amount');
define('MODULE_PAYMENT_MCP_PREPAY_MINIMUM_AMOUNT_DESC','Minimum amount for this payment method');
define('MODULE_PAYMENT_MCP_PREPAY_MAXIMUM_AMOUNT_TITLE','Maximum amount');
define('MODULE_PAYMENT_MCP_PREPAY_MAXIMUM_AMOUNT_DESC','Maximum amount for this payment method');
define('MODULE_PAYMENT_MCP_PREPAY_SORT_ORDER_TITLE','Positioning');
define('MODULE_PAYMENT_MCP_PREPAY_SORT_ORDER_DESC','Positioning in the payment method selection');
define('MODULE_PAYMENT_MCP_PREPAY_ALLOWED_TITLE','Country selection');
define('MODULE_PAYMENT_MCP_PREPAY_ALLOWED_DESC','Allow orders only from these countries (Comma seperated list DE,EN)');

define('MODULE_PAYMENT_MCP_PREPAY_COMMENT_INIT','Pending Payment. Expires on %s');
define('MODULE_PAYMENT_MCP_PREPAY_COMMENT_PAYIN','Paid in %s %s');
define('MODULE_PAYMENT_MCP_PREPAY_COMMENT_EXPIRED','No deposit');
?>
