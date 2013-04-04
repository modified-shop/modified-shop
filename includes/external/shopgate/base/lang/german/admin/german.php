<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );


### Plugin ###
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Encoding des Shopsystems');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION',
		'W&auml;hlen Sie das Encoding Ihres Shopsystems. &Uuml;blicherweise ist f&uuml;r Versionen vor 1.06 "ISO-8859-15" zu w&auml;hlen.');
define('SHOPGATE_CONFIG_WIKI_LINK', 'http://wiki.shopgate.com/Modified/de');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'Was ist Shopgate');
define('BOX_SHOPGATE_HELP', 'Installationshilfe');
define('BOX_SHOPGATE_REGISTER', 'Registrierung');
define('BOX_SHOPGATE_CONFIG', 'Einstellungen');
define('BOX_SHOPGATE_MERCHANT', 'Shopgate-Login');

### Links ###
define('SHOPGATE_LINK_HOME', 'http://www.shopgate.com');
define('SHOPGATE_LINK_REGISTER', 'https://www.shopgate.com/welcome/shop_register');
define('SHOPGATE_LINK_LOGIN', 'https://www.shopgate.com/users/login/0/2');

### Konfiguration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'FEHLER:');
define('SHOPGATE_CONFIG_ERROR_SAVING', 'Fehler beim Speichern der Konfiguration. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', 'Fehler beim Laden der Konfiguration. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', 'Bitte überprüfen Sie die Schreibrechte (777) für den Ordner "/shopgate_library/config/" des Shopgate-Plugins.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', 'Bitte überprüfen Sie ihre Eingaben in den folgenden Feldern: ');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', 'Es existieren mehrere Konfigurationen mit der gleichen Shop-Nummer. Dies kann zu erheblichen Problemen führen!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', 'Es existieren Konfigurationen f&uuml;r mehrere Marktpl&auml;tze.');
define('SHOPGATE_CONFIG_SAVE', 'Speichern');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', 'Globale Konfiguration');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', 'F&uuml;r diese Sprache die globale Konfiguration nutzen.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', 'Mehrere Shopgate-Marktpl&auml;tze einrichten');
define('SHOPGATE_CONFIG_LANGUAGE_SELECTION',
		'Bei Shopgate ben&ouml;tigen Sie pro Marktplatz einen Shop, der auf eine Sprache und eine W&auml;hrung festgelegt ist. Hier haben Sie die M&ouml;glichkeit, Ihre konfigurierten '.
		'Sprachen mit Ihren Shopgate-Shops auf unterschiedlichen Marktpl&auml;tzen zu verbinden. W&auml;hlen Sie eine Sprache und tragen Sie die Zugangsdaten zu Ihrem Shopgate-Shop auf '.
		'dem entsprechenden Marktplatz ein. Wenn Sie f&uuml;r eine Sprache keinen eigenen Shop bei Shopgate haben, wird daf&uuml;r die "Globale Konfiguration" genutzt.'
);

### Verbindungseinstellungen ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', 'Verbindungseinstellungen');

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Kundennummer');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'Tragen Sie hier Ihre Kundennummer ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shopnummer');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'Tragen Sie hier die Shopnummer Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_APIKEY', 'API-Key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'Tragen Sie hier den API-Key Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

### Mobile Weiterleitung ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', 'Mobile Weiterleitung');

define('SHOPGATE_CONFIG_ALIAS', 'Shop-Alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'Tragen Sie hier den Alias Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_CNAME', 'Eigene URL zur mobilen Webseite (mit http://)');
define('SHOPGATE_CONFIG_CNAME_DESCRIPTION',
		'Tragen Sie hier eine eigene (per CNAME definierte) URL zur mobilen Webseite Ihres Shops ein. Sie finden die URL im Tab &quot;Integration&quot; Ihres Shops, '.
		'nachdem Sie diese Option unter &quot;Einstellungen&quot; &equals;&gt; &quot;Mobile Webseite / Webapp&quot; aktiviert haben.'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', 'Weitergeleitete Sprachen');
define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
		'W&auml;hlen Sie die Sprachen aus, die auf diesen Shopgate-Shop weitergeleitet werden sollen. Es muss mindestens '.
		'eine Sprache ausgew&auml;hlt werden. Halten Sie STRG gedr&uuml;ckt, um mehrere Eintr&auml;ge zu w&auml;hlen.'
);

### Export ###
define('SHOPGATE_CONFIG_EXPORT_SETTINGS', 'Kategorie- und Produktexport');

define('SHOPGATE_CONFIG_LANGUAGE', 'Sprache');
define('SHOPGATE_CONFIG_LANGUAGE_DESCRIPTION', 'W&auml;hlen Sie die Sprache, in der Kategorien und Produkte exportiert werden sollen.');

define('SHOPGATE_CONFIG_EXTENDED_CURRENCY', 'W&auml;hrung');
define('SHOPGATE_CONFIG_EXTENDED_CURRENCY_DESCRIPTION', 'W&auml;hlen Sie die W&auml;hrung f&uuml;r den Produktexport.');

define('SHOPGATE_CONFIG_EXTENDED_COUNTRY', 'Land');
define('SHOPGATE_CONFIG_EXTENDED_COUNTRY_DESCRIPTION', 'W&auml;hlen Sie das Land, f&uuml;r das Ihre Produkte und Kategorien exportiert werden sollen.');

define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE', 'Steuerzone f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_TAX_ZONE_DESCRIPTION', 'Geben Sie die Steuerzone an, die f&uuml;r Shopgate g&uuml;ltig sein soll.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER', 'Kategorie-Reihenfolge umkehren');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_ON', 'Ja');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_OFF', 'Nein');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
		'W&auml;hlen Sie hier "Ja" aus, wenn die Sortierung Ihrer Kategorien in Ihrem mobilen Shop genau falsch herum ist.');

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', 'Produkt-Reihenfolge umkehren');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', 'Ja');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', 'Nein');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
		'W&auml;hlen Sie hier "Ja" aus, wenn die Sortierung Ihrer Produkte in Ihrem mobilen Shop genau falsch herum ist.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Preisgruppe f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Preisgruppe, die f&uuml;r Shopgate gilt (bzw. die Kundengruppe, aus welcher die Preisinformationen beim Produktexport verwendet werden).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deaktiviert --');

### Bestellungsimport ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', 'Bestellungsimport');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Kundengruppe');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Gruppe f&uuml;r Shopgate-Kunden (die Kundengruppe, unter welcher alle Gastkunden von Shopgate beim Bestellungsimport eingerichtet werden).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', 'Versandart');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', 'W&auml;hlen Sie die Versandart f&uuml;r den Bestellungsimport. Diese wird f&uuml;r die Ausweisung der Steuern der Versandkosten genutzt, sofern eine Steuerklasse f&uuml;r die Versandart ausgew&auml;hlt ist.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '-- keine Auswahl --');

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

### Systemeinstellungen ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', 'Systemeinstellungen');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Benutzerdefinierte URL zum Shopgate-Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'W&auml;hlen Sie hier die Server-Verbindung zu Shopgate aus.');
