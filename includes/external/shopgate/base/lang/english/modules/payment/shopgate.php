<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

define('MODULE_PAYMENT_SHOPGATE_TEXT_TITLE', 'Shopgate');
define('MODULE_PAYMENT_SHOPGATE_TEXT_DESCRIPTION', 'Shopgate - Mobile Shopping.');
define('MODULE_PAYMENT_SHOPGATE_TEXT_INFO', 'Orders are already paid at Shopgate.');

define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SHIPPING', 'Shipping');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SUBTOTAL', 'Subtotal');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_PAYMENTFEE', 'Payment Fees');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_TOTAL', 'Total');

define('MODULE_PAYMENT_SHOPGATE_TEXT_EMAIL_FOOTER', "");
define('MODULE_PAYMENT_SHOPGATE_STATUS_TITLE', 'Shopgate payment module activated:');

define('MODULE_PAYMENT_SHOPGATE_STATUS_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_ALLOWED_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_ALLOWED_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_PAYTO_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_PAYTO_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_SORT_ORDER_TITLE', 'Sort order of display');
define('MODULE_PAYMENT_SHOPGATE_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_SHOPGATE_ZONE_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_ZONE_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID_TITLE', 'Status');
define('MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID_DESC', 'Set status of orders imported by this module to:');
define('MODULE_PAYMENT_SHOPGATE_ERROR_READING_LANGUAGES', 'Error configuring language settings.');
define('MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG', 'Error loading configuration.');
define(
'MODULE_PAYMENT_SHOPGATE_ERROR_SAVING_CONFIG',
    'Error saving configuration. ' .
    'Please check the permissions (777) for the folder ' .
    '&quot;/shopgate_library/config&quot; of the Shopgate plugin.'
);

define("MODULE_PAYMENT_SHOPGATE_LABEL_NEW_PRODUCTS", "New products");
define("MODULE_PAYMENT_SHOPGATE_LABEL_SPECIAL_PRODUCTS", "Special products");
defined('SHOPGATE_ORDER_CUSTOM_FIELD') OR define('SHOPGATE_ORDER_CUSTOM_FIELD', 'Custom field(s) of this Shopgate order:');

define("SHOPGATE_COUPON_ERROR_NEED_ACCOUNT", "You need do be logged in to use this coupon");
define("SHOPGATE_COUPON_ERROR_RESTRICTED_PRODUCTS", "This coupon is restricted to special products");
define("SHOPGATE_COUPON_ERROR_RESTRICTED_CATEGORIES", "This coupon is restricted to special categories");
define("SHOPGATE_COUPON_ERROR_MINIMUM_ORDER_AMOUNT_NOT_REACHED", "This coupon has a minimum order amount which has not been reached");
