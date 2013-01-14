<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'What is Shopgate');
define('BOX_SHOPGATE_HELP', 'Installation aid');
define('BOX_SHOPGATE_REGISTER', '1. Registration');
define('BOX_SHOPGATE_CONFIG', '2. Basic settings');
define('BOX_SHOPGATE_QRCODE', '3. QR-Code Marketing');
define('BOX_SHOPGATE_CONFIG_EXTENDED', 'Extended settings');
define('BOX_SHOPGATE_MERCHANT', 'Shopgate-Login');

### Configuration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'ERROR:');
define('SHOPGATE_CONFIG_SAVE', 'Save');

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Customer number');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'You can find your customer number at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shop number');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'You can find the shop number at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_APIKEY', 'API key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'You can find the API key at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_ALIAS', 'Shop alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'You can find the alias at the &quot;Integration&quot; section of your shop.');

define('SHOPGATE_CONFIG_CNAME', 'Custom URL to mobile webpage (CNAME) incl. http(s)://');
define('SHOPGATE_CONFIG_CNAME_DESCRIPTION',
		'Enter a custom URL (defined by CNAME) for your mobile website. You can find the URL at the &quot;Integration&quot; section of your shop '.
		'after you activated this option in the &quot;Settings&quot; &equals;&gt; &quot;Mobile website / webapp&quot; section.'
);

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Custom Shopgate server url');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'Choose the Shopgate server to connect to.');

define('SHOPGATE_CONFIG_SHOP_ACTIVATED', 'Shop is activated');
define('SHOPGATE_CONFIG_SHOP_ACTIVATED_ON', 'Yes');
define('SHOPGATE_CONFIG_SHOP_ACTIVATED_OFF', 'No');
define('SHOPGATE_CONFIG_SHOP_ACTIVATED_DESCRIPTION',
		'Is your shop activated?<br><strong>Important:</strong> Some functionality will only work when this is set so &quot;Yes&quot; (e.g. Mobile Website).'
);

define('SHOPGATE_CONFIG_MOBILE_WEBSITE', 'Mobile Website');
define('SHOPGATE_CONFIG_MOBILE_WEBSITE_ON', 'On');
define('SHOPGATE_CONFIG_MOBILE_WEBSITE_OFF', 'Off');
define('SHOPGATE_CONFIG_MOBILE_WEBSITE_DESCRIPTION', 'Turn this &quot;On&quot; to automatically redirect your visitors with mobile devices to your Mobile Website.');

### Extended Configuration ###
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Shop system encoding');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', 'Choose the encoding of your shop system. This is usually "ISO-8859-15" for versions before 1.06.');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Tax zone for Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Choose the valid tax zone for Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Price group for Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deactivated --');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'Choose the valid price group for Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Customer group');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'Choose the Shopgate customer\'s group.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'Currency');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'Choose the currency for products export.');

define('SHOPGATE_CONFIG_EXTENDED_LANGUAGE', 'Language');
define('SHOPGATE_CONFIG_EXTENDED_LANGUAGE_DESCRIPTION', 'Choose the language for exports.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Land');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'Choose the country for which your products should be exported');

### Extended Configuration - Orders status settings ###
define('SHOPGATE_CONFIG_EXTENDED_STATUS_SETTINGS', 'Orders status settings');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Shipping not blocked');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION', 'Choose the status for orders that are not blocked for shipping by Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Shipping blocked');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION', 'Choose the status for orders that are blocked for shipping by Shopgate.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Shipped');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'Choose the status you apply to orders that have been shipped.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Cancelled');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status not set -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'Choose the status for orders that have been cancelled.');