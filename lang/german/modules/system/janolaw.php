<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003   nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2005 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: billiger.php 950 2005-05-14 16:45:21Z mz $)
   (c) 2008 Gambio OHG (billiger.php 2008-11-11 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_JANOLAW_TEXT_TITLE', 'janolaw AGB Hosting-Service');
define('MODULE_JANOLAW_TEXT_DESCRIPTION', '<a href="https://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><img src="images/janolaw/janolaw_185x35.png" border=0></a><br /><br />Deutschlands gro&szlig;es Rechtsportal janolaw bietet ma&szlig;geschneiderte L&ouml;sungen f&uuml;r Ihre Rechtsfragen - von der Anwaltshotline bis zu individuellen Vertr&auml;gen mit Anwaltsgarantie. Mit dem AGB Hosting-Service f&uuml;r Internetshops k&ouml;nnen Sie die rechtlichen Kerndokumente AGB, Widerrufsbelehrung, Impressum und Datenschutzerkl&auml;rung individuell auf Ihren Shop anpassen und laufend durch das janolaw Team aktualisieren lassen. Mehr Schutz geht nicht.<br /><br /><a href="https://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><strong><u>Hier geht&#x27;s zum Angebot<u></strong></a>');
define('MODULE_JANOLAW_USER_ID_TITLE', '<hr noshade>User-ID');
define('MODULE_JANOLAW_USER_ID_DESC', 'Ihre User-ID');
define('MODULE_JANOLAW_SHOP_ID_TITLE', 'Shop-ID');
define('MODULE_JANOLAW_SHOP_ID_DESC', 'Die Shop-ID Ihres Onlineshops');
define('MODULE_JANOLAW_STATUS_DESC', 'Modul aktivieren?');
define('MODULE_JANOLAW_STATUS_TITLE', 'Status');
define('MODULE_JANOLAW_TYPE_TITLE', '<hr noshade>Speichern als');
define('MODULE_JANOLAW_TYPE_DESC', 'Sollen die Daten in einer Datei oder in der Datenbank gespeichert werden?');
define('MODULE_JANOLAW_FORMAT_TITLE', 'Format Typ');
define('MODULE_JANOLAW_FORMAT_DESC', 'Sollen die Daten als Text oder HTML gespeichert werden?');
define('MODULE_JANOLAW_UPDATE_INTERVAL_TITLE', '<hr noshade>Update Intervall');
define('MODULE_JANOLAW_UPDATE_INTERVAL_DESC', 'In welchen Abst&auml;nden sollen die Daten aktualisiert werden?');
define('MODULE_JANOLAW_ERROR', 'Bitte pr&uuml;fen Sie die Zuordnung der Dokumente.');

define('MODULE_JANOLAW_TYPE_DATASECURITY_TITLE', '<hr noshade>Rechtstext Datenschutz');
define('MODULE_JANOLAW_TYPE_DATASECURITY_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_DATASECURITY_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_DATASECURITY_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_DATASECURITY_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_DATASECURITY_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_TERMS_TITLE', '<hr noshade>Rechtstext AGB');
define('MODULE_JANOLAW_TYPE_TERMS_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_TERMS_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_TERMS_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_TERMS_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_TERMS_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_LEGALDETAILS_TITLE', '<hr noshade>Rechtstext Impressum');
define('MODULE_JANOLAW_TYPE_LEGALDETAILS_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_LEGALDETAILS_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_LEGALDETAILS_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_LEGALDETAILS_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_LEGALDETAILS_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_REVOCATION_TITLE', '<hr noshade>Rechtstext Widerruf');
define('MODULE_JANOLAW_TYPE_REVOCATION_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_REVOCATION_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_REVOCATION_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_REVOCATION_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_REVOCATION_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_WITHDRAWAL_TITLE', '<hr noshade>Rechtstext Widerrufsformular');
define('MODULE_JANOLAW_TYPE_WITHDRAWAL_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.<br/><br/><b>Wichtig:</b> das funktioniert erst ab Version 3. Die Umstellung kann bei Janolaw veranlasst werden.');
define('MODULE_JANOLAW_PDF_WITHDRAWAL_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_WITHDRAWAL_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_WITHDRAWAL_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_WITHDRAWAL_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');
define('MODULE_JANOLAW_WITHDRAWAL_COMBINE_TITLE', 'Kombinierte Widerrufsbelehrung/Widerrufsformular');
define('MODULE_JANOLAW_WITHDRAWAL_COMBINE_DESC', 'Soll eine kombinierte Widerrufsbelehrung mit Widerrufsformular erstellt werden?');

?>