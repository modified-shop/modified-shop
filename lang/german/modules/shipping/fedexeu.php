<?php
/* -----------------------------------------------------------------------------------------
   $Id: fedexeu.php 5123 2013-07-18 11:49:11Z Tomcraft $

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



define('MODULE_SHIPPING_FEDEXEU_TEXT_TITLE', 'FedEx Express Europa');
define('MODULE_SHIPPING_FEDEXEU_TEXT_DESCRIPTION', 'FedEx Express Europa');
define('MODULE_SHIPPING_FEDEXEU_TEXT_WAY', 'Versand nach');
define('MODULE_SHIPPING_FEDEXEU_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_FEDEXEU_INVALID_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('MODULE_SHIPPING_FEDEXEU_UNDEFINED_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

define('MODULE_SHIPPING_FEDEXEU_STATUS_TITLE' , 'FedEx Express Europe');
define('MODULE_SHIPPING_FEDEXEU_STATUS_DESC' , 'Wollen Sie den Versand durch FedEx Express Europa anbieten?');
define('MODULE_SHIPPING_FEDEXEU_HANDLING_TITLE' , 'Handling Geb&uuml;hr');
define('MODULE_SHIPPING_FEDEXEU_HANDLING_DESC' , 'Bearbeitungsgeb&uuml;hr f&uuml;r diese Versandart in Euro');
define('MODULE_SHIPPING_FEDEXEU_TAX_CLASS_TITLE' , 'Steuersatz');
define('MODULE_SHIPPING_FEDEXEU_TAX_CLASS_DESC' , 'W&auml;hlen Sie den MwSt.-Satz f&uuml;r diese Versandart aus.');
define('MODULE_SHIPPING_FEDEXEU_ZONE_TITLE' , 'Versand Zone');
define('MODULE_SHIPPING_FEDEXEU_ZONE_DESC' , 'Wenn Sie eine Zone ausw&auml;hlen, wird diese Versandart nur in dieser Zone angeboten.');
define('MODULE_SHIPPING_FEDEXEU_SORT_ORDER_TITLE' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_FEDEXEU_SORT_ORDER_DESC' , 'Niedrigste wird zuerst angezeigt.');
define('MODULE_SHIPPING_FEDEXEU_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_FEDEXEU_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. z.B. AT,DE');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_1_TITLE' , 'Europazone 1 L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_1_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 1 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_1_TITLE' , 'Tariftabelle f&uuml;r Zone 1 bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_1_DESC' , 'Tarif Tabelle f&uuml;r die Zone 1, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_1_TITLE' , 'Tariftabelle f&uuml;r Zone 1 bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_1_DESC' , 'Tarif Tabelle f&uuml;r die Zone 1, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_1_TITLE' , 'Tariftabelle f&uuml;r Zone 1 bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_1_DESC' , 'Tarif Tabelle f&uuml;r die Zone 1, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_1_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_1_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_1_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_1_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_1_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_1_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_2_TITLE' , 'Europazone 2 L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_2_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 2 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_2_TITLE' , 'Tariftabelle f&uuml;r Zone 2 bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_2_DESC' , 'Tarif Tabelle f&uuml;r die Zone 2, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_2_TITLE' , 'Tariftabelle f&uuml;r Zone 2 bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_2_DESC' , 'Tarif Tabelle f&uuml;r die Zone 2, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_2_TITLE' , 'Tariftabelle f&uuml;r Zone 2 bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_2_DESC' , 'Tarif Tabelle f&uuml;r die Zone 2, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_2_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_2_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_2_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_2_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_2_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_2_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_3_TITLE' , 'Europazone 3 L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_3_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone 3 sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_3_TITLE' , 'Tariftabelle f&uuml;r Zone 3 bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_3_DESC' , 'Tarif Tabelle f&uuml;r die Zone 3, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_3_TITLE' , 'Tariftabelle f&uuml;r Zone 3 bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_3_DESC' , 'Tarif Tabelle f&uuml;r die Zone 3, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_3_TITLE' , 'Tariftabelle f&uuml;r Zone 3 bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_3_DESC' , 'Tarif Tabelle f&uuml;r die Zone 3, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_3_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_3_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_3_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_3_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_3_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_3_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_4_TITLE' , 'Weltzone A L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_4_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone A sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_4_TITLE' , 'Tariftabelle f&uuml;r Zone A bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_4_DESC' , 'Tarif Tabelle f&uuml;r die Zone A, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_4_TITLE' , 'Tariftabelle f&uuml;r Zone A bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_4_DESC' , 'Tarif Tabelle f&uuml;r die Zone A, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_4_TITLE' , 'Tariftabelle f&uuml;r Zone A bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_4_DESC' , 'Tarif Tabelle f&uuml;r die Zone A, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_4_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_4_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_4_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_4_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_4_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_4_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_5_TITLE' , 'Weltzone B L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_5_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone B sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_5_TITLE' , 'Tariftabelle f&uuml;r Zone B bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_5_DESC' , 'Tarif Tabelle f&uuml;r die Zone B, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_5_TITLE' , 'Tariftabelle f&uuml;r Zone B bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_5_DESC' , 'Tarif Tabelle f&uuml;r die Zone B, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_5_TITLE' , 'Tariftabelle f&uuml;r Zone B bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_5_DESC' , 'Tarif Tabelle f&uuml;r die Zone B, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_5_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_5_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_5_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_5_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_5_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_5_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_6_TITLE' , 'Weltzone C L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_6_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone C sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_6_TITLE' , 'Tariftabelle f&uuml;r Zone C bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_6_DESC' , 'Tarif Tabelle f&uuml;r die Zone C, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_6_TITLE' , 'Tariftabelle f&uuml;r Zone C bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_6_DESC' , 'Tarif Tabelle f&uuml;r die Zone C, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_6_TITLE' , 'Tariftabelle f&uuml;r Zone C bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_6_DESC' , 'Tarif Tabelle f&uuml;r die Zone C, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_6_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_6_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_6_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_6_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_6_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_6_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_7_TITLE' , 'Weltzone D L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_7_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone D sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_7_TITLE' , 'Tariftabelle f&uuml;r Zone D bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_7_DESC' , 'Tarif Tabelle f&uuml;r die Zone D, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_7_TITLE' , 'Tariftabelle f&uuml;r Zone D bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_7_DESC' , 'Tarif Tabelle f&uuml;r die Zone D, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_7_TITLE' , 'Tariftabelle f&uuml;r Zone D bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_7_DESC' , 'Tarif Tabelle f&uuml;r die Zone D, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_7_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_7_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_7_TITLE' , 'Erh&ouml;hungszuschlag bis 40 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_7_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_7_TITLE' , 'Erh&ouml;hungszuschlag bis 70 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_7_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');

define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_8_TITLE' , 'Weltzone E L&auml;nder');
define('MODULE_SHIPPING_FEDEXEU_COUNTRIES_8_DESC' , 'Durch Komma getrennte Liste der L&auml;nder als zwei Zeichen ISO-Code Landeskennzahlen, die Teil der Zone E sind (WORLD eintragen f&uuml;r den Rest der Welt.).');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_8_TITLE' , 'Tariftabelle f&uuml;r Zone E bis 2.50 kg PAK');
define('MODULE_SHIPPING_FEDEXEU_COST_PAK_8_DESC' , 'Tarif Tabelle f&uuml;r die Zone E, basierend auf <b>\'PAK\'</b> bis 2.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_8_TITLE' , 'Tariftabelle f&uuml;r Zone E bis 0.50 kg ENV');
define('MODULE_SHIPPING_FEDEXEU_COST_ENV_8_DESC' , 'Tarif Tabelle f&uuml;r die Zone E, basierend auf <b>\'ENV\'</b> bis 60 Seiten im Format DIN A4 & 0.50 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_8_TITLE' , 'Tariftabelle f&uuml;r Zone E bis 10 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_COST_BOX_8_DESC' , 'Tarif Tabelle f&uuml;r die Zone E, basierend auf <b>\'BOX\'</b> bis 10 kg Versandgewicht.');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_8_TITLE' , 'Erh&ouml;hungszuschlag bis 20 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_20_8_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_8_TITLE' , 'Erh&ouml;hungszuschlag bis 30 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_40_8_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_8_TITLE' , 'Erh&ouml;hungszuschlag bis 50 kg BOX');
define('MODULE_SHIPPING_FEDEXEU_STEP_BOX_70_8_DESC' , 'Erh&ouml;hungszuschlag pro &uuml;bersteigende 0,50 kg in EUR');
?>