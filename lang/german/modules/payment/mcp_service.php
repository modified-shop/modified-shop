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
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_TITLE',' Status');
define('MODULE_PAYMENT_MCP_SERVICE_STATUS_DESC','Einschalten der micropayment&trade; Module');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_TITLE',' Positionierung');
define('MODULE_PAYMENT_MCP_SERVICE_SORT_ORDER_DESC','Position in der Liste');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_TITLE','<div style="color:#850000;font-style: italic;">Die folgenden Einstellungen sind f&uuml;r alle Bezahlmodule von micropayment&trade; g&uuml;ltig und m&uuml;ssen nur einmalig eingetragen werden.</div><br /> Account-ID');
define('MODULE_PAYMENT_MCP_SERVICE_ACCOUNT_ID_DESC','Account-ID von micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_TITLE',' Access-Key');
define('MODULE_PAYMENT_MCP_SERVICE_ACCESS_KEY_DESC','Access-Key von micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_TITLE',' Projektcode');
define('MODULE_PAYMENT_MCP_SERVICE_PROJECT_CODE_DESC','Projektcode von micropayment&trade;');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_TITLE',' Bezahltext');
define('MODULE_PAYMENT_MCP_SERVICE_PAYTEXT_DESC','Dies wird auf der Rechnung bzw. als Titel im Bezahlfenster angezeigt. Mit dem Platzhalter #ORDER# k&ouml;nnen Sie die OrderID der Bestellung mit einf&uuml;gen. z.B. "Bestellung #ORDER#" w&uuml;rde "Bestellung 0000023" anzeigen.');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_TITLE',' Theme');
define('MODULE_PAYMENT_MCP_SERVICE_THEME_DESC','Theme f&uuml;r das Bezahlfenster, Standard ist x1');
define('MODULE_PAYMENT_MCP_SERVICE_TESTMODE_TITLE',' Test Mode');
define('MODULE_PAYMENT_MCP_SERVICE_TESTMODE_DESC','Einschalten des Test Modus');

define('MODULE_PAYMENT_MCP_SERVICE_GFX_TITLE',' Logo-Code');
define('MODULE_PAYMENT_MCP_SERVICE_GFX_DESC','Tragen Sie hier Ihren Logo-Code ein.');

define('MODULE_PAYMENT_MCP_SERVICE_BGGFX_TITLE',' Hintergrund-Grafik-Code');
define('MODULE_PAYMENT_MCP_SERVICE_BGGFX_DESC','Tragen Sie hier den Hintergrund-Grafik-Code ein.');

define('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR_TITLE',' Hintergrundfarbe');
define('MODULE_PAYMENT_MCP_SERVICE_BGCOLOR_DESC','Tragen Sie hier die Hintergrundfarbe ein.');

define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_TITLE',' Sicherheitsfeld Name');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_DESC','F&uuml;r mehr Sicherheit in der Server-zu-Server Kommunikation geben Sie hier einen Namen ein den nur Sie kennen.');

define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE_TITLE',' Sicherheitsfeld Wert');
define('MODULE_PAYMENT_MCP_SERVICE_SECRET_FIELD_VALUE_DESC','Geben Sie hier einen Wert ein den der micropayment&trade; Server Ihrem Shop mitgeben soll um die Sicherheit zu verbessern.');

