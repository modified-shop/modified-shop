<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: ot_sofort.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

$num = 3;

define('MODULE_ORDER_TOTAL_SOFORT_TITLE', 'sofort.com discount module');
define('MODULE_ORDER_TOTAL_SOFORT_DESCRIPTION', 'Discount for payment by sofort.com');

define('MODULE_ORDER_TOTAL_SOFORT_STATUS_TITLE', 'Show discount');
define('MODULE_ORDER_TOTAL_SOFORT_STATUS_DESC', 'Do you want to turn on the payment discount?');

define('MODULE_ORDER_TOTAL_SOFORT_SORT_ORDER_TITLE', 'sort sequence');
define('MODULE_ORDER_TOTAL_SOFORT_SORT_ORDER_DESC', 'Order of display. Smallest number will show first.');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SU_TITLE', 'Discounts for SOFORT Banking');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SU_DESC', 'Discount (minimum value: percentage&fixed amount)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SL_TITLE', 'Discounts for SOFORT Lastschrift');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SL_DESC', 'Discount (minimum value: percentage&fixed amount)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SR_TITLE', 'Discounts for Rechnung by SOFORT');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SR_DESC', 'Discount (minimum value: percentage&fixed amount)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SV_TITLE', 'Discounts for Vorkasse by SOFORT');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_SV_DESC', 'Discount (minimum value: percentage&fixed amount)');

define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_LS_TITLE', 'Discounts for Lastschrift by SOFORT');
define('MODULE_ORDER_TOTAL_SOFORT_PERCENTAGE_LS_DESC', 'Discount (minimum value: percentage&fixed amount)');

define('MODULE_ORDER_TOTAL_SOFORT_INC_SHIPPING_TITLE', 'Including shipping');
define('MODULE_ORDER_TOTAL_SOFORT_INC_SHIPPING_DESC', 'Shipping costs are calculated with discount');

define('MODULE_ORDER_TOTAL_SOFORT_INC_TAX_TITLE', 'Inclusive Ust');
define('MODULE_ORDER_TOTAL_SOFORT_INC_TAX_DESC', 'Ust with discount');

define('MODULE_ORDER_TOTAL_SOFORT_CALC_TAX_TITLE', 'Sales tax calculation');
define('MODULE_ORDER_TOTAL_SOFORT_CALC_TAX_DESC', 're-calculate the tax amount');

define('MODULE_ORDER_TOTAL_SOFORT_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_ORDER_TOTAL_SOFORT_ALLOWED_DESC' , 'Please enter <b>einzeln</b> the zones, which should be allowed for this module. (eg allow AT, DE (if empty, all zones))');

define('MODULE_ORDER_TOTAL_SOFORT_DISCOUNT', 'discount');
define('MODULE_ORDER_TOTAL_SOFORT_FEE', 'extra charge');

define('MODULE_ORDER_TOTAL_SOFORT_TAX_CLASS_TITLE','tax class');
define('MODULE_ORDER_TOTAL_SOFORT_TAX_CLASS_DESC','The tax class is irrelevant and only serves to prevent an error message.');

define('MODULE_ORDER_TOTAL_SOFORT_BREAK_TITLE','Multiple calculation');
define('MODULE_ORDER_TOTAL_SOFORT_BREAK_DESC','Should multiple calculations be possible? If not, it is cancelled after the first suitable discount.');
?>