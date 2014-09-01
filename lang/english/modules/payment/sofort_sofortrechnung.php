<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-13 16:24:37 +0200 (Thu, 13 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_sofortrechnung.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */


//include language-constants used in all Multipay Projects
require_once 'sofort_general.php';

define('MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '');
define('MODULE_PAYMENT_SOFORT_SR_CHECKOUT_CONDITIONS', '
	<script type="text/javascript">
		function showSrConditions() {
			srOverlay = new sofortOverlay(jQuery(".srOverlay"), "callback/sofort/ressources/scripts/getContent.php", "https://documents.sofort.com/de/sr/privacy_de");
			srOverlay.trigger();
		}
		document.write(\'<a id="srNotice" href="javascript:void(0)" onclick="showSrConditions();">I have read the Privacy Policy.</a>\');
	</script>
	
	<div style="display:none; z-index: 1001;filter: alpha(opacity=92);filter: progid :DXImageTransform.Microsoft.Alpha(opacity=92);-moz-opacity: .92;-khtml-opacity: 0.92;opacity: 0.92;background-color: black;position: fixed;top: 0px;left: 0px;width: 100%;height: 100%;text-align: center;vertical-align: middle;" class="srOverlay">
		<div class="loader" style="z-index: 1002;position: relative;background-color: #fff;top: 40px;overflow: scroll;padding: 4px;border-radius: 7px;-moz-border-radius: 7px;-webkit-border-radius: 7px;margin: auto;width: 620px;height: 400px;overflow: scroll; overflow-x: hidden;">
			<div class="closeButton" style="position: fixed; top: 54px; background: url(callback/sofort/ressources/images/close.png) right top no-repeat;cursor:pointer;height: 30px;width: 30px;"></div>
			<div class="content"></div>
		</div>
	</div>
	<noscript>
		<a href="https://documents.sofort.com/de/sr/privacy_de" target="_blank">I have read the Privacy Policy.</a>
	</noscript>
');

define('MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'checkout.sr.description');

define('MODULE_PAYMENT_SOFORT_SR_TEXT_TITLE', 'Rechnung by SOFORT <br /><img src="https://images.sofort.com/en/sr/logo_90x30.png"  alt="Logo Rechnung by SOFORT"/>');
define('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_TEXT_TITLE', 'Kauf auf Rechnung (Purchase on account)');
define('MODULE_PAYMENT_SOFORT_SR_TEXT_ERROR_MESSAGE', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_SR', 'Payment is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_SR_CHECKOUT_TEXT', '');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_CONFIRM_SR', 'Acknowledge this invoice:');
define('MODULE_PAYMENT_SOFORT_SR_STATUS_TITLE', 'Activate sofort.de module');
define('MODULE_PAYMENT_SOFORT_SR_STATUS_DESC', 'Activates/deactivates the complete module');
define('MODULE_PAYMENT_SOFORT_SR_SORT_ORDER_TITLE', 'sort sequence');
define('MODULE_PAYMENT_SOFORT_SR_SORT_ORDER_DESC', 'Order of display. Smallest number will show first.');
define('MODULE_PAYMENT_SOFORT_SR_TEXT_DESCRIPTION', 'Buy on account with consumer protection');
define('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_ALLOWED_TITLE', 'Allowed zones');
define('MODULE_PAYMENT_SOFORT_SOFORTRECHNUNG_ALLOWED_DESC', 'Please enter <b>einzeln</b> the zones, which should be allowed for this module. (eg allow AT, DE (if empty, all zones))');
define('MODULE_PAYMENT_SOFORT_SR_ZONE_TITLE', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE);
define('MODULE_PAYMENT_SOFORT_SR_ZONE_DESC', MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC);
define('MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID_TITLE', 'Confirmed order status');
define('MODULE_PAYMENT_SOFORT_SR_ORDER_STATUS_ID_DESC', 'Order status after the successful and confirmed transaction and approval of invoice by the retailer.');
define('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID_TITLE', 'Unconfirmed order state');
define('MODULE_PAYMENT_SOFORT_SR_UNCONFIRMED_STATUS_ID_DESC', 'Oder status after successfull payment.The bill has not yet been released by the merchant.');
define('MODULE_PAYMENT_SOFORT_SR_TMP_STATUS_ID_TITLE', 'Temporary order status');
define('MODULE_PAYMENT_SOFORT_SR_TMP_STATUS_ID_DESC', 'Order state for non-completed transactions. The order has been created but the transaction has not yet been confirmed by SOFORT AG.');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID_TITLE', 'Order status at full cancelation');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_STATUS_ID_DESC', 'Canceled order status<br />Status after a full cancelation of the invoice.');

define('MODULE_PAYMENT_SOFORT_SR_PENDINIG_NOT_CONFIRMED_COMMENT', 'Order with Payment by Invoice successfully submitted. The merchant is yet to acknowledge the order. Your transaction ID:');

define('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_TITLE', 'recommend payment method');
define('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_DESC', '"Mark this payment method as "recommended payment method". On the payment selection page a note will be displayed right behind the payment method."');
define('MODULE_PAYMENT_SOFORT_SR_RECOMMENDED_PAYMENT_TEXT', '(recommend payment method)');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME', 'time');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_DATE', 'date');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_AMOUNT', 'Amount');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_COMMENT', 'Comment');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_ORDER_HISTORY', 'Order history');
define('MODULE_PAYMENT_SOFORT_SR_PRICE_CHANGED_CUSTOMERINFO', 'Due to the rounding of the price, a new, slightly differing invoice amount has shown. Please note this on receipt of the invoice! New invoice amount:');

/////////////////////////////////////////////////
//////// Seller-Backend and callback.php ////////
/////////////////////////////////////////////////

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_BACK', 'back');

define('MODULE_PAYMENT_SOFORT_SR_CONFIRM_INVOICE', 'confirm invoice');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE', 'cancel invoice');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE', 'Credit invoice');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_INVOICE_QUESTION', 'Are you really sure you want to cancel the invoice? This process can not be undone.');
define('MODULE_PAYMENT_SOFORT_SR_CANCEL_CONFIRMED_INVOICE_QUESTION', 'Are you sure you want to credit the invoice? This action can not be undone.');

define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE', 'download invoice');
define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_HINT', 'You can download the appropriate document (invoice preview, invoice, credit note) here.');
define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_CREDIT_MEMO', 'download credit note');
define('MODULE_PAYMENT_SOFORT_SR_DOWNLOAD_INVOICE_PREVIEW', 'download invoice preview');

define('MODULE_PAYMENT_SOFORT_SR_EDIT_CART', 'Edit cart');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART', 'save cart');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_QUESTION', 'Do you really want to update the cart?');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_ERROR', 'An error occured while updating the cart.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CART_HINT', 'Save your cart-changes here. When updating a confirmed invoice, reducing the quantity or deleting an article will cause a credit.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_HINT', 'You can adjust discounts and surcharges. Surcharges may not be increased and discount amounts have to be greater than zero. The total amount of the invoice may not be increased by the adjustment.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_DISCOUNTS_GTZERO_HINT', 'Discounts may not have a greater amount than zero.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY', 'adjust quantity');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_HINT', 'You can adjust the number of items per position. Amounts may be decreased, but mustn\'t be increased.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_TOTAL_GTZERO', 'The quantity of the item cannot be lowered, since the total sum of the invoice must not be negative.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_QUANTITY_ZERO_HINT', 'The quantity must be greater than 0. To delete an item, please mark the item at the end of the line.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE', 'adjust price');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_HINT', 'You can adjust the price of each item per position. Prices may be decreased, but mustn\'t be increased.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_TOTAL_GTZERO', 'The price cannot be lowered, since the total sum of the invoice must not be negative.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_HINT', 'Price and quantity mustn\'t be adjusted at the same time.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_PRICE_AND_QUANTITY_NAN', 'You entered invalid characters. These adjustments allow only numbers.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_VALUE_LTZERO_HINT', 'Value must be greater than zero.');

