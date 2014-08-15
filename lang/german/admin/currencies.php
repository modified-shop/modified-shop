<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.15 2003/05/02); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.4 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (currencies.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'W&auml;hrungen');

define('TABLE_HEADING_CURRENCY_NAME', 'Name der W&auml;hrung');
define('TABLE_HEADING_CURRENCY_CODES', 'W&auml;hrungs&uuml;rzel');
define('TABLE_HEADING_CURRENCY_VALUE', 'Wechselkurs');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_EDIT_INTRO', 'Bitte f&uuml;hren Sie alle notwendigen &Auml;nderungen durch');
define('TEXT_INFO_CURRENCY_TITLE', 'Name der W&auml;hrung:');
define('TEXT_INFO_CURRENCY_CODE', 'W&auml;hrungscode nach ISO 4217:');
define('TEXT_INFO_CURRENCY_SYMBOL_LEFT', 'W&auml;hrungsymbol links:');
define('TEXT_INFO_CURRENCY_SYMBOL_RIGHT', 'W&auml;hrungsymbol rechts:');
define('TEXT_INFO_CURRENCY_DECIMAL_POINT', 'Symbol f&uuml;r Dezimalkomma:');
define('TEXT_INFO_CURRENCY_THOUSANDS_POINT', 'Symbol f&uuml;r Tausenderpunkt:');
define('TEXT_INFO_CURRENCY_DECIMAL_PLACES', 'Dezimalstellen:');
define('TEXT_INFO_CURRENCY_LAST_UPDATED', 'letzte &Auml;nderung:');
define('TEXT_INFO_CURRENCY_VALUE', 'Wechselkurs (bei Basisw&auml;hrung -> 1.0000):');
define('TEXT_INFO_CURRENCY_EXAMPLE', 'Umrechnungsbeispiel:');
define('TEXT_INFO_INSERT_INTRO', 'Bitte geben Sie die neue W&auml;hrung mit allen relevanten Daten ein');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese W&auml;hrung l&ouml;schen m&ouml;chten?');
define('TEXT_INFO_HEADING_NEW_CURRENCY', 'neue W&auml;hrung');
define('TEXT_INFO_HEADING_EDIT_CURRENCY', 'W&auml;hrung bearbeiten');
define('TEXT_INFO_HEADING_DELETE_CURRENCY', 'W&auml;hrung l&ouml;schen');
define('TEXT_INFO_SET_AS_DEFAULT', TEXT_SET_DEFAULT . ' (manuelles Aktualisieren der Wechselkurse erforderlich.)');
define('TEXT_INFO_CURRENCY_UPDATED', 'Der Wechselkurs %s (%s) wurde erfolgreich aktualisiert');

define('ERROR_REMOVE_DEFAULT_CURRENCY', 'Fehler: Die Standardw&auml;hrung darf nicht gel&ouml;scht werden. Bitte definieren Sie eine neue Standardw&auml;hrung und wiederholen Sie den Vorgang.');
define('ERROR_CURRENCY_INVALID', 'Fehler: Der Wechselkurs f&uuml;r %s (%s) wurde nicht aktualisiert. Ist dies ein g&uuml;ltiges W&auml;hrungsk&uuml;rzel?');
define('WARNING_PRIMARY_SERVER_FAILED','Warnung: Das Update &uuml;ber den prim&auml;ren Wechselkurs Server (%s) misslangen f&uuml;r %s (%s) - starte Versuch &uuml;ber den sekund&auml;ren Wechselkurs Server');
?>