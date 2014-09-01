<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortlastschrift.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SL_TEXT_TITLE', 'SOFORT Lastschrift <br /><img src="https://images.sofort.com/en/sl/logo_90x30.png" alt="logo SOFORT Lastschrift"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_TEXT_TITLE', 'SOFORT Lastschrift (direct debit + online banking precheck)');
define('MODULE_PAYMENT_SOFORT_SL_TEXT_ERROR_MESSAGE', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SL', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_SL_CHECKOUT_TEXT', '<ul><li>Payment system with TÜV-certified Privacy Policy</li><li>No registration required</li><li>Goods / services can be dispatched IMMEDIATELY</li><li>Please have your online banking PIN ready</li></ul>');
define('MODULE_PAYMENT_SOFORT_SL_STATUS_TITLE', 'Activate sofort.de module');
define('MODULE_PAYMENT_SOFORT_SL_STATUS_DESC', 'Activates/deactivates the complete module');
define('MODULE_PAYMENT_SOFORT_SL_SORT_ORDER_TITLE', 'sort sequence');
define('MODULE_PAYMENT_SOFORT_SL_SORT_ORDER_DESC', 'Order of display. Smallest number will show first.');
define('MODULE_PAYMENT_SOFORT_SL_TEXT_DESCRIPTION', 'SOFORT Lastschrift is an advanced payment system based on one of the most popular German payment methods, ELV.');
define('MODULE_PAYMENT_SOFORT_SL_TEXT_ERROR_HEADING', 'Error while processing the order.');

define('MODULE_PAYMENT_SOFORT_SL_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '<table border="0" cellspacing="0" cellpadding="0"><tr><td valign="bottom">
<a onclick="javascript:window.open(\'https://images.sofort.com/en/sl/landing.php\',\'customer information\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">
{{image}}
</a></td><td rowspan="2" width="30px">&nbsp;</td><td rowspan="2">
</td>      </tr>      <tr> <td class="main">{{text}}</td>      </tr>      </table>');
define('MODULE_PAYMENT_SOFORT_SL_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'SOFORT Lastschrift');
define('MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_PAYMENT_SOFORT_SOFORTLASTSCHRIFT_ALLOWED_DESC', 'Please enter <b>einzeln</b> the zones, which should be allowed for this module. (eg allow AT, DE (if empty, all zones))');
define('MODULE_PAYMENT_SOFORT_SL_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SL_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);

define('MODULE_PAYMENT_SOFORT_SL_ORDER_STATUS_ID_TITLE', 'Confirmed order status');
define('MODULE_PAYMENT_SOFORT_SL_ORDER_STATUS_ID_DESC', 'Confirmed order status<br />Order status after successfully completing a transaction.');

define('MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT_TITLE', 'recommend payment method');
define('MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT_DESC', '"Mark this payment method as "recommended payment method". On the payment selection page a note will be displayed right behind the payment method."');
define('MODULE_PAYMENT_SOFORT_SL_RECOMMENDED_PAYMENT_TEXT', '(recommend payment method)');

?>