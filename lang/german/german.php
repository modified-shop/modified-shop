<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(german.php,v 1.119 2003/05/19); www.oscommerce.com
   (c) 2003 nextcommerce (german.php,v 1.25 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Coding: utf-8[äöüßÄÖÜ]
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
 *
 *  DATE / TIME
 *
 */

define('TITLE', STORE_NAME);
define('HEADER_TITLE_TOP', 'Startseite');
define('HEADER_TITLE_CATALOG', 'Katalog');
define('HTML_PARAMS','xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr"');
//@setlocale(LC_TIME, 'de_DE@euro', 'de_DE', 'de-DE', 'de', 'ge', 'de_DE.UTF-8', 'German');
@setlocale(LC_TIME, 'de_DE.UTF-8' ,'de_DE@euro', 'de_DE', 'de-DE', 'de', 'ge', 'de_DE.ISO_8859-1', 'German','de_DE.ISO_8859-15');

define('DATE_FORMAT_SHORT', '%d.%m.%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A, %d. %B %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd.m.Y');  // this is used for strftime()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
define('DOB_FORMAT_STRING', 'tt.mm.jjjj');

function xtc_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
  }
}


// BOF - vr - 2009-12-11 - Added language dependent currency code
// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency when changing language,
// instead of staying with the applications default currency
define('LANGUAGE_CURRENCY', 'EUR');
// EOF - vr - 2009-12-11 - Added language dependent currency code

define('MALE', 'Herr');
define('FEMALE', 'Frau');

/*
 *
 *  BOXES
 *
 */

// text for gift voucher redeeming
define('IMAGE_REDEEM_GIFT','Gutschein Einl&ouml;sen!');

define('BOX_TITLE_STATISTICS','Statistik:');
define('BOX_ENTRY_CUSTOMERS','Kunden:');
define('BOX_ENTRY_PRODUCTS','Artikel:');
define('BOX_ENTRY_REVIEWS','Bewertungen:');
define('TEXT_VALIDATING','Nicht best&auml;tigt:');

// manufacturer box text
define('BOX_MANUFACTURER_INFO_HOMEPAGE', '%s Homepage');
define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', 'Mehr Artikel');

define('BOX_HEADING_ADD_PRODUCT_ID','In den Korb legen');

define('BOX_LOGINBOX_STATUS','Kundengruppe: ');
define('BOX_LOGINBOX_DISCOUNT','Artikelrabatt');
define('BOX_LOGINBOX_DISCOUNT_TEXT','Rabatt');
define('BOX_LOGINBOX_DISCOUNT_OT','');

// reviews box text in includes/boxes/reviews.php
define('BOX_REVIEWS_WRITE_REVIEW', 'Bewerten Sie diesen Artikel!');
define('BOX_REVIEWS_NO_WRITE_REVIEW', 'Keine Bewertung m&ouml;glich.');
define('BOX_REVIEWS_TEXT_OF_5_STARS', '%s von 5 Sternen!');

// pull down default text
define('PULL_DOWN_DEFAULT', 'Bitte w&auml;hlen');

// javascript messages
define('JS_ERROR', 'Notwendige Angaben fehlen! Bitte richtig ausf&uuml;llen.\n\n');

define('JS_REVIEW_TEXT', '* Der Text muss aus mindestens ' . REVIEW_TEXT_MIN_LENGTH . ' Buchstaben bestehen.\n\n');
define('JS_REVIEW_RATING', '* Geben Sie Ihre Bewertung ein.\n\n');
define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Bitte w&auml;hlen Sie eine Zahlungsweise f&uuml;r Ihre Bestellung.\n');
define('JS_ERROR_SUBMITTED', 'Diese Seite wurde bereits best&auml;tigt. Klicken Sie bitte auf OK und warten Sie, bis der Prozess durchgef&uuml;hrt wurde.');
define('ERROR_NO_PAYMENT_MODULE_SELECTED', '* Bitte w&auml;hlen Sie eine Zahlungsweise f&uuml;r Ihre Bestellung.');

/*
 *
 * ACCOUNT FORMS
 *
 */

define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_GENDER_ERROR', 'Bitte w&auml;hlen Sie Ihre Anrede aus.');
define('ENTRY_GENDER_TEXT', '*');
define('ENTRY_FIRST_NAME_ERROR', 'Ihr Vorname muss aus mindestens ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_FIRST_NAME_TEXT', '*');
define('ENTRY_LAST_NAME_ERROR', 'Ihr Nachname muss aus mindestens ' . ENTRY_LAST_NAME_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_LAST_NAME_TEXT', '*');
define('ENTRY_DATE_OF_BIRTH_ERROR', 'Ihr Geburtsdatum muss im Format TT.MM.JJJJ (zB. 21.05.1970) eingeben werden');
define('ENTRY_DATE_OF_BIRTH_TEXT', '* (zB. 21.05.1970)');
define('ENTRY_EMAIL_ADDRESS_ERROR', 'Ihre E-Mail-Adresse muss aus mindestens ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Ihre eingegebene E-Mail-Adresse ist fehlerhaft - bitte &uuml;berpr&uuml;fen Sie diese.');
define('ENTRY_EMAIL_ERROR_NOT_MATCHING', 'Ihre E-Mail-Adressen stimmen nicht &uuml;berein.'); // Hetfield - 2009-08-15 - confirm e-mail at registration
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', 'Ihre eingegebene E-Mail-Adresse existiert bereits - bitte &uuml;berpr&uuml;fen Sie diese.');
define('ENTRY_EMAIL_ADDRESS_TEXT', '*');
define('ENTRY_STREET_ADDRESS_ERROR', 'Strasse/Nr. muss aus mindestens ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_STREET_ADDRESS_TEXT', '*');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POST_CODE_ERROR', 'Ihre Postleitzahl muss aus mindestens ' . ENTRY_POSTCODE_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_POST_CODE_TEXT', '*');
define('ENTRY_CITY_ERROR', 'Ort muss aus mindestens ' . ENTRY_CITY_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_CITY_TEXT', '*');
define('ENTRY_STATE_ERROR', 'Ihr Bundesland muss aus mindestens ' . ENTRY_STATE_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_STATE_ERROR_SELECT', 'Bitte w&auml;hlen Sie ihr Bundesland aus der Liste aus.');
define('ENTRY_STATE_TEXT', '*');
define('ENTRY_COUNTRY_ERROR', 'Bitte w&auml;hlen Sie Ihr Land aus der Liste aus.');
define('ENTRY_COUNTRY_TEXT', '*');
define('ENTRY_TELEPHONE_NUMBER_ERROR', 'Ihre Telefonnummer muss aus mindestens ' . ENTRY_TELEPHONE_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_TELEPHONE_NUMBER_TEXT', '*');
define('ENTRY_FAX_NUMBER_TEXT', '');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_PASSWORD_ERROR', 'Ihr Passwort muss aus mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'Ihre Passw&ouml;rter stimmen nicht &uuml;berein.');
define('ENTRY_PASSWORD_TEXT', '*');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT_ERROR', 'Ihr Passwort muss aus mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_PASSWORD_NEW_TEXT', '*');
define('ENTRY_PASSWORD_NEW_ERROR', 'Ihr neues Passwort muss aus mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen bestehen.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', 'Ihre Passw&ouml;rter stimmen nicht &uuml;berein.');

/*
 *
 *  RESULT PAGES
 *
 */

define('TEXT_RESULT_PAGE', 'Seiten:');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS', 'Zeige <strong>%d</strong> bis <strong>%d</strong> (von insgesamt <strong>%d</strong> Artikeln)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS', 'Zeige <strong>%d</strong> bis <strong>%d</strong> (von insgesamt <strong>%d</strong> Bestellungen)');
define('TEXT_DISPLAY_NUMBER_OF_REVIEWS', 'Zeige <strong>%d</strong> bis <strong>%d</strong> (von insgesamt <strong>%d</strong> Bewertungen)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW', 'Zeige <strong>%d</strong> bis <strong>%d</strong> (von insgesamt <strong>%d</strong> neuen Artikeln)');
define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'Zeige <strong>%d</strong> bis <strong>%d</strong> (von insgesamt <strong>%d</strong> Angeboten)');

/*
 *
 * SITE NAVIGATION
 *
 */

define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'vorherige Seite');
define('PREVNEXT_TITLE_NEXT_PAGE', 'n&auml;chste Seite');
define('PREVNEXT_TITLE_PAGE_NO', 'Seite %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Vorhergehende %d Seiten');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'N&auml;chste %d Seiten');

