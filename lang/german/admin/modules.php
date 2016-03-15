<?php
/* --------------------------------------------------------------
   $Id: modules.php 2957 2012-05-31 11:55:56Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(modules.php,v 1.8 2002/04/09); www.oscommerce.com
   (c) 2003 nextcommerce (modules.php,v 1.5 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (modules.php 899 2005-04-29)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('HEADING_TITLE_MODULES_PAYMENT', 'Zahlungsweisen');
define('HEADING_TITLE_MODULES_SHIPPING', 'Versandarten');
define('HEADING_TITLE_MODULES_ORDER_TOTAL', 'Order Total Modul');

define('TABLE_HEADING_MODULES', 'Module');
define('TABLE_HEADING_SORT_ORDER', 'Sortierreihenfolge');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_MODULE_DIRECTORY', 'Modul Verzeichnis:');
define('TEXT_MODULE_FILE_MISSING', '<b>Sprachdatei "%s" fehlt, Modul "%s" wird nicht angezeigt!</b>');
define('TABLE_HEADING_FILENAME','Modulname (f&uuml;r internen Gebrauch)');

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('TEXT_INFO_DELETE_PAYPAL', 'Wenn Sie dieses Modul jetzt deinstallieren werden die PayPal Transaktions-Daten gel&ouml;scht!<br />Wollen Sie diese Daten erhalten, dr&uuml;cken Sie jetzt auf Abbruch und deaktivieren Sie das Modul nur. (Modul aktivieren = False)');
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('TABLE_HEADING_MODULES_INSTALLED', 'Folgende Module wurden installiert');
define('TABLE_HEADING_MODULES_PREFERRED', 'Beliebte Module');
define('TABLE_HEADING_MODULES_NOT_INSTALLED', 'Folgende Module sind noch verf&uuml;gbar');
define('TEXT_MODULE_UPDATE_NEEDED', 'Folgende Module wurden aktualisiert und ben&ouml;tigen ein Update der Datenbank. Dazu sichern Sie bitte die Einstellungen und installieren Sie diese Module neu.');
?>