<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banktransfer.php,v 1.9 2003/02/18 19:22:15); www.oscommerce.com
   (c) 2003	 nextcommerce (banktransfer.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

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
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_INFO', ((MODULE_PAYMENT_BANKTRANSFER_IBAN_ONLY != 'True') ? 'Bitte beachten Sie, dass das Lastschriftverfahren ohne Angabe von IBAN/BIC <b>nur</b> von einem <b>deutschen Girokonto</b> aus m&ouml;glich ist. Durch Angabe von IBAN/BIC k&ouml;nnen Sie das Lastschriftverfahren <b>EU-weit</b> nutzen.<br/>' : '') . 'Felder mit (*) sind Pflichtangaben. Bei einer deutschen IBAN ist der BIC optional.<br/><br/>');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_OWNER', 'Kontoinhaber:*');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_OWNER_EMAIL', 'E-Mail Kontoinhaber:*');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_NUMBER', 'KtoNr / IBAN:*');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_IBAN', 'IBAN:*');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_BLZ', 'BLZ / BIC:*');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_BIC', 'BIC:*');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_NAME', 'Bank:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_FAX', 'Einzugserm&auml;chtigung wird per Fax best&auml;tigt');

// Note these MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_X texts appear also in the URL, so no html-entities are allowed here
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR', 'FEHLER: ');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_1', 'Kontonummer und Bankleitzahl passen nicht zusammen, bitte korrigieren Sie Ihre Angabe.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_2', 'Diese Kontonummer ist nicht pr&uuml;fbar, bitte kontrollieren Sie zur Sicherheit Ihre Eingabe nochmals.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_3', 'Diese Kontonummer ist nicht pr&uuml;fbar, bitte kontrollieren Sie zur Sicherheit Ihre Eingabe nochmals.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_4', 'Diese Kontonummer ist nicht pr&uuml;fbar, bitte kontrollieren Sie zur Sicherheit Ihre Eingabe nochmals.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_5', 'Diese Bankleitzahl existiert nicht, bitte korrigieren Sie Ihre Angabe.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_8', 'Sie haben keine korrekte Bankleitzahl eingegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_9', 'Sie haben keine korrekte Kontonummer eingegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_10', 'Sie haben keinen Kontoinhaber angegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_11', 'Sie haben keinen korrekten BIC angegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_12', 'Sie haben keine korrekte IBAN eingegeben.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_13', 'Ung&uuml;ltige E-Mail-Adresse f&uuml;r die Benachrichtigung des Kontoinhabers.');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_14', 'Keine Lastschriftfreigabe f&uuml;r dieses SEPA-Land.');

define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE', 'Hinweis:');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE2', 'Wenn Sie aus Sicherheitsbedenken keine Bankdaten &uuml;ber das Internet<br />&uuml;bertragen wollen, k&ouml;nnen Sie sich unser ');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE3', 'Faxformular');
define('MODULE_PAYMENT_BANKTRANSFER_TEXT_NOTE4', ' herunterladen und uns ausgef&uuml;llt zusenden.');

define('JS_BANK_BLZ', '* Bitte geben Sie die BLZ / BIC Ihrer Bank ein!\n\n');
define('JS_BANK_NAME', '* Bitte geben Sie den Namen Ihrer Bank ein!\n\n');
define('JS_BANK_NUMBER', '* Bitte geben Sie Ihre Kontonummer / IBAN ein!\n\n');
define('JS_BANK_OWNER', '* Bitte geben Sie den Namen des Kontoinhabers ein!\n\n');
define('JS_BANK_OWNER_EMAIL', '* Bitte geben Sie die E-Mail-Adresse des Kontoinhabers ein!\n\n');

define('MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ_TITLE', 'Datenbanksuche f&uuml;r die Bankleitzahlen-Pr&uuml;fung verwenden?');
define('MODULE_PAYMENT_BANKTRANSFER_DATABASE_BLZ_DESC', 'M&ouml;chten Sie die Datenbank f&uuml;r die Bankleitzahlen-Plausibilit&auml;tspr&uuml;fung verwenden ("true")?<br/>Vergewissern Sie sich, dass die Bankleitzahlen in der Datenbank auf dem aktuellen Stand sind!<br/><a href="'.xtc_href_link(defined('FILENAME_BLZ_UPDATE')?FILENAME_BLZ_UPDATE:'').'" target="_blank"><strong>Link: --> BLZ UPDATE <-- </strong></a><br/><br/>Bei "false" (standard) wird die mitgelieferte blz.csv Datei verwendet, die m&ouml;glicherweise veraltete Eintr&auml;ge enth&auml;lt!');
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
define('MODULE_PAYMENT_BANKTRANSFER_STATUS_TITLE', 'Banktransfer Zahlungen erlauben');
define('MODULE_PAYMENT_BANKTRANSFER_STATUS_DESC', 'M&ouml;chten Sie Banktransfer Zahlungen erlauben?');
define('MODULE_PAYMENT_BANKTRANSFER_MIN_ORDER_TITLE', 'Notwendige Bestellungen');
define('MODULE_PAYMENT_BANKTRANSFER_MIN_ORDER_DESC', 'Die Mindestanzahl an Bestellungen, die ein Kunde haben muss, damit die Option zur Verf&uuml;gung steht.');
define('MODULE_PAYMENT_BANKTRANSFER_IBAN_ONLY_TITLE', 'IBAN Mode');
define('MODULE_PAYMENT_BANKTRANSFER_IBAN_ONLY_DESC', 'M&ouml;chten Sie nur IBAN Zahlungen erlauben?');

// SEPA
define('MODULE_PAYMENT_BANKTRANSFER_CI_TITLE', 'Gl&auml;ubiger-Identifikationsnummer (CI)');
define('MODULE_PAYMENT_BANKTRANSFER_CI_DESC', 'Geben Sie hier Ihre SEPA-Gl&auml;ubiger-ID ein');
define('MODULE_PAYMENT_BANKTRANSFER_REFERENCE_PREFIX_TITLE', 'Pr&auml;fix f&uuml;r Mandatsreferenz (optional)');
define('MODULE_PAYMENT_BANKTRANSFER_REFERENCE_PREFIX_DESC', 'Geben Sie hier ein Pr&auml;fix f&uuml;r die Mandatsreferenz ein');
define('MODULE_PAYMENT_BANKTRANSFER_DUE_DELAY_TITLE', 'F&auml;lligkeit');
define('MODULE_PAYMENT_BANKTRANSFER_DUE_DELAY_DESC', 'Geben Sie ein, nach welcher Frist (in Tagen) Sie die Lastschrift ausf&uuml;hren');

define('MODULE_PAYMENT_BANKTRANSFER_TEXT_EXTENDED_DESCRIPTION', '<strong><font color="red">ACHTUNG:</font></strong> Bitte aktualisieren Sie noch die Bankleitzahlen unter "Hilfsprogramme" -> <a href="'.xtc_href_link('blz_update.php').'"><strong>"Bankleitzahlen aktualisieren"</strong></a>!');

?>