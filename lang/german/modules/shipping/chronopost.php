<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(chronopost.php,v 1.0 2002/04/01 07:07:45); www.oscommerce.com 
   (c) 2003	 nextcommerce (chronopost.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions: 
   chronopost-1.0.1       	Autor:	devteam@e-network.fr | www.oscommerce-fr.info

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   

define('MODULE_SHIPPING_CHRONOPOST_TEXT_TITLE', 'Chronopost');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_DESCRIPTION', 'Chronopost Zone Based Rates');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_CHRONOPOST_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_CHRONOPOST_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

define('MODULE_SHIPPING_CHRONOPOST_STATUS_TITLE' , 'Chronopost');
define('MODULE_SHIPPING_CHRONOPOST_STATUS_DESC' , 'Wollen Sie den Versand &uuml;ber Chronopost anbieten?');
define('MODULE_SHIPPING_CHRONOPOST_HANDLING_TITLE' , 'Bearbeitungsgeb&uuml;hr');
define('MODULE_SHIPPING_CHRONOPOST_HANDLING_DESC' , 'Bearbeitungsgeb&uuml;hr f&uuml;r diese Versandart');
define('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS_TITLE' , 'Steuersatz');
define('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS_DESC' , 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_CHRONOPOST_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_CHRONOPOST_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_CHRONOPOST_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_CHRONOPOST_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. z.B. AT,DE');

for ($module_shipping_chp_i = 1; $module_shipping_chp_i <= 10; $module_shipping_chp_i ++) {
  define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_'.$module_shipping_chp_i.'_TITLE' , '<hr/>Chronopost Zone '.$module_shipping_chp_i.' L&auml;nder');
  define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_'.$module_shipping_chp_i.'_DESC' , 'Kommagetrennte Liste von der 2stelligen ISO country codes der Zone '.$module_shipping_chp_i.' (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_CHRONOPOST_COST_'.$module_shipping_chp_i.'_TITLE' , 'Chronopost Zone '.$module_shipping_chp_i.' Versandtabelle');
  define('MODULE_SHIPPING_CHRONOPOST_COST_'.$module_shipping_chp_i.'_DESC' , 'Versandkosten der Zone '.$module_shipping_chp_i.' bezogen auf Bestellungsgewicht. Beispiel: 0-2000:28.71,2000-5000:34.38... Gewichte gr&ouml;&szlig;er 0 und kleiner 2 kosten 28,71, kleiner als 5 kostet 34.38 f&uuml;r Zone '.$module_shipping_chp_i.'.');
}
