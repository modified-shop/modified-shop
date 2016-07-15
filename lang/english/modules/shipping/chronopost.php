<?php
/* -----------------------------------------------------------------------------------------
   $Id: chronopost.php 5121 2013-07-18 11:38:19Z Tomcraft $   

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
   


define('MODULE_SHIPPING_CHRONOPOST_TEXT_TITLE', 'Chronopost Zone Rates');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_DESCRIPTION', 'Chronopost Zone Based Rates');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_WAY', 'Shipping to');
define('MODULE_SHIPPING_CHRONOPOST_TEXT_UNITS', 'kg');
define('MODULE_SHIPPING_CHRONOPOST_INVALID_ZONE', 'Chronopost No shipping available to the selected country');
define('MODULE_SHIPPING_CHRONOPOST_UNDEFINED_RATE', 'Chronopost The shipping rate cannot be determined at this time');

define('MODULE_SHIPPING_CHRONOPOST_STATUS_TITLE' , 'Activer Chronopost');
define('MODULE_SHIPPING_CHRONOPOST_STATUS_DESC' , 'Vous souhaitez activer l\'exp&eacute;dition via Chronopost? (True, False)');
define('MODULE_SHIPPING_CHRONOPOST_HANDLING_TITLE' , 'Les tarifs');
define('MODULE_SHIPPING_CHRONOPOST_HANDLING_DESC' , 'Les tarifs pour l\'exp&eacute;dition via chronopost');
define('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS_TITLE' , 'Tax Class');
define('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS_DESC' , 'Use the following tax class on the shipping fee.');
define('MODULE_SHIPPING_CHRONOPOST_ZONE_TITLE' , 'Shipping Zone');
define('MODULE_SHIPPING_CHRONOPOST_ZONE_DESC' , 'If a zone is selected, only enable this shipping method for that zone.');
define('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER_TITLE' , 'Sort Order');
define('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER_DESC' , 'Sort order of display.');
define('MODULE_SHIPPING_CHRONOPOST_ALLOWED_TITLE' , 'Einzelne Versandzonen');
define('MODULE_SHIPPING_CHRONOPOST_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. z.B. AT,DE');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_1_TITLE' , 'Chronopost Zone 1 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_1_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_1_TITLE' , 'Chronopost Zone 1 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_1_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 1. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_2_TITLE' , 'Chronopost Zone 2 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_2_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_2_TITLE' , 'Chronopost Zone 2 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_2_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 2. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_3_TITLE' , 'Chronopost Zone 3 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_3_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_3_TITLE' , 'Chronopost Zone 3 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_3_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 3. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_4_TITLE' , 'Chronopost Zone 4 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_4_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_4_TITLE' , 'Chronopost Zone 4 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_4_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 4. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_5_TITLE' , 'Chronopost Zone 5 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_5_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_5_TITLE' , 'Chronopost Zone 5 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_5_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 5. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_6_TITLE' , 'Chronopost Zone 6 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_6_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_6_TITLE' , 'Chronopost Zone 6 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_6_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 6. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_7_TITLE' , 'Chronopost Zone 7 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_7_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_7_TITLE' , 'Chronopost Zone 7 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_7_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 7. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_8_TITLE' , 'Chronopost Zone 8 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_8_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_8_TITLE' , 'Chronopost Zone 8 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_8_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 8. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_9_TITLE' , 'Chronopost Zone 9 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_9_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_9_TITLE' , 'Chronopost Zone 9 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_9_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 9. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');

define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_10_TITLE' , 'Chronopost Zone 10 (pays)');
define('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_10_DESC' , 'Ins&eacute;rer une virgule entre 2 codes ISO de pays qui font partie de la m&ecirc;me zone (Enter WORLD for the rest of the world.).');
define('MODULE_SHIPPING_CHRONOPOST_COST_10_TITLE' , 'Chronopost Zone 10 (poids:tarifs)');
define('MODULE_SHIPPING_CHRONOPOST_COST_10_DESC' , 'Tarifs Chronopost pour les destinations de la Zone 10. Principe : une fourchette de poids (grammes) suivie du tarif (euros TTC). Exemple: 0-2000:28.71,2000-5000:34.38,... Les colis pesant moins de 2kg seront factur&eacute;s 28,71 EUR TTC pour les destinations de');
?>