<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_2.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_2.php
* location = /lang/german/modules/payment
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 Bernd Blazynski
* @license GPLv2
*/

// BOF - Changed to white label solution
/*
define('MODULE_PAYMENT_BILLSAFE_2_TEXT_TITLE', 'Kauf auf Rechnung mit BillSAFE');
define('MODULE_PAYMENT_BILLSAFE_2_CHECKOUT_TEXT_INFO', 'Bezahlen Sie bequem per Rechnung. Ganz einfach und unb&uuml;rokratisch mit BillSAFE, einem Service von PayPal.');
*/
define('MODULE_PAYMENT_BILLSAFE_2_TEXT_TITLE', 'Rechnung');
define('MODULE_PAYMENT_BILLSAFE_2_CHECKOUT_TEXT_INFO', 'Kaufen Sie jetzt auf Rechnung und begutachten Sie Ihre Eink&auml;ufe in Ruhe bevor Sie bezahlen.');
// EOF - Changed to white label solution
define('MODULE_PAYMENT_BILLSAFE_2_SCHG_TEXT_INFO', 'F&uuml;r diese Zahlweise erheben wir einen Zuschlag von: ');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMMON', 'Leider ist der Rechnungskauf &uuml;ber BillSAFE nicht m&ouml;glich. Bitte w&auml;hlen Sie eine andere Zahlungsweise.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_101', 'F&uuml;r diese Transaktion steht die Zahlart BillSAFE nicht zur Verf&uuml;gung. Bitte w&auml;hlen Sie eine andere Zahlungsweise.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_102', 'Bei der Daten&uuml;bertragung ist ein Fehler aufgetreten. Bitte kontaktieren Sie uns.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_215', 'Bei der Daten&uuml;bertragung sind nicht alle erforderlichen Parameter &uuml;bergeben worden. Bitte kontaktieren Sie uns.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_216', 'Bei der Daten&uuml;bertragung sind ung&uuml;ltige Parameter &uuml;bergeben worden. Bitte kontaktieren Sie uns.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_COMPANY', 'Die Zahlung &uuml;ber BillSAFE ist leider nur f&uuml;r Privatpersonen m&ouml;glich.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_ADDRESS', 'Die Zahlung &uuml;ber BillSAFE ist leider nicht bei abweichender Lieferadresse m&ouml;glich.');
define('MODULE_PAYMENT_BILLSAFE_2_ERROR_MESSAGE_INLINE', 'Sie haben nicht alle Pflichtangaben gemacht. Bitte &uuml;berpr&uuml;fen Sie Ihre Angaben zur gew&auml;hlten Zahlweise!');
define('MODULE_PAYMENT_BILLSAFE_2_STATUS_TEXT', 'Status');
define('MODULE_PAYMENT_BILLSAFE_2_TRANSACTIONID', 'BillSAFE Transaktions-ID');
define('MODULE_PAYMENT_BILLSAFE_2_CODE_TEXT', 'Code');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_TEXT', 'Message');
define('MODULE_PAYMENT_BILLSAFE_2_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0" />&nbsp;<a href="https://client.billsafe.de" target="_blank" style="text-decoration: underline; font-weight: bold;">Zur BillSAFE-Website</a>');
define('MODULE_PAYMENT_BILLSAFE_2_STATUS_TITLE', 'BillSAFE Rechnungskauf aktivieren');
define('MODULE_PAYMENT_BILLSAFE_2_STATUS_DESC', 'M&ouml;chten Sie Kauf auf Rechnung mit BillSAFE anbieten?');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID_TITLE', 'Merchant-ID');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_ID_DESC', 'Die Merchant-ID, die mit der BillSAFE-API genutzt wird.');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE_TITLE', 'Merchant-License');
define('MODULE_PAYMENT_BILLSAFE_2_MERCHANT_LICENSE_DESC', 'Die Merchant-License, die mit der BillSAFE-API genutzt wird.');
define('MODULE_PAYMENT_BILLSAFE_2_PKEY_TITLE', 'Public Key');
define('MODULE_PAYMENT_BILLSAFE_2_PKEY_DESC', 'Von BillSAFE bereitgestellter Sicherheitscode. Sie k&ouml;nnen Ihren Code im H&auml;ndlerportal einsehen.');
define('MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER_TITLE', 'Mindest-Bestellwert');
define('MODULE_PAYMENT_BILLSAFE_2_MIN_ORDER_DESC', 'Betrag, ab dem Kauf auf Rechnung mit BillSAFE angeboten wird.');
define('MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER_TITLE', 'H&ouml;chst-Bestellwert');
define('MODULE_PAYMENT_BILLSAFE_2_MAX_ORDER_DESC', 'Betrag, bis zu dem Kauf auf Rechnung mit BillSAFE angeboten wird.');
define('MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL_TITLE', 'BillSAFE Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2_BILLSAFE_LOGO_URL_DESC', 'Speicherort des BillSAFE-Logos.');
define('MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL_TITLE', 'Shop Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2_SHOP_LOGO_URL_DESC', 'Speicherort des Shop-Logos.');
define('MODULE_PAYMENT_BILLSAFE_2_SERVER_TITLE', 'BillSAFE Server');
define('MODULE_PAYMENT_BILLSAFE_2_SERVER_DESC', 'Benutzen Sie das "LIVE"- oder "SANDBOX"-Gateway zur Zahlungsabwicklung?');
define('MODULE_PAYMENT_BILLSAFE_2_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_BILLSAFE_2_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLSAFE_2_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen.');
define('MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLSAFE_2_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_BILLSAFE_2_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLSAFE_2_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z. B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FSHIPMENT', 'Komplettlieferung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PSHIPMENT', 'Teillieferung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FSTORNO', 'Komplettstornierung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PSTORNO', 'Teilstornierung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_FRETOURE', 'Komplettretoure war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PRETOURE', 'Teilretoure war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_VOUCHER', 'Anbietergutschrift war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_PAUSETRANSACTION', 'Zahlungspause war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_DETAILS', 'BillSAFE Details');
define('MODULE_PAYMENT_BILLSAFE_2_BADDRESS', 'Rechnungsadresse (BillSAFE)');
define('MODULE_PAYMENT_BILLSAFE_2_SADDRESS', 'Versandadresse');
define('MODULE_PAYMENT_BILLSAFE_2_EMAIL', 'E-Mail');
define('MODULE_PAYMENT_BILLSAFE_2_BANK_DETAILS', 'Bank Details');
define('MODULE_PAYMENT_BILLSAFE_2_BANK_CODE', 'Bankleitzahl');
define('MODULE_PAYMENT_BILLSAFE_2_BANK_NAME', 'Name der Bank');
define('MODULE_PAYMENT_BILLSAFE_2_ACCOUNT_NUMBER', 'Kontonummer');
define('MODULE_PAYMENT_BILLSAFE_2_RECIPIENT', 'Zahlungsempf&auml;nger');
define('MODULE_PAYMENT_BILLSAFE_2_BIC', 'BIC');
define('MODULE_PAYMENT_BILLSAFE_2_IBAN', 'IBAN');
define('MODULE_PAYMENT_BILLSAFE_2_REFERENCE', 'Verwendungszweck');
define('MODULE_PAYMENT_BILLSAFE_2_REFERENCE2', 'Verwendungszweck 2');
define('MODULE_PAYMENT_BILLSAFE_2_NOTE', 'Hinweis');
define('MODULE_PAYMENT_BILLSAFE_2_AMOUNT', 'Zu zahlender Betrag');
define('MODULE_PAYMENT_BILLSAFE_2_PRODUCTS', 'Artikel');
define('MODULE_PAYMENT_BILLSAFE_2_MODEL', 'Artikel-Nr.');
define('MODULE_PAYMENT_BILLSAFE_2_TAX', 'MwSt.');
define('MODULE_PAYMENT_BILLSAFE_2_PRICE_EX', 'Preis (exkl.)');
define('MODULE_PAYMENT_BILLSAFE_2_PRICE_INC', 'Preis (inkl.)');
define('MODULE_PAYMENT_BILLSAFE_2_CHECK', 'Auswahl');
define('MODULE_PAYMENT_BILLSAFE_2_INC', 'inkl. ');
define('MODULE_PAYMENT_BILLSAFE_2_TOTAL', 'Gesamtbetrag');
define('MODULE_PAYMENT_BILLSAFE_2_FREPORT_SHIPMENT', 'Komplettlieferung');
define('MODULE_PAYMENT_BILLSAFE_2_PREPORT_SHIPMENT', 'Teillieferung');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOFULL', 'Komplettstornierung');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTSTORNOPART', 'Teilstornierung');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREFULL', 'Komplettretoure');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTRETOUREPART', 'Teilretoure');
define('MODULE_PAYMENT_BILLSAFE_2_UPDATEARTICLELISTVOUCHER', 'Anbietergutschrift');
define('MODULE_PAYMENT_BILLSAFE_2_PREPORT_METHOD', 'Methode');
define('MODULE_PAYMENT_BILLSAFE_2_PREPORT_DATE', 'Datum');
define('MODULE_PAYMENT_BILLSAFE_2_JALERT', 'Bitte w&auml;hlen Sie mindestens ein Produkt aus.');
define('MODULE_PAYMENT_BILLSAFE_2_NO_ORDERID', 'Bestellnummer konnte nicht gefunden werden.');
define('MODULE_PAYMENT_BILLSAFE_2_VAT', '% MwSt.');
define('MODULE_PAYMENT_BILLSAFE_2_VALUE', 'Warenwert');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_TITLE', 'Log aktivieren');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_DESC', 'BillSAFE-Server R&uuml;ckmeldungen zur Fehlersuche verwenden.');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE_TITLE', 'Log-Art ausw&auml;hlen: Echo, per eMail senden lassen oder als Datei im Verzeichnis "/export" speichern lassen.');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_TYPE_DESC', '<b>Achtung</b>: "Echo" ist nur f&uuml;r Testzwecke im Backend des Shops gedacht. <b>Es sind keine Bestellungen m&ouml;glich!</b>');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR_TITLE', 'eMail-Adresse(n) f&uuml;r das Log');
define('MODULE_PAYMENT_BILLSAFE_2_LOG_ADDR_DESC', 'Mehrere eMail-Adressen mit "," trennen.');
define('MODULE_PAYMENT_BILLSAFE_2_PAUSETRANSACTION', 'Zahlungspause');
define('MODULE_PAYMENT_BILLSAFE_2_PAUSEDAYS', 'Tage');
define('MODULE_PAYMENT_BILLSAFE_2_SCHG_TITLE', 'Zahlartenaufschlag');
define('MODULE_PAYMENT_BILLSAFE_2_SCHG_DESC', 'Aufschlag f&uuml;r die Zahlung per BillSAFE. Ohne Aufschlag Feld leer lassen. Fixer Aufschlag als Nettobetrag, prozentualer Aufschlag mit "%-Zeichen" (z. B. 3%). <b>Achtung: Der Aufschlag muss mit BillSAFE vereinbart sein und darf den dort hinterlegten Wert nicht &uuml;berschreiten!</b>');
define('MODULE_PAYMENT_BILLSAFE_2_SCHGTAX_TITLE', 'Steuersatz f&uuml;r Zahlartenaufschlag');
define('MODULE_PAYMENT_BILLSAFE_2_SCHGTAX_DESC', 'Den gew&uuml;nschten Steuersatz ausw&auml;hlen.');
define('MODULE_PAYMENT_BILLSAFE_2_MP', 'H&auml;ndlerportal');
define('MODULE_PAYMENT_BILLSAFE_2_BUTTON', 'Zu BillSAFE');
define('MODULE_PAYMENT_BILLSAFE_2_DPAYMENT', 'Direktzahlung durch Kunden');
define('MODULE_PAYMENT_BILLSAFE_2_REPORT_DPAYMENT', 'Direktzahlung jetzt melden');
define('MODULE_PAYMENT_BILLSAFE_2_MESSAGE_DPAYMENT', 'Meldung Direktzahlung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2_DAY', 'Tag');
define('MODULE_PAYMENT_BILLSAFE_2_MONTH', 'Monat');
define('MODULE_PAYMENT_BILLSAFE_2_YEAR', 'Jahr');
define('MODULE_PAYMENT_BILLSAFE_2_LAYER_TITLE', 'Payment Layer');
define('MODULE_PAYMENT_BILLSAFE_2_LAYER_DESC', 'M&ouml;chten Sie den Layer-Modus f&uuml;r Zahlungen per BillSAFE aktivieren? <b>Hinweis: Funktioniert nicht zusammen mit Inline-Checkout! Achtung: Unbedingt in den <i>Sessions</i>-Einstellungen den Parameter <i>Cookie Benutzung bevorzugen</i> auf <i>False</i> setzen!</b>');
define('MODULE_PAYMENT_BILLSAFE_2_INLINE_TITLE', 'Onsite Checkout');
define('MODULE_PAYMENT_BILLSAFE_2_INLINE_DESC', 'M&ouml;chten Sie den Onsite Checkout f&uuml;r Zahlungen per BillSAFE aktivieren? <b>Hinweis: Funktioniert nicht zusammen mit dem Payment Layer! F&uuml;r diese Funktionalit&auml;t ist eine Zusatzvereinbarung mit BillSAFE erforderlich.</b>');
define('MODULE_PAYMENT_BILLSAFE_2_DOB', 'Bitte geben Sie hier noch Ihr Geburtsdatum ein (z. B. 21.05.1970):');
define('MODULE_PAYMENT_BILLSAFE_2_TC', '<b>Ich stimme den <a class="info" href="https://www.billsafe.de/privacy-policy/buyer" target="_blank">Datenschutzgrunds&auml;tzen</a> und der <a class="info" href="https://www.billsafe.de/privacy-policy/credit-check" target="_blank">Bonit&auml;tspr&uuml;fung</a> von <a class="info" href="https://www.billsafe.de/imprint" target="_blank">PayPal</a> zu. Es gelten die <a class="info" href="https://www.billsafe.de/resources/docs/pdf/Kaeufer_AGB.pdf" target="_blank">Allgemeinen Nutzungsbedingungen</a> f&uuml;r den Rechnungskauf.</b>');
?>