/*
 *
 * PRODUCT NAVIGATION
 *
 */

define('PREVNEXT_BUTTON_PREV', '[&lt;&lt;&nbsp;vorherige]');
define('PREVNEXT_BUTTON_NEXT', '[n&auml;chste&nbsp;&gt;&gt;]');

/*
 *
 * IMAGE BUTTONS
 *
 */

define('IMAGE_BUTTON_ADD_ADDRESS', 'Neue Adresse');
define('IMAGE_BUTTON_BACK', 'Zur&uuml;ck');
define('IMAGE_BUTTON_CHANGE_ADDRESS', 'Adresse &auml;ndern');
define('IMAGE_BUTTON_CHECKOUT', 'Kasse');
define('IMAGE_BUTTON_CONFIRM_ORDER', 'Kaufen');
define('IMAGE_BUTTON_CONTINUE', 'Weiter');
define('IMAGE_BUTTON_DELETE', 'L&ouml;schen');
define('IMAGE_BUTTON_LOGIN', 'Anmelden');
define('IMAGE_BUTTON_IN_CART', 'In den Warenkorb');
define('IMAGE_BUTTON_SEARCH', 'Suchen');
define('IMAGE_BUTTON_UPDATE', 'Aktualisieren');
define('IMAGE_BUTTON_UPDATE_CART', 'Warenkorb aktualisieren');
define('IMAGE_BUTTON_WRITE_REVIEW', 'Bewertung schreiben');
define('IMAGE_BUTTON_ADMIN', 'Admin');
define('IMAGE_BUTTON_PRODUCT_EDIT', 'Produkt bearbeiten');
define('IMAGE_BUTTON_CONTENT_EDIT', 'Inhalt bearbeiten');
// BOF - vr - 2010-02-20 removed double definition
// define('IMAGE_BUTTON_LOGIN', 'Anmelden');
// EOF - vr - 2010-02-20 removed double definition
define('IMAGE_BUTTON_SEND', 'Absenden'); //DokuMan - 2010-03-15 - Added button description for contact form
define('IMAGE_BUTTON_CONTINUE_SHOPPING', 'Einkauf Fortsetzen'); //Hendrik - 2010-11-12 - used in template xtc5...shopping_cart.html

define('SMALL_IMAGE_BUTTON_DELETE', 'L&ouml;schen');
define('SMALL_IMAGE_BUTTON_EDIT', '&Auml;ndern');
define('SMALL_IMAGE_BUTTON_VIEW', 'Anzeigen');

define('ICON_ARROW_RIGHT', 'Zeige mehr');
define('ICON_CART', 'In den Warenkorb');
define('ICON_SUCCESS', 'Erfolg');
define('ICON_WARNING', 'Warnung');
define('ICON_ERROR', 'Fehler');

define('TEXT_PRINT', 'drucken'); //DokuMan - 2009-05-26 - Added description for 'account_history_info.php'

/*
 *
 *  GREETINGS
 *
 */

define('TEXT_GREETING_PERSONAL', 'Sch&ouml;n, dass Sie wieder da sind, <span class="greetUser">%s!</span> M&ouml;chten Sie sich unsere <a style="text-decoration:underline;" href="%s">neuen Artikel</a> ansehen?');
define('TEXT_GREETING_PERSONAL_RELOGON', '<small>Wenn Sie nicht %s sind, melden Sie sich bitte <a style="text-decoration:underline;" href="%s">hier</a> mit Ihren Anmeldedaten an.</small>');
define('TEXT_GREETING_GUEST', 'Herzlich Willkommen <span class="greetUser">Gast!</span> M&ouml;chten Sie sich <a style="text-decoration:underline;" href="%s">anmelden</a>? Oder wollen Sie ein <a style="text-decoration:underline;" href="%s">Kundenkonto</a> er&ouml;ffnen?');

define('TEXT_SORT_PRODUCTS', 'Sortierung der Artikel ist ');
define('TEXT_DESCENDINGLY', 'absteigend');
define('TEXT_ASCENDINGLY', 'aufsteigend');
define('TEXT_BY', ' nach ');

define('TEXT_OF_5_STARS', '%s von 5 Sternen!');
define('TEXT_REVIEW_BY', 'von %s');
define('TEXT_REVIEW_WORD_COUNT', '%s Worte');
define('TEXT_REVIEW_RATING', 'Bewertung: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', 'Hinzugef&uuml;gt am: %s');
define('TEXT_NO_REVIEWS', 'Es liegen noch keine Bewertungen vor.');
define('TEXT_NO_NEW_PRODUCTS', 'Keine neuen Artikel in den letzten '.MAX_DISPLAY_NEW_PRODUCTS_DAYS.' Tagen erschienen. Stattdessen sehen Sie hier die 10 zuletzt erschienenen Artikel.'); // Hetfield - 2009-08-11 - changed text for new products_new function
define('TEXT_UNKNOWN_TAX_RATE', 'Unbekannter Steuersatz');

