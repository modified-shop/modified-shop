<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_AFTERBUY_TEXT_TITLE', 'Afterbuy');
define('MODULE_AFTERBUY_TEXT_DESCRIPTION', 'Transfers incoming orders to Afterbuy. You will find detailed Afterbuy info here: <a href="https://www.afterbuy.de" target="_blank">https://www.afterbuy.de</a>');

define('MODULE_AFTERBUY_STATUS_TITLE', 'Enable module?');
define('MODULE_AFTERBUY_STATUS_DESC', 'Activate afterbuy module');

define('MODULE_AFTERBUY_PARTNERID_TITLE', 'Partner ID');
define('MODULE_AFTERBUY_PARTNERID_DESC', 'Your Afterbuy Partner ID');

define('MODULE_AFTERBUY_PARTNERPASS_TITLE', 'Partner Password');
define('MODULE_AFTERBUY_PARTNERPASS_DESC', 'Your partner password for Afterbuy XML module');

define('MODULE_AFTERBUY_USERID_TITLE', 'User ID');
define('MODULE_AFTERBUY_USERID_DESC', 'Your Afterbuy user ID');

define('MODULE_AFTERBUY_ORDERSTATUS_TITLE', 'Order Status');
define('MODULE_AFTERBUY_ORDERSTATUS_DESC', 'Order status for exported orders');

define('MODULE_AFTERBUY_DEALERS_TITLE', 'Mark as dealer');
define('MODULE_AFTERBUY_DEALERS_DESC', 'Select the customer groups Afterbuy shall treat as dealers.');

define('MODULE_AFTERBUY_IGNORE_GROUPS_TITLE', 'Ignore customer groups');
define('MODULE_AFTERBUY_IGNORE_GROUPS_DESC', 'Select the customer groups whose orders are not transferred.');

define('MODULE_AFTERBUY_ORDER_MAIL_TITLE', 'Send order confirmation');
define('MODULE_AFTERBUY_ORDER_MAIL_DESC', 'Shall the shop send the order confirmation to the customer? Set to "false" when Afterbuy takes care of that mail. The confirmation is then skipped for successfully transferred orders only, sending it by hand from the admin still works.');
