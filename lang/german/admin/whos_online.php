<?php
/* --------------------------------------------------------------
   $Id: whos_online.php 3072 2012-06-18 15:01:13Z hhacker $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(whos_online.php,v 1.7 2002/03/30); www.oscommerce.com 
   (c) 2003	 nextcommerce (whos_online.php,v 1.4 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Wer ist Online');

define('TABLE_HEADING_ONLINE', 'Online');
define('TABLE_HEADING_CUSTOMER_ID', 'ID');
define('TABLE_HEADING_FULL_NAME', 'Name');
define('TABLE_HEADING_IP_ADDRESS', 'IP Adresse');
define('TABLE_HEADING_COUNTRY', 'Land');
define('TABLE_HEADING_ENTRY_TIME', 'Startzeit');
define('TABLE_HEADING_LAST_CLICK', 'Letzter Klick');
define('TABLE_HEADING_LAST_PAGE_URL', 'Letzte URL');
define('TABLE_HEADING_HTTP_REFERER', 'HTTP Referer');
define('TABLE_HEADING_ACTION', 'Aktion');
define('TABLE_HEADING_SHOPPING_CART', 'Warenkorb');
define('TEXT_SHOPPING_CART_SUBTOTAL', 'Insgesamt');
define('TEXT_NUMBER_OF_CUSTOMERS', 'Es sind zur Zeit %s Kunden online');
define('TEXT_EMPTY_CART', 'Warenkorb des Kunden ist leer');
define('TEXT_SESSION_IS_ENCRYPTED', '<hr><b>HINWEIS</b>:<br />Der Warenkorbinhalt kann nicht angezeigt werden.<br />Die Session ist mit Suhosin verschl&uuml;sselt<br />(suhosin.session.encrypt = On)<br />Zum Deaktivieren der Verschl&uuml;sselung wenden Sie sich an Ihren Provider.');
?>