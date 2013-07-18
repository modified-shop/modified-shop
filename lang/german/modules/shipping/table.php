<?php
/* -----------------------------------------------------------------------------------------
   $Id: table.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (table.php,v 1.6 2003/02/16); www.oscommerce.com 
   (c) 2003 nextcommerce (table.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_TABLE_TEXT_TITLE', 'Tabellarische Versandkosten');
define('MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION', 'Tabellarische Versandkosten');
define('MODULE_SHIPPING_TABLE_TEXT_WAY', 'Bester Weg (%01.2f kg)');
define('MODULE_SHIPPING_TABLE_TEXT_WEIGHT', 'Gewicht');
define('MODULE_SHIPPING_TABLE_TEXT_AMOUNT', 'Menge');
define('MODULE_SHIPPING_TABLE_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht berechnet werden.');
define('MODULE_SHIPPING_TABLE_INVALID_ZONE', 'Es ist kein Versand in dieses Land m&ouml;glich!');

define('MODULE_SHIPPING_TABLE_STATUS_TITLE' , 'Tabellarische Versandkosten aktivieren');
define('MODULE_SHIPPING_TABLE_STATUS_DESC' , 'M&ouml;chten Sie Tabellarische Versandkosten anbieten?');
define('MODULE_SHIPPING_TABLE_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_TABLE_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_TABLE_MODE_TITLE' , 'Versandkosten Methode');
define('MODULE_SHIPPING_TABLE_MODE_DESC' , 'Die Versandkosten basieren auf Gesamtkosten oder Gesamtgewicht der bestellten Waren.');
define('MODULE_SHIPPING_TABLE_TAX_CLASS_TITLE' , 'Steuerklasse');
define('MODULE_SHIPPING_TABLE_TAX_CLASS_DESC' , 'Folgende Steuerklasse an Versandkosten anwenden');
define('MODULE_SHIPPING_TABLE_ZONE_TITLE' , 'Versandzone');
define('MODULE_SHIPPING_TABLE_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, wird diese Versandmethode ausschlie&szlig;lich f&uuml;r diese Zone angewendet');
define('MODULE_SHIPPING_TABLE_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_TABLE_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_TABLE_NUMBER_ZONES_TITLE' , 'Anzahl der Zonen');
define('MODULE_SHIPPING_TABLE_NUMBER_ZONES_DESC' , 'Anzahl der bereitgestellten Zonen');
define('MODULE_SHIPPING_TABLE_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_TABLE_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn kein Versand in das Land m&ouml;glich ist bzw. keine Versandkosten berechnet werden konnten?');

for ($module_shipping_table_i = 1; $module_shipping_table_i <= MODULE_SHIPPING_TABLE_NUMBER_ZONES; $module_shipping_table_i ++) {
  define('MODULE_SHIPPING_TABLE_COUNTRIES_'.$module_shipping_table_i.'_TITLE' , '<hr/>Zone '.$module_shipping_table_i.' L&auml;nder');
  define('MODULE_SHIPPING_TABLE_COUNTRIES_'.$module_shipping_table_i.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$module_shipping_table_i.' sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_TABLE_COST_'.$module_shipping_table_i.'_TITLE' , 'Zone '.$module_shipping_table_i.' Versandkosten');
  define('MODULE_SHIPPING_TABLE_COST_'.$module_shipping_table_i.'_DESC' , 'Versandkosten nach Zone '.$module_shipping_table_i.' Bestimmungsorte, basierend auf einer Gruppe von max. Bestellgewichten oder Warenkorbwert, je nach Moduleinstellung. Beispiel: 3:8.50,7:10.50,... Gewicht/Preis von kleiner oder gleich 3 w&uuml;rde 8.50 f&uuml;r die Zone '.$module_shipping_table_i.' Bestimmungsl&auml;nder kosten.');
  define('MODULE_SHIPPING_TABLE_HANDLING_'.$module_shipping_table_i.'_TITLE' , 'Zone '.$module_shipping_table_i.' Handling Geb&uuml;hr');
  define('MODULE_SHIPPING_TABLE_HANDLING_'.$module_shipping_table_i.'_DESC' , 'Handling Geb&uuml;hr f&uuml;r diese Versandzone');
}
?>
