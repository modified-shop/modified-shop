<?php

/* -----------------------------------------------------------------------------------------
   $Id: cash.php 1102 2005-07-24 15:05:38Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003	 nextcommerce (invoice.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_CASH_TEXT_DESCRIPTION', 'Barzahlung');
define('MODULE_PAYMENT_CASH_TEXT_TITLE', 'Barzahlung');
define('MODULE_PAYMENT_CASH_TEXT_INFO', '');
define('MODULE_PAYMENT_CASH_STATUS_TITLE', 'Barzahlungsmodul aktivieren');
define('MODULE_PAYMENT_CASH_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Barzahlung akzeptieren?');
define('MODULE_PAYMENT_CASH_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_CASH_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_CASH_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_CASH_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_CASH_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_CASH_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_CASH_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_CASH_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
?>