define('MODULE_PAYMENT_MCP_SERVICE_SUCCESS_TRANSACTION','Die Bestellung wurde bezahlt. Der Auth-Code ist: %s');
define('MODULE_PAYMENT_MCP_SERVICE_IP_NOT_ALLOWED','The IP-Adress is invalid.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_TERMINATED','Die Anfrage ist ung&uuml;ltig.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_UNKNOWN_ORDER_ID','Die Bestellung existiert nicht');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_SECRET_FIELD_MISSMATCH','Sicherheitsfeld stimmt nicht &uuml;berein!');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_AMOUNT_MISSMATCH','Die Summe stimmt nicht mit dem Bezahltem Wert &uuml;berein! Ist: %s  Soll: %s');
define('MODULE_PAYMENT_MCP_SERVICE_PAYIN_MESSAGE','%s %s has been paid.');
define('MODULE_PAYMENT_MCP_SERVICE_UNKNOWN_FUNCTION','Funktion ist nicht bekannt.');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_INVALID_AUTH_CODE','Auth code ist fehlerhaft');
define('MODULE_PAYMENT_MCP_SERVICE_ERROR_INVALID_AMOUNT_VALUE','Der Amount ist fehlerhaft');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_TITLE',' Bestellstatus: in Bezahlung');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PENDING_PAYMENT_ID_DESC','Kunde ist am bezahlen der Bestellung');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_TITLE',' Bestellstatus: bezahlt');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PROCESSING_ID_DESC','Kunde hat erfolgreich bezahlt.');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_TITLE',' Bestellstatus: Abgebrochen');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CANCELLED_ID_DESC','Wenn eine Bestellung storniert wird, wird dieser Status gesetzt');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_REFUNDED_ID_TITLE',' Bestellstatus: Refunded');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_REFUNDED_ID_DESC','Wenn ein Refund ausgel&ouml;st wird, wird dieser Status gesetzt');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID_TITLE',' Bestellstatus: Bezahlung pr&uuml;fen');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PAYMENT_REVIEW_ID_DESC','Bei problemen, wird dieser Status gesetzt, damit Sie dies pr&uuml;fen k&ouml;nnen.');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID_TITLE',' Bestellstatus: Konflikt');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_CONFLICT_ID_DESC','Sollte es zu einem abweichendem Event-Workflow kommen, wird dieser Status gesetzt.');

define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID_TITLE',' Bestellstatus: Vorkasse - Teilzahlung');
define('MODULE_PAYMENT_MCP_SERVICE_ORDER_STATUS_PARTPAY_ID_DESC','Wenn ein Zahlungseingang bei Vorkasse gemeldet wird, die Bestellung aber nicht voll bezahlt ist, wird dieser Status gesetzt.');

define('MODULE_PAYMENT_MCP_SERVICE_REFUND_COMMENT','Ein Refund wurde ausgel&ouml;st.');
define('MODULE_PAYMENT_MCP_SERVICE_PENDING_PAYMENT','Payment pending. Automatic cancellation %s');
define('MODULE_PAYMENT_MCP_PREPAY_EXPIRED','No receipt of payment, automatic cancellation');

define('MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS_TITLE','L&ouml;schen von nicht bezahlten Bestellungen');
define('MODULE_PAYMENT_MCP_SERVICE_EXPIRE_DAYS_DESC','Wie lang kann eine Bestellung sich noch im Status "Bezahlung steht aus" befinden, bevor Sie vom "Bestellung aufr&auml;umen" Button entfernt wird. Bitte legen Sie die Anzahl von Tagen fest. WICHTIG: Hierbei werden keine Vorkassen Bestellung entfernt.');

define('MODULE_PAYMENT_MCP_SERVICE_NEW_VERSION','%s<div class="mcp_notice_register">Es steht eine neue micropayment&trade; Modul-Version mit neuen Funktionen und Features f&uuml;r Ihr Shopsystem zur Verf&uuml;gung. <a href="http://ecommerce.micropayment.de/modifiedshop/" target="_new">Zur neuen Modulversion.</a></div>');

define('MODULE_PAYMENT_MCP_SERVICE_NO_ACCOUNT','%s<div class="mcp_notice_register">Damit die Bezahlmodule von Micropayment&trade; funktionieren, m&uuml;ssen Sie einen Account bei Micropayment&trade; anlegen und ein Projekt erstellen. <a href="https://%s.micropayment.de" target="blank">Klicken Sie hier um sich zu Registrieren.</a></div>');
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
