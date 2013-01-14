<?php

/* -----------------------------------------------------------------------------------------
   $Id: banktransfer.php 998 2005-07-07 14:18:20Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banktransfer.php,v 1.9 2003/02/18 19:22:15); www.oscommerce.com
   (c) 2003	 nextcommerce (banktransfer.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   OSC German Banktransfer v0.85a       	Autor:	Dominik Guder <osc@guder.org>

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_TYPE_PERMISSION', 'bt');

define('MODULE_PAYMENT_BANKTRANSFER_TEXT_TITLE', 'Lastschriftverfahren');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_DESCRIPTION', 'Lastschriftverfahren');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_INFO', '');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK', 'Bankeinzug');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_EMAIL_FOOTER', 'Hinweis: Sie k&ouml;nnen sich unser Faxformular unter ' . HTTP_SERVER . DIR_WS_CATALOG . MODULE_PAYMENT_BANKTRANSFER_URL_NOTE . ' herunterladen und es ausgef&uuml;llt an uns zur&uuml;cksenden.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_INFO', 'Bitte beachten Sie, dass das Lastschriftverfahren <b>nur</b> von einem <b>deutschen Girokonto</b> aus m&ouml;glich ist');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_OWNER', 'Kontoinhaber:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_NUMBER', 'Kontonummer:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_BLZ', 'BLZ:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_NAME', 'Bank:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_FAX', 'Einzugserm&auml;chtigung wird per Fax best&auml;tigt');

define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR', 'FEHLER: ');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_1', 'Kontonummer und Bankleitzahl stimmen nicht &uuml;berein, bitte korrigieren Sie Ihre Angabe.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_2', 'Diese Kontonummer ist nicht pr&uuml;fbar, bitte kontrollieren zur Sicherheit Sie Ihre Eingabe nochmals.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_3', 'Diese Kontonummer ist nicht pr&uuml;fbar, bitte kontrollieren zur Sicherheit Sie Ihre Eingabe nochmals.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_4', 'Diese Kontonummer ist nicht pr&uuml;fbar, bitte kontrollieren zur Sicherheit Sie Ihre Eingabe nochmals.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_5', 'Diese Bankleitzahl existiert nicht, bitte korrigieren Sie Ihre Angabe.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_8', 'Sie haben keine korrekte Bankleitzahl eingegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_9', 'Sie haben keine korrekte Kontonummer eingegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_10', 'Sie haben keinen Kontoinhaber angegeben.');

define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE', 'Hinweis:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE2', 'Wenn Sie aus Sicherheitsbedenken keine Bankdaten &uuml;ber das Internet<br />&uuml;bertragen wollen, k&ouml;nnen Sie sich unser ');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE3', 'Faxformular');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE4', ' herunterladen und uns ausgef&uuml;llt zusenden.');

define('JS_BANK_BLZ', '* Bitte geben Sie die BLZ Ihrer Bank ein!\n\n');
define('JS_BANK_NAME', '* Bitte geben Sie den Namen Ihrer Bank ein!\n\n');
define('JS_BANK_NUMBER', '* Bitte geben Sie Ihre Kontonummer ein!\n\n');
define('JS_BANK_OWNER', '* Bitte geben Sie den Namen des Kontobesitzers ein!\n\n');

define('MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ_TITLE', 'Datenbanksuche f&uuml;r die BLZ verwenden?');
define('MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ_DESC', 'M&ouml;chten Sie die Datenbanksuche f&uuml;r die BLZ verwenden? Vergewissern Sie sich, dass die Datenbanktabelle <strong>banktransfer_blz</strong> vorhanden und auch Bankdaten enth&auml;lt (standardm&auml;&szlig;ig nicht der Fall)! Bei false (standard) wird die mitgelieferte blz.csv Datei verwendet!');
define('MODULE_PAYMENT_BANKTRANSFER_URL_NOTE_TITLE', 'Fax-URL');
define('MODULE_PAYMENT_BANKTRANSFER_URL_NOTE_DESC', 'Die Fax-Best&auml;tigungsdatei. Diese muss im Catalog-Verzeichnis liegen');
define('MODULE_PAYMENT_BANKTRANSFER_FAX_CONFIRMATION_TITLE', 'Fax Best&auml;tigung erlauben');
define('MODULE_PAYMENT_BANKTRANSFER_FAX_CONFIRMATION_DESC', 'M&ouml;chten Sie die Fax Best&auml;tigung erlauben?');
define('MODULE_PAYMENT_BANKTRANSFER_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BANKTRANSFER_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_BANKTRANSFER_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BANKTRANSFER_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_BANKTRANSFER_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_BANKTRANSFER_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_BANKTRANSFER_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_BANKTRANSFER_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_BANKTRANSFER_STATUS_TITLE', 'Banktranfer Zahlungen erlauben');
define('MODULE_PAYMENT_BANKTRANSFER_STATUS_DESC', 'M&ouml;chten Banktranfer Zahlungen erlauben?');
define('MODULE_PAYMENT_BANKTRANSFER_MIN_ORDER_TITLE', 'Notwendige Bestellungen');
define('MODULE_PAYMENT_BANKTRANSFER_MIN_ORDER_DESC', 'Die Mindestanzahl an Bestellungen die ein Kunden haben muss damit die Option zur Verf&uuml;gung steht.');
?>