define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE', 'Please enter a comment');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_CONFIRMED_INVOICE_HINT', 'When adjusting a confirmed invoice, an appropriate reason must be provided. This reason will later appear on the credit note as a comment to the article.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_HINT', 'You can adjust the price for shipping. You can only reduce the amount, not increase.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_COSTS_HINT', 'On returns shipping costs are not allowed as a standalone item on an invoice.');
define('MODULE_PAYMENT_SOFORT_SR_UPDATE_SHIPPING_TOTAL_GTZERO', 'The shipping costs cannot be lowered, since the total sum of the invoice must not be negative.');

define('MODULE_PAYMENT_SOFORT_SR_RECALCULATION', 'will be recalculated');

define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_TOTAL_GTZERO','This item can not be deleted, since the total sum of the invoice must not be negative.');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_ARTICLE_FROM_INVOICE', 'Remove item');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE', 'delete article');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_QUESTION', 'Do you really want to delete following articles: %s ?');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_FROM_INVOICE_HINT', 'Select items to delete them. Deleting an item from a confirmed invoice will cause a credit note.');
define('MODULE_PAYMENT_SOFORT_SR_REMOVE_LAST_ARTICLE_HINT', 'By reducing the number of all or by removing the last item, the invoice will be canceled completely.');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED', 'The invoice has been canceled.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CONFIRMED', 'The goods are prepared for shipping.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_PENDINIG_NOT_CONFIRMED', 'Payment method Purchase on account chosen. Transaction not finished.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED', 'The invoice has been canceled. Refund created.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REANIMATED', 'Die Stornierung der Rechnung wurde rückgängig gemacht.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCEL_30_DAYS', 'The invoice was automatically canceled. The confirmation period of 30 days has expired.');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CURRENT_TOTAL', 'current invoice amount:');
define('MODULE_PAYMENT_SOFORT_SR_SUCCESS_ADDRESS_UPDATED', 'Delivery and invoice address updated successfully.');
define('MODULE_PAYMENT_SOFORT_SR_STATUSUPDATE_UNNECESSARY', 'status update not necessary');
define('MODULE_PAYMENT_SOFORT_SR_UNKNOWN_STATUS', 'Unknown payment/invoice status found.');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_DOWNLOAD_INVOICE', 'download invoice');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_DOWNLOAD_INVOICE_CREDITMEMO', 'download invoice');

