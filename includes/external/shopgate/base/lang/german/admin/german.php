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
define('SHOPGATE_CONFIG_EXTENDED_ENCODING', 'Encoding des Shopsystems');
define('SHOPGATE_CONFIG_EXTENDED_ENCODING_DESCRIPTION', 'W&auml;hlen Sie das Encoding Ihres Shopsystems. &Uuml;blicherweise ist f&uuml;r Versionen vor 1.06 "ISO-8859-15" zu w&auml;hlen.');

### Menu ###
define('BOX_SHOPGATE', 'Shopgate');
define('BOX_SHOPGATE_INFO', 'Was ist Shopgate');
define('BOX_SHOPGATE_HELP', 'Installationshilfe');
define('BOX_SHOPGATE_CONFIG', 'Einstellungen');

### Links ###
define('SHOPGATE_LINK_HOME', 'https://www.shopgate.com/de/?partner=30051');
define('SHOPGATE_LINK_WIKI', 'https://support.shopgate.com/hc/de/articles/202911763');

### Konfiguration ###
define('SHOPGATE_CONFIG_TITLE', 'SHOPGATE');
define('SHOPGATE_CONFIG_ERROR', 'FEHLER:');
define('SHOPGATE_CONFIG_ERROR_SAVING', 'Fehler beim Speichern der Konfiguration. ');
define('SHOPGATE_CONFIG_ERROR_LOADING', 'Fehler beim Laden der Konfiguration. ');
define('SHOPGATE_CONFIG_ERROR_READ_WRITE', 'Bitte &uuml;berpr&uuml;fen Sie die Schreibrechte (777) f&uuml;r den Ordner "/shopgate_library/config/" des Shopgate-Plugins.');
define('SHOPGATE_CONFIG_ERROR_INVALID_VALUE', 'Bitte &uuml;berpr&uuml;fen Sie Ihre Eingaben in den folgenden Feldern: ');
define('SHOPGATE_CONFIG_ERROR_DUPLICATE_SHOP_NUMBERS', 'Es existieren mehrere Konfigurationen mit der gleichen Shop-Nummer. Dies kann zu erheblichen Problemen f&uuml;hren!');
define('SHOPGATE_CONFIG_INFO_MULTIPLE_CONFIGURATIONS', 'Es existieren Konfigurationen f&uuml;r mehrere Marktpl&auml;tze.');
define('SHOPGATE_CONFIG_SAVE', 'Speichern');
define('SHOPGATE_CONFIG_GLOBAL_CONFIGURATION', 'Globale Konfiguration');
define('SHOPGATE_CONFIG_USE_GLOBAL_CONFIG', 'F&uuml;r diese Sprache die globale Konfiguration nutzen.');
define('SHOPGATE_CONFIG_MULTIPLE_SHOPS_BUTTON', 'Mehrere Shopgate-Marktpl&auml;tze einrichten');
define(
'SHOPGATE_CONFIG_LANGUAGE_SELECTION',
    'Bei Shopgate ben&ouml;tigen Sie pro Marktplatz einen Shop, der auf eine Sprache und eine W&auml;hrung festgelegt ist. Hier haben Sie die M&ouml;glichkeit, Ihre konfigurierten '
    .
    'Sprachen mit Ihren Shopgate-Shops auf unterschiedlichen Marktpl&auml;tzen zu verbinden. W&auml;hlen Sie eine Sprache und tragen Sie die Zugangsdaten zu Ihrem Shopgate-Shop auf '
    .
    'dem entsprechenden Marktplatz ein. Wenn Sie f&uuml;r eine Sprache keinen eigenen Shop bei Shopgate haben, wird daf&uuml;r die "Globale Konfiguration" genutzt.'
);

### Verbindungseinstellungen ###
define('SHOPGATE_CONFIG_CONNECTION_SETTINGS', 'Verbindungseinstellungen');

define('SHOPGATE_CONFIG_CUSTOMER_NUMBER', 'Kundennummer');
define('SHOPGATE_CONFIG_CUSTOMER_NUMBER_DESCRIPTION', 'Tragen Sie hier Ihre Kundennummer ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_SHOP_NUMBER', 'Shopnummer');
define('SHOPGATE_CONFIG_SHOP_NUMBER_DESCRIPTION', 'Tragen Sie hier die Shopnummer Ihres Shops ein. Sie finden diese im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_APIKEY', 'API-Key');
define('SHOPGATE_CONFIG_APIKEY_DESCRIPTION', 'Tragen Sie hier den API-Key Ihres Shops ein. Sie finden diesen im Tab &quot;Integration&quot; Ihres Shops.');

