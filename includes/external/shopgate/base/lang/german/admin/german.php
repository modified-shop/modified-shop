<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'Was ist Shopgate');
define('BOX_SHOPGATE_HELP', 'Installationshilfe');
define('BOX_SHOPGATE_REGISTER', '1. Registrierung');
define('BOX_SHOPGATE_CONFIG', '2. Grundeinstellungen');
define('BOX_SHOPGATE_QRCODE', '3. QR-Code Marketing');
define('BOX_SHOPGATE_CONFIG_EXTENDED', 'Erweiterte Einstellungen');
define('BOX_SHOPGATE_MERCHANT', 'Shopgate-Login');

### Konfiguration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'FEHLER:');
define('SHOPGATE_CONFIG_SAVE', 'Speichern');

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Kundennummer');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'Tragen Sie hier Ihre Kundennummer ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shopnummer');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'Tragen Sie hier den die Shopnummer Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_APIKEY', 'API-Key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'Tragen Sie hier den API-Key Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_ALIAS', 'Shop-Alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'Tragen Sie hier den Alias Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_CNAME', 'Eigene URL zur mobilen Webseite (mit http(s)://)');
define('SHOPGATE_CONFIG_CNAME_DESCRIPTION',
		'Tragen Sie hier eine eigene (per CNAME definierte) URL zur mobilen Webseite Ihres Shops ein. Sie finden die URL im Tab &quot;Integration&quot; Ihres Shops, '.
		'nachdem Sie diese Option unter &quot;Einstellungen&quot; &equals;&gt; &quot;Mobile Webseite / Webapp&quot; aktiviert haben.'
);

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Benutzerdefinierte URL zum Shopgate-Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'W&auml;hlen Sie hier die Server-Verbindung zu Shopgate aus.');

define('SHOPGATE_CONFIG_SHOP_ACTIVATED', 'Bei Shopgate freigeschaltet');
define('SHOPGATE_CONFIG_SHOP_ACTIVATED_ON', 'Ja');
define('SHOPGATE_CONFIG_SHOP_ACTIVATED_OFF', 'Nein');
define('SHOPGATE_CONFIG_SHOP_ACTIVATED_DESCRIPTION',
		'Ist Ihr Shop bei Shopgate freigeschaltet?<br><strong>Hinweis:</strong> Einige Funktionen werden erst freigeschaltet, '.
		'wenn diese Option auf Ja steht. (Beispiel: Mobile Weiterleitung)'
);

define('SHOPGATE_CONFIG_MOBILE_WEBSITE', 'Mobile Website');
define('SHOPGATE_CONFIG_MOBILE_WEBSITE_ON', 'An');
define('SHOPGATE_CONFIG_MOBILE_WEBSITE_OFF', 'Aus');
define('SHOPGATE_CONFIG_MOBILE_WEBSITE_DESCRIPTION',
		'W&auml;hlen Sie &quot;An&quot;, um Besucher Ihres shops mit mobilen Endger&auml;ten auf ihre Mobile Website '.
		'weiterzuleiten.'
);

### Erweiterte Konfiguration ###
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Encoding des Shopsystems');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION',
		'W&auml;hlen Sie das Encoding Ihres Shopsystems. &Uuml;blicherweise ist f&uuml;r Versionen vor 1.06 "ISO-8859-15" zu w&auml;hlen.');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Steuerzone f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Geben Sie die Steuerzone an, die f&uuml;r Shopgate g&uuml;ltig sein soll.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Preisgruppe f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deaktiviert --');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Preisgruppe, die f&uuml;r Shopgate gilt.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Kundengruppe');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Gruppe f&uuml;r Shopgate-Kunden.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'W&auml;hrung');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'W&auml;hlen Sie die W&auml;hrung f&uuml;r den Produktexport.');

define('SHOPGATE_CONFIG_EXTENDED_LANGUAGE', 'Sprache');
define('SHOPGATE_CONFIG_EXTENDED_LANGUAGE_DESCRIPTION', 'W&auml;hlen Sie die Sprache f&uuml;r die Exporte.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Land');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'W&auml;hlen Sie das Land, f&uuml;r das Ihre Produkte exportiert werden sollen.');

### Erweiterte Konfiguration - Bestellstatus-Einstellungen ###
define('SHOPGATE_CONFIG_EXTENDED_STATUS_SETTINGS', 'Bestellstatus-Einstellungen');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Versand nicht blockiert');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION',
		'W&auml;hlen Sie den Status f&uuml;r Bestellungen, deren Versand bei Shopgate nicht blockiert ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Versand blockiert');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION',
		'W&auml;hlen Sie den Status f&uuml;r Bestellungen, deren Versand bei Shopgate blockiert ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Versendet');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'W&auml;hlen Sie den Status, mit dem Sie Bestellungen als &quot;versendet&quot; markieren.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Storniert');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status nicht ausgew&auml;hlt -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'W&auml;hlen Sie den Status f&uuml;r stornierte Bestellungen.');