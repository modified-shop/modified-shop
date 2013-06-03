<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce( freeamount.php,v 1.01 2002/01/24 03:25:00); www.oscommerce.com 
   (c) 2003	 nextcommerce (freeamount.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   freeamountv2-p1         	Autor:	dwk

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// CUSTOMIZE THIS SETTING
define('NUMBER_OF_ZONES_FREEAMOUNT', 2);

define('MODULE_SHIPPING_FREEAMOUNT_TEXT_TITLE', 'Versandkostenfrei');
define('MODULE_SHIPPING_FREEAMOUNT_TEXT_DESCRIPTION', 'Versandkostenfreie Lieferung');
define('MODULE_SHIPPING_FREEAMOUNT_TEXT_WAY', 'ab %s  Bestellwert versenden wir Ihre Bestellung versandkostenfrei');
define('MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER', 'Sortierreihenfolge');

define('MODULE_SHIPPING_FREEAMOUNT_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_FREEAMOUNT_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_FREEAMOUNT_STATUS_TITLE' , 'Versandkostenfreie Lieferung aktivieren');
define('MODULE_SHIPPING_FREEAMOUNT_STATUS_DESC' , 'M&ouml;chten Sie Versandkostenfreie Lieferung anbieten?');
define('MODULE_SHIPPING_FREEAMOUNT_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_FREEAMOUNT_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn der Mindestbetrag zur VK-freien Lieferung nicht erreicht ist?');
define('MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');

for ($ii=1; $ii<=NUMBER_OF_ZONES_FREEAMOUNT; $ii++) {
  define('MODULE_SHIPPING_FREEAMOUNT_COUNTRIES_'.$ii.'_TITLE' , 'Zone '.$ii.' L&auml;nder');
  define('MODULE_SHIPPING_FREEAMOUNT_COUNTRIES_'.$ii.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$ii.' sind.');
  define('MODULE_SHIPPING_FREEAMOUNT_AMOUNT_'.$ii.'_TITLE' , 'Zone '.$ii.' Mindestbetrag');
  define('MODULE_SHIPPING_FREEAMOUNT_AMOUNT_'.$ii.'_DESC' , 'Midestbestellwert f&uuml;r Zone '.$ii.' damit der Versand kostenlos ist.');
}
?>
