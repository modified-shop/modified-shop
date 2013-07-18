<?php
/* -----------------------------------------------------------------------------------------
   $Id: item.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(item.php,v 1.6 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (item.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_ITEM_TEXT_TITLE', 'Versandkosten pro St&uuml;ck');
define('MODULE_SHIPPING_ITEM_TEXT_DESCRIPTION', 'Versandkosten pro St&uuml;ck');
define('MODULE_SHIPPING_ITEM_TEXT_WAY', 'Bester Weg');
define('MODULE_SHIPPING_ITEM_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');

define('MODULE_SHIPPING_ITEM_STATUS_TITLE' , 'Versandkosten pro St&uuml;ck aktivieren');
define('MODULE_SHIPPING_ITEM_STATUS_DESC' , 'M&ouml;chten Sie Versandkosten pro St&uuml;ck anbieten?');
define('MODULE_SHIPPING_ITEM_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_ITEM_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_ITEM_TAX_CLASS_TITLE' , 'Steuerklasse');
define('MODULE_SHIPPING_ITEM_TAX_CLASS_DESC' , 'Folgende Steuerklasse an Versandkosten anwenden');
define('MODULE_SHIPPING_ITEM_ZONE_TITLE' , 'Versandzone');
define('MODULE_SHIPPING_ITEM_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, wird diese Versandmethode ausschlie&szlig;lich f&uuml;r diese Zone angewendet');
define('MODULE_SHIPPING_ITEM_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_ITEM_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_ITEM_NUMBER_ZONES_TITLE' , 'Anzahl der Zonen');
define('MODULE_SHIPPING_ITEM_NUMBER_ZONES_DESC' , 'Anzahl der bereitgestellten Zonen');
define('MODULE_SHIPPING_ITEM_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_ITEM_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn kein Versand in das Land m&ouml;glich ist bzw. keine Versandkosten berechnet werden konnten?');

for ($module_shipping_item_i = 1; $module_shipping_item_i <= MODULE_SHIPPING_ITEM_NUMBER_ZONES; $module_shipping_item_i ++) {
  define('MODULE_SHIPPING_ITEM_COUNTRIES_'.$module_shipping_item_i.'_TITLE' , '<hr/>Zone '.$module_shipping_item_i.' L&auml;nder');
  define('MODULE_SHIPPING_ITEM_COUNTRIES_'.$module_shipping_item_i.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$module_shipping_item_i.' sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_ITEM_COST_'.$module_shipping_item_i.'_TITLE' , 'Zone '.$module_shipping_item_i.' Versandkosten');
  define('MODULE_SHIPPING_ITEM_COST_'.$module_shipping_item_i.'_DESC' , 'Versandkosten nach Zone '.$module_shipping_item_i.' werden mit der Anzahl an Artikel einer Bestellung multipliziert, wenn diese Versandart angegeben ist.');
  define('MODULE_SHIPPING_ITEM_HANDLING_'.$module_shipping_item_i.'_TITLE' , 'Zone '.$module_shipping_item_i.' Handling Geb&uuml;hr');
  define('MODULE_SHIPPING_ITEM_HANDLING_'.$module_shipping_item_i.'_DESC' , 'Handling Geb&uuml;hr f&uuml;r diese Versandzone');
}
?>
