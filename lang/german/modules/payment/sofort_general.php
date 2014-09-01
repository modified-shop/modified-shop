<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 14:27:56 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofort_general.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

define('MODULE_PAYMENT_SOFORT_MULTIPAY_JS_LIBS', '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="'.DIR_WS_CATALOG.'callback/sofort/ressources/javascript/sofortbox.js"></script>');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_STATUS_TITLE', 'sofort.de Modul aktivieren');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_STATUS_DESC', 'Aktiviert/deaktiviert das komplette Modul');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY_TITLE', 'Konfigurationsschlüssel');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_APIKEY_DESC', 'Von SOFORT AG zugewiesener Konfigurationsschlüssel');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH_TITLE', 'Konfigurationsschlüssel/API-Key testen');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_AUTH_DESC', '<noscript>Bitte Javascript aktivieren!</noscript><script src="../callback/sofort/ressources/javascript/testAuth.js"></script>');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_ZONE_DESC', 'Wenn eine Zone ausgewählt ist, gilt die Zahlungsmethode nur für diese Zone.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1_TITLE', 'Verwendungszweck 1');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_REASON_1_DESC', 'Im Verwendungszweck 1 können folgende Optionen ausgewählt werden');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2_TITLE', 'Verwendungszweck 2');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_REASON_2_DESC', 'Im Verwendungszweck (maximal 27 Zeichen) werden folgende Platzhalter ersetzt:<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_ERROR_HEADING', 'Folgender Fehler wurde während des Prozesses gemeldet:');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_TEXT_ERROR_MESSAGE', 'Die gewählte Zahlart ist leider nicht möglich oder wurde auf Kundenwunsch abgebrochen. Bitte wählen Sie eine andere Zahlweise.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE_TITLE', 'Banner oder Text bei der Auswahl der Zahlungsoptionen');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_IMAGE_DESC', 'Banner oder Text bei der Auswahl der Zahlungsoptionen');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID_TITLE', 'Bestellstatus zur manuellen Prüfung');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_CHECK_STATUS_ID_DESC', 'Bestellstatus für Bestellungen mit ungewöhnlichem Zahlverhalten wie fehlerhaften Zahlbeträgen, kein Geldeingang, Rücklastschriften u.ä. Diese Bestellungen müssen manuell geprüft werden.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_ORDER_CANCELED', 'Die Bestellung wurde abgebrochen.'); //Die Bestellung wurde abgebrochen.

define('MODULE_PAYMENT_SOFORT_STATUS_NOT_CREDITED_YET', 'Bestellung mit {{paymentMethodStr}} erfolgreich übermittelt. Transaktions-ID: {{tId}} {{time}}');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_CREDITED_TO_SELLER', 'Zahlungseingang auf Händlerkonto ist erfolgt.');
define('MODULE_PAYMENT_SOFORT_STATUS_WAIT_FOR_MONEY', 'Warte auf Geldeingang. Transaktions-ID: {{tId}} {{time}}');

define('MODULE_PAYMENT_SOFORT_STATUS_PARTIALLY_CREDITED', '{{paymentMethodStr}} - Es wurde nur ein Teilbetrag des ursprünglich geforderten Betrages empfangen. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_OVERPAYMENT', '{{paymentMethodStr}} - Es wurde ein höherer Betrag als ursprünglich gefordert empfangen. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_SV_COMPENSATION', 'Ein Teil des Rechnungsbetrages wird zurückerstattet. Insgesamt zurückgebuchter Betrag: {{refunded_amount}}. {{time}}');

define('MODULE_PAYMENT_SOFORT_STATUS_RECEIVED', '{{paymentMethodStr}} - Geld ist eingegangen. {{time}}');
define('MODULE_PAYMENT_SOFORT_STATUS_DEFAULT', '{{paymentMethod}} {{status}} {{statusReason}} {{time}}');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_TRANSACTION_ID', 'Transaktions-ID');

define('MODULE_PAYMENT_SOFORT_ERROR_ORDER_NOT_FOUND', 'Fehler: Bestellung nicht gefunden.\n');
define('MODULE_PAYMENT_SOFORT_SUCCESS_CALLBACK', 'Bestellstatus erfolgreich aktualisiert.');
define('MODULE_PAYMENT_SOFORT_ERROR_UNEXPECTED_STATUS', 'Fehler: Unbekannter Zahlstatus.');
define('MODULE_PAYMENT_SOFORT_ERROR_TERMINATED', 'Script abgebrochen.');

define('MODULE_PAYMENT_SOFORT_MULTIPAY_FORWARDING', 'Ihre Anfrage wird geprüft, bitte gedulden Sie sich einen Moment und brechen Sie nicht ab. Der Vorgang kann bis zu 30 Sekunden dauern.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_VERSIONNUMBER', 'Versionsnummer');

define('MODULE_PAYMENT_SOFORT_KEYTEST_SUCCESS', 'API-Key erfolgreich validiert!');
define('MODULE_PAYMENT_SOFORT_KEYTEST_SUCCESS_DESC', 'Test OK am');
define('MODULE_PAYMENT_SOFORT_KEYTEST_ERROR', 'API-Key konnte nicht validiert werden!');
define('MODULE_PAYMENT_SOFORT_KEYTEST_ERROR_DESC', 'Achtung: API-Key fehlerhaft');
define('MODULE_PAYMENT_SOFORT_KEYTEST_DEFAULT', 'API-Key noch nicht getestet');

define('MODULE_PAYMENT_SOFORT_REFRESH_INFO', 'Falls Sie diese Bestellung gerade bestätigt, angepasst, storniert oder gutgeschrieben haben, müssen Sie diese Seite ggf. {{refresh}} damit alle Änderungen sichtbar werden.');
define('MODULE_PAYMENT_SOFORT_REFRESH_PAGE', 'Klicken Sie hier, um die Seite neu zu laden');

//definition of error-codes that can resolve by calling the SOFORT-API
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_0',		'Es ist ein unbekannter Fehler aufgetreten.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8002',		'Fehler bei der Validierung aufgetreten.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010',		'Die Daten sind unvollständig oder fehlerhaft. Bitte korrigieren Sie diese oder kontaktieren Sie den Shopbetreiber.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8011',		'Nicht im Bereich gültiger Werte.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8012',		'Wert muss positiv sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8013',		'Es werden im Moment nur Bestellungen in Euro unterstützt. Bitte korrigieren Sie dies und versuchen es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8015',		'Der Gesamtbetrag ist zu groß oder zu klein. Bitte korrigieren Sie dies oder kontaktieren Sie den Shopbetreiber.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8017',		'Unbekannte Zeichen.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8018',		'Maximale Anzahl an Zeichen überschritten (max. 27).');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8019',		'Die Bestellung kann aufgrund fehlerhafter E-Mail-Adresse nicht durchgeführt werden. Bitte korrigieren Sie diese und versuchen Sie es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8020',		'Die Bestellung kann aufgrund fehlerhafter Telefonnummer nicht durchgeführt werden. Bitte korrigieren Sie diese und versuchen Sie es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8021',		'Die Länderkennung wird nicht unterstützt, bitte wenden Sie sich an Ihren Shopbetreiber.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8022',		'Die angegebene BIC ist nicht gültig.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8023',		'Die Bestellung kann aufgrund fehlerhafter BIC (Bank Identifier Code) nicht durchgeführt werden. Bitte korrigieren Sie diese und versuchen Sie es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8024',		'Die Bestellung kann aufgrund fehlerhafter Länderkennung nicht durchgeführt werden. Die Liefer-/Rechnungsadresse muss in Deutschland liegen. Bitte korrigieren Sie diese und versuchen Sie es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8029',		'Es werden nur deutsche Konten unterstützt. Bitte korrigieren Sie dies oder wählen Sie eine andere Zahlart.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8033',		'Der Gesamtbetrag ist zu hoch. Bitte korrigieren Sie dies und versuchen es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8034',		'Der Gesamtbetrag ist zu niedrig. Bitte korrigieren Sie dies und versuchen es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8041',		'Wert für Mehrwertsteuer fehlerhaft. Gültige Werte: 0, 7 oder 19.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8046',		'Die Validierung des Bankkontos und der Bankleitzahl ist fehlgeschlagen.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8047',		'Die maximale Anzahl von 255 Zeichen wurde überschritten.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8051',		'Die Anfrage enthielt ungültige Warenkorbpositionen. Bitte korrigieren Sie dies oder kontaktieren Sie den Shopbetreiber.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8058',		'Bitte geben Sie mindestens den Kontoinhaber an und versuchen Sie es dann erneut.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8061',		'Eine Transaktion mit den von Ihnen übermittelten Daten existiert bereits.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8068',		'Kauf auf Rechnung steht momentan nur Privatkunden zur Verfügung.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10001', 	'Bitte füllen Sie die Felder Kontonummer, Bankleitzahl und Kontoinhaber vollständig aus.'); //LS: holder and bankdata missing
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10002',	'Bitte die Datenschutzhinweise akzeptieren.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10003',	'Mit der gewählten Zahlart können Artikel wie Downloads oder Geschenkgutscheine leider nicht bezahlt werden.');  //RBS and virtual content is not allowed
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10004',	'Es ist ein unbekannter Fehler aufgetreten.');  //order could not be saved in table sofort_orders
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10005',	'Es ist ein unbekannter Fehler aufgetreten.');  //saving of order (after successful payment-process) MAYBE failed, seller informed
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_10006',	'Es ist ein unbekannter Fehler aufgetreten.');  //saving of order (after successful payment-process) REALLY failed, seller informed

//check for empty fields failed (code 8010 = 'must not be empty')
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.EMAIL_CUSTOMER',				'Die E-Mail-Adresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.PHONE_CUSTOMER',				'Die Telefonnummer darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.FIRSTNAME',	'Der Vorname der Rechnungsadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.FIRSTNAME',	'Der Vorname der Versandadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.LASTNAME',	'Der Nachname der Rechnungsadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.LASTNAME',	'Der Nachname der Versandadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.STREET',		'Straße und Hausnummer müssen durch ein Leerzeichen getrennt werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.STREET',		'Straße und Hausnummer müssen durch ein Leerzeichen getrennt werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.STREET_NUMBER',	'Straße und Hausnummer müssen durch ein Leerzeichen getrennt werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.STREET_NUMBER',	'Straße und Hausnummer müssen durch ein Leerzeichen getrennt werden.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.ZIPCODE',		'Die Postleitzahl der Rechnungsadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.ZIPCODE',	'Die Postleitzahl der Versandadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.CITY',		'Der Städtename der Rechnungsadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.CITY',		'Der Städtename der Versandadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.INVOICE_ADDRESS.COUNTRY_CODE',	'Das Länderkennzeichen der Rechnungsadresse darf nicht leer sein.');
define('MODULE_PAYMENT_SOFORT_MULTIPAY_XML_FAULT_8010.SHIPPING_ADDRESS.COUNTRY_CODE',	'Das Länderkennzeichen der Versandadresse darf nicht leer sein.');