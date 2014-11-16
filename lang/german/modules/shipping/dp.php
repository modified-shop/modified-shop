<?php
/* -----------------------------------------------------------------------------------------
   $Id: dp.php 5122 2013-07-18 11:47:17Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dp.php,v 1.4 2003/02/18 04:28:00); www.oscommerce.com
   (c) 2003 nextcommerce (dp.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   German Post (Deutsche Post WorldNet)         	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   enhanced on 2010-12-08 18:17:30Z franky_n
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_DP_TEXT_TITLE', 'Deutsche Post');
define('MODULE_SHIPPING_DP_TEXT_DESCRIPTION', 'Deutsche Post - Weltweites Versandmodul');
define('MODULE_SHIPPING_DP_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_DP_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_DP_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_DP_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

define('MODULE_SHIPPING_DP_STATUS_TITLE' , 'Deutsche Post WorldNet');
define('MODULE_SHIPPING_DP_STATUS_DESC' , 'Wollen Sie den Versand &uuml;ber die deutsche Post anbieten?');
define('MODULE_SHIPPING_DP_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_DP_HANDLING_DESC' , 'Bearbeitungsgeb&uuml;hr f&uuml;r diese Versandart in Euro');
define('MODULE_SHIPPING_DP_TAX_CLASS_TITLE' , 'Steuerklasse');
define('MODULE_SHIPPING_DP_TAX_CLASS_DESC' , 'Folgende Steuerklasse an Versandkosten anwenden.');
define('MODULE_SHIPPING_DP_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_DP_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_DP_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_DP_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_DP_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_DP_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. z.B. AT,DE');
define('MODULE_SHIPPING_DP_NUMBER_ZONES_TITLE' , 'Anzahl der Zonen');
define('MODULE_SHIPPING_DP_NUMBER_ZONES_DESC' , 'Anzahl der bereitgestellten Zonen');
define('MODULE_SHIPPING_DP_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_DP_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn kein Versand in das Land m&ouml;glich ist bzw. keine Versandkosten berechnet werden konnten?');

for ($module_shipping_dp_i = 1; $module_shipping_dp_i <= MODULE_SHIPPING_DP_NUMBER_ZONES; $module_shipping_dp_i ++) {
  define('MODULE_SHIPPING_DP_COUNTRIES_'.$module_shipping_dp_i.'_TITLE' , '<hr/>DP Zone '.$module_shipping_dp_i.' L&auml;nder');
  define('MODULE_SHIPPING_DP_COUNTRIES_'.$module_shipping_dp_i.'_DESC' , 'Kommagetrennte Liste von der 2stelligen ISO country codes der Zone '.$module_shipping_dp_i.' (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_DP_COST_'.$module_shipping_dp_i.'_TITLE' , 'DP Zone '.$module_shipping_dp_i.' Versandtabelle');
  define('MODULE_SHIPPING_DP_COST_'.$module_shipping_dp_i.'_DESC' , 'Versandkosten der Zone '.$module_shipping_dp_i.' bezogen auf Bestellungsgewicht. Beispiel: 3:8.50,7:10.50,99999:12.00... Gewichte gr&ouml;sser 0 und kleiner 3 kosten 8.50, kleiner als 7 kostet 10.50 f&uuml;r Zone '.$module_shipping_dp_i.'.');
}
?>