### Mobile Weiterleitung ###
define('SHOPGATE_CONFIG_MOBILE_REDIRECT_SETTINGS', 'Mobile Weiterleitung');

define('SHOPGATE_CONFIG_ALIAS', 'Shop-Alias');
define('SHOPGATE_CONFIG_ALIAS_DESCRIPTION', 'Tragen Sie hier den Alias Ihres Shops ein. Sie finden diesen im Tab &quot;Integration&quot; Ihres Shops.');

define('SHOPGATE_CONFIG_CNAME', 'Eigene URL zur mobilen Webseite (mit http://)');
define(
'SHOPGATE_CONFIG_CNAME_DESCRIPTION',
    'Tragen Sie hier eine eigene (per CNAME definierte) URL zur mobilen Webseite Ihres Shops ein. Sie finden die URL im Tab &quot;Integration&quot; Ihres Shops, '
    .
    'nachdem Sie diese Option unter &quot;Einstellungen&quot; &equals;&gt; &quot;Mobile Webseite / Webapp&quot; aktiviert haben.'
);

define('SHOPGATE_CONFIG_REDIRECT_LANGUAGES', 'Weitergeleitete Sprachen');
define(
'SHOPGATE_CONFIG_REDIRECT_LANGUAGES_DESCRIPTION',
    'W&auml;hlen Sie die Sprachen aus, die auf diesen Shopgate-Shop weitergeleitet werden sollen. Es muss mindestens ' .
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
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_CATEGORIES_SORT_ORDER_DESCRIPTION',
'W&auml;hlen Sie hier "Ja" aus, wenn die Sortierung Ihrer Kategorien in Ihrem mobilen Shop genau falsch herum ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER', 'Produkt-Reihenfolge umkehren');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_ON', 'Ja');
define('SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_OFF', 'Nein');
define(
'SHOPGATE_CONFIG_EXTENDED_REVERSE_ITEMS_SORT_ORDER_DESCRIPTION',
'W&auml;hlen Sie hier "Ja" aus, wenn die Sortierung Ihrer Produkte in Ihrem mobilen Shop genau falsch herum ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION', 'Produktbeschreibung');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_DESC_ONLY', 'Nur Beschreibung');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_SHORTDESC_ONLY', 'Nur Kurzbeschreibung');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_DESC_SHORTDESC', 'Beschreibung + Kurzbeschreibung');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_SHORTDESC_DESC', 'Kurzbeschreibung + Beschreibung');
define('SHOPGATE_CONFIG_EXTENDED_PRODUCTSDESCRIPTION_DESCRIPTION', 'W&auml;hlen Sie hier aus, wie die Produktbeschreibung im mobilen Shop zusammengesetzt sein soll.');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP', 'Preisgruppe f&uuml;r Shopgate');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Preisgruppe, die f&uuml;r Shopgate gilt (bzw. die Kundengruppe, aus welcher die Preisinformationen beim Produktexport verwendet werden).');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_PRICE_GROUP_OFF', '-- Deaktiviert --');

define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY', 'Export der "Neu" Kategorie');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_DESCRIPTION', 'Dieses Shopsystem bietet die M&ouml;glichkeit eine virtuelle Kategorie f&uuml;r neue Produkte anzulegen. Diese kann mit Hilfe dieser Option als Kategorie exportiert werden. Weiterhin ist es m&ouml;glich, im Eingabefeld eine einzigartige ID f&uuml;r diese Kategorie festzulegen.');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_ON', 'Ja');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_OFF', 'Nein');
define('SHOPGATE_CONFIG_EXPORT_NEW_PRODUCTS_CATEGORY_MAX_ID', 'Aktuell h&ouml;chste Kategorie ID Ihres Shopsystems');

define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY', 'Export der "Spezial" Kategorie');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_DESCRIPTION', 'Dieses Shopsystem bietet die M&ouml;glichekeit eine virtuelle Kategorie f&uuml;r Spezial-Produkte anzulegen. Diese kann mit Hilfe dieser Option als Kategorie exportiert werden. Weiterhin ist es m&ouml;glich, im Eingabefeld, eine einzigartige ID f&uuml;r diese Kategorie festzulegen.');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_ON', 'Ja');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_OFF', 'Nein');
define('SHOPGATE_CONFIG_EXPORT_SPECIAL_PRODUCTS_CATEGORY_MAX_ID', 'Aktuell h&ouml;chste Kategorie ID Ihres Shopsystems');

define('SHOPGATE_CONFIG_EXPORT_OPTIONS_AS_INPUT_FIELD', 'Export von Produkoptionen als Eingabefelder');
define('SHOPGATE_CONFIG_EXPORT_OPTIONS_AS_INPUT_FIELD_DESCRIPTION', 'Die IDs der Produktoptionen (siehe "Artikelmerkmale"), die als Eingabefelder exportiert werden m&uuml;ssen. Beispiel: 1,2,3');

define('SHOPGATE_PLUGIN_FIELD_AVAILABLE_TEXT_AVAILABLE_ON_DATE', 'Verf&uuml;gbar ab dem #DATE#');

### Bestellungsimport ###
define('SHOPGATE_CONFIG_ORDER_IMPORT_SETTINGS', 'Bestellungsimport');
define('SHOPGATE_ORDER_CUSTOM_FIELD', 'Benutzerdefinierte Eingabefelder zu einer Shopgate Bestellung:');

define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP', 'Kundengruppe');
define('SHOPGATE_CONFIG_EXTENDED_CUSTOMER_GROUP_DESCRIPTION', 'W&auml;hlen Sie die Gruppe f&uuml;r Shopgate-Kunden (die Kundengruppe, unter welcher alle Gastkunden von Shopgate beim Bestellungsimport eingerichtet werden).');

define('SHOPGATE_CONFIG_EXTENDED_SHIPPING', 'Versandart');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_DESCRIPTION', 'W&auml;hlen Sie die Versandart f&uuml;r den Bestellungsimport. Diese wird f&uuml;r die Ausweisung der Steuern der Versandkosten genutzt, sofern eine Steuerklasse f&uuml;r die Versandart ausgew&auml;hlt ist.');
define('SHOPGATE_CONFIG_EXTENDED_SHIPPING_NO_SELECTION', '-- keine Auswahl --');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED', 'Versand nicht blockiert');
define(
'SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_APPROVED_DESCRIPTION',
'W&auml;hlen Sie den Status f&uuml;r Bestellungen, deren Versand bei Shopgate nicht blockiert ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED', 'Versand blockiert');
define(
'SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SHIPPING_BLOCKED_DESCRIPTION',
'W&auml;hlen Sie den Status f&uuml;r Bestellungen, deren Versand bei Shopgate blockiert ist.'
);

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT', 'Versendet');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_SENT_DESCRIPTION', 'W&auml;hlen Sie den Status, mit dem Sie Bestellungen als &quot;versendet&quot; markieren.');

define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED', 'Storniert');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_NOT_SET', '- Status nicht ausgew&auml;hlt -');
define('SHOPGATE_CONFIG_EXTENDED_STATUS_ORDER_CANCELED_DESCRIPTION', 'W&auml;hlen Sie den Status f&uuml;r stornierte Bestellungen.');

define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL', 'Bestellbest&auml;tigungs-Email');
define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL_ON', 'Ja');
define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL_OFF', 'Nein');
define('SHOPGATE_CONFIG_SEND_ORDER_EMAIL_DESCRIPTION', 'Nachdem eine Bestellung &uuml;ber Shopgate abgeschlossen wurde, bekommt der Kunde eine Bestellbest&auml;tigungs-Email. Sollte der Shop Produkte zum Kauf anbieten, welche heruntergeladen werden k&ouml;nnen, enth&auml;lt diese Mail den Downloadlink.');

define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING', 'Anzeigenamen f&uuml;r Zahlungsweisen');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_DESCRIPTION', "Individuelle Namen f&uuml;r Zahlungsweisen, die beim Bestellungsimport verwendet werden. Definiert durch '=' und getrennt durch ';'.<br/>(Beispiel: PREPAY=Vorkasse;SHOPGATE=Abwicklung durch Shopgate)<br/>");
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK', 'https://support.shopgate.com/hc/de/articles/202911763-Anbindung-an-modified-eCommerce#4.4');
define('SHOPGATE_CONFIG_PAYMENT_NAME_MAPPING_LINK_DESCRIPTION', "Link zur Anleitung");

### Systemeinstellungen ###
define('SHOPGATE_CONFIG_SYSTEM_SETTINGS', 'Systemeinstellungen');

define('SHOPGATE_CONFIG_SERVER_TYPE', 'Shopgate Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_LIVE', 'Live');
define('SHOPGATE_CONFIG_SERVER_TYPE_PG', 'Playground');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM', 'Custom');
define('SHOPGATE_CONFIG_SERVER_TYPE_CUSTOM_URL', 'Benutzerdefinierte URL zum Shopgate-Server');
define('SHOPGATE_CONFIG_SERVER_TYPE_DESCRIPTION', 'W&auml;hlen Sie hier die Server-Verbindung zu Shopgate aus.');