/*
 *
 * WARNINGS
 *
 */

define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Warnung: Das Installationverzeichnis ist noch vorhanden auf: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/xtc_installer. Bitte l&ouml;schen Sie das Verzeichnis aus Gr&uuml;nden der Sicherheit!');
define('WARNING_CONFIG_FILE_WRITEABLE', 'Warnung: xtcModified kann in die Konfigurationsdatei schreiben: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php. Das stellt ein m&ouml;gliches Sicherheitsrisiko dar - bitte korrigieren Sie die Benutzerberechtigungen zu dieser Datei!');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'Warnung: Das Verzeichnis f&uuml;r die Sessions existiert nicht: ' . xtc_session_save_path() . '. Die Sessions werden nicht funktionieren bis das Verzeichnis erstellt wurde!');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'Warnung: xtcModified kann nicht in das Sessions Verzeichnis schreiben: ' . xtc_session_save_path() . '. Die Sessions werden nicht funktionieren bis die richtigen Benutzerberechtigungen gesetzt wurden!');
define('WARNING_SESSION_AUTO_START', 'Warnung: session.auto_start ist aktiviert (enabled) - Bitte deaktivieren (disabled) Sie dieses PHP Feature in der php.ini und starten Sie den WEB-Server neu!');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'Warnung: Das Verzeichnis f&uuml;r den Artikel Download existiert nicht: ' . DIR_FS_DOWNLOAD . '. Diese Funktion wird nicht funktionieren bis das Verzeichnis erstellt wurde!');

define('SUCCESS_ACCOUNT_UPDATED', 'Ihr Konto wurde erfolgreich aktualisiert.');
define('SUCCESS_PASSWORD_UPDATED', 'Ihr Passwort wurde erfolgreich ge&auml;ndert!');
define('ERROR_CURRENT_PASSWORD_NOT_MATCHING', 'Das eingegebene Passwort stimmt nicht mit dem gespeicherten Passwort &uuml;berein. Bitte versuchen Sie es noch einmal.');
define('TEXT_MAXIMUM_ENTRIES', 'Hinweis: Ihnen stehen %s Adressbucheintr&auml;ge zur Verf&uuml;gung!');
define('SUCCESS_ADDRESS_BOOK_ENTRY_DELETED', 'Der ausgew&auml;hlte Eintrag wurde erfolgreich gel&ouml;scht.');
define('SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED', 'Ihr Adressbuch wurde erfolgreich aktualisiert!');
define('WARNING_PRIMARY_ADDRESS_DELETION', 'Die Standardadresse kann nicht gel&ouml;scht werden. Bitte erst eine andere Standardadresse w&auml;hlen. Danach kann der Eintrag gel&ouml;scht werden.');
define('ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY', 'Dieser Adressbucheintrag ist nicht vorhanden.');
define('ERROR_ADDRESS_BOOK_FULL', 'Ihr Adressbuch kann keine weiteren Adressen aufnehmen. Bitte l&ouml;schen Sie eine nicht mehr ben&ouml;tigte Adresse. Danach k&ouml;nnen Sie einen neuen Eintrag speichern.');
define('ERROR_CHECKOUT_SHIPPING_NO_METHOD', 'Es wurde keine Versandart ausgew&auml;hlt.');
define('ERROR_CHECKOUT_SHIPPING_NO_MODULE', 'Es ist keine Versandart vorhanden.');

//  conditions check

define('ERROR_CONDITIONS_NOT_ACCEPTED', '* Sofern Sie unsere Allgemeinen Geschäftsbedingungen nicht akzeptieren,\n können wir Ihre Bestellung bedauerlicherweise nicht entgegennehmen!\n\n');

define('SUB_TITLE_OT_DISCOUNT','Rabatt:');

define('TAX_ADD_TAX','inkl. ');
define('TAX_NO_TAX','zzgl. ');
define('TAX_SHORT_DISPLAY',' MwSt. '); //DokuMan - 2010-09-28 - display VAT description multilingually

define('NOT_ALLOWED_TO_SEE_PRICES','Sie k&ouml;nnen als Gast (bzw. mit Ihrem derzeitigen Status) keine Preise sehen');
define('NOT_ALLOWED_TO_SEE_PRICES_TEXT','Sie haben keine Erlaubnis, Preise zu sehen. Erstellen Sie bitte ein Kundenkonto.');

define('TEXT_DOWNLOAD','Download');
define('TEXT_VIEW','Ansehen');

define('TEXT_BUY', '1 x \'');
define('TEXT_NOW', '\' bestellen');
define('TEXT_GUEST',' Gast');
define('TEXT_SEARCH_ENGINE_AGENT','Suchmaschine');

/*
 *
 * ADVANCED SEARCH
 *
 */

define('TEXT_ALL_CATEGORIES', 'Alle Kategorien');
define('TEXT_ALL_MANUFACTURERS', 'Alle Hersteller');
define('JS_AT_LEAST_ONE_INPUT', '* Eines der folgenden Felder muss ausgef&uuml;llt werden:\n    Stichworte\n    Preis ab\n    Preis bis\n');
define('AT_LEAST_ONE_INPUT', 'Eines der folgenden Felder muss ausgef&uuml;llt werden:<br />Stichworte mit mindestens drei Zeichen<br />Preis ab<br />Preis bis<br />');
define('TEXT_SEARCH_TERM','Ihre Suche nach: ');
define('JS_INVALID_FROM_DATE', '* ung&uuml;ltiges Datum (von)\n');
define('JS_INVALID_TO_DATE', '* ung&uuml;ltiges Datum (bis)\n');
define('JS_TO_DATE_LESS_THAN_FROM_DATE', '* Das Datum(von) muss gr&ouml;&szlig;er oder gleich sein als das Datum (bis)\n');
define('JS_PRICE_FROM_MUST_BE_NUM', '* \"Preis ab\" muss eine Zahl sein\n\n');
define('JS_PRICE_TO_MUST_BE_NUM', '* \"Preis bis\" muss eine Zahl sein\n\n');
define('JS_PRICE_TO_LESS_THAN_PRICE_FROM', '* Preis bis muss gr&ouml;&szlig;er oder gleich Preis ab sein.\n');
define('JS_INVALID_KEYWORDS', '* Suchbegriff unzul&auml;ssig\n');
define('TEXT_LOGIN_ERROR', '<font color="#ff0000"><strong>FEHLER:</strong></font> Keine &Uuml;bereinstimmung der eingegebenen \'E-Mail-Adresse\' und/oder dem \'Passwort\'.');
define('TEXT_NO_EMAIL_ADDRESS_FOUND', '<font color="#ff0000"><strong>ACHTUNG:</strong></font> Die eingegebene E-Mail-Adresse ist nicht registriert. Bitte versuchen Sie es noch einmal.');
define('TEXT_PASSWORD_SENT', 'Ein neues Passwort wurde per E-Mail verschickt.');
define('TEXT_PRODUCT_NOT_FOUND', 'Artikel wurde nicht gefunden!');
define('TEXT_MORE_INFORMATION', 'F&uuml;r weitere Informationen besuchen Sie bitte die <a style="text-decoration:underline;" href="%s" onclick="window.open(this.href); return false;">Homepage</a> zu diesem Artikel.');
define('TEXT_DATE_ADDED', 'Diesen Artikel haben wir am %s in unseren Katalog aufgenommen.');
define('TEXT_DATE_AVAILABLE', '<font color="#ff0000">Dieser Artikel wird voraussichtlich ab dem %s wieder vorr&auml;tig sein.</font>');
define('SUB_TITLE_SUB_TOTAL', 'Zwischensumme:');

define('OUT_OF_STOCK_CANT_CHECKOUT', 'Die mit ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' markierten Artikel sind leider nicht in der von Ihnen gew&uuml;nschten Menge auf Lager.<br />Bitte reduzieren Sie Ihre Bestellmenge f&uuml;r die gekennzeichneten Artikel. Vielen Dank');
define('OUT_OF_STOCK_CAN_CHECKOUT', 'Die mit ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' markierten Artikel sind leider nicht in der von Ihnen gew&uuml;nschten Menge auf Lager.<br />Die bestellte Menge wird kurzfristig von uns geliefert. Wenn Sie es w&uuml;nschen, nehmen wir auch eine Teillieferung vor.');

define('MINIMUM_ORDER_VALUE_NOT_REACHED_1', 'Sie haben den Mindestbestellwert von: ');
define('MINIMUM_ORDER_VALUE_NOT_REACHED_2', ' leider noch nicht erreicht.<br />Bitte bestellen Sie f&uuml;r mindestens weitere: ');
define('MAXIMUM_ORDER_VALUE_REACHED_1', 'Sie haben die H&ouml;chstbestellsumme von: ');
define('MAXIMUM_ORDER_VALUE_REACHED_2', '&uuml;berschritten.<br /> Bitte reduzieren Sie Ihre Bestellung um mindestens: ');

define('ERROR_INVALID_PRODUCT', 'Der von Ihnen gew&auml;hlte Artikel wurde nicht gefunden!');

/*
 *
 * NAVBAR TITLE
 *
 */

define('NAVBAR_TITLE_ACCOUNT', 'Mein Konto');
define('NAVBAR_TITLE_1_ACCOUNT_EDIT', 'Mein Konto');
define('NAVBAR_TITLE_2_ACCOUNT_EDIT', 'Ihre pers&ouml;nliche Daten &auml;ndern');
define('NAVBAR_TITLE_1_ACCOUNT_HISTORY', 'Mein Konto');
define('NAVBAR_TITLE_2_ACCOUNT_HISTORY', 'Ihre get&auml;tigten Bestellungen');
define('NAVBAR_TITLE_1_ACCOUNT_HISTORY_INFO', 'Mein Konto');
define('NAVBAR_TITLE_2_ACCOUNT_HISTORY_INFO', 'Get&auml;tigte Bestellung');
define('NAVBAR_TITLE_3_ACCOUNT_HISTORY_INFO', 'Bestellnummer %s');
define('NAVBAR_TITLE_1_ACCOUNT_PASSWORD', 'Mein Konto');
define('NAVBAR_TITLE_2_ACCOUNT_PASSWORD', 'Passwort &auml;ndern');
define('NAVBAR_TITLE_1_ADDRESS_BOOK', 'Mein Konto');
define('NAVBAR_TITLE_2_ADDRESS_BOOK', 'Adressbuch');
define('NAVBAR_TITLE_1_ADDRESS_BOOK_PROCESS', 'Mein Konto');
define('NAVBAR_TITLE_2_ADDRESS_BOOK_PROCESS', 'Adressbuch');
define('NAVBAR_TITLE_ADD_ENTRY_ADDRESS_BOOK_PROCESS', 'Neuer Eintrag');
define('NAVBAR_TITLE_MODIFY_ENTRY_ADDRESS_BOOK_PROCESS', 'Eintrag &auml;ndern');
define('NAVBAR_TITLE_DELETE_ENTRY_ADDRESS_BOOK_PROCESS', 'Eintrag l&ouml;schen');
define('NAVBAR_TITLE_ADVANCED_SEARCH', 'Erweiterte Suche');
define('NAVBAR_TITLE1_ADVANCED_SEARCH', 'Erweiterte Suche');
define('NAVBAR_TITLE2_ADVANCED_SEARCH', 'Suchergebnisse');
define('NAVBAR_TITLE_1_CHECKOUT_CONFIRMATION', 'Kasse');
define('NAVBAR_TITLE_2_CHECKOUT_CONFIRMATION', 'Best&auml;tigung');
define('NAVBAR_TITLE_1_CHECKOUT_PAYMENT', 'Kasse');
define('NAVBAR_TITLE_2_CHECKOUT_PAYMENT', 'Zahlungsweise');
define('NAVBAR_TITLE_1_PAYMENT_ADDRESS', 'Kasse');
define('NAVBAR_TITLE_2_PAYMENT_ADDRESS', 'Rechnungsadresse &auml;ndern');
define('NAVBAR_TITLE_1_CHECKOUT_SHIPPING', 'Kasse');
define('NAVBAR_TITLE_2_CHECKOUT_SHIPPING', 'Versandinformationen');
define('NAVBAR_TITLE_1_CHECKOUT_SHIPPING_ADDRESS', 'Kasse');
define('NAVBAR_TITLE_2_CHECKOUT_SHIPPING_ADDRESS', 'Versandadresse &auml;ndern');
define('NAVBAR_TITLE_1_CHECKOUT_SUCCESS', 'Kasse');
define('NAVBAR_TITLE_2_CHECKOUT_SUCCESS', 'Erfolg');
define('NAVBAR_TITLE_CREATE_ACCOUNT', 'Konto erstellen');
if (isset($navigation) && $navigation->snapshot['page'] == FILENAME_CHECKOUT_SHIPPING) {
  define('NAVBAR_TITLE_LOGIN', 'Bestellen');
} else {
  define('NAVBAR_TITLE_LOGIN', 'Anmelden');
}
define('NAVBAR_TITLE_LOGOFF','Auf Wiedersehen');
define('NAVBAR_TITLE_PRODUCTS_NEW', 'Neue Artikel');
define('NAVBAR_TITLE_SHOPPING_CART', 'Warenkorb');
define('NAVBAR_TITLE_SPECIALS', 'Angebote');
define('NAVBAR_TITLE_COOKIE_USAGE', 'Cookie-Nutzung');
define('NAVBAR_TITLE_PRODUCT_REVIEWS', 'Bewertungen');
define('NAVBAR_TITLE_REVIEWS_WRITE', 'Bewertungen');
define('NAVBAR_TITLE_REVIEWS','Bewertungen');
define('NAVBAR_TITLE_SSL_CHECK', 'Sicherheitshinweis');
define('NAVBAR_TITLE_CREATE_GUEST_ACCOUNT','Ihre Kundenadresse');
define('NAVBAR_TITLE_PASSWORD_DOUBLE_OPT','Passwort vergessen?');
define('NAVBAR_TITLE_NEWSLETTER','Newsletter');
define('NAVBAR_GV_REDEEM', 'Gutschein einl&ouml;sen');
define('NAVBAR_GV_SEND', 'Gutschein versenden');

/*
 *
 *  MISC
 *
 */

define('TEXT_NEWSLETTER','Sie m&ouml;chten immer auf dem Laufenden bleiben?<br />Kein Problem, tragen Sie sich in unseren Newsletter ein und Sie sind immer auf dem neuesten Stand.');
define('TEXT_EMAIL_INPUT','Ihre E-Mail-Adresse wurde in unser System eingetragen.<br />Gleichzeitig wurde Ihnen vom System eine E-Mail mit einem Aktivierungslink geschickt. Bitte klicken Sie nach dem Erhalt der E-Mail auf den Link, um Ihre Eintragung zu best&auml;tigen. Ansonsten bekommen Sie keinen Newsletter von uns zugestellt!');

define('TEXT_WRONG_CODE','<font color="#ff0000">Ihr eingegebener Sicherheitscode stimmte nicht mit dem angezeigten Code &uuml;berein. Bitte versuchen Sie es erneut.</font>');
define('TEXT_EMAIL_EXIST_NO_NEWSLETTER','<font color="#ff0000">Diese E-Mail-Adresse existiert bereits in unserer Datenbank, ist aber noch nicht f&uuml;r den Empfang des Newsletters freigeschaltet!</font>');
define('TEXT_EMAIL_EXIST_NEWSLETTER','<font color="#ff0000">Diese E-Mail-Adresse existiert bereits in unserer Datenbank und ist f&uuml;r den Newsletterempfang bereits freigeschaltet!</font>');
define('TEXT_EMAIL_NOT_EXIST','<font color="#ff0000">Diese E-Mail-Adresse existiert nicht in unserer Datenbank!</font>');
define('TEXT_EMAIL_DEL','Ihre E-Mail-Adresse wurde aus unserer Newsletterdatenbank gel&ouml;scht.');
define('TEXT_EMAIL_DEL_ERROR','<font color="#ff0000">Es ist ein Fehler aufgetreten, Ihre E-Mail-Adresse wurde nicht gel&ouml;scht!</font>');
define('TEXT_EMAIL_ACTIVE','<font color="#ff0000">Ihre E-Mail-Adresse wurde erfolgreich f&uuml;r den Newsletterempfang freigeschaltet!</font>');
define('TEXT_EMAIL_ACTIVE_ERROR','<font color="#ff0000">Es ist ein Fehler aufgetreten, Ihre E-Mail-Adresse wurde nicht freigeschaltet!</font>');
define('TEXT_EMAIL_SUBJECT','Ihre Newsletter-Anmeldung');

define('TEXT_CUSTOMER_GUEST',' Gast');

define('TEXT_LINK_MAIL_SENDED','Ihre Anfrage nach einem neuen Passwort muss von Ihnen erst best&auml;tigt werden.<br />Deshalb wurde Ihnen vom System eine E-Mail mit einem Best&auml;tigungslink geschickt. Bitte klicken Sie nach Erhalt der E-Mail auf den mitgeschickten Link, um eine weitere E-Mail mit Ihrem neuen Anmelde-Passwort zu erhalten. Andernfalls wird Ihnen das neue Passwort nicht zugestellt oder eingerichtet!');
define('TEXT_PASSWORD_MAIL_SENDED','Eine E-Mail mit einem neuen Anmelde-Passwort wurde Ihnen soeben zugestellt.<br />Bitte &auml;ndern Sie nach Ihrer n&auml;chsten Anmeldung Ihr Passwort wie gew&uuml;nscht.');
define('TEXT_CODE_ERROR','Bitte geben Sie den Sicherheitscode erneut ein.<br />Achten Sie dabei auf Tippfehler!');
define('TEXT_EMAIL_ERROR','Diese E-Mail Adresse ist bei uns nicht registriert.<br />Achten Sie dabei auf Tippfehler!');
define('TEXT_NO_ACCOUNT','Leider m&uuml;ssen wir Ihnen mitteilen, dass Ihre Anfrage f&uuml;r ein neues Anmelde-Passwort entweder ung&uuml;ltig war oder abgelaufen ist.<br />Bitte versuchen Sie es erneut.');
define('HEADING_PASSWORD_FORGOTTEN','Passwort vergessen?');
define('TEXT_PASSWORD_FORGOTTEN','&Auml;ndern Sie Ihr Passwort in drei leichten Schritten.');
define('TEXT_EMAIL_PASSWORD_FORGOTTEN','Bestätigungs-E-Mail für Passwortänderung'); // ä und ü für korrekte E-Mail Betreffszeile lassen!
define('TEXT_EMAIL_PASSWORD_NEW_PASSWORD','Ihr neues Passwort');
define('ERROR_MAIL','Bitte &uuml;berpr&uuml;fen Sie Ihre eingegebenen Daten im Formular');

define('CATEGORIE_NOT_FOUND','Kategorie wurde nicht gefunden');

define('GV_FAQ', 'Gutschein FAQ');
define('ERROR_NO_REDEEM_CODE', 'Sie haben leider keinen Code eingegeben.');
define('ERROR_NO_INVALID_REDEEM_GV', 'Ung&uuml;ltiger Gutscheincode');
define('TABLE_HEADING_CREDIT', 'Guthaben');
define('EMAIL_GV_TEXT_SUBJECT', 'Ein Geschenk von %s');
define('MAIN_MESSAGE', 'Sie haben sich dazu entschieden, einen Gutschein im Wert von %s an %s zu versenden, dessen E-Mail-Adresse %s lautet.<br /><br />Folgender Text erscheint in Ihrer E-Mail:<br /><br />Hallo %s<br /><br />Ihnen wurde ein Gutschein im Wert von %s durch %s geschickt.');
define('REDEEMED_AMOUNT','Ihr Gutschein wurde erfolgreich auf Ihr Konto verbucht. Gutscheinwert:');
define('REDEEMED_COUPON','Ihr Coupon wurde erfolgreich eingebucht und wird bei Ihrer Bestellung automatisch eingel&ouml;st.');

define('ERROR_INVALID_USES_USER_COUPON','Sie k&ouml;nnen den Coupon nur ');
define('ERROR_INVALID_USES_COUPON','Dieser Coupon k&ouml;nnen Kunden nur ');
define('TIMES',' mal einl&ouml;sen.');
define('ERROR_INVALID_STARTDATE_COUPON','Ihr Coupon ist noch nicht verf&uuml;gbar.');
define('ERROR_INVALID_FINISDATE_COUPON','Ihr Coupon ist bereits abgelaufen.');
define('PERSONAL_MESSAGE', '%s schreibt:');

//Popup Window
// BOF - DokuMan - 2010-02-25 removed double definition
//define('TEXT_CLOSE_WINDOW', 'Fenster schliessen.');
// EOF - DokuMan - 2010-02-25 removed double definition

/*
 *
 *  COUPON POPUP
 *
 */

define('TEXT_CLOSE_WINDOW', 'Fenster schliessen [x]');
define('TEXT_COUPON_HELP_HEADER', 'Ihr Gutschein/Coupon wurde erfolgreich verbucht.');
define('TEXT_COUPON_HELP_NAME', '<br /><br />Gutschein-/Couponbezeichnung: %s');
define('TEXT_COUPON_HELP_FIXED', '<br /><br />Der Gutschein-/Couponwert betr&auml;gt %s ');
define('TEXT_COUPON_HELP_MINORDER', '<br /><br />Der Mindestbestellwert betr&auml;gt %s ');
define('TEXT_COUPON_HELP_FREESHIP', '<br /><br />Gutschein f&uuml;r kostenlosen Versand');
define('TEXT_COUPON_HELP_DESC', '<br /><br />Couponbeschreibung: %s');
define('TEXT_COUPON_HELP_DATE', '<br /><br />Dieser Coupon ist g&uuml;ltig vom %s bis %s');
define('TEXT_COUPON_HELP_RESTRICT', '<br /><br />Artikel / Kategorie Einschr&auml;nkungen');
define('TEXT_COUPON_HELP_CATEGORIES', 'Kategorie');
define('TEXT_COUPON_HELP_PRODUCTS', 'Artikel');
//BOF - DokuMan - 2010-10-28 - Added text-constant for emailing voucher
define('ERROR_ENTRY_AMOUNT_CHECK', 'Ung&uuml;ltiger Gutscheinbetrag');
define('ERROR_ENTRY_EMAIL_ADDRESS_CHECK', 'Ung&uuml;ltige E-Mail Adresse');
//EOF - DokuMan - 2010-10-28 - Added text-constant for emailing voucher

// VAT Reg No
define('ENTRY_VAT_TEXT', 'Nur f&uuml;r Deutschland und EU!');
define('ENTRY_VAT_ERROR', 'Die eingegebene USt-IdNr. ist ung&uuml;ltig oder kann derzeit nicht &uuml;berpr&uuml;ft werden! Bitte geben Sie eine g&uuml;ltige ID ein oder lassen Sie das Feld zun&auml;chst leer.');
define('MSRP','UVP');
define('YOUR_PRICE','Ihr Preis ');
// BOF - Tomcraft - 2009-10-09 - Added text-constant for unit price
define('UNIT_PRICE','St&uuml;ckpreis ');
// EOF - Tomcraft - 2009-10-09 - Added text-constant for unit price
define('ONLY',' Jetzt nur ');// DokuMan - Werbung mit durchgestrichenen Statt-Preisen ist zulässig
define('FROM','Ab ');
define('YOU_SAVE','Sie sparen ');
define('INSTEAD','Unser bisheriger Preis ');// DokuMan - Werbung mit durchgestrichenen Statt-Preisen ist zulässig
define('TXT_PER',' pro ');
define('TAX_INFO_INCL','inkl. %s MwSt.');
define('TAX_INFO_EXCL','exkl. %s MwSt.');
define('TAX_INFO_ADD','zzgl. %s MwSt.');
define('SHIPPING_EXCL','zzgl.');
define('SHIPPING_COSTS','Versandkosten');

// changes 3.0.4 SP2
define('SHIPPING_TIME','Lieferzeit: ');
define('MORE_INFO','[Mehr]');

// changes 3.0.4 SP2.2
define('ENTRY_PRIVACY_ERROR','Bitte akzeptieren Sie unsere Datenschutzrichtlinien!');
define('TEXT_PAYMENT_FEE','Zahlungsgeb&uuml;hr');

define('_MODULE_INVALID_SHIPPING_ZONE', 'Es ist leider kein Versand in dieses Land m&ouml;glich');
define('_MODULE_UNDEFINED_SHIPPING_RATE', 'Die Versandkosten k&ouml;nnen im Moment nicht errechnet werden');

//Dokuman - 2009-08-21 - Added 'delete account' functionality for customers
define('NAVBAR_TITLE_1_ACCOUNT_DELETE', 'Mein Konto');
define('NAVBAR_TITLE_2_ACCOUNT_DELETE', 'Konto l&ouml;schen');

//contact-form messages
define('ERROR_EMAIL','<p><b>Ihre E-Mail-Adresse:</b> Keine oder ung&uuml;ltige Eingabe!</p>');
define('ERROR_VVCODE','<p><b>Sicherheitscode:</b> Keine &Uuml;bereinstimmung, bitte geben Sie den Sicherheitscode erneut ein!</p>');
// BOF - DokuMan - 2010-09-06 - combined contact form messages in one language file
define('ERROR_MSG_BODY','<p><b>Ihre Nachricht:</b> Keine Eingabe!</p>');
define('EMAIL_COMPANY', 'Firma: ');
define('EMAIL_STREET', 'Strasse: ');
define('EMAIL_POSTCODE', 'PLZ: ');
define('EMAIL_CITY', 'Stadt: ');
define('EMAIL_PHONE', 'Telefon: ');
define('EMAIL_FAX', 'Fax: ');
define('EMAIL_SENT_BY', '&Uuml;bermittelt von %s %s am %s um %s Uhr');
define('EMAIL_NOTIFY', 'ACHTUNG, diese E-Mail kann NICHT mit -ABSENDER ANTWORTEN- beantwortet werden!');
define('EMAIL_NAME', 'Name: ');
define('EMAIL_EMAIL', 'Email: ');
define('EMAIL_MESSAGE', 'Nachricht: ');
// EOF - DokuMan - 2010-09-06 - combined contact form messages in one language file

// BOF - DokuMan - 2010-09-06 - combined checkout_confirmation messages in one language file
//Table Header checkout_confirmation.php
define('HEADER_QTY', 'Anzahl');
define('HEADER_ARTICLE', 'Artikel');
define('HEADER_MODEL', 'Artikelnummer');
define('HEADER_SINGLE', 'Einzelpreis');
define('HEADER_TOTAL','Summe');
// EOF - DokuMan - 2010-09-06 - combined checkout_confirmation messages in one language file

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('NAVBAR_TITLE_PAYPAL_CHECKOUT','PayPal-Checkout');
define('PAYPAL_ERROR','PayPal Abbruch');
define('PAYPAL_NOT_AVIABLE','PayPal Express steht zur Zeit leider nicht zur Verf&uuml;gung.<br />Bitte w&auml;hlen Sie eine andere Zahlungsart<br />oder versuchen Sie es sp&auml;ter noch einmal.<br />Danke f&uuml;r Ihr Verst&auml;ndnis.<br />');
define('PAYPAL_FEHLER','PayPal hat einen Fehler bei der Abwicklung gemeldet.<br />Ihre Bestellung ist gespeichert, wird aber nicht ausgef&uuml;hrt.<br />Bitte geben Sie eine neue Bestellung ein.<br />Danke f&uuml;r Ihr Verst&auml;ndnis.<br />');
define('PAYPAL_WARTEN','PayPal hat einen Fehler bei der Abwicklung gemeldet.<br />Sie m&uuml;ssen noch einmal zu PayPal, um die Bestellung zu bezahlen.<br />Unten sehen Sie die gespeicherte Bestellung.<br />Danke f&uuml;r Ihr Verst&auml;ndnis.<br />Bitte dr&uuml;cken Sie erneut den Button PayPal Express.<br />');
define('PAYPAL_NEUBUTTON','Bitte erneut dr&uuml;cken, um die Bestellung zu bezahlen.<br />Jede andere Taste f&uuml;hrt zum Abbruch der Bestellung.');
define('ERROR_ADDRESS_NOT_ACCEPTED', '* Solange Sie Ihre Rechnungs- und Versandadresse nicht akzeptieren,\n k&ouml;nnen wir Ihre Bestellung bedauerlicherweise nicht entgegennehmen!\n\n');
define('PAYPAL_GS','Gutschein/Coupon');
define('PAYPAL_TAX','MwSt.');
define('PAYPAL_EXP_WARN','Achtung! Eventuell anfallende Versandkosten werden erst im Shop endg&uuml;ltig berechnet.');
define('PAYPAL_EXP_VORL','Vorl&auml;ufige Versandkosten');
define('PAYPAL_EXP_VERS','6.90');
// 09.01.11
define('PAYPAL_ADRESSE','Das Land in Ihrer PayPal-Versand-Adresse ist in unserem Shop nicht eingetragen.<br />Bitte nehmen Sie mit uns Kontakt auf.<br />Danke f&uuml;r Ihr Verst&auml;ndnis.<br />Von PayPal empfangenes Land: ');
// 17.09.11
define('PAYPAL_AMMOUNT_NULL','Die zu erwartende Auftrags-Summe (ohne Versand) ist gleich 0.<br />Dadurch steht PayPal Express nicht zur Verf&uuml;gung.<br />Bitte w&auml;hlen Sie eine andere Zahlungsart.<br />Danke f&uuml;r Ihr Verst&auml;ndnis.<br />');
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

define('BASICPRICE_VPE_TEXT','bei dieser Menge nur '); // Hetfield - 2009-11-26 - Added language definition for vpe at graduated prices
//web - 2010-07-11 - Preisanzeige bei Staffelpreisen (größte Staffel)
define('GRADUATED_PRICE_MAX_VALUE', 'ab');


// bof - hendrik 2011-07-17 - translations in current language
// countries[ID of country in database] = name of country in this language if different from english name in database
// you need only any entry if the name of this country in this language is different from english name (in database)
$countries_names[2] = 'Albanien';
$countries_names[3] = 'Algerien';
$countries_names[4] = 'Amerikanisch Samoa';
$countries_names[8] = 'Antarktika';
$countries_names[9] = 'Antigua und Barbuda';
$countries_names[10] = 'Argentinien';
$countries_names[11] = 'Armenien';
$countries_names[13] = 'Australien';
$countries_names[14] = '&Ouml;sterreich';
$countries_names[15] = 'Aserbaidschan';
$countries_names[18] = 'Bangladesch';
$countries_names[20] = 'Wei&szlig;russland';
$countries_names[21] = 'Belgien';
$countries_names[26] = 'Bolivien';
$countries_names[27] = 'Bosnien und Herzegowina';
$countries_names[29] = 'Bouvetinsel';
$countries_names[30] = 'Brasilien';
$countries_names[31] = 'Britisches Territorium im Indischen Ozean';
$countries_names[32] = 'Sultanat Brunei';
$countries_names[33] = 'Bulgarien';
$countries_names[36] = 'Kambodscha';
$countries_names[37] = 'Kamerun';
$countries_names[38] = 'Kanada';
$countries_names[39] = 'Cap Verde';
$countries_names[40] = 'Kaimaninseln';
$countries_names[41] = 'Zentralafrikanische Republik';
$countries_names[42] = 'Tschad';
$countries_names[44] = 'China';
$countries_names[45] = 'Weihnachtsinsel';
$countries_names[46] = 'Kokos-(Keeling) Inseln ';
$countries_names[47] = 'Kolumbien';
$countries_names[48] = 'Komoren';
$countries_names[49] = 'Kongo';
$countries_names[50] = 'Cookinseln';
$countries_names[52] = 'Elfenbeink&uuml;ste';
$countries_names[53] = 'Kroatien';
$countries_names[54] = 'Kuba';
$countries_names[55] = 'Zypern';
$countries_names[56] = 'Tschechische Republik';
$countries_names[57] = 'D&auml;nemark';
$countries_names[58] = 'Dschibuti';
$countries_names[60] = 'Dominikanische Republik';
$countries_names[61] = 'Osttimor';
$countries_names[63] = '&Auml;gypten';
$countries_names[65] = '&Auml;quatorialguinea';
$countries_names[66] = 'Eritrea';
$countries_names[67] = 'Estland';
$countries_names[68] = '&Auml;thiopien ';
$countries_names[69] = 'Falklandinseln (Malvinen)';
$countries_names[70] = 'F&auml;r&ouml;er-Inseln';
$countries_names[71] = 'Fidschi';
$countries_names[72] = 'Finnland';
$countries_names[73] = 'Frankreich';
$countries_names[74] = 'Metropolitan-Frankreich';
$countries_names[75] = 'Franz&ouml;sisch Guiana';
$countries_names[76] = 'Franz&ouml;sisch Polynesien';
$countries_names[77] = 'Franz&ouml;sischen S&uuml;d- und Antarktisgebiete';
$countries_names[80] = 'Georgien';
$countries_names[81] = 'Deutschland';
$countries_names[84] = 'Griechenland';
$countries_names[85] = 'Gr&ouml;nland';
$countries_names[91] = 'Guinea-Bissau';
$countries_names[94] = 'Heard und McDonaldinseln';
$countries_names[97] = 'Ungarn';
$countries_names[98] = 'Island';
$countries_names[99] = 'Indien';
$countries_names[100] = 'Indonesien';
$countries_names[101] = 'Iran (Islamische Republik)';
$countries_names[102] = 'Irak';
$countries_names[103] = 'Irland';
$countries_names[105] = 'Italien';
$countries_names[106] = 'Jamaika';
$countries_names[108] = 'Jordanien';
$countries_names[109] = 'Kasachstan';
$countries_names[110] = 'Kenia';
$countries_names[112] = 'Korea, Demokratische Voksrepublik';
$countries_names[113] = 'Korea, Republik';
$countries_names[115] = 'Kirgisistan';
$countries_names[116] = 'Laos';
$countries_names[117] = 'Lettland';
$countries_names[118] = 'Libanon';
$countries_names[121] = 'Libyen';
$countries_names[123] = 'Litauen';
$countries_names[124] = 'Luxemburg';
$countries_names[125] = 'Macao';
$countries_names[126] = 'Mazedonien, Ehemalige Jugoslawische Republik';
$countries_names[127] = 'Madagaskar';
$countries_names[130] = 'Malediven';
$countries_names[133] = 'Marshallinseln';
$countries_names[135] = 'Mauretanien';
$countries_names[139] = 'Mikronesien, F&ouml;derierte Staaten von';
$countries_names[140] = 'Moldawien, Republik';
$countries_names[141] = 'Monako';
$countries_names[142] = 'Mongolei';
$countries_names[144] = 'Marokko';
$countries_names[145] = 'Mosambik';
$countries_names[146] = 'Myanmar (Birma)';
$countries_names[150] = 'Niederlande';
$countries_names[151] = 'Niederl&auml;ndische Antillen';
$countries_names[152] = 'Neukaledonien';
$countries_names[153] = 'Neuseeland';
$countries_names[158] = 'Norfolkinsel';
$countries_names[159] = 'N&ouml;rdliche Marianen';
$countries_names[160] = 'Norwegen';
$countries_names[165] = 'Papua-Neuguinea';
$countries_names[168] = 'Philippinen';
$countries_names[170] = 'Polen';
$countries_names[173] = 'Katar, Emirat';
$countries_names[174] = 'R&eacute;union';
$countries_names[175] = 'Rum&auml;nien';
$countries_names[176] = 'Russische F&ouml;deration';
$countries_names[177] = 'Ruanda';
$countries_names[178] = 'St. Kitts und Nevis';
$countries_names[180] = 'St. Vincent und die Grenadinen';
$countries_names[183] = 'S&atilde;o Tom&eacute; und Pr&iacute;ncipe';
$countries_names[184] = 'Saudi Arabien';
$countries_names[240] = 'Serbien';
$countries_names[186] = 'Seychellen';
$countries_names[188] = 'Singapur';
$countries_names[189] = 'Slovakische Republik';
$countries_names[190] = 'Slovenien';
$countries_names[191] = 'Salomonen';
$countries_names[193] = 'S&uuml;dafrika';
$countries_names[194] = 'S&uuml;dgeorgien und die S&uuml;dlichen Sandwichinseln';
$countries_names[195] = 'Spanien';
$countries_names[198] = 'Saint-Pierre und Miquelon';
$countries_names[201] = 'Spitzbergen und Jan Mayen Insel';
$countries_names[203] = 'Schweden';
$countries_names[204] = 'schweiz';
$countries_names[205] = 'Syrien';
$countries_names[207] = 'Tadschikistan';
$countries_names[208] = 'Tansania, Vereinigte Republik';
$countries_names[212] = 'Tonga';
$countries_names[213] = 'Trinidad und Tobago';
$countries_names[214] = 'Tunesien';
$countries_names[215] = 'T&uuml;rkei';
$countries_names[216] = 'Turkmenistan';
$countries_names[217] = 'Turks- und Caicosinseln';
$countries_names[221] = 'Vereinigte Arabische Emirate';
$countries_names[222] = 'Gro&szlig;britannien';
$countries_names[223] = 'Vereinigte Staaten von Amerika';
$countries_names[226] = 'Usbekistan';
$countries_names[228] = 'Vatikanstadt';
$countries_names[230] = 'Vietnam';
$countries_names[231] = 'Jungferninseln (Britisch)';
$countries_names[232] = 'Jungferninseln (U.S.)';
$countries_names[233] = 'Wallis und Futuna';
$countries_names[234] = 'Westsahra';
$countries_names[235] = 'Jemen';
define('COUNTRIES_NAMES_CONVERT_SORT_FROM',  'ÄÖÜäöüéãíß'); // special chars
define('COUNTRIES_NAMES_CONVERT_SORT_TO',    'aouaoueais'); // for sort convert before to
// eof - hendrik 2011-07-17 - translations in current language
  
// BOF - DokuMan - 2011-09-20 - E-Mail SQL errors
define('ERROR_SQL_DB_QUERY','Es tut uns leid, aber es ist ein Datenbankfehler aufgetreten.');
define('ERROR_SQL_DB_QUERY_REDIRECT','Sie werden in %s Sekunden auf unsere Homepage weitergeleitet!');
// EOF - DokuMan - 2011-09-20 - E-Mail SQL errors

define('TEXT_AGB_CHECKOUT','Bitte nehmen Sie unsere AGB und Kundeninformationen %s,<br/>sowie unsere Widerrufsbelehrung %s zur Kenntnis.');
?>