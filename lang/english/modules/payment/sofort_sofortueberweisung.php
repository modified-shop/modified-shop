<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortueberweisung.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SU_TEXT_TITLE', 'SOFORT Banking <br /> <img src="https://images.sofort.com/en/su/logo_90x30.png" alt="SOFORT Banking"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_TEXT_TITLE', 'SOFORT Banking');
define('MODULE_PAYMENT_SOFORT_SU_KS_TEXT_TITLE', 'SOFORT Banking with customer protection');
define('MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION', 'SOFORT Banking is the free of charge, TÜV certified payment method by SOFORT AG.');


define('MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '     <table border="0" cellspacing="0" cellpadding="0">      <tr>        <td valign="bottom">
	<a onclick="javascript:window.open(\'https://images.sofort.com/en/su/landing.php\',\'customer information\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">
		{{image}}
	</a>
	</td>      </tr>      <tr> <td class="main">{{text}}</td>      </tr>      </table>');

define('MODULE_PAYMENT_SOFORT_SU_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'SOFORT Banking');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_TEXT', '<ul><li>Payment system with data protection certified by TÜV </li><li>No registration required</li><li>Immediate shipping of stock goods</li><li>Please keep your online banking login data ready</li></ul>');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_TEXT_KS', '<ul><li>If paying with SOFORT Banking you enjoy buyer protection! [[link_beginn]]More info[[link_end]]</li><li>Payment system with TÜV-certified privacy policy</li><li>No registration needed</li><li>Goods/service will be shipped immediately, if available</li><li>Please keep your online banking data ready</li></ul>');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_SU_CHECKOUT_INFOLINK_KS', 'https://www.sofort-bank.com/de/kaeuferbereich/kaeuferschutz');
define('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_PAYMENT_SOFORT_SOFORTUEBERWEISUNG_ALLOWED_DESC', 'Please enter <b>einzeln</b> the zones, which should be allowed for this module. (eg allow AT, DE (if empty, all zones))');
define('MODULE_PAYMENT_SOFORT_SU_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SU_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);
define('MODULE_PAYMENT_SOFORT_SU_STATUS_TITLE', 'Activate sofort.de module');
define('MODULE_PAYMENT_SOFORT_SU_STATUS_DESC', 'Activates/deactivates the complete module');

define('MODULE_PAYMENT_SOFORT_SU_SORT_ORDER_TITLE', 'sort sequence');
define('MODULE_PAYMENT_SOFORT_SU_SORT_ORDER_DESC', 'Order of display. Smallest number will show first.');
define('MODULE_PAYMENT_SOFORT_SU_KS_STATUS_TITLE', 'Customer protection activated');
define('MODULE_PAYMENT_SOFORT_SU_KS_STATUS_DESC', 'Activate customer protection for SOFORT Banking');

define('MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID_TITLE', 'Confirmed order status');
define('MODULE_PAYMENT_SOFORT_SU_ORDER_STATUS_ID_DESC', 'Confirmed order status<br />Order status after successfully completing a transaction.');

define('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_TITLE', 'recommend payment method');
define('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_DESC', '"Mark this payment method as "recommended payment method". On the payment selection page a note will be displayed right behind the payment method."');
define('MODULE_PAYMENT_SOFORT_SU_RECOMMENDED_PAYMENT_TEXT', '(recommend payment method)');

define('MODULE_PAYMENT_SOFORT_SU_TEXT_ERROR_MESSAGE', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SU', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_STATUS_CONFIRM_INVOICE', 'Order with {{paymentMethodStr}} successfully submitted. Transaction-ID: {{tId}} {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_SU_LOSS', 'Up till now it has not been possible to confirm the payment. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_DEBIT_RETURNED', 'There is a chargeback linked to this transaction. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_REFUNDED', 'Invoice amount will be refunded {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_INVOICE_CANCELED', 'The invoice has been cancelled by the merchant {{time}}');

?>