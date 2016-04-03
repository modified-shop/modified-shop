<?php
/* -----------------------------------------------------------------------------------------
   $Id: upse.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce( fedexeu.php,v 1.01 2003/02/18 03:25:00); www.oscommerce.com 
   (c) 2003	 nextcommerce (fedexeu.php,v 1.5 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   fedex_europe_1.02        	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/



define('MODULE_SHIPPING_UPSE_TEXT_TITLE', 'United Parcel Service Express');
define('MODULE_SHIPPING_UPSE_TEXT_DESCRIPTION', 'United Parcel Service Express - Versandmodul');
define('MODULE_SHIPPING_UPSE_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_UPSE_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_UPSE_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich.');
define('MODULE_SHIPPING_UPSE_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden.');

define('MODULE_SHIPPING_UPSE_STATUS_TITLE' , 'UPS Express');
define('MODULE_SHIPPING_UPSE_STATUS_DESC' , 'Wollen Sie den Versand durch UPS Express anbieten?');
define('MODULE_SHIPPING_UPSE_HANDLING_TITLE' , 'Zuschlag');
define('MODULE_SHIPPING_UPSE_HANDLING_DESC' , 'Bearbeitungszuschlag f&uuml;r diese Versandart in Euro');
define('MODULE_SHIPPING_UPSE_TAX_CLASS_TITLE' , 'Steuersatz');
define('MODULE_SHIPPING_UPSE_TAX_CLASS_DESC' , 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_UPSE_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_UPSE_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_UPSE_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_UPSE_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_UPSE_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_UPSE_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll, z. B.: AT,DE.');




/* UPS Express

*/

define('MODULE_SHIPPING_UPSE_COUNTRIES_1_TITLE' , 'Staaten f&uuml;r UPS Express Zone 1');
define('MODULE_SHIPPING_UPSE_COUNTRIES_1_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 1 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_1_TITLE' , 'Tarife f&uuml;r UPS Express Zone 1');
define('MODULE_SHIPPING_UPSE_COST_1_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 1. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 22,70 = 0.5:22.7,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_2_TITLE' , 'Staaten f&uuml;r UPS Express Zone 2');
define('MODULE_SHIPPING_UPSE_COUNTRIES_2_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 2 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_2_TITLE' , 'Tarife f&uuml;r UPS Express Zone 2');
define('MODULE_SHIPPING_UPSE_COST_2_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 2. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 51,55 = 0.5:51.55,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_3_TITLE' , 'Staaten f&uuml;r UPS Express Zone 3');
define('MODULE_SHIPPING_UPSE_COUNTRIES_3_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 3 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_3_TITLE' , 'Tarife f&uuml;r UPS Express Zone 3');
define('MODULE_SHIPPING_UPSE_COST_3_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 3. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 60,70 = 0.5:60.70,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_4_TITLE' , 'Staaten f&uuml;r UPS Express Zone 4');
define('MODULE_SHIPPING_UPSE_COUNTRIES_4_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 4 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_4_TITLE' , 'Tarife f&uuml;r UPS Express Zone 4');
define('MODULE_SHIPPING_UPSE_COST_4_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 4. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 66,90 = 0.5:66.90,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_5_TITLE' , 'Staaten f&uuml;r UPS Express Zone 41');
define('MODULE_SHIPPING_UPSE_COUNTRIES_5_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 41 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_5_TITLE' , 'Tarife f&uuml;r UPS Express Zone 41');
define('MODULE_SHIPPING_UPSE_COST_5_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 41. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 82,10 = 0.5:82.10,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_6_TITLE' , 'Staaten f&uuml;r UPS Express Zone 42');
define('MODULE_SHIPPING_UPSE_COUNTRIES_6_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 42 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_6_TITLE' , 'Tarife f&uuml;r UPS Express Zone 42');
define('MODULE_SHIPPING_UPSE_COST_6_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 42. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 82,90 = 0.5:82.90,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_7_TITLE' , 'Staaten f&uuml;r UPS Express Zone 5');
define('MODULE_SHIPPING_UPSE_COUNTRIES_7_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 5 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_7_TITLE' , 'Tarife f&uuml;r UPS Express Zone 5');
define('MODULE_SHIPPING_UPSE_COST_7_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 5. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 59,00 = 0.5:59.00,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_8_TITLE' , 'Staaten f&uuml;r UPS Express Zone 6');
define('MODULE_SHIPPING_UPSE_COUNTRIES_8_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 6 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_8_TITLE' , 'Tarife f&uuml;r UPS Express Zone 6');
define('MODULE_SHIPPING_UPSE_COST_8_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 6. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 84,50 = 0.5:84.50,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_9_TITLE' , 'Staaten f&uuml;r UPS Express Zone 7');
define('MODULE_SHIPPING_UPSE_COUNTRIES_9_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 7 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_9_TITLE' , 'Tarife f&uuml;r UPS Express Zone 7');
define('MODULE_SHIPPING_UPSE_COST_9_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 7. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 71,85 = 0.5:71.85,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_10_TITLE' , 'Staaten f&uuml;r UPS Express Zone 8');
define('MODULE_SHIPPING_UPSE_COUNTRIES_10_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 8 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_10_TITLE' , 'Tarife f&uuml;r UPS Express Zone 8');
define('MODULE_SHIPPING_UPSE_COST_10_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 8. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 80,05 = 0.5:80.05,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_11_TITLE' , 'Staaten f&uuml;r UPS Express Zone 9');
define('MODULE_SHIPPING_UPSE_COUNTRIES_11_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 9 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_11_TITLE' , 'Tarife f&uuml;r UPS Express Zone 9');
define('MODULE_SHIPPING_UPSE_COST_11_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 9. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 85,20 = 0.5:85.20,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_12_TITLE' , 'Staaten f&uuml;r UPS Express Zone 10');
define('MODULE_SHIPPING_UPSE_COUNTRIES_12_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 10 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_12_TITLE' , 'Tarife f&uuml;r UPS Express Zone 10');
define('MODULE_SHIPPING_UPSE_COST_12_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 10. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 93,10 = 0.5:93.10,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_13_TITLE' , 'Staaten f&uuml;r UPS Express Zone 11');
define('MODULE_SHIPPING_UPSE_COUNTRIES_13_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 11 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_13_TITLE' , 'Tarife f&uuml;r UPS Express Zone 11');
define('MODULE_SHIPPING_UPSE_COST_13_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 11. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 103,50 = 0.5:103.50,...');

define('MODULE_SHIPPING_UPSE_COUNTRIES_14_TITLE' , 'Staaten f&uuml;r UPS Express Zone 12');
define('MODULE_SHIPPING_UPSE_COUNTRIES_14_DESC' , 'Durch Komma getrennte ISO-K&uuml;rzel der Staaten f&uuml;r Zone 12 (WORLD eintragen f&uuml;r den Rest der Welt.):');
define('MODULE_SHIPPING_UPSE_COST_14_TITLE' , 'Tarife f&uuml;r UPS Express Zone 12');
define('MODULE_SHIPPING_UPSE_COST_14_DESC' , 'Gewichtsbasierte Versandkosten innerhalb Zone 12. Beispiel: Sendung zwischen 0 und 0,5kg kostet EUR 105,20 = 0.5:105.20,...');
?>
