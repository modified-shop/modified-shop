<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003 nextcommerce (invoice.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_INVOICE_TEXT_DESCRIPTION', 'Rechnung');
define('MODULE_PAYMENT_INVOICE_TEXT_TITLE', 'Rechnung');
define('MODULE_PAYMENT_INVOICE_TEXT_INFO','');
define('MODULE_PAYMENT_INVOICE_STATUS_TITLE' , 'Rechnungsmodul aktivieren');
define('MODULE_PAYMENT_INVOICE_STATUS_DESC' , 'M&ouml;chten Sie Zahlungen per Invoices akzeptieren?');
define('MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID_TITLE' , 'Bestellstatus festlegen');
define('MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID_DESC' , 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_INVOICE_SORT_ORDER_TITLE' , 'Anzeigereihenfolge');
define('MODULE_PAYMENT_INVOICE_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_INVOICE_ZONE_TITLE' , 'Zahlungszone');
define('MODULE_PAYMENT_INVOICE_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_INVOICE_ALLOWED_TITLE' , 'Erlaubte Zonen');
define('MODULE_PAYMENT_INVOICE_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_INVOICE_MIN_ORDER_TITLE' , 'Notwendige Bestellungen');
define('MODULE_PAYMENT_INVOICE_MIN_ORDER_DESC' , 'Die Mindestanzahl an Bestellungen die ein Kunden haben muss damit die Option zur Verf&uuml;gung steht.');

// BOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
define('MODULE_PAYMENT_INVOICE_NEG_SHIPPING_TITLE', 'Ausschlu&szlig; bei Versandmodulen');
define('MODULE_PAYMENT_INVOICE_NEG_SHIPPING_DESC', 'Dieses Zahlungsmodul deaktivieren wenn Versandmodul gew&auml;hlt (Komma separierte Liste)');
// EOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
?>