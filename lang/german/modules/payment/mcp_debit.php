<?php
require_once('mcp_service.php');
define('MODULE_PAYMENT_MCP_DEBIT_TEXT_DESCRIPTION', 'micropayment&trade; Lastschrift Modul');
define('MODULE_PAYMENT_MCP_DEBIT_TEXT_TITLE', 'micropayment&trade; Lastschrift<br /><img src="http://www.micropayment.de/resources/?what=img&group=dbt&show=type-h.4" />');
define('MODULE_PAYMENT_MCP_DEBIT_TEXT_TITLE_EXTERN', 'Lastschrift');
define('MODULE_PAYMENT_MCP_DEBIT_TEXT_INFO', '
<div style="margin:10px;">
<div style="float:right;"><img src="./images/micropayment/logo_small.png" width="150"/></div><div style="float:left;">
Ihre Vorteile:<br />
- T&Uuml;V-gepr&uuml;fter Zahlungsanbieter<br />
- Sichere Daten&uuml;bertragung ( 128-Bit SSL)<br />
- keine Registrierung notwendig<br /><br />
</div>
<div style="clear:both;"></div>
Sie werden zu micropayment&trade; weitergeleitet und Ihre Bestellung wird nach dem erfolgreichen Bezahlvorgang sofort bearbeitet!
</div>
');

define('MODULE_PAYMENT_MCP_DEBIT_STATUS_TITLE','Lastschrift');
define('MODULE_PAYMENT_MCP_DEBIT_STATUS_DESC','Lastschriftmodul von micropayment&trade;');
define('MODULE_PAYMENT_MCP_DEBIT_MINIMUM_AMOUNT_TITLE','Minimum Warenkorbwert');
define('MODULE_PAYMENT_MCP_DEBIT_MINIMUM_AMOUNT_DESC','Mindestwert des Warenkorbs für diese Bezahlmethode');
define('MODULE_PAYMENT_MCP_DEBIT_MAXIMUM_AMOUNT_TITLE','Maximum Warenkorbwert');
define('MODULE_PAYMENT_MCP_DEBIT_MAXIMUM_AMOUNT_DESC','Maximalwert des Warenkorbs für diese Bezahlmethode');
define('MODULE_PAYMENT_MCP_DEBIT_SORT_ORDER_TITLE','Positionierung');
define('MODULE_PAYMENT_MCP_DEBIT_SORT_ORDER_DESC','Positionierung in der Bezahlmethodenauswahl');
define('MODULE_PAYMENT_MCP_DEBIT_ALLOWED_TITLE','L&auml;nderauswahl');
define('MODULE_PAYMENT_MCP_DEBIT_ALLOWED_DESC','Bestellungen nur aus den L&auml;ndern erlauben (Komma separierte Liste z.b. DE,EN)');

?>
