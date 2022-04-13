<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dp.php,v 1.4 2003/02/18 04:28:00); www.oscommerce.com 
   (c) 2003	nextcommerce (dp.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2009	shd-media (gls.php 899 27.05.2009);

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   GLS (German Logistic Service) based on DP (Deutsche Post)        
   (c) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Pl&auml;nkers | http://www.themedia.at & http://www.oscommerce.at
    
   GLS contribution made by shd-media (c) 2009 shd-media - www.shd-media.de
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_GLS_TEXT_TITLE', 'GLS');
define('MODULE_SHIPPING_GLS_TEXT_DESCRIPTION', 'GLS - Europaweites Versandmodul');
define('MODULE_SHIPPING_GLS_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_GLS_POSTCODE_INFO_TEXT', 'inkl. Inselzuschlag');
define('MODULE_SHIPPING_GLS_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_GLS_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_GLS_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

define('MODULE_SHIPPING_GLS_STATUS_TITLE' , 'GLS');
define('MODULE_SHIPPING_GLS_STATUS_DESC' , 'Wollen Sie den Versand &uuml;ber GLS anbieten?');
define('MODULE_SHIPPING_GLS_HANDLING_TITLE' , 'Bearbeitungsgeb&uuml;hr');
define('MODULE_SHIPPING_GLS_HANDLING_DESC' , 'Bearbeitungsgeb&uuml;hr f&uuml;r diese Versandart in Euro');
define('MODULE_SHIPPING_GLS_TAX_CLASS_TITLE' , 'Steuersatz');
define('MODULE_SHIPPING_GLS_TAX_CLASS_DESC' , 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_GLS_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_GLS_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_GLS_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_GLS_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_GLS_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_GLS_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll, z.B.: AT,DE');
define('MODULE_SHIPPING_GLS_DISPLAY_TITLE' , 'Anzeige aktivieren');
define('MODULE_SHIPPING_GLS_DISPLAY_DESC' , 'M&ouml;chten Sie anzeigen, wenn kein Versand in das Land m&ouml;glich ist bzw. keine Versandkosten berechnet werden konnten?');

define('MODULE_SHIPPING_GLS_POSTCODE_TITLE' , 'GLS Inselzuschlag - Postleitzahlen');
define('MODULE_SHIPPING_GLS_POSTCODE_DESC' , 'Postleitzahlengebiete');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_TITLE' , 'GLS Inselzuschlag - Kosten');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_DESC' , 'Inselzuschlag: Tragen Sie hier ein, wieviel auf die Versandkosten aufgeschlagen werden soll, wenn die Lieferadresse auf einer Deutschen Insel liegt');

for ($module_shipping_gls_i = 1; $module_shipping_gls_i <= 6; $module_shipping_gls_i ++) {
  define('MODULE_SHIPPING_GLS_COUNTRIES_'.$module_shipping_gls_i.'_TITLE' , '<hr/>Zone '.$module_shipping_gls_i.' L&auml;nder');
  define('MODULE_SHIPPING_GLS_COUNTRIES_'.$module_shipping_gls_i.'_DESC' , 'Durch Komma getrennte Liste von ISO L&auml;ndercodes (2 Zeichen), welche Teil von Zone '.$module_shipping_gls_i.' sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
  define('MODULE_SHIPPING_GLS_COST_'.$module_shipping_gls_i.'_TITLE' , 'Zone '.$module_shipping_gls_i.' Versandkosten');
  define('MODULE_SHIPPING_GLS_COST_'.$module_shipping_gls_i.'_DESC' , 'Versandkosten nach Zone '.$module_shipping_gls_i.' Bestimmungsorte, basierend auf einer Gruppe von max. Bestellgewichten. Beispiel: 3:8.50,7:10.50,... Gewicht von kleiner oder gleich 3 w&uuml;rde 8.50 f&uuml;r die Zone '.$module_shipping_gls_i.' Bestimmungsl&auml;nder kosten.');
}
?>