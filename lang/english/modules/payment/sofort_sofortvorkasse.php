<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 11:51:22 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortvorkasse.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */
//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_CONDITIONS', '
	<script type="text/javascript">
		function showSvConditions() {
			svOverlay = new sofortOverlay(jQuery(".svOverlay"), "callback/sofort/ressources/scripts/getContent.php", "https://documents.sofort.com/de/sv/privacy_de");
			svOverlay.trigger();
		}
		document.write(\'<a id="svNotice" href="javascript:void(0)" onclick="showSvConditions()">I have read the Privacy Policy.</a>\');
	</script>
	<div style="display:none; z-index: 1001;filter: alpha(opacity=92);filter: progid :DXImageTransform.Microsoft.Alpha(opacity=92);-moz-opacity: .92;-khtml-opacity: 0.92;opacity: 0.92;background-color: black;position: fixed;top: 0px;left: 0px;width: 100%;height: 100%;text-align: center;vertical-align: middle;" class="svOverlay">
		<div class="loader" style="z-index: 1002;position: relative;background-color: #fff;border: 5px solid #C0C0C0;top: 40px;overflow: scroll;padding: 4px;border-radius: 7px;-moz-border-radius: 7px;-webkit-border-radius: 7px;margin: auto;width: 620px;height: 400px;overflow: scroll; overflow-x: hidden;">
			<div class="closeButton" style="position: fixed; top: 54px; background: url(callback/sofort/ressources/images/close.png) right top no-repeat;cursor:pointer;height: 30px;width: 30px;"></div>
			<div class="content"></div>
		</div>
	</div>
	<noscript>
		<a href="https://documents.sofort.com/de/sv/privacy_de" target="_blank">I have read the Privacy Policy.</a>
	</noscript>
');
define('MODULE_PAYMENT_SOFORT_SV_TEXT_TITLE', 'Vorkasse by SOFORT <br /> <img src="https://images.sofort.com/en/sv/logo_90x30.png" alt="Logo Vorkasse by SOFORT"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_TEXT_TITLE', 'Vorkasse (pay in advance)');
define('MODULE_PAYMENT_SOFORT_SV_KS_TEXT_TITLE', 'Payment in advance with consumer protection');
define('MODULE_PAYMENT_SOFORT_SV_TEXT_ERROR_MESSAGE', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SV', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_SV_CHECKOUT_TEXT', '');
define('MODULE_PAYMENT_SOFORT_SV_STATUS_TITLE', 'Activate sofort.de module');
define('MODULE_PAYMENT_SOFORT_SV_STATUS_DESC', 'Activates/deactivates the complete module');
define('MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION', 'Payment in advance with automatic reconcilement.');
define('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_PAYMENT_SOFORT_SOFORTVORKASSE_ALLOWED_DESC', 'Please enter <b>einzeln</b> the zones, which should be allowed for this module. (eg allow AT, DE (if empty, all zones))');
define('MODULE_PAYMENT_SOFORT_SV_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SV_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);
define('MODULE_PAYMENT_SOFORT_SV_SORT_ORDER_TITLE', 'sort sequence');
define('MODULE_PAYMENT_SOFORT_SV_SORT_ORDER_DESC', 'Order of display. Smallest number will show first.');
define('MODULE_PAYMENT_SOFORT_SV_TMP_COMMENT', 'Payment in advance as payment method chosen. Transaction not finished yet.');
define('MODULE_PAYMENT_SOFORT_SV_REASON_2_TITLE','Reason 2');
define('MODULE_PAYMENT_SOFORT_SV_REASON_2_DESC','Following placeholders will be replaced inside the reason (max 27 characters):<br />{{transaction_id}}<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');

define('MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '');



define('MODULE_PAYMENT_SOFORT_SV_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'Payment with Vorkasse by SOFORT: No registration required. You commit the payment yourself at your bank.');

define('MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID_TITLE', 'Confirmed order status');
define('MODULE_PAYMENT_SOFORT_SV_ORDER_STATUS_ID_DESC', 'Confirmed Order <br /> Order after payment.');
define('MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID_TITLE', 'Temporary order status');
define('MODULE_PAYMENT_SOFORT_STATUS_SV_LOSS', 'Up till now the payment could not be confirmed. {{time}}');
define('MODULE_PAYMENT_SOFORT_SV_TMP_STATUS_ID_DESC', 'Order state for non-completed transactions. The order has been created but the transaction has not yet been confirmed by SOFORT AG.');

define('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_TITLE', 'recommend payment method');
define('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_DESC', '"Mark this payment method as "recommended payment method". On the payment selection page a note will be displayed right behind the payment method."');
define('MODULE_PAYMENT_SOFORT_SV_RECOMMENDED_PAYMENT_TEXT', '(recommend payment method)');

define('MODULE_PAYMENT_SOFORT_SV_KS_STATUS_TITLE', 'Customer protection activated');
define('MODULE_PAYMENT_SOFORT_SV_KS_STATUS_DESC', 'Activate customer protection for  Vorkasse by SOFORT');
define('MODULE_PAYMENT_SOFORT_SV_KS_STATUS_TEXT', 'Customer protection activated');

define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HEADING_TEXT', 'bank account');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_TEXT', 'Please use the following account data.:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_HOLDER_TEXT', 'account holder:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_ACCOUNT_NUMBER_TEXT', 'account number:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BANK_CODE_TEXT', 'Bank code number:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_IBAN_TEXT', 'IBAN:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_BIC_TEXT', 'BIC:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_AMOUNT_TEXT', 'Amount:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_1_TEXT', 'reason:');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_2_TEXT', '');
define('MODULE_PAYMENT_SOFORT_SV_CHECKOUT_REASON_HINT','Please be sure to use the stated purpose when transfering the money, so that we can match your payment properly.');


?>