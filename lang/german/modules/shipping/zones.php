<?php
/* -----------------------------------------------------------------------------------------
   $Id: zones.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

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
   
// CUSTOMIZE THIS SETTING
define('NUMBER_OF_ZONES',9);

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
define('MODULE_SHIPPING_ZONES_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_ZONES_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');

for ($sz=1;$sz<=NUMBER_OF_ZONES;$sz++) {
define('MODULE_SHIPPING_ZONES_COUNTRIES_'.$sz.'_TITLE' , 'Zone '.$sz.' L&auml;nder');
define('MODULE_SHIPPING_ZONES_COUNTRIES_'.$sz.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$sz.' sind.');
define('MODULE_SHIPPING_ZONES_COST_'.$sz.'_TITLE' , 'Zone '.$sz.' Versandkosten');
define('MODULE_SHIPPING_ZONES_COST_'.$sz.'_DESC' , 'Versandkosten nach Zone '.$sz.' Bestimmungsorte, basierend auf einer Gruppe von max. Bestellgewichten. Beispiel: 3:8.50,7:10.50,... Gewicht von kleiner oder gleich 3 w&uuml;rde 8.50 f&uuml;r die Zone '.$sz.' Bestimmungsl&auml;nder kosten.');
define('MODULE_SHIPPING_ZONES_HANDLING_'.$sz.'_TITLE' , 'Zone '.$sz.' Handling Geb&uuml;hr');
define('MODULE_SHIPPING_ZONES_HANDLING_'.$sz.'_DESC' , 'Handling Geb&uuml;hr f&uuml;r diese Versandzone');
}
?>