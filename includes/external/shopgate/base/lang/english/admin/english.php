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


### Plugin ###
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Shop system encoding');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', 'Choose the encoding of your shop system. This is usually "ISO-8859-15" for versions before 1.06.');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'What is Shopgate');
define('BOX_SHOPGATE_HELP', 'Installation aid');
define('BOX_SHOPGATE_CONFIG', 'Settings');

### Links ###
define('SHOPGATE_LINK_HOME', 'https://www.shopgate.com/en/?partner=30051');
define('SHOPGATE_LINK_WIKI', 'https://support.shopgate.com/hc/en-us/articles/202911763');

### Configuration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'ERROR:');
define('SHOPGATE_CONFIG_ERROR_SAVING', 'Error saving configuration. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', 'Error loading configuration. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', 'Please check the permissions (777) for the folder &quot;/shopgate_library/config&quot; of the Shopgate plugin.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', 'Please check your input in the following fields: ');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', 'There are multiple configurations with the same shop number. This can cause major unforeseen issues!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', 'Configurations for multiple market places are active.');
define('SHOPGATE_CONFIG_SAVE', 'Save');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', 'Global configuration');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', 'Use the global configuration for this language.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', 'Setup multiple Shopgate marketplaces');
define(
'SHOPGATE_CONFIG_LANGUAGE_SELECTION',
    'At Shopgate you need a shop for each marketplace restricted to one language and currency. Here you can map the configured languages to your Shopgate shops on different '
    .
    'marketplaces. Choose a language and enter the credentials of your Shopgate shop at the corresponding marketplace. If you do not have a Shopgate shop for a certain language '
    .
    'the global configuration will be used for this one.'
);

### Connection Settings ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', 'Connection Settings');

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Customer number');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'You can find your customer number at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shop number');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'You can find the shop number at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_APIKEY', 'API key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'You can find the API key at the &quot;Integration&quot; section of your shop.');

### Mobile Redirect ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', 'Mobile Redirect');

define('SHOPGATE_CONFIG_ALIAS', 'Shop alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'You can find the alias at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_CNAME', 'Custom URL to mobile webpage (CNAME) incl. http://');
define(
'SHOPGATE_CONFIG_CNAME_DESCRIPTION',
    'Enter a custom URL (defined by CNAME) for your mobile website. You can find the URL at the &quot;Integration&quot; section of your shop '
    .
    'after you activated this option in the &quot;Settings&quot; &equals;&gt; &quot;Mobile website / webapp&quot; section.'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', 'Redirected languages');
define(
'SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
'Choose the languages that should be redirected to this Shopgate shop. At least one language must be selected. Hold CTRL to select multiple entries.'
);

### Export ###
define('SHOPGATE_CONFIG_EXPORT_SETTINGS', 'Exporting Categories and Products');

define('SHOPGATE_CONFIG_LANGUAGE', 'Language');
define('SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION', 'Choose the language in which categories and products should be exported.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'Currency');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'Choose the currency for products export.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Country');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'Choose the country for which your products should be exported');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Tax zone for Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Choose the valid tax zone for Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER', 'Reverse category sort order');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON', 'Yes');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF', 'No');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
'Choose "Yes" if the sort order of the categories in your mobile shop appears upside down.'
);

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', 'Reverse products sort order');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', 'Yes');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', 'No');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
'Choose "Yes" if the sort order of the products in your mobile shop appears upside down.'
);

define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION', 'Products description');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_DESC_ONLY', 'Description only');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_SHORTDESC_ONLY', 'Short description only');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_DESC_SHORTDESC', 'Description and short description');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_SHORTDESC_DESC', 'Short description and description');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_DESCRIPTION', 'Please select the method to be used to build descriptions for the mobile shop.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Price group for Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'Choose the valid price group for Shopgate (the customer group of which the price information is taken for the products export).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deactivated --');

define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY', 'Export the "New" category');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_DESCRIPTION', 'This shopsystem allows you to show all new products in an virtual category. With this option it is possible to export the new products in an real category. Therefore you can use the input field to change the category id.');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_ON', 'Yes');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_OFF', 'No');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_MAX_ID', 'Currently highest categorie-Id in the shopsystem');

define('SHOPGATE_CONFIG_EXPORT_OPTIONS_AS_INPUT_FIELD', 'Product options to be exported as input fields');
define('SHOPGATE_CONFIG_EXPORT_OPTIONS_AS_INPUT_FIELD_DESCRIPTION', 'Add the option IDs (see "Product Options") that should be exported as input fields. Example: 1,2,3');

define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY', 'Export the "Special" category');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_DESCRIPTION', 'This shopsystem allows you to show all special products in an virtual category. With this option it is possible to export the new products in an real category. Therefore you can use the input field to change the category id.');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_ON', 'Yes');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_OFF', 'No');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_MAX_ID', 'Currently highest categorie-Id in the shopsystem');
define('SHOPGATE_PLUGIN_FIELD_AVAILABLE_TEXT_AVAILABLE_ON_DATE', 'Available on #DATE#');

### Orders Import ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', 'Importing Orders');
define('SHOPGATE_ORDER_CUSTOM_FIELD', 'Custom field(s) of this Shopgate order:');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Customer group');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'Choose the Shopgate customer group (the customer group that all guest customers will be set to on importing orders).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', 'Shipping method');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', 'Choose the shipping method for the import of the orders. This will be used to calculate the tax for the shipping costs.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '-- no selection --');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Shipping not blocked');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION', 'Choose the status for orders that are not blocked for shipping by Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Shipping blocked');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION', 'Choose the status for orders that are blocked for shipping by Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Shipped');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'Choose the status you apply to orders that have been shipped.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Cancelled');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status not set -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'Choose the status for orders that have been cancelled.');

define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL', 'Confirmation Mail');
define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL_ON', 'Yes');
define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL_OFF', 'No');
define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL_DESCRIPTION', 'After an shopgate order has been successfully completed, an confirmation mail will be send to the customer.if the product(s) were downloadble the download link is included in this mail.');

define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING', 'Display names for payment methods');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_DESCRIPTION', "Individual names for payment methods, which are used on order import. Defined by '=' and separated by ';'.<br/>(Example: PREPAY=Prepay;SHOPGATE=Handled by Shopgate)<br/>");
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK', 'https://support.shopgate.com/hc/en-us/articles/202911763-Connecting-to-modified-eCommerce#4.4');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK_DESCRIPTION', "Link to the support page");

### System Settings ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', 'System Settings');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Custom Shopgate server url');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'Choose the Shopgate server to connect to.');
