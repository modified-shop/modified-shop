<?php
/* --------------------------------------------------------------
   $Id: orders_edit.php,v 1.0 

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.27 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2006 XT-Commerce (orders_edit.php)

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

// Allgemeine Texte
define('TABLE_HEADING', 'Bestelldaten bearbeiten');
define('TABLE_HEADING_ORDER', 'Bestellung Nr:&nbsp;');
define('TEXT_SAVE_ORDER', 'Bestellung abschlie&szlig;en und neu berechnen.&nbsp;');

define('TEXT_EDIT_ADDRESS', 'Adress- und Kundendaten');
define('TEXT_EDIT_PRODUCTS', 'Artikel, Artikeloptionen und Preise');
define('TEXT_EDIT_OTHER', 'Versandkosten, Zahlungsweisen, W&auml;hrungen, Sprachen, Summen, MwSt., Rabatte, usw.');
define('TEXT_EDIT_GIFT', 'Gutscheine und Rabatt');
define('TEXT_EDIT_ADDRESS_SUCCESS', 'Adress&auml;nderung wurde gespeichert.');

define('IMAGE_EDIT_ADDRESS', 'Adressen bearbeiten oder einf&uuml;gen');
define('IMAGE_EDIT_PRODUCTS', 'Artikel und Optionen bearbeiten oder einf&uuml;gen');
define('IMAGE_EDIT_OTHER', 'Versandkosten, Zahlung, Gutscheine usw. bearbeiten oder einf&uuml;gen');

// Adressaenderung
define('TEXT_INVOICE_ADDRESS', 'Kundenadresse');
define('TEXT_SHIPPING_ADDRESS', 'Versandadresse');
define('TEXT_BILLING_ADDRESS', 'Rechnungsadresse');

define('TEXT_COMPANY', 'Firma:');
define('TEXT_NAME', 'Name:');
define('TEXT_STREET', 'Stra&szlig;e');
define('TEXT_ZIP', 'PLZ:');
define('TEXT_CITY', 'Stadt:');
define('TEXT_COUNTRY', 'Land:');
define('TEXT_CUSTOMER_GROUP', 'Kundengruppe in der Bestellung');
define('TEXT_CUSTOMER_EMAIL', 'E-Mail:');
define('TEXT_CUSTOMER_TELEPHONE', 'Telefon:');
define('TEXT_CUSTOMER_UST', 'USt-IdNr.:');
define('TEXT_CUSTOMER_CID', 'Kundennummer:');
define('TEXT_ORDERS_ADDRESS_EDIT_INFO', 'Bitte beachten Sie, dass die hier eingetragenen Daten nur in der Bestellung und nicht im Kundenkonto ge&auml;ndert werden!');

// Artikelbearbeitung

define('TEXT_SMALL_NETTO', '(Netto)');
define('TEXT_PRODUCT_ID', 'pID:');
define('TEXT_PRODUCTS_MODEL', 'Art.Nr:');
define('TEXT_QUANTITY', 'Anzahl:');
define('TEXT_WEIGHT', 'Gewicht:');
define('TEXT_PRODUCT', 'Artikel:');
define('TEXT_TAX', 'MWSt.:');
define('TEXT_PRICE', 'Preis:');
define('TEXT_FINAL', 'Gesamt:');
define('TEXT_PRODUCT_SEARCH', 'Artikelsuche:');

define('TEXT_PRODUCT_OPTION', 'Artikelmerkmale:');
define('TEXT_PRODUCT_OPTION_VALUE', 'Optionswert:');
define('TEXT_PRICE_PREFIX', 'Preis Prefix:');
define('TEXT_WEIGHT_PREFIX', 'Gewicht Prefix:');
define('TEXT_INS', 'Hinzuf&uuml;gen:');
define('TEXT_COD_COSTS', 'Nachnahmekosten');
define('TEXT_VALUE', 'Preis');
define('TEXT_DESC', 'Einf&uuml;gen');

// Sonstiges

define('TEXT_PAYMENT', 'Zahlungsweise:');
define('TEXT_SHIPPING', 'Versandart:');
define('TEXT_LANGUAGE', 'Sprache:');
define('TEXT_CURRENCIES', 'W&auml;hrungen:');
define('TEXT_ORDER_TOTAL', 'Zusammenfassung:');
define('TEXT_SAVE', 'Speichern');
define('TEXT_ACTUAL', 'Aktuell: ');
define('TEXT_NEW', 'Neu: ');

define('TEXT_ORDERS_EDIT_INFO', '<b>Wichtige Hinweise:</b><br />
Bitte bei den Adress-/Kundendaten die richtige Kundengruppe w&auml;hlen.<br />
Bei einem Wechsel der Kundengruppe sind alle Einzelposten der Rechnung neu abzuspeichern!<br />
Versandkosten m&uuml;ssen manuell ge&auml;ndert werden!<br />
Hierbei sind je nach Kundengruppe die Versandkosten brutto oder netto einzutragen!<br />
');

define('TEXT_CUSTOMER_GROUP_INFO', ' Bei einem Wechsel der Kundengruppe sind alle Einzelposten der Rechnung neu abzuspeichern!');

define('TEXT_ORDER_TITLE', 'Titel:');
define('TEXT_ORDER_VALUE', 'Wert:');
define('ERROR_INPUT_TITLE', 'Keine Eingabe bei Titel');
define('ERROR_INPUT_EMPTY', 'Keine Eingabe bei Titel und Wert');
define('ERROR_INPUT_SHIPPING_TITLE', 'Es wurde noch kein Versandkostenmodul ausgew&auml;hlt!');

// note for graduated prices
define('TEXT_ORDERS_PRODUCT_EDIT_INFO', '<b>Hinweis:</b> Bei Staffelpreisen muss der Einzelpreis manuell angepasst werden!');

define('TEXT_FIRSTNAME', 'Vorname:');
define('TEXT_LASTNAME', 'Nachname:');

define('TEXT_GENDER', 'Anrede:'); 
define('TEXT_MR', 'Herr'); 
define('TEXT_MRS', 'Frau'); 

define('TEXT_SAVE_CUSTOMERS_DATA', 'Kundendaten speichern');

define('TEXT_PRODUCTS_SEARCH_INFO', ' Artikelname oder Art.-Nr. oder GTIN/EAN');
define('TEXT_PRODUCTS_STATUS', 'Status:');
define('TEXT_PRODUCTS_IMAGE', 'Artikelbild:');
define('TEXT_PRODUCTS_QTY', 'Lagerbestand:');
define('TEXT_PRODUCTS_EAN', 'GTIN/EAN:');
define('TEXT_PRODUCTS_TAX_RATE', 'Steuersatz:');
define('TEXT_PRODUCTS_DATE_AVAILABLE', 'Erscheinungsdatum:');
define('TEXT_IMAGE_NONEXISTENT', '---');

define('ERROR_STATUS_CHANGE', 'ACHTUNG: Nach &Auml;nderung der Kundengruppe m&uuml;ssen eventuell Versandkosten, Zuschl&auml;ge und Rabatte manuell angepasst werden.');