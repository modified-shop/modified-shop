<?php
/* -----------------------------------------------------------------------------------------
   $Id: cod.php 998 2005-07-07 14:18:20Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.7 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (cod.php,v 1.5 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
define('MODULE_PAYMENT_TYPE_PERMISSION', 'cod');
define('MODULE_PAYMENT_COD_TEXT_TITLE', 'Cash on Delivery');
define('MODULE_PAYMENT_COD_TEXT_DESCRIPTION', 'Payment via Cash on Delivery');
define('MODULE_PAYMENT_COD_TEXT_INFO','Please note that an additional 2 euros delivery fee must be paid to the delivery site.');
define('MODULE_PAYMENT_COD_ZONE_TITLE' , 'Payment Zone');
define('MODULE_PAYMENT_COD_ZONE_DESC' , 'If a zone is selected, only enable this payment method for that zone.');
define('MODULE_PAYMENT_COD_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_PAYMENT_COD_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('MODULE_PAYMENT_COD_STATUS_TITLE' , 'Enable Cash On Delivery Module');
define('MODULE_PAYMENT_COD_STATUS_DESC' , 'Do you want to accept Cash On Delevery payments?');
define('MODULE_PAYMENT_COD_SORT_ORDER_TITLE' , 'Sort order of display');
define('MODULE_PAYMENT_COD_SORT_ORDER_DESC' , 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_COD_ORDER_STATUS_ID_TITLE' , 'Set Order Status');
define('MODULE_PAYMENT_COD_ORDER_STATUS_ID_DESC' , 'Set the status of orders made with this payment module to this value');
define('MODULE_PAYMENT_COD_LIMIT_ALLOWED_TITLE', 'Maximum amount');
define('MODULE_PAYMENT_COD_LIMIT_ALLOWED_DESC', 'From which amount shall cod not be allowed?<br />The entered value will be compared with the subtotal which will be rounded.<br />This means, that only the pure merchandise value will be considered, without shipping costs and any possible additional fees.');
define('MODULE_PAYMENT_COD_DISPLAY_INFO_TITLE', 'Display in checkout');
define('MODULE_PAYMENT_COD_DISPLAY_INFO_DESC', 'Dispaly a note about additional costs in the checkout?');
define('MODULE_PAYMENT_COD_DISPLAY_INFO_TEXT', 'Please note that an additional 2 euros delivery fee must be paid to the delivery site.<br/>');
?>