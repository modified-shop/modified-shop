<?php

/* -----------------------------------------------------------------------------------------
   $Id: amoneybookers.php 85 2007-01-14 15:19:44Z mzanier $

   xt:Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2006 xt:Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneybookers.php,v 1.01 2003/01/20); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   Moneybookers v1.0                       Autor:    Gabor Mate  <gabor(at)jamaga.hu>

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_TITLE', 'Sicher bezahlen &uuml;ber Skrill');
//define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_DESCRIPTION', 'Skrill<br /><br /><img src="images/icon_arrow_right.gif"> <b><a href="http://www.xt-commerce.com/index.php?option=com_content&task=view&id=76&lang=de" target="_blank">Hilfe zu Einstellungen</a></b>');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_DESCRIPTION', 'Skrill');
define('MODULE_PAYMENT_AMONEYBOOKERS_NOCURRENCY_ERROR', 'Es ist keine von Skrill akzeptierte W&auml;hrung installiert!');
define('MODULE_PAYMENT_AMONEYBOOKERS_ERRORTEXT1', 'payment_error=');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO', '');
define('MODULE_PAYMENT_AMONEYBOOKERS_ERRORTEXT2', '&error=Fehler w&auml;hrend Ihrer Bezahlung bei Skrill!');
define('MODULE_PAYMENT_AMONEYBOOKERS_ORDER_TEXT', 'Bestelldatum: ');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_ERROR', 'Fehler bei Zahlung!');
define('MODULE_PAYMENT_AMONEYBOOKERS_CONFIRMATION_TEXT', 'Danke f&uuml;r Ihre Bestellung!');
define('MODULE_PAYMENT_AMONEYBOOKERS_TRANSACTION_FAILED_TEXT', 'Ihre Zahlungstransaktion bei skrill.com ist fehlgeschlagen. Bitte versuchen Sie es nochmal, oder w&auml;hlen Sie eine andere Zahlungsm&ouml;glichkeit!');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_2', '<b>100%-ige Sicherheit</b> - Ihre Daten werden nach h&ouml;chstem Sicherheitsstandard verschl&uuml;sselt.');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_3', '<b>100%-iger Datenschutz</b> - Ihre pers&ouml;nlichen Daten verbleiben bei Skrill als lizenziertem Finanzinstitut und werden nicht an den Shop weitergegeben.');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_4', '<b>maximaler Komfort</b> - Nachdem Sie einmalig bei Skrill registriert sind, reichen Ihre E-Mail-Adresse und Passwort f&uuml;r alle k&uuml;nftigen Zahlungen.');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_5', '<b>hohe Akzeptanz</b> - Mit Skrill k&ouml;nnen Sie in mehreren tausend Shops einkaufen.');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_1', '<br /><br />Direkt und bequem zahlen mit...');

define('MB_TEXT_MBDATE', 'Letzte Aktualisierung:');
define('MB_TEXT_MBTID', 'TR ID:');
define('MB_TEXT_MBERRTXT', 'Status:');

define('MODULE_PAYMENT_AMONEYBOOKERS_PROCESSED_STATUS_ID_TITLE', 'Bestellstatus - Processed');
define('MODULE_PAYMENT_AMONEYBOOKERS_PROCESSED_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_PENDING_STATUS_ID_TITLE', 'Bestellstatus - Scheduled');
define('MODULE_PAYMENT_AMONEYBOOKERS_PENDING_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_CANCELED_STATUS_ID_TITLE', 'Bestellstatus - Canceled');
define('MODULE_PAYMENT_AMONEYBOOKERS_CANCELED_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID_TITLE', 'Bestellstatus - Temp');
define('MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_ICONS_TITLE', 'Icons');
define('MODULE_PAYMENT_AMONEYBOOKERS_ICONS_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_STATUS_TITLE', 'Skrill aktivieren');
//define('MODULE_PAYMENT_AMONEYBOOKERS_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Skrill akzeptieren?<br /><br /><img src="images/icon_arrow_right.gif"> <b><a href="http://www.xt-commerce.com/index.php?option=com_content&task=view&id=76&lang=de" target="_blank">Hilfe zu Einstellungen</a></b>');
define('MODULE_PAYMENT_AMONEYBOOKERS_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Skrill akzeptieren?');
define('MODULE_PAYMENT_AMONEYBOOKERS_EMAILID_TITLE', 'Skrill E-Mail-Adresse');
define('MODULE_PAYMENT_AMONEYBOOKERS_EMAILID_DESC', 'E-Mail-Adresse, die bei Skrill registriert ist. <br /><font color="#ff0000">* Erforderlich</font>');
define('MODULE_PAYMENT_AMONEYBOOKERS_PWD_TITLE', 'Skrill Geheimwort');
define('MODULE_PAYMENT_AMONEYBOOKERS_PWD_DESC', 'Geben Sie Ihr Skrill Geheimwort ein (dies ist nicht Ihr Passwort!)');
define('MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID_TITLE', 'H&auml;ndler ID ');
define('MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID_DESC', 'Ihre pers&ouml;nliche H&auml;ndler ID bei Skrill <br /><font color="#ff0000">* Erforderlich</font>');
define('MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY_TITLE', 'Transaktionsw&auml;hrung');
define('MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY_DESC', 'Die W&auml;hrung, in der der Zahlungsvorgang abgewickelt wird. Wenn Ihre gew&auml;hlte W&auml;hrung nicht bei Skrill verf&uuml;gbar ist, wird diese W&auml;hrung ausgew&auml;hlt.');
define('MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE_TITLE', 'Transaktionssprache');
define('MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE_DESC', 'Die Sprache, in der der Zahlungsvorgang abgewickelt wird. Wenn Ihre gew&auml;hlte Shopsprache nicht bei Skrill verf&uuml;gbar ist, wird diese Sprache ausgew&auml;hlt.');
define('MODULE_PAYMENT_AMONEYBOOKERS_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_AMONEYBOOKERS_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_AMONEYBOOKERS_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_AMONEYBOOKERS_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
?>