define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CLOSE_WINDOW', 'close window');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CONFIRMATION_CANCEL', 'Are you really sure you want to cancel the invoice? This process can not be undone.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_YES', 'Yes');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_NO', 'No');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_REFRESH_WINDOW', 'reload window');

define('MODULE_PAYMENT_SOFORT_SR_GLOBAL_ERROR', 'Error! Please contact the administrator.');

define('MODULE_PAYMENT_SOFORT_SR_INVOICE_CONFIRMED', 'Invoice was confirmed');
define('MODULE_PAYMENT_SOFORT_SR_INVOICE_CANCELED', 'The invoice has been canceled.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_DETAILS', 'Invoice details');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TRANSACTION_ID', 'transaction ID');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_ORDER_NUMBER', 'order number');
define('MODULE_PAYMENT_SOFORT_SR_ADMIN_TITLE', 'Rechnung by SOFORT');
define('MODULE_PAYMENT_SOFORT_SR_CONFIRM_CANCEL', 'Are you really sure you want to cancel the invoice? This process can not be undone.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_REMINDER', 'Dunning level {{d}}');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_DELCREDERE', 'Collection transfer');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CREDITED_TO_SELLER', 'Payment to the merchant account has been completed.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CREDITED_TO_SELLER_CUSTOMER_PENDING', 'Payment to merchant account is done. Customer payment outstanding.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CANCELED_REFUNDED', 'The invoice has been canceled. Refund created. {{time}}');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_RECEIVED', 'Received.');
define('MODULE_PAYMENT_SOFORT_SR_PENDINIG_NOT_CONFIRMED_COMMENT_ADMIN', 'Order with purchase on account successfully transmitted. Merchant confirmation not yet taken place.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_EDITED', 'The cart has been edited.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_RESET', 'The cart has been reset.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CONFIRMED_SELLER', 'Transaction state: Invoice was confirmed. Waiting for payment. Invoice state: Invoice pending.');
define('MODULE_PAYMENT_SOFORT_SR_TRANSLATE_INVOICE_CANCELED_REFUNDED_SELLER', 'Transaction state: The money will be refunded. Invoice state: The invoice will be refunded.');
define('MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_RECEIVED_SELLER', 'Transaction state: Invoice was confirmed. Waiting for payment. Invoice state: customer paid the receipt.');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_RECEIVED_SELLER', 'Transaction state: The invoice was paid. Invoice state: customer paid the receipt.');
define('MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_REMINDER_SELLER', 'Transaction state: Invoice was confirmed. Waiting for payment. Invoice state: Dunning level {{d}}');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_REMINDER_SELLER', 'Transaction state: The invoice was paid. Invoice state: Dunning level {{d}}');
define('MODULE_PAYMENT_SOFORT_SR_PENDING_NOT_CREDITED_YET_DELCREDERE_SELLER', 'Transaction state: Invoice was confirmed. Waiting for payment. Invoice state: Collection transfer');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_DELCREDERE_SELLER', 'Transaction state: The invoice was paid. Invoice state: Collection transfer');
define('MODULE_PAYMENT_SOFORT_SR_RECEIVED_CREDITED_PENDING_SELLER', 'Transaction state: The invoice was paid. Invoice state: Customer payment pending.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9000', 'No invoice transaction found.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9001', 'The invoice could not be confirmed.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9002', 'The provided invoice amount exceeds the credit limit.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9003', 'The invoice could not be canceled.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9004', 'The request contained invalid cart positions.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9005', 'The cart could not be modified.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9006', 'Access to the interface is not longer possible 30 days after receipt of payment.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9007', 'The invoice has already been canceled.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9008', 'The amount of the provided tax is too high.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9009', 'The amounts given to the VAT rates of the items relate to each other in conflict.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9010', 'Modifying the cart is not possible.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9011', 'No comment was provided to the cart update.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9012', 'You can not add positions to the cart. Similarly, the amount per invoice item can not be increased.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9013', 'There are only non factorable items in your shopping cart.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9014', 'The provided invoice number is already in use.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9015', 'The provided credit number is already in use.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9016', 'The provided order number is already in use.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9017', 'The invoice has already been confirmed.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_9018', 'There where no data updated to the invoice.');