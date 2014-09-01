<?php
require_once('mcp_prepay.php');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_TITLE','(global) Status');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_DESC','(global) Einschalten der micropayment&trade; Module');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_TITLE','Positionierung');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_DESC','Position in der Liste');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_TITLE','(global) Account-ID');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_DESC','Account-ID von micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_TITLE','Access-Key');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_DESC','Access-Key von micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_TITLE','Projektcode');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_DESC','Projektcode von micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_TITLE','Bezahltext');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_DESC','Text der zur Identifikation genutzt wird.');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_TITLE','Theme');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_DESC','Theme f&uuml;r das Bezahlfenster, Default ist x1');

define('MODULE_PAYMENT_MCP_SERVICE_GFX_TITLE','Logo-Code');
define('MODULE_PAYMENT_MCP_SERVICE_GFX_DESC','Tragen Sie hier Ihren Logo-Code ein.');

define('MODULE_PAYMENT_MCP_SERVICE_BGGFX_TITLE','Hintergrund-Grafik-Code');
define('MODULE_PAYMENT_MCP_SERVICE_BGGFX_DESC','Tragen Sie hier den Hintergrund-Grafik-Code ein.');

define('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR_TITLE','Hintergrundfarbe');
define('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR_DESC','Tragen Sie hier die Hintergrundfarbe ein.');

define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_TITLE','Sicherheitsfeld Name');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_DESC','F&uuml;r mehr Sicherheit in der Server-zu-Server Kommunikation geben Sie hier einen Namen ein den nur Sie kennen.');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE_TITLE','Sicherheitsfeld Wert');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE_DESC','Geben Sie hier einen Wert ein den der micropayment&trade; Server Ihrem Shop mitgeben soll um die Sicherheit zu verbessern.');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_TITLE','Bestellstatus: in bezahlung');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_DESC','Kunde ist am bezahlen der Bestellung');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_TITLE','Bestellstatus: bezahlt');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_DESC','Kunde hat erfolgreich bezahlt.');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_TITLE','Bestellstatus: Abgebrochen / Fehler');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_DESC','Wenn eine R&uuml;ckbuchung erfolgt, wird dieser Status gesetzt');

define('MODULE_PAYMENT_MCP_SERVICE_ALLOWED_IP_ADDRESSES','193.159.183.234;193.159.183.235;193.159.183.236');

define('MODULE_PAYMENT_MCP_SERVICE_SUCCESS_TRANSACTION','Die Bestellung wurde bezahlt. Der Auth-Code ist: %s');
define('MODULE_PAYMENT_MCP_SERVICE_IP_NOT_ALLOWED','Die IP-Adresse ist ung&uuml;ltig.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_TERMINATED','Die Anfrage ist ung&uuml;ltig.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_UNKNOWN_ORDER_ID','Die Bestellung existiert nicht');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_SECRET_FIELD_MISSMATCH','Sicherheitsfeld stimmt nicht &uuml;berein!');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_AMOUNT_MISSMATCH','Die Summe stimmt nicht mit dem Bezahltem Wert &uuml;berein! Ist: %s  Soll: %s');

define('MODULE_PAYMENT_MCP_SERVICE_NO_ACCOUNT','%s<div class="mcp_notice_register">Damit die Bezahlmodule von Micropayment&trade; funktionieren, m&uuml;ssen Sie einen Account bei Micropayment&trade; anlegen und ein Projekt erstellen.<a href="%s" target="blank">Klicken Sie hier um sich zu Registrieren.</a></div>');
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