<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('MODULE_DHL_BUSINESS_TEXT_TITLE', 'DHL Anbindung');
  define('MODULE_DHL_BUSINESS_TEXT_DESCRIPTION', 'Bequem DHL Paketscheine aus dem Shop heraus drucken.');

  define('MODULE_DHL_BUSINESS_STATUS_TITLE', 'Status');
  define('MODULE_DHL_BUSINESS_STATUS_DESC', 'Modul aktivieren');
  define('MODULE_DHL_BUSINESS_USER_TITLE', '<hr noshade>Benutzer');
  define('MODULE_DHL_BUSINESS_USER_DESC', 'Benutzername vom DHL Gesch&auml;ftskundenportal');
  define('MODULE_DHL_BUSINESS_SIGNATURE_TITLE', 'Passwort');
  define('MODULE_DHL_BUSINESS_SIGNATURE_DESC', 'Passwort vom DHL Gesch&auml;ftskundenportal');
  define('MODULE_DHL_BUSINESS_EKP_TITLE', 'EKP');
  define('MODULE_DHL_BUSINESS_EKP_DESC', 'DHL Kundennummer');
  define('MODULE_DHL_BUSINESS_ACCOUNT_TITLE', 'Account');
  define('MODULE_DHL_BUSINESS_ACCOUNT_DESC', 'Account ID im Format ISO2:ID getrennt durch Komma (standard WORLD:01).<br>Sollte die Warenpost oder Retoure eine abweichende ID haben, dann mit Zusatz PK (Paket), WP (Warenpost) oder RT (Retoure). Beispiel: WORLD:01PK,WORLD:02WP,WORLD:03RT');
  define('MODULE_DHL_BUSINESS_PREFIX_TITLE', 'Absenderreferenz Prefix');
  define('MODULE_DHL_BUSINESS_PREFIX_DESC', 'Geben Sie ein Prefix f&uuml;r die Absenderreferenz an. Die Bestellnummer wird automatisch mit angeh&auml;ngt.');
  define('MODULE_DHL_BUSINESS_WEIGHT_CN23_TITLE', 'Gewicht CN23');
  define('MODULE_DHL_BUSINESS_WEIGHT_CN23_DESC', 'Geben Sie das Artikelgewicht f&uuml;r die Zollerkl&auml;rung an, sofern keines beim Artikel hinterlegt ist.');
  define('MODULE_DHL_BUSINESS_LOGLEVEL_TITLE', 'Log Level');
  define('MODULE_DHL_BUSINESS_LOGLEVEL_DESC', 'W&auml;hlen Sie das Log Level.');
  
  define('MODULE_DHL_BUSINESS_NOTIFICATION_TITLE', '<hr noshade>Benachrichtigung');
  define('MODULE_DHL_BUSINESS_NOTIFICATION_DESC', 'Soll als Standard Benachrichtigung via DHL vorausgew&auml;hlt werden?<br>Der Kunde wird von DHL per eMail &uuml;ber den Versand benachrichtigt.<br><b>Hinweis:</b> daf&uuml;r muss eine Einverst&auml;ndniserkl&auml;rung zur Weitergabe der E-Mail Adresse vom Kunden vorhanden sein.');
  define('MODULE_DHL_BUSINESS_STATUS_UPDATE_TITLE', 'Benachrichtigung &amp; Status aktualisieren');
  define('MODULE_DHL_BUSINESS_STATUS_UPDATE_DESC', 'Der Kunde wird per Mail inkl. Trackinginformation benachrichtigt und die Bestellung auf diesen Status gesetzt.');
  define('MODULE_DHL_BUSINESS_CODING_TITLE', 'Leitcodierung');
  define('MODULE_DHL_BUSINESS_CODING_DESC', 'Soll als Standard die Leitcodierung vorausgew&auml;hlt werden?');
  define('MODULE_DHL_BUSINESS_DISPLAY_LABEL_TITLE', 'Label anzeigen');
  define('MODULE_DHL_BUSINESS_DISPLAY_LABEL_DESC', 'Soll das DHL Label nach Erzeugung angezeigt (Popup) werden?');
  define('MODULE_DHL_BUSINESS_RETOURE_TITLE', 'Retouren Label');
  define('MODULE_DHL_BUSINESS_RETOURE_DESC', 'Soll zus&auml;tzlich noch ein Retourenlabel erzeugt werden?');
  define('MODULE_DHL_BUSINESS_PERSONAL_TITLE', 'Eigenh&auml;ndig');
  define('MODULE_DHL_BUSINESS_PERSONAL_DESC', 'Soll als Standard Eigenh&auml;ndig vorausgew&auml;hlt werden?');
  define('MODULE_DHL_BUSINESS_BULKY_TITLE', 'Sperrgut');
  define('MODULE_DHL_BUSINESS_BULKY_DESC', 'Soll als Standard Sperrgut vorausgew&auml;hlt werden?');
  define('MODULE_DHL_BUSINESS_NO_NEIGHBOUR_TITLE', 'Keine Nachbarschaftszustellung');
  define('MODULE_DHL_BUSINESS_NO_NEIGHBOUR_DESC', 'Soll als Standard keine Nachbarschaftszustellung vorausgew&auml;hlt werden?');
  define('MODULE_DHL_BUSINESS_PARCEL_OUTLET_TITLE', 'Filialrouting');
  define('MODULE_DHL_BUSINESS_PARCEL_OUTLET_DESC', 'Soll als Standard Filialrouting vorausgew&auml;hlt werden?');
  define('MODULE_DHL_BUSINESS_AVS_TITLE', 'Alterssichtpr&uuml;fung');
  define('MODULE_DHL_BUSINESS_AVS_DESC', 'Was soll als Standard f&uuml;r die Alterssichtpr&uuml;fung vorausgew&auml;hlt werden (0 ist deaktiviert)?');
  define('MODULE_DHL_BUSINESS_IDENT_TITLE', 'Alterspr&uuml;fung');
  define('MODULE_DHL_BUSINESS_IDENT_DESC', 'Was soll als Standard f&uuml;r die Alterspr&uuml;fung vorausgew&auml;hlt werden (0 ist deaktiviert)?');
  define('MODULE_DHL_BUSINESS_PREMIUM_TITLE', 'Premium');
  define('MODULE_DHL_BUSINESS_PREMIUM_DESC', 'Soll als Standard Premium vorausgew&auml;hlt werden?');
  define('MODULE_DHL_BUSINESS_ENDORSEMENT_TITLE', 'Vorausverf&uuml;gung');
  define('MODULE_DHL_BUSINESS_ENDORSEMENT_DESC', 'Wie sollen internationale Pakete behandelt werden, wenn sie nicht zugestellt werden k&ouml;nnen?');
  define('MODULE_DHL_BUSINESS_DUTYPAID_TITLE', 'Postversand verzollt');
  define('MODULE_DHL_BUSINESS_DUTYPAID_DESC', 'Die Deutsche Post und der Absender &uuml;bernehmen die Einfuhrabgaben anstelle des Empf&auml;ngers');
  define('MODULE_DHL_BUSINESS_DROPPOINT_TITLE', 'N&auml;chste Abgabestelle');
  define('MODULE_DHL_BUSINESS_DROPPOINT_DESC', 'Lieferung an eine Packstation, die der Adresse des Empf&auml;ngers der Sendung am n&auml;chsten liegt');
  define('MODULE_DHL_BUSINESS_SIGNED_TITLE', 'Empf&auml;nger Unterschrift');
  define('MODULE_DHL_BUSINESS_SIGNED_DESC', 'Soll die Lieferung vom Empf&auml;nger und nicht vom DHL Fahrer unterzeichnet werden?');

  define('MODULE_DHL_BUSINESS_COMPANY_TITLE', '<hr noshade>Kundendetails<br/>');
  define('MODULE_DHL_BUSINESS_COMPANY_DESC', 'Firma:');
  define('MODULE_DHL_BUSINESS_FIRSTNAME_TITLE', '');
  define('MODULE_DHL_BUSINESS_FIRSTNAME_DESC', 'Vorname:');
  define('MODULE_DHL_BUSINESS_LASTNAME_TITLE', '');
  define('MODULE_DHL_BUSINESS_LASTNAME_DESC', 'Nachname:');
  define('MODULE_DHL_BUSINESS_ADDRESS_TITLE', '');
  define('MODULE_DHL_BUSINESS_ADDRESS_DESC', 'Adresse:');
  define('MODULE_DHL_BUSINESS_POSTCODE_TITLE', '');
  define('MODULE_DHL_BUSINESS_POSTCODE_DESC', 'PLZ:');
  define('MODULE_DHL_BUSINESS_CITY_TITLE', '');
  define('MODULE_DHL_BUSINESS_CITY_DESC', 'Stadt:');
  define('MODULE_DHL_BUSINESS_TELEPHONE_TITLE', '');
  define('MODULE_DHL_BUSINESS_TELEPHONE_DESC', 'Telefon:');
  
  define('MODULE_DHL_BUSINESS_ACCOUNT_OWNER_TITLE', '<hr noshade>Bankdaten<br/>');
  define('MODULE_DHL_BUSINESS_ACCOUNT_OWNER_DESC', 'Kontoinhaber:');
  define('MODULE_DHL_BUSINESS_ACCOUNT_NUMBER_TITLE', '');
  define('MODULE_DHL_BUSINESS_ACCOUNT_NUMBER_DESC', 'Kontonummer:');
  define('MODULE_DHL_BUSINESS_BANK_CODE_TITLE', '');
  define('MODULE_DHL_BUSINESS_BANK_CODE_DESC', 'Bankleitzahl:');
  define('MODULE_DHL_BUSINESS_BANK_NAME_TITLE', '');
  define('MODULE_DHL_BUSINESS_BANK_NAME_DESC', 'Bankname:');
  define('MODULE_DHL_BUSINESS_IBAN_TITLE', '');
  define('MODULE_DHL_BUSINESS_IBAN_DESC', 'IBAN:');
  define('MODULE_DHL_BUSINESS_BIC_TITLE', '');
  define('MODULE_DHL_BUSINESS_BIC_DESC', 'BIC:');
