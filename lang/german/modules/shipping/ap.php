<?php
/* -----------------------------------------------------------------------------------------
   $Id: ap.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ap.php,v 1.02 2003/02/18); www.oscommerce.com 
   (c) 2003	 nextcommerce (ap.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   austrian_post_1.05       	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   

define('MODULE_SHIPPING_AP_TEXT_TITLE', '&Ouml;sterreichische Post AG');
define('MODULE_SHIPPING_AP_TEXT_DESCRIPTION', '&Ouml;sterreichische Post AG - Weltweites Versandmodul');
define('MODULE_SHIPPING_AP_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_AP_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_AP_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_AP_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

define('MODULE_SHIPPING_AP_STATUS_TITLE' , '&Ouml;sterreichische Post AG');
define('MODULE_SHIPPING_AP_STATUS_DESC' , 'Wollen Sie den Versand &uuml;ber die &Ouml;sterreichische Post AG anbieten?');
define('MODULE_SHIPPING_AP_TAX_CLASS_TITLE' , 'Steuersatz');
define('MODULE_SHIPPING_AP_TAX_CLASS_DESC' , 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_AP_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_AP_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_AP_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_AP_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_AP_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_AP_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. z.B. AT,DE');
define('MODULE_SHIPPING_AP_NUMBER_ZONES_TITLE' , 'Anzahl der Zonen');
define('MODULE_SHIPPING_AP_NUMBER_ZONES_DESC' , 'Anzahl der bereitgestellten Zonen');
define('MODULE_SHIPPING_AP_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_AP_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn kein Versand in das Land m&ouml;glich ist bzw. keine Versandkosten berechnet werden konnten?');

for ($module_shipping_ap_i = 1; $module_shipping_ap_i <= MODULE_SHIPPING_AP_NUMBER_ZONES; $module_shipping_ap_i ++) {
  define('MODULE_SHIPPING_AP_COUNTRIES_'.$module_shipping_ap_i.'_TITLE' , '<hr/>Zone '.$module_shipping_ap_i.' L&auml;nder');
  define('MODULE_SHIPPING_AP_COUNTRIES_'.$module_shipping_ap_i.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$module_shipping_ap_i.' sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_AP_COST_'.$module_shipping_ap_i.'_TITLE' , 'Zone '.$module_shipping_ap_i.' Tarif Tabelle bis 20 kg');
  define('MODULE_SHIPPING_AP_COST_'.$module_shipping_ap_i.'_DESC' , 'Versandkosten nach Zone '.$module_shipping_ap_i.' Bestimmungsorte, basierend auf einer Gruppe von max. Bestellgewichten. Beispiel: 3:8.50,7:10.50,... Gewicht von kleiner oder gleich 3 w&uuml;rde 8.50 f&uuml;r die Zone '.$module_shipping_ap_i.' Bestimmungsl&auml;nder kosten.');
  define('MODULE_SHIPPING_AP_HANDLING_'.$module_shipping_ap_i.'_TITLE' , 'Zone '.$module_shipping_ap_i.' Handling Geb&uuml;hr');
  define('MODULE_SHIPPING_AP_HANDLING_'.$module_shipping_ap_i.'_DESC' , 'Handling Geb&uuml;hr f&uuml;r diese Versandzone');
}
?>