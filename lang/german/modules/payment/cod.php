<?php
/* -----------------------------------------------------------------------------------------
   $Id: cod.php 998 2005-07-07 14:18:20Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.7 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (cod.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_TYPE_PERMISSION', 'cod');
define('MODULE_PAYMENT_COD_TEXT_TITLE', 'Nachnahme');
define('MODULE_PAYMENT_COD_TEXT_DESCRIPTION', 'Bezahlung per Nachnahme');
define('MODULE_PAYMENT_COD_TEXT_INFO', 'Bitte beachten Sie, dass zus&auml;tzlich 2 Euro Zustellgeb&uuml;hr an den Zusteller vor Ort zu entrichten sind.');
define('MODULE_PAYMENT_COD_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_COD_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_COD_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_COD_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_COD_STATUS_TITLE', 'Nachnahme Modul aktivieren');
define('MODULE_PAYMENT_COD_STATUS_DESC', 'M&ouml;chten Sie Zahlungen per Nachnahme akzeptieren?');
define('MODULE_PAYMENT_COD_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_COD_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_COD_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_COD_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen');
define('MODULE_PAYMENT_COD_LIMIT_ALLOWED_TITLE', 'Maximalbetrag');
define('MODULE_PAYMENT_COD_LIMIT_ALLOWED_DESC', 'Ab welchem Betrag soll Nachnahme nicht mehr erlaubt werden?<br />Der eingegebene Wert wird mit der Zwischensumme (subtotal) verglichen, welche gerundet wird.<br />Das bedeutet, dass der nur reine Warenwert, ohne Versandkosten und evtl. Zuschl&auml;ge ber&uuml;cksichtigt wird.');
define('MODULE_PAYMENT_COD_DISPLAY_INFO_TITLE', 'Anzeige im Checkout');
define('MODULE_PAYMENT_COD_DISPLAY_INFO_DESC', 'Soll ein Hinweis auf zus&auml;tzlich anfallende Kosten im Checkout angezeigt werden?');
define('MODULE_PAYMENT_COD_DISPLAY_INFO_TEXT', 'Bitte beachten Sie, dass zus&auml;tzlich 2 Euro Zustellgeb&uuml;hr an den Zusteller vor Ort zu entrichten sind.<br/>');
?>