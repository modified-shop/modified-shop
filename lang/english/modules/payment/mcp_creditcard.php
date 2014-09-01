<?php
require_once('mcp_service.php');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_DESCRIPTION', 'Creditcard Module <br />Creditcard Test data:<br /><br />CC#: 4111111111111111<br />Valid until: anytime<br />CVC#: 666');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_TITLE', 'micropayment&trade; Creditcard<br /><img src="http://www.micropayment.de/resources/?what=img&group=cc&show=type-h.4" />');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_TITLE_EXTERN', 'Creditcard');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_INFO', '
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
define('MODULE_PAYMENT_MCP_CREDITCARD_STATUS_TITLE','Creditcard');
define('MODULE_PAYMENT_MCP_CREDITCARD_STATUS_DESC','Creditcard module by micropayment&trade;');
define('MODULE_PAYMENT_MCP_CREDITCARD_MINIMUM_AMOUNT_TITLE','Minimum amount');
define('MODULE_PAYMENT_MCP_CREDITCARD_MINIMUM_AMOUNT_DESC','Minimum amount');
define('MODULE_PAYMENT_MCP_CREDITCARD_MAXIMUM_AMOUNT_TITLE','Maximum amount');
define('MODULE_PAYMENT_MCP_CREDITCARD_MAXIMUM_AMOUNT_DESC','Maximum amount');
define('MODULE_PAYMENT_MCP_CREDITCARD_SORT_ORDER_TITLE','Positioning');
define('MODULE_PAYMENT_MCP_CREDITCARD_SORT_ORDER_DESC','Positioning in the payment method selection');
define('MODULE_PAYMENT_MCP_CREDITCARD_ALLOWED_TITLE','Country selection');
define('MODULE_PAYMENT_MCP_CREDITCARD_ALLOWED_DESC','Allow orders only from these countries (Comma seperated list DE,EN)');

?>
