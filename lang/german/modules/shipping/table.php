<?php
/* -----------------------------------------------------------------------------------------
   $Id: table.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(table.php,v 1.6 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (table.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_TABLE_TEXT_TITLE', 'Tabellarische Versandkosten');
define('MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION', 'Tabellarische Versandkosten');
define('MODULE_SHIPPING_TABLE_TEXT_WAY', 'Bester Weg');
define('MODULE_SHIPPING_TABLE_TEXT_WEIGHT', 'Gewicht');
define('MODULE_SHIPPING_TABLE_TEXT_AMOUNT', 'Menge');

define('MODULE_SHIPPING_TABLE_STATUS_TITLE' , 'Tabellarische Versandkosten aktivieren');
define('MODULE_SHIPPING_TABLE_STATUS_DESC' , 'M&ouml;chten Sie Tabellarische Versandkosten anbieten?');
define('MODULE_SHIPPING_TABLE_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_TABLE_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_TABLE_COST_TITLE' , 'Versandkosten');
define('MODULE_SHIPPING_TABLE_COST_DESC' , 'Die Versandkosten basieren auf Gesamtkosten oder Gesamtgewicht der bestellten Waren. Beispiel: 25:5.50,50:8.50,etc.. Bis 25 werden 5.50 verrechnet, dar&uuml;ber bis 50 werden 8.50 verrechnet, etc');
define('MODULE_SHIPPING_TABLE_MODE_TITLE' , 'Versandkosten Methode');
define('MODULE_SHIPPING_TABLE_MODE_DESC' , 'Die Versandkosten basieren auf Gesamtkosten oder Gesamtgewicht der bestellten Waren.');
define('MODULE_SHIPPING_TABLE_HANDLING_TITLE' , 'Handling Geb&uuml;hr');
define('MODULE_SHIPPING_TABLE_HANDLING_DESC' , 'Handling Geb&uuml;hr f&uuml;r diese Versandmethode');
define('MODULE_SHIPPING_TABLE_TAX_CLASS_TITLE' , 'Steuerklasse');
define('MODULE_SHIPPING_TABLE_TAX_CLASS_DESC' , 'Folgende Steuerklasse an Versandkosten anwenden');
define('MODULE_SHIPPING_TABLE_ZONE_TITLE' , 'Versandzone');
define('MODULE_SHIPPING_TABLE_ZONE_DESC' , 'Wenn eine Zone ausgew&auml;hlt ist, wird diese Versandmethode ausschlie&szlig;lich f&uuml;r diese Zone angewendet');
define('MODULE_SHIPPING_TABLE_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_TABLE_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');
?>