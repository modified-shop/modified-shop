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

define('HEADING_TITLE', 'Who\'s Online');

define('TABLE_HEADING_ONLINE', 'Online');
define('TABLE_HEADING_CUSTOMER_ID', 'ID');
define('TABLE_HEADING_FULL_NAME', 'Full Name');
define('TABLE_HEADING_IP_ADDRESS', 'IP Adress');
define('TABLE_HEADING_COUNTRY', 'Country');
define('TABLE_HEADING_ENTRY_TIME', 'Entry Time');
define('TABLE_HEADING_LAST_CLICK', 'Last Click');
define('TABLE_HEADING_LAST_PAGE_URL', 'Last URL');
define('TABLE_HEADING_HTTP_REFERER', 'HTTP Referer');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_SHOPPING_CART', 'Users Shopping Cart');
define('TEXT_SHOPPING_CART_SUBTOTAL', 'Subtotal');
define('TEXT_NUMBER_OF_CUSTOMERS', 'Currently there are %s customers online');
define('TEXT_EMPTY_CART', 'Users Shopping Cart is empty');
define('TEXT_SESSION_IS_ENCRYPTED', '<hr><b>Note</b>:<br />The basket contents may not be displayed.<br />The session is encrypted with Suhosin<br />(suhosin.session.encrypt = On)<br />To turn off encryption, contact your provider.');
?>