<?php
/* -----------------------------------------------------------------------------------------
   $Id: zones.php 5118 2013-07-18 10:58:36Z Tomcraft $   

    modified eCommerce Shopsoftware
    http://www.modified-shop.org

    Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(zones.php,v 1.3 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (zones.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2003  xt-commerce.com (zones.php  2005-04-29); www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_ZONES_TEXT_TITLE', 'Versandkosten nach Zonen');
define('MODULE_SHIPPING_ZONES_TEXT_DESCRIPTION', 'Versandkosten Zonenbasierend');
define('MODULE_SHIPPING_ZONES_TEXT_WAY', 'Versand nach:');
define('MODULE_SHIPPING_ZONES_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_ZONES_INVALID_ZONE', 'Es ist kein Versand in dieses Land m&ouml;glich!');
define('MODULE_SHIPPING_ZONES_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht berechnet werden.');

define('MODULE_SHIPPING_ZONES_STATUS_TITLE' , 'Versandkosten nach Zonen Methode aktivieren');
define('MODULE_SHIPPING_ZONES_STATUS_DESC' , 'M&ouml;chten Sie Versandkosten nach Zonen anbieten?');
define('MODULE_SHIPPING_ZONES_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_ZONES_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_ZONES_TAX_CLASS_TITLE' , 'Steuerklasse');
define('MODULE_SHIPPING_ZONES_TAX_CLASS_DESC' , 'Folgende Steuerklasse an Versandkosten anwenden');
define('MODULE_SHIPPING_ZONES_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_ZONES_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_ZONES_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_ZONES_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_ZONES_NUMBER_ZONES_TITLE' , 'Anzahl der Zonen');
define('MODULE_SHIPPING_ZONES_NUMBER_ZONES_DESC' , 'Anzahl der bereitgestellten Zonen');
define('MODULE_SHIPPING_ZONES_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_ZONES_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn kein Versand in das Land m&ouml;glich ist bzw. keine Versandkosten berechnet werden konnten?');

for ($module_shipping_zones_i = 1; $module_shipping_zones_i <= MODULE_SHIPPING_ZONES_NUMBER_ZONES; $module_shipping_zones_i ++) {
  define('MODULE_SHIPPING_ZONES_COUNTRIES_'.$module_shipping_zones_i.'_TITLE' , '<hr/>Zone '.$module_shipping_zones_i.' L&auml;nder');
  define('MODULE_SHIPPING_ZONES_COUNTRIES_'.$module_shipping_zones_i.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$module_shipping_zones_i.' sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_ZONES_COST_'.$module_shipping_zones_i.'_TITLE' , 'Zone '.$module_shipping_zones_i.' Versandkosten');
  define('MODULE_SHIPPING_ZONES_COST_'.$module_shipping_zones_i.'_DESC' , 'Versandkosten nach Zone '.$module_shipping_zones_i.' Bestimmungsorte, basierend auf einer Gruppe von max. Bestellgewichten. Beispiel: 3:8.50,7:10.50,... Gewicht von kleiner oder gleich 3 w&uuml;rde 8.50 f&uuml;r die Zone '.$module_shipping_zones_i.' Bestimmungsl&auml;nder kosten.');
  define('MODULE_SHIPPING_ZONES_HANDLING_'.$module_shipping_zones_i.'_TITLE' , 'Zone '.$module_shipping_zones_i.' Handling Geb&uuml;hr');
  define('MODULE_SHIPPING_ZONES_HANDLING_'.$module_shipping_zones_i.'_DESC' , 'Handling Geb&uuml;hr f&uuml;r diese Versandzone');
}
?>