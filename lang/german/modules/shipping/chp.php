<?php
/* -----------------------------------------------------------------------------------------
   $Id: chp.php 5123 2013-07-18 11:49:11Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(chp.php,v 1.01 2003/02/18 03:30:00); www.oscommerce.com 
   (c) 2003	 nextcommerce (chp.php,v 1.4 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   swiss_post_1.02       	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


define('MODULE_SHIPPING_CHP_TEXT_TITLE', 'Schweizerische Post');
define('MODULE_SHIPPING_CHP_TEXT_DESCRIPTION', 'Die Schweizerische Post');
define('MODULE_SHIPPING_CHP_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_CHP_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_CHP_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_CHP_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

define('MODULE_SHIPPING_CHP_STATUS_TITLE' , 'Schweizerische Post');
define('MODULE_SHIPPING_CHP_STATUS_DESC' , 'Wollen Sie den Versand &uuml;ber die schweizerische Post anbieten?');
define('MODULE_SHIPPING_CHP_HANDLING_TITLE' , 'Handling Fee');
define('MODULE_SHIPPING_CHP_HANDLING_DESC' , 'Bearbeitungsgeb&uuml;hr f&uuml;r diese Versandart in CHF');
define('MODULE_SHIPPING_CHP_TAX_CLASS_TITLE' , 'Steuersatz');
define('MODULE_SHIPPING_CHP_TAX_CLASS_DESC' , 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_CHP_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_CHP_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_CHP_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_CHP_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_CHP_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_CHP_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. z.B. AT,DE');

define('MODULE_SHIPPING_CHP_COUNTRIES_1_TITLE' , 'Tarifzone 0 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_1_DESC' , 'Inlandszone');
define('MODULE_SHIPPING_CHP_COST_ECO_1_TITLE' , 'Tariftabelle f&uuml;r Zone 0 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_1_DESC' , 'Tarif Tabelle f&uuml;r die Inlandszone, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_1_TITLE' , 'Tariftabelle f&uuml;r Zone 0 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_1_DESC' , 'Tarif Tabelle f&uuml;r die Inlandszone, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');

define('MODULE_SHIPPING_CHP_COUNTRIES_2_TITLE' , 'Tarifzone 1 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_2_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 1 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_CHP_COST_ECO_2_TITLE' , 'Tariftabelle f&uuml;r Zone 1 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_2_DESC' , 'Tarif Tabelle f&uuml;r die Zone 1, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_2_TITLE' , 'Tariftabelle f&uuml;r Zone 1 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_2_DESC' , 'Tarif Tabelle f&uuml;r die Zone 1, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_URG_2_TITLE' , 'Tariftabelle f&uuml;r Zone 1 bis 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_2_DESC' , 'Tarif Tabelle f&uuml;r die Zone 1, basiered auf <b>\'URG\'</b> bis 30 kg Versandgewicht.');

define('MODULE_SHIPPING_CHP_COUNTRIES_3_TITLE' , 'Tarifzone 2 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_3_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 2 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_CHP_COST_ECO_3_TITLE' , 'Tariftabelle f&uuml;r Zone 2 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_3_DESC' , 'Tarif Tabelle f&uuml;r die Zone 2, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_3_TITLE' , 'Tariftabelle f&uuml;r Zone 2 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_3_DESC' , 'Tarif Tabelle f&uuml;r die Zone 2, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_URG_3_TITLE' , 'Tariftabelle f&uuml;r Zone 2 bis 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_3_DESC' , 'Tarif Tabelle f&uuml;r die Zone 2, basiered auf <b>\'URG\'</b> bis 30 kg Versandgewicht.');

define('MODULE_SHIPPING_CHP_COUNTRIES_4_TITLE' , 'Tarifzone 3 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_4_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 3 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_CHP_COST_ECO_4_TITLE' , 'Tariftabelle f&uuml;r Zone 3 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_4_DESC' , 'Tarif Tabelle f&uuml;r die Zone 3, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_4_TITLE' , 'Tariftabelle f&uuml;r Zone 3 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_4_DESC' , 'Tarif Tabelle f&uuml;r die Zone 3, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_URG_4_TITLE' , 'Tariftabelle f&uuml;r Zone 3 bis 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_4_DESC' , 'Tarif Tabelle f&uuml;r die Zone 3, basiered auf <b>\'URG\'</b> bis 30 kg Versandgewicht.');

define('MODULE_SHIPPING_CHP_COUNTRIES_5_TITLE' , 'Tarifzone 4 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_5_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 4 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_CHP_COST_ECO_5_TITLE' , 'Tariftabelle f&uuml;r Zone 4 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_5_DESC' , 'Tarif Tabelle f&uuml;r die Zone 4, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_5_TITLE' , 'Tariftabelle f&uuml;r Zone 4 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_5_DESC' , 'Tarif Tabelle f&uuml;r die Zone 4, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_URG_5_TITLE' , 'Tariftabelle f&uuml;r Zone 4 bis 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_5_DESC' , 'Tarif Tabelle f&uuml;r die Zone 4, basiered auf <b>\'URG\'</b> bis 30 kg Versandgewicht.');

define('MODULE_SHIPPING_CHP_COUNTRIES_6_TITLE' , 'Tarifzone 4 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_6_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 4 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_CHP_COST_ECO_6_TITLE' , 'Tariftabelle f&uuml;r Zone 4 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_6_DESC' , 'Tarif Tabelle f&uuml;r die Zone 4, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_6_TITLE' , 'Tariftabelle f&uuml;r Zone 4 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_6_DESC' , 'Tarif Tabelle f&uuml;r die Zone 4, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_URG_6_TITLE' , 'Tariftabelle f&uuml;r Zone 4 bis 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_6_DESC' , 'Tarif Tabelle f&uuml;r die Zone 4, basiered auf <b>\'URG\'</b> bis 30 kg Versandgewicht.');

define('MODULE_SHIPPING_CHP_COUNTRIES_7_TITLE' , 'Tarifzone 5 L&auml;nder');
define('MODULE_SHIPPING_CHP_COUNTRIES_7_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 5 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_CHP_COST_ECO_7_TITLE' , 'Tariftabelle f&uuml;r Zone 5 bis 30 kg ECO');
define('MODULE_SHIPPING_CHP_COST_ECO_7_DESC' , 'Tarif Tabelle f&uuml;r die Zone 5, basiered auf <b>\'ECO\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_PRI_7_TITLE' , 'Tariftabelle f&uuml;r Zone 5 bis 30 kg PRI');
define('MODULE_SHIPPING_CHP_COST_PRI_7_DESC' , 'Tarif Tabelle f&uuml;r die Zone 5, basiered auf <b>\'PRI\'</b> bis 30 kg Versandgewicht.');
define('MODULE_SHIPPING_CHP_COST_URG_7_TITLE' , 'Tariftabelle f&uuml;r Zone 5 bis 30 kg URG');
define('MODULE_SHIPPING_CHP_COST_URG_7_DESC' , 'Tarif Tabelle f&uuml;r die Zone 5, basiered auf <b>\'URG\'</b> bis 30 kg Versandgewicht.');
?>