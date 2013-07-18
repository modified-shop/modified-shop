<?php
/* -----------------------------------------------------------------------------------------
   $Id: gls.php 5121 2013-07-18 11:38:19Z Tomcraft $   

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

define('MODULE_SHIPPING_GLS_COUNTRIES_1_TITLE' , 'GLS Zone 1 L&auml;nder');
define('MODULE_SHIPPING_GLS_COUNTRIES_1_DESC' , 'Kommagetrennte Liste zweistelliger ISO L&auml;nderk&uuml;rzel, die Teil der Zone 1 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_GLS_COST_1_TITLE' , 'GLS Zone 1 Versandkostentabelle');
define('MODULE_SHIPPING_GLS_COST_1_DESC' , 'Versandkosten f&uuml;r L&auml;nder in Zone 1 basierend auf einer  Gewichtsangabe (von-bis) der Bestellung. Beispiel: 0-3:8.50,3-7:10.50,usw. Gewichte gr&ouml;&szlig;er als 0 und kleiner gleich 3 w&uuml;rden 8.50 f&uuml;r Zone 1 L&auml;nder betragen.');

define('MODULE_SHIPPING_GLS_COUNTRIES_2_TITLE' , 'GLS Zone 2 L&auml;nder');
define('MODULE_SHIPPING_GLS_COUNTRIES_2_DESC' , 'Kommagetrennte Liste zweistelliger ISO L&auml;nderk&uuml;rzel, die Teil der Zone 2 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_GLS_COST_2_TITLE' , 'GLS Zone 2 Versandkostentabelle');
define('MODULE_SHIPPING_GLS_COST_2_DESC' , 'Versandkosten f&uuml;r L&auml;nder in Zone 1 basierend auf einer  Gewichtsangabe (von-bis) der Bestellung. Beispiel: 0-3:8.50,3-7:10.50,usw. Gewichte gr&ouml;&szlig;er als 0 und kleiner gleich 3 w&uuml;rden 8.50 f&uuml;r Zone 1 L&auml;nder betragen.');

define('MODULE_SHIPPING_GLS_COUNTRIES_3_TITLE' , 'GLS Zone 3 L&auml;nder');
define('MODULE_SHIPPING_GLS_COUNTRIES_3_DESC' , 'Kommagetrennte Liste zweistelliger ISO L&auml;nderk&uuml;rzel, die Teil der Zone 3 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_GLS_COST_3_TITLE' , 'GLS Zone 3 Versandkostentabelle');
define('MODULE_SHIPPING_GLS_COST_3_DESC' , 'Versandkosten f&uuml;r L&auml;nder in Zone 1 basierend auf einer  Gewichtsangabe (von-bis) der Bestellung. Beispiel: 0-3:8.50,3-7:10.50,usw. Gewichte gr&ouml;&szlig;er als 0 und kleiner gleich 3 w&uuml;rden 8.50 f&uuml;r Zone 1 L&auml;nder betragen.');

define('MODULE_SHIPPING_GLS_COUNTRIES_4_TITLE' , 'GLS Zone 4 L&auml;nder');
define('MODULE_SHIPPING_GLS_COUNTRIES_4_DESC' , 'Kommagetrennte Liste zweistelliger ISO L&auml;nderk&uuml;rzel, die Teil der Zone 4 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_GLS_COST_4_TITLE' , 'GLS Zone 4 Versandkostentabelle');
define('MODULE_SHIPPING_GLS_COST_4_DESC' , 'Versandkosten f&uuml;r L&auml;nder in Zone 1 basierend auf einer  Gewichtsangabe (von-bis) der Bestellung. Beispiel: 0-3:8.50,3-7:10.50,usw. Gewichte gr&ouml;&szlig;er als 0 und kleiner gleich 3 w&uuml;rden 8.50 f&uuml;r Zone 1 L&auml;nder betragen.');

define('MODULE_SHIPPING_GLS_COUNTRIES_5_TITLE' , 'GLS Zone 5 L&auml;nder');
define('MODULE_SHIPPING_GLS_COUNTRIES_5_DESC' , 'Kommagetrennte Liste zweistelliger ISO L&auml;nderk&uuml;rzel, die Teil der Zone 5 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_GLS_COST_5_TITLE' , 'GLS Zone 5 Versandkostentabelle');
define('MODULE_SHIPPING_GLS_COST_5_DESC' , 'Versandkosten f&uuml;r L&auml;nder in Zone 1 basierend auf einer  Gewichtsangabe (von-bis) der Bestellung. Beispiel: 0-3:8.50,3-7:10.50,usw. Gewichte gr&ouml;&szlig;er als 0 und kleiner gleich 3 w&uuml;rden 8.50 f&uuml;r Zone 1 L&auml;nder betragen.');

define('MODULE_SHIPPING_GLS_COUNTRIES_6_TITLE' , 'GLS Zone 6 L&auml;nder');
define('MODULE_SHIPPING_GLS_COUNTRIES_6_DESC' , 'Kommagetrennte Liste zweistelliger ISO L&auml;nderk&uuml;rzel, die Teil der Zone 6 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_GLS_COST_6_TITLE' , 'GLS Zone 6 Versandkostentabelle');
define('MODULE_SHIPPING_GLS_COST_6_DESC' , 'Versandkosten f&uuml;r L&auml;nder in Zone 1 basierend auf einer  Gewichtsangabe (von-bis) der Bestellung. Beispiel: 0-3:8.50,3-7:10.50,usw. Gewichte gr&ouml;&szlig;er als 0 und kleiner gleich 3 w&uuml;rden 8.50 f&uuml;r Zone 1 L&auml;nder betragen.');

define('MODULE_SHIPPING_GLS_POSTCODE_TITLE' , 'GLS Inselzuschlag - Postleitzahlen');
define('MODULE_SHIPPING_GLS_POSTCODE_DESC' , 'Postleitzahlengebiete');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_TITLE' , 'GLS Inselzuschlag - Kosten');
define('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST_DESC' , 'Inselzuschlag: Tragen Sie hier ein, wieviel auf die Versandkosten aufgeschlagen werden soll, wenn die Lieferadresse auf einer Deutschen Insel liegt');
?>