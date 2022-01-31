<?php
/* --------------------------------------------------------------
   $Id: countries.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(countries.php,v 1.8 2002/01/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (countries.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE',	'Shop online/offline');
define('HEADING_SUB_TITLE',	'Configuration');
define('BOX_SHOP_OFFLINE', 'Shop online/offline - all languages');
define('SETTINGS_OFFLINE', 'Shop offline <br /><span class="col-red">(Only with Admin access data via the URL <a href="'. HTTP_SERVER.DIR_WS_CATALOG.'login_admin.php" target="_blank"><span class="col-red">'. HTTP_SERVER.DIR_WS_CATALOG.'login_admin.php</span></a>)</span>');
define('SETTINGS_OFFLINE_MSG', 'Offline message');

define ('SHOP_OFFLINE_ALLOWED_CUSTOMERS_GROUPS_TXT', '<b>Allowable customer groups: </b> <br /> (for these groups of customers the shop is still visible)');
define ('SHOP_OFFLINE_ALLOWED_CUSTOMERS_EMAILS_TXT', '<b>Allowable email addresses (comma separated): </b> <br /> (for customers with these email addresses is the shop still visible)');
?>