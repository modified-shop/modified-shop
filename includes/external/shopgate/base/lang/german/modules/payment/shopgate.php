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
define('MODULE_PAYMENT_SHOPGATE_TEXT_INFO', 'Bestellungen sind bereits bei Shopgate bezahlt.');

define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SHIPPING', 'Versand');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SUBTOTAL', 'Zwischensumme');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_PAYMENTFEE', 'Zahlungsartkosten');
define('MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_TOTAL', 'Summe');

define('MODULE_PAYMENT_SHOPGATE_TEXT_EMAIL_FOOTER', '');
define('MODULE_PAYMENT_SHOPGATE_STATUS_TITLE', 'Shopgate-Zahlungsmodul aktiviert:');

define('MODULE_PAYMENT_SHOPGATE_STATUS_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_ALLOWED_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_ALLOWED_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_PAYTO_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_PAYTO_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_SHOPGATE_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_SHOPGATE_ZONE_TITLE', '');
define('MODULE_PAYMENT_SHOPGATE_ZONE_DESC', '');
define('MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID_TITLE', 'Status');
define('MODULE_PAYMENT_SHOPGATE_ORDER_STATUS_ID_DESC', 'Bestellungen, die mit diesem Modul importiert werden, auf diesen Status setzen:');
define('MODULE_PAYMENT_SHOPGATE_ERROR_READING_LANGUAGES', 'Fehler beim Konfigurieren der Spracheinstellungen.');
define('MODULE_PAYMENT_SHOPGATE_ERROR_LOADING_CONFIG', 'Fehler beim Laden der Konfiguration.');
define(
'MODULE_PAYMENT_SHOPGATE_ERROR_SAVING_CONFIG',
    'Fehler beim Speichern der Konfiguration. ' .
    'Bitte &uuml;berpr&uuml;fen Sie die Schreibrechte (777) f&uuml;r ' .
    'den Ordner &quot;/shopgate_library/config/&quot; des Shopgate-Plugins.'
);

define("MODULE_PAYMENT_SHOPGATE_LABEL_NEW_PRODUCTS", "Neue Produkte");
define("MODULE_PAYMENT_SHOPGATE_LABEL_SPECIAL_PRODUCTS", "Spezial Produkte");
defined('SHOPGATE_ORDER_CUSTOM_FIELD') OR define('SHOPGATE_ORDER_CUSTOM_FIELD', 'Benutzerdefinierte Eingabefelder zu einer Shopgate Bestellung:');

define("SHOPGATE_COUPON_ERROR_NEED_ACCOUNT", "Um diesen Gutschein verwenden zu können, müssen Sie angemeldet sein.");
define("SHOPGATE_COUPON_ERROR_RESTRICTED_PRODUCTS", "Dieser Gutschein ist auf bestimmte Produkte beschränkt");
define("SHOPGATE_COUPON_ERROR_RESTRICTED_CATEGORIES", "Dieser Gutschein ist auf bestimmte Kategorien beschränkt");
define("SHOPGATE_COUPON_ERROR_MINIMUM_ORDER_AMOUNT_NOT_REACHED", "Der Mindestbestellwert, um diesen Gutschein nutzen zu können, wurde nicht erreicht");
