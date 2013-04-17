<?php
require_once('mcp_service.php');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_DESCRIPTION', 'micropayment&trade; Kreditkarten Modul <br />Kreditkarte Testinfo:<br /><br />CC#: 4111111111111111<br />G&uuml;ltig bis: Jederzeit');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_TITLE', 'micropayment&trade; Kreditkarte<br /><img src="http://www.micropayment.de/resources/?what=img&group=cc&show=type-h.4" />');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_TITLE_EXTERN', 'Kreditkarte');
define('MODULE_PAYMENT_MCP_CREDITCARD_TEXT_INFO', '
<div style="margin:10px;">
<div style="float:right;"><img src="./images/micropayment/logo_small.png" width="150"/></div><div style="float:left;">
Ihre Vorteile:<br />
- T&Uuml;V-gepr&uuml;fter Zahlungsanbieter<br />
- Sichere Daten&uuml;bertragung (128-Bit SSL)<br />
- keine Registrierung notwendig<br /><br />
</div>
<div style="clear:both;"></div>
Sie werden zu micropayment&trade; weitergeleitet und Ihre Bestellung wird nach dem erfolgreichen Bezahlvorgang sofort bearbeitet!
</div>
');
define('MODULE_PAYMENT_MCP_CREDITCARD_STATUS_TITLE','Kreditkarte');
define('MODULE_PAYMENT_MCP_CREDITCARD_STATUS_DESC','Kreditkartenmodul von micropayment&trade;');
define('MODULE_PAYMENT_MCP_CREDITCARD_MINIMUM_AMOUNT_TITLE','Mindestbestellwert');
define('MODULE_PAYMENT_MCP_CREDITCARD_MINIMUM_AMOUNT_DESC','Mindestbestellwert');
define('MODULE_PAYMENT_MCP_CREDITCARD_MAXIMUM_AMOUNT_TITLE','Maximalbestellwert');
define('MODULE_PAYMENT_MCP_CREDITCARD_MAXIMUM_AMOUNT_DESC','Maximalbestellwert');
define('MODULE_PAYMENT_MCP_CREDITCARD_SORT_ORDER_TITLE','Positionierung');
define('MODULE_PAYMENT_MCP_CREDITCARD_SORT_ORDER_DESC','Position in der Liste der Bezahlarten');
define('MODULE_PAYMENT_MCP_CREDITCARD_ALLOWED_TITLE','L&auml;nderauswahl');
define('MODULE_PAYMENT_MCP_CREDITCARD_ALLOWED_DESC','Bestellungen nur aus den L&auml;ndern erlauben (Komma separierte Liste z.b. DE,EN)');

?>
