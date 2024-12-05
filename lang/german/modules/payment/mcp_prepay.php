<?php
/**
 *
 * @package    micropayment
 * @copyright  Copyright (c) 2022 Micropayment GmbH (http://www.micropayment.de)
 * @author     micropayment GmbH (TE) <support@micropayment.de>
 */
include_once('mcp_service.php');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_DESCRIPTION', 'Micropayment Vorkasse Modul
<br /><br />
Links<br />
<b>Tools</b><br />
<a target="_new" href="../callback/micropayment/cleanup.php">
  <input class="button" type="button" value="Bestellungen aufr&auml;umen">
</a><br />
<br />
<b>Extern</b><br />
<a href="https://www.micropayment.de/products/plugins/modified/?_r=gst&_src=ctor">
  <input class="button" type="button" value="Handbuch">
</a>&nbsp;
<a target="_new" href="https://r120.micropayment.de">
  <input class="button" type="button" value="Micropayment Registrierung">
</a>');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_TITLE', 'micropayment&trade; Vorkasse');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_TITLE_EXTERN', 'Vorkasse');
define('MODULE_PAYMENT_MCP_PREPAY_TEXT_INFO', '
<div style="margin:10px; height:140px;">
  <div style="float:right;"><img src="./images/micropayment/logo_small.png" width="150"/></div>
  <div style="float:left;font-family: Arial, Helvetica, sans-serif;">
    <b>Der vorausgef&uuml;llte Zahlschein wird Ihnen per Email zugesandt.</b><br />
    Um Ihre Bestellung abzuschlie&szlig;en, leiten wir Sie nun auf die Webseite<br /> unseres Zahlungsdienstleisters micropayment&trade; weiter.<br /><br />
    &#10004; sicher &nbsp; &#10004; einfach &nbsp; &#10004; registrierungsfrei
  </div>
</div>');
define('MODULE_PAYMENT_MCP_PREPAY_STATUS_TITLE','Vorkasse');
define('MODULE_PAYMENT_MCP_PREPAY_STATUS_DESC','Vorkasse-Modul von Micropayment');
define('MODULE_PAYMENT_MCP_PREPAY_MINIMUM_AMOUNT_TITLE','Minimum Warenkorbwert');
define('MODULE_PAYMENT_MCP_PREPAY_MINIMUM_AMOUNT_DESC','Mindestwert des Warenkorbs f&uuml;r diese Bezahlmethode');
define('MODULE_PAYMENT_MCP_PREPAY_MAXIMUM_AMOUNT_TITLE','Maximum Warenkorbwert');
define('MODULE_PAYMENT_MCP_PREPAY_MAXIMUM_AMOUNT_DESC','Maximalwert des Warenkorbs f&uuml;r diese Bezahlmethode');
define('MODULE_PAYMENT_MCP_PREPAY_SORT_ORDER_TITLE','Positionierung');
define('MODULE_PAYMENT_MCP_PREPAY_SORT_ORDER_DESC','Positionierung in der Bezahlmethodenauswahl');
define('MODULE_PAYMENT_MCP_PREPAY_ALLOWED_TITLE','L&auml;nderauswahl');
define('MODULE_PAYMENT_MCP_PREPAY_ALLOWED_DESC','Bestellungen nur aus den L&auml;ndern erlauben (Komma separierte Liste z.b. DE,EN)');

define('MODULE_PAYMENT_MCP_PREPAY_COMMENT_INIT','init:Warte auf Zahlungseingang. Automatische Stornierung am %s');
define('MODULE_PAYMENT_MCP_PREPAY_COMMENT_PAYIN','Es wurden %s %s angezahlt.');
define('MODULE_PAYMENT_MCP_PREPAY_COMMENT_EXPIRED','Kein Zahlungseingang, automatische Stornierung');

define('MODULE_PAYMENT_MCP_SERVICE_TRANSACTION_CANCELLED','storno:Die Bestellung wurde storniert.');