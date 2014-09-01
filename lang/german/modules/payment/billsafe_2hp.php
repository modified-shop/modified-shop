<?php
/* -----------------------------------------------------------------------------------------
   $Id: billsafe_2hp.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = billsafe_2hp.php
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

define('MODULE_PAYMENT_BILLSAFE_2HP_TEXT_TITLE', 'Kauf auf Raten');
define('MODULE_PAYMENT_BILLSAFE_2HP_CHECKOUT_TEXT_INFO', 'Kaufen Sie bequem und schnell in %d Raten ab %s Euro/Monat. <br />(eff. Jahreszins: %s%%)');
//define('MODULE_PAYMENT_BILLSAFE_2HP_CHECKOUT_TEXT_INFO', 'Kaufen Sie bequem und schnell in %d Raten ab %s Euro/Monat. <br />(Bearbeitungsgeb&uuml;hr: %s eff. Jahreszins: %s%%)');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_COMMON', 'Leider ist der Kauf auf Raten nicht m&ouml;glich. Bitte w&auml;hlen Sie eine andere Zahlungsweise.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_101', 'Der Kauf auf Raten steht derzeit leider nicht zur Verf&uuml;gung, bitte w&auml;hlen Sie eine andere Zahlungsweise.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_102', 'Bei der Daten&uuml;bertragung ist ein Fehler aufgetreten. Bitte kontaktieren Sie uns.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_215', 'Bei der Daten&uuml;bertragung sind nicht alle erforderlichen Parameter &uuml;bergeben worden. Bitte kontaktieren Sie uns.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_216', 'Bei der Daten&uuml;bertragung sind ung&uuml;ltige Parameter &uuml;bergeben worden. Bitte kontaktieren Sie uns.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_COMPANY', 'Der Kauf auf Raten ist leider nur f&uuml;r Privatpersonen m&ouml;glich.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ERROR_MESSAGE_ADDRESS', 'Der Kauf auf Raten ist leider nicht bei abweichender Lieferadresse m&ouml;glich.');
define('MODULE_PAYMENT_BILLSAFE_2HP_STATUS_TEXT', 'Status');
define('MODULE_PAYMENT_BILLSAFE_2HP_TRANSACTIONID', 'BillSAFE Transaktions-ID');
define('MODULE_PAYMENT_BILLSAFE_2HP_CODE_TEXT', 'Code');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_TEXT', 'Message');
define('MODULE_PAYMENT_BILLSAFE_2HP_TEXT_DESCRIPTION', '<img src="images/icon_popup.gif" border="0" />&nbsp;<a href="https://client.billsafe.de" target="_blank" style="text-decoration: underline; font-weight: bold;">Zur BillSAFE-Website</a>');
define('MODULE_PAYMENT_BILLSAFE_2HP_STATUS_TITLE', 'BillSAFE Ratenkauf aktivieren');
define('MODULE_PAYMENT_BILLSAFE_2HP_STATUS_DESC', 'M&ouml;chten Sie Kauf auf Raten mit BillSAFE anbieten?');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_ID_TITLE', 'Merchant-ID');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_ID_DESC', 'Die Merchant-ID, die mit der BillSAFE-API genutzt wird.');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_LICENSE_TITLE', 'Merchant-License');
define('MODULE_PAYMENT_BILLSAFE_2HP_MERCHANT_LICENSE_DESC', 'Die Merchant-License, die mit der BillSAFE-API genutzt wird.');
define('MODULE_PAYMENT_BILLSAFE_2HP_MIN_ORDER_TITLE', 'Mindest-Bestellwert');
define('MODULE_PAYMENT_BILLSAFE_2HP_MIN_ORDER_DESC', 'Betrag, ab dem Kauf auf Rate mit BillSAFE angeboten wird.');
define('MODULE_PAYMENT_BILLSAFE_2HP_MAX_ORDER_TITLE', 'H&ouml;chst-Bestellwert');
define('MODULE_PAYMENT_BILLSAFE_2HP_MAX_ORDER_DESC', 'Betrag, bis zu dem Kauf auf Rate mit BillSAFE angeboten wird.');
define('MODULE_PAYMENT_BILLSAFE_2HP_BILLSAFE_LOGO_URL_TITLE', 'BillSAFE Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2HP_BILLSAFE_LOGO_URL_DESC', 'Speicherort des BillSAFE-Logos.');
define('MODULE_PAYMENT_BILLSAFE_2HP_SHOP_LOGO_URL_TITLE', 'Shop Logo URL');
define('MODULE_PAYMENT_BILLSAFE_2HP_SHOP_LOGO_URL_DESC', 'Speicherort des Shop-Logos.');
define('MODULE_PAYMENT_BILLSAFE_2HP_SERVER_TITLE', 'BillSAFE Server');
define('MODULE_PAYMENT_BILLSAFE_2HP_SERVER_DESC', 'Benutzen Sie das "LIVE"- oder "SANDBOX"-Gateway zur Zahlungsabwicklung?');
define('MODULE_PAYMENT_BILLSAFE_2HP_ZONE_TITLE', 'Zahlungszone');
define('MODULE_PAYMENT_BILLSAFE_2HP_ZONE_DESC', 'Wenn eine Zone ausgew&auml;hlt ist, gilt die Zahlungsmethode nur f&uuml;r diese Zone.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ORDER_STATUS_ID_TITLE', 'Bestellstatus festlegen');
define('MODULE_PAYMENT_BILLSAFE_2HP_ORDER_STATUS_ID_DESC', 'Bestellungen, welche mit diesem Modul gemacht werden, auf diesen Status setzen.');
define('MODULE_PAYMENT_BILLSAFE_2HP_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BILLSAFE_2HP_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');
define('MODULE_PAYMENT_BILLSAFE_2HP_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_BILLSAFE_2HP_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z. B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_FSHIPMENT', 'Komplettlieferung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PSHIPMENT', 'Teillieferung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_FSTORNO', 'Komplettstornierung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PSTORNO', 'Teilstornierung war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_FRETOURE', 'Komplettretoure war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PRETOURE', 'Teilretoure war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_VOUCHER', 'Anbietergutschrift war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_MESSAGE_PAUSETRANSACTION', 'Zahlungspause war erfolgreich');
define('MODULE_PAYMENT_BILLSAFE_2HP_DETAILS', 'BillSAFE Details');
define('MODULE_PAYMENT_BILLSAFE_2HP_BADDRESS', 'Rechnungsadresse (BillSAFE)');
define('MODULE_PAYMENT_BILLSAFE_2HP_SADDRESS', 'Versandadresse');
define('MODULE_PAYMENT_BILLSAFE_2HP_EMAIL', 'E-Mail');
define('MODULE_PAYMENT_BILLSAFE_2HP_PDETAILS', 'Ratenkauf Details');
define('MODULE_PAYMENT_BILLSAFE_2HP_NOTE', 'Hinweis');
define('MODULE_PAYMENT_BILLSAFE_2HP_PRODUCTS', 'Artikel');
define('MODULE_PAYMENT_BILLSAFE_2HP_MODEL', 'Artikel-Nr.');
define('MODULE_PAYMENT_BILLSAFE_2HP_TAX', 'MwSt.');
define('MODULE_PAYMENT_BILLSAFE_2HP_PRICE_EX', 'Preis (exkl.)');
define('MODULE_PAYMENT_BILLSAFE_2HP_PRICE_INC', 'Preis (inkl.)');
define('MODULE_PAYMENT_BILLSAFE_2HP_CHECK', 'Auswahl');
define('MODULE_PAYMENT_BILLSAFE_2HP_INC', 'inkl. ');
define('MODULE_PAYMENT_BILLSAFE_2HP_FREPORT_SHIPMENT', 'Komplettlieferung');
define('MODULE_PAYMENT_BILLSAFE_2HP_PREPORT_SHIPMENT', 'Teillieferung');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTSTORNOFULL', 'Komplettstornierung');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTSTORNOPART', 'Teilstornierung');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTRETOUREFULL', 'Komplettretoure');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTRETOUREPART', 'Teilretoure');
define('MODULE_PAYMENT_BILLSAFE_2HP_UPDATEARTICLELISTVOUCHER', 'Anbietergutschrift');
define('MODULE_PAYMENT_BILLSAFE_2HP_PREPORT_METHOD', 'Methode');
define('MODULE_PAYMENT_BILLSAFE_2HP_PREPORT_DATE', 'Datum');
define('MODULE_PAYMENT_BILLSAFE_2HP_JALERT', 'Bitte w&auml;hlen Sie mindestens ein Produkt aus.');
define('MODULE_PAYMENT_BILLSAFE_2HP_NO_ORDERID', 'Bestellnummer konnte nicht gefunden werden.');
define('MODULE_PAYMENT_BILLSAFE_2HP_VAT', '% MwSt.');
define('MODULE_PAYMENT_BILLSAFE_2HP_VALUE', 'Warenwert');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_TITLE', 'Log aktivieren');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_DESC', 'BillSAFE-Server R&uuml;ckmeldungen zur Fehlersuche verwenden.');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_TYPE_TITLE', 'Log-Art ausw&auml;hlen: Echo, per eMail senden lassen oder als Datei im Verzeichnis "/export" speichern lassen.');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_TYPE_DESC', '<b>Achtung</b>: "Echo" ist nur f&uuml;r Testzwecke im Backend des Shops gedacht. <b>Es sind keine Bestellungen m&ouml;glich!</b>');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_ADDR_TITLE', 'eMail-Adresse(n) f&uuml;r das Log');
define('MODULE_PAYMENT_BILLSAFE_2HP_LOG_ADDR_DESC', 'Mehrere eMail-Adressen mit "," trennen.');
define('MODULE_PAYMENT_BILLSAFE_2HP_MP', 'H&auml;ndlerportal');
define('MODULE_PAYMENT_BILLSAFE_2HP_BUTTON', 'Zu BillSAFE');
define('MODULE_PAYMENT_BILLSAFE_2HP_LAYER_TITLE', 'Payment Layer');
define('MODULE_PAYMENT_BILLSAFE_2HP_LAYER_DESC', 'M&ouml;chten Sie den Layer-Modus f&uuml;r Zahlungen per BillSAFE aktivieren? <b>Achtung: Unbedingt in den <i>Sessions</i>-Einstellungen den Parameter <i>Cookie Benutzung bevorzugen</i> auf <i>False</i> setzen!</b>');
?>