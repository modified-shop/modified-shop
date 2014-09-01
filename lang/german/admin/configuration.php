<?php
/* -----------------------------------------------------------------------------------------
   $Id: configuration.php 3130 2012-06-28 11:17:12Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.8 2002/01/04); www.oscommerce.com
   (c) 2003	 nextcommerce (configuration.php,v 1.16 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('TABLE_HEADING_CONFIGURATION_TITLE', 'Name');
define('TABLE_HEADING_CONFIGURATION_VALUE', 'Wert');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_EDIT_INTRO', 'Bitte f&uuml;hren Sie alle notwendigen &Auml;nderungen durch');
define('TEXT_INFO_DATE_ADDED', 'hinzugef&uuml;gt am:');
define('TEXT_INFO_LAST_MODIFIED', 'letzte &Auml;nderung:');

// language definitions for config
define('STORE_NAME_TITLE' , 'Name des Shops');
define('STORE_NAME_DESC' , 'Der Name dieses Online Shops');
define('STORE_OWNER_TITLE' , 'Inhaber');
define('STORE_OWNER_DESC' , 'Der Name des Shop-Betreibers');
define('STORE_OWNER_EMAIL_ADDRESS_TITLE' , 'E-Mail Adresse');
define('STORE_OWNER_EMAIL_ADDRESS_DESC' , 'Die E-Mail Adresse des Shop-Betreibers');

define('EMAIL_FROM_TITLE' , 'E-Mail von');
define('EMAIL_FROM_DESC' , 'E-Mail Adresse, die beim Versenden (send mail) benutzt werden soll.');

define('STORE_COUNTRY_TITLE' , 'Land');
define('STORE_COUNTRY_DESC' , 'Das Land, aus dem der Versand erfolgt <br /><br /><b>Hinweis: Bitte nicht vergessen, die Region richtig anzupassen.</b>');
define('STORE_ZONE_TITLE' , 'Region');
define('STORE_ZONE_DESC' , 'Die Region des Landes, aus dem der Versand erfolgt.');

define('EXPECTED_PRODUCTS_SORT_TITLE' , 'Reihenfolge f&uuml;r Artikelank&uuml;ndigungen');
define('EXPECTED_PRODUCTS_SORT_DESC' , 'Das ist die Reihenfolge, wie angek&uuml;ndigte Artikel angezeigt werden.');
define('EXPECTED_PRODUCTS_FIELD_TITLE' , 'Sortierfeld f&uuml;r Artikelank&uuml;ndigungen');
define('EXPECTED_PRODUCTS_FIELD_DESC' , 'Das ist die Spalte, die zum Sortieren angek&uuml;ndigter Artikel benutzt wird.');

define('USE_DEFAULT_LANGUAGE_CURRENCY_TITLE' , 'Auf die Landesw&auml;hrung automatisch umstellen');
define('USE_DEFAULT_LANGUAGE_CURRENCY_DESC' , 'Wenn die Spracheinstellung gewechselt wird, automatisch die W&auml;hrung anpassen.');

define('SEND_EXTRA_ORDER_EMAILS_TO_TITLE' , 'Senden einer extra Bestell-E-Mail an:');
define('SEND_EXTRA_ORDER_EMAILS_TO_DESC' , 'Wenn zus&auml;tzlich eine Kopie des Bestell-E-Mails versendet werden soll, bitte in dieser Weise die Empfangs-Adressen auflisten: Name 1 &lt;E-Mail@adresse1&gt;, Name 2 &lt;E-Mail@adresse2&gt;');

define('SEARCH_ENGINE_FRIENDLY_URLS_TITLE' , 'Suchmaschinenfreundliche URLs benutzen?');
define('SEARCH_ENGINE_FRIENDLY_URLS_DESC' , 'Die Seiten URLs k&ouml;nnen automatisch f&uuml;r Suchmaschinen optimiert angezeigt werden.<br /><br /><strong>F&uuml;r suchmaschinenoptimierte URLs muss die Datei _.htaccess im Hauptverzeichnis des Shops aktiviert bzw. in .htaccess umbenannt werden! Au&szlig;erdem muss der Webserver <a href="http://www.modrewrite.de/" target="_blank">mod_rewrite</a> unterst&uuml;tzen!</strong> (Fragen sie ihren Webhoster, wenn sie das nicht &uuml;berpr&uuml;fen k&ouml;nnen.)');

define('DISPLAY_CART_TITLE' , 'Soll Warenkorb nach dem Einf&uuml;gen angezeigt werden?');
define('DISPLAY_CART_DESC' , 'Nach dem Hinzuf&uuml;gen eines Artikels zum Warenkorb, oder zur&uuml;ck zum Artikel?');

define('ALLOW_GUEST_TO_TELL_A_FRIEND_TITLE' , 'G&auml;sten erlauben, ihre Bekannten per E-Mail zu informieren?');
define('ALLOW_GUEST_TO_TELL_A_FRIEND_DESC' , 'G&auml;sten erlauben, ihre Bekannten per E-Mail &uuml;ber Artikel zu informieren?');

define('ADVANCED_SEARCH_DEFAULT_OPERATOR_TITLE' , 'Suchverkn&uuml;pfungen');
define('ADVANCED_SEARCH_DEFAULT_OPERATOR_DESC' , 'Standardoperator zum Verkn&uuml;pfen von Suchw&ouml;rtern.');

define('STORE_NAME_ADDRESS_TITLE' , 'Gesch&auml;ftsadresse und Telefonnummer etc');
define('STORE_NAME_ADDRESS_DESC' , 'Tragen Sie hier Ihre Gesch&auml;ftsadresse wie in einem Briefkopf ein.');

define('SHOW_COUNTS_TITLE' , 'Artikelanzahl hinter Kategorienamen?');
define('SHOW_COUNTS_DESC' , 'Z&auml;hlt rekursiv die Anzahl der verschiedenen Artikel pro Warengruppe, und zeigt die Anzahl (x) hinter jedem Kategorienamen');

define('DISPLAY_PRICE_WITH_TAX_TITLE' , 'Preis inkl. MwSt. anzeigen');
define('DISPLAY_PRICE_WITH_TAX_DESC' , 'Preise inklusive Steuer anzeigen (true) oder am Ende aufrechnen (false)');

define('DEFAULT_CUSTOMERS_STATUS_ID_ADMIN_TITLE' , 'Kundenstatus(Kundengruppe) f&uuml;r Administratoren');
define('DEFAULT_CUSTOMERS_STATUS_ID_ADMIN_DESC' , 'W&auml;hlen Sie den Kundenstatus(Gruppe) f&uuml;r Administratoren anhand der jeweiligen ID!');
define('DEFAULT_CUSTOMERS_STATUS_ID_GUEST_TITLE' , 'Kundenstatus(Kundengruppe) f&uuml;r G&auml;ste');
define('DEFAULT_CUSTOMERS_STATUS_ID_GUEST_DESC' , 'W&auml;hlen Sie den Kundenstatus(Gruppe) f&uuml;r G&auml;ste anhand der jeweiligen ID!');
define('DEFAULT_CUSTOMERS_STATUS_ID_TITLE' , 'Kundenstatus f&uuml;r Neukunden');
define('DEFAULT_CUSTOMERS_STATUS_ID_DESC' , 'W&auml;hlen Sie den Kundenstatus(Gruppe) f&uuml;r G&auml;ste anhand der jeweiligen ID!<br />TIPP: Sie k&ouml;nnen im Men&uuml; Kundengruppen weitere Gruppen einrichten und zb Aktionswochen machen: Diese Woche 10% Rabatt f&uuml;r alle Neukunden?');

define('ALLOW_ADD_TO_CART_TITLE' , 'Erlaubt, Artikel in den Einkaufswagen zu legen');
define('ALLOW_ADD_TO_CART_DESC' , 'Erlaubt das Einf&uuml;gen von Artikeln in den Warenkorb auch dann, wenn "Preise anzeigen" in der Kundengruppe auf "Nein" steht');
define('ALLOW_DISCOUNT_ON_PRODUCTS_ATTRIBUTES_TITLE' , 'Rabatte auch auf die Artikelattribute verwenden?');
define('ALLOW_DISCOUNT_ON_PRODUCTS_ATTRIBUTES_DESC' , 'Erlaubt, den eingestellten Rabatt der Kundengruppe auch auf die Artikelattribute anzuwenden (Nur wenn der Artikel nicht als "Sonderangebot" ausgewiesen ist)');
define('CURRENT_TEMPLATE_TITLE' , 'Templateset (Theme)');
define('CURRENT_TEMPLATE_DESC' , 'W&auml;hlen Sie ein Templateset (Theme) aus. Das Theme muss sich im Ordner www.Ihre-Domain.com/templates/ befinden.');
define('CC_KEYCHAIN_TITLE','CC String');
define('CC_KEYCHAIN_DESC','String zur verschl&uuml;sselung der CC Informationen (Bitte umbedingt &auml;ndern!)');

define('ENTRY_FIRST_NAME_MIN_LENGTH_TITLE' , 'Vorname');
define('ENTRY_FIRST_NAME_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des Vornamens');
define('ENTRY_LAST_NAME_MIN_LENGTH_TITLE' , 'Nachname');
define('ENTRY_LAST_NAME_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des Nachnamens');
define('ENTRY_DOB_MIN_LENGTH_TITLE' , 'Geburtsdatum');
define('ENTRY_DOB_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des Geburtsdatums');
define('ENTRY_EMAIL_ADDRESS_MIN_LENGTH_TITLE' , 'E-Mail Adresse');
define('ENTRY_EMAIL_ADDRESS_MIN_LENGTH_DESC' , 'Minimum L&auml;nge der E-Mailadresse');
define('ENTRY_STREET_ADDRESS_MIN_LENGTH_TITLE' , 'Strasse');
define('ENTRY_STREET_ADDRESS_MIN_LENGTH_DESC' , 'Minimum L&auml;nge der Strassenanschrift');
define('ENTRY_COMPANY_MIN_LENGTH_TITLE' , 'Firma');
define('ENTRY_COMPANY_MIN_LENGTH_DESC' , 'Minimuml&auml;nge des Firmennamens');
define('ENTRY_POSTCODE_MIN_LENGTH_TITLE' , 'Postleitzahl');
define('ENTRY_POSTCODE_MIN_LENGTH_DESC' , 'Minimum L&auml;nge der Postleitzahl');
define('ENTRY_CITY_MIN_LENGTH_TITLE' , 'Stadt');
define('ENTRY_CITY_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des St&auml;dtenamens');
define('ENTRY_STATE_MIN_LENGTH_TITLE' , 'Bundesland');
define('ENTRY_STATE_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des Bundeslandes');
define('ENTRY_TELEPHONE_MIN_LENGTH_TITLE' , 'Telefon Nummer');
define('ENTRY_TELEPHONE_MIN_LENGTH_DESC' , 'Minimum L&auml;nge der Telefonnummer');
define('ENTRY_PASSWORD_MIN_LENGTH_TITLE' , 'Passwort');
define('ENTRY_PASSWORD_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des Passworts');

define('CC_OWNER_MIN_LENGTH_TITLE' , 'Kreditkarteninhaber');
define('CC_OWNER_MIN_LENGTH_DESC' , 'Minimum L&auml;nge des Namens des Kreditkarteninhabers');
define('CC_NUMBER_MIN_LENGTH_TITLE' , 'Kreditkartennummer');
define('CC_NUMBER_MIN_LENGTH_DESC' , 'Minimum L&auml;nge von Kreditkartennummern');

define('REVIEW_TEXT_MIN_LENGTH_TITLE' , 'Bewertungen');
define('REVIEW_TEXT_MIN_LENGTH_DESC' , 'Minimum L&auml;nge der Texteingabe bei Bewertungen');

define('MIN_DISPLAY_BESTSELLERS_TITLE' , 'Bestseller');
define('MIN_DISPLAY_BESTSELLERS_DESC' , 'Minimum Anzahl der Bestseller, die angezeigt werden sollen');
define('MIN_DISPLAY_ALSO_PURCHASED_TITLE' , 'Ebenfalls gekauft');
define('MIN_DISPLAY_ALSO_PURCHASED_DESC' , 'Minimum Anzahl der ebenfalls gekauften Artikel, die bei der Artikelansicht angezeigt werden sollen');

define('MAX_ADDRESS_BOOK_ENTRIES_TITLE' , 'Adressbuch Eintr&auml;ge');
define('MAX_ADDRESS_BOOK_ENTRIES_DESC' , 'Maximum Anzahl an Adressbucheintr&auml;gen pro Kunde');
define('MAX_DISPLAY_SEARCH_RESULTS_TITLE' , 'Suchergebnisse');
define('MAX_DISPLAY_SEARCH_RESULTS_DESC' , 'Anzahl der Artikel die als Suchergebnis angezeigt werden sollen');
define('MAX_DISPLAY_PAGE_LINKS_TITLE' , 'Seiten bl&auml;ttern');
define('MAX_DISPLAY_PAGE_LINKS_DESC' , 'Anzahl der Einzelseiten, f&uuml;r die ein Link angezeigt werden soll im Seitennavigationsmen&uuml;');
define('MAX_DISPLAY_SPECIAL_PRODUCTS_TITLE' , 'Sonderangebote');
define('MAX_DISPLAY_SPECIAL_PRODUCTS_DESC' , 'Maximum Anzahl an Sonderangeboten, die angezeigt werden sollen');
define('MAX_DISPLAY_NEW_PRODUCTS_TITLE' , 'Neue Artikel Anzeigemodul');
define('MAX_DISPLAY_NEW_PRODUCTS_DESC' , 'Maximum Anzahl an neuen Artikeln, die bei den Warenkategorien angezeigt werden sollen');
define('MAX_DISPLAY_UPCOMING_PRODUCTS_TITLE' , 'Erwartete Artikel Anzeigemodul');
define('MAX_DISPLAY_UPCOMING_PRODUCTS_DESC' , 'Maximum Anzahl an erwarteten Artikeln die auf der Startseite angezeigt werden sollen');
define('MAX_DISPLAY_MANUFACTURERS_IN_A_LIST_TITLE' , 'Hersteller-Liste Schwellenwert');
define('MAX_DISPLAY_MANUFACTURERS_IN_A_LIST_DESC' , 'In der Hersteller Box; Wenn die Anzahl der Hersteller diese Schwelle &uuml;bersteigt wird anstatt der &uuml;blichen Liste eine Popup Liste angezeigt');
define('MAX_MANUFACTURERS_LIST_TITLE' , 'Hersteller Liste');
define('MAX_MANUFACTURERS_LIST_DESC' , 'In der Hersteller Box; Wenn der Wert auf "1" gesetzt wird, wird die Herstellerbox als Drop Down Liste angezeigt. Andernfalls als Liste.');
define('MAX_DISPLAY_MANUFACTURER_NAME_LEN_TITLE' , 'L&auml;nge des Herstellernamens');
define('MAX_DISPLAY_MANUFACTURER_NAME_LEN_DESC' , 'In der Hersteller Box; Maximum L&auml;nge von Namen in der Herstellerbox');
define('MAX_DISPLAY_NEW_REVIEWS_TITLE' , 'Neue Bewertungen');
define('MAX_DISPLAY_NEW_REVIEWS_DESC' , 'Maximum Anzahl an neuen Bewertungen die angezeigt werden sollen');
define('MAX_RANDOM_SELECT_REVIEWS_TITLE' , 'Auswahlpool der Bewertungen');
define('MAX_RANDOM_SELECT_REVIEWS_DESC' , 'Aus wieviel Bewertungen sollen die zuf&auml;llig angezeigten Bewertungen in der Box ausgew&auml;hlt werden?');
define('MAX_RANDOM_SELECT_NEW_TITLE' , 'Auswahlpool der Neuen Artikel');
define('MAX_RANDOM_SELECT_NEW_DESC' , 'Aus wieviel neuen Artikeln sollen die zuf&auml;llig angezeigten neuen Artikel in der Box ausgew&auml;hlt werden?');
define('MAX_RANDOM_SELECT_SPECIALS_TITLE' , 'Auswahlpool der Sonderangebote');
define('MAX_RANDOM_SELECT_SPECIALS_DESC' , 'Aus wieviel Sonderangeboten sollen die zuf&auml;llig angezeigten Sonderangebote in der Box ausgew&auml;hlt werden?');
define('MAX_DISPLAY_CATEGORIES_PER_ROW_TITLE' , 'Anzahl an Warengruppen');
define('MAX_DISPLAY_CATEGORIES_PER_ROW_DESC' , 'Anzahl an Warengruppen die pro Zeile in den &Uuml;bersichten angezeigt werden sollen.');
define('MAX_DISPLAY_PRODUCTS_NEW_TITLE' , 'Neue Artikel Liste');
define('MAX_DISPLAY_PRODUCTS_NEW_DESC' , 'Maximum Anzahl neuer Artikel die in der Liste angezeigt werden sollen.');
define('MAX_DISPLAY_BESTSELLERS_TITLE' , 'Bestsellers');
define('MAX_DISPLAY_BESTSELLERS_DESC' , 'Maximum Anzahl an Bestsellern die angezeigt werden sollen');
define('MAX_DISPLAY_ALSO_PURCHASED_TITLE' , 'Ebenfalls gekauft');
define('MAX_DISPLAY_ALSO_PURCHASED_DESC' , 'Maximum Anzahl der ebenfalls gekauften Artikel, die bei der Artikelansicht angezeigt werden sollen');
define('MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX_TITLE' , 'Bestell&uuml;bersichts Box');
define('MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX_DESC' , 'Maximum Anzahl an Artikeln die in der pers&ouml;nlichen Bestell&uuml;bersichts Box des Kunden angezeigt werden sollen.');
define('MAX_DISPLAY_ORDER_HISTORY_TITLE' , 'Bestell&uuml;bersicht');
define('MAX_DISPLAY_ORDER_HISTORY_DESC' , 'Maximum Anzahl an Bestellungen die in der &Uuml;bersicht im Kundenbereich des Shop angezeigt werden sollen.');
define('MAX_PRODUCTS_QTY_TITLE', 'Maximale Produktanzahl');
define('MAX_PRODUCTS_QTY_DESC', 'Maximale Anzahl eines Artikels im Warenkorb');
define('MAX_DISPLAY_NEW_PRODUCTS_DAYS_TITLE' , 'Anzahl der Tage f&uuml;r Neue Produkte');
define('MAX_DISPLAY_NEW_PRODUCTS_DAYS_DESC' , 'Maximum Anzahl an Tagen die neue Artikel angezeigt werden sollen');

define('PRODUCT_IMAGE_THUMBNAIL_WIDTH_TITLE' , 'Breite der Artikel-Thumbnails');
define('PRODUCT_IMAGE_THUMBNAIL_WIDTH_DESC' , 'Maximale Breite der Artikel-Thumbnails in Pixel (Standard: 120). Bei gr&ouml;&szlig;eren Werten ist evtl. "productPreviewImage" in der stylesheet.css Datei des Templates anzupassen.');
define('PRODUCT_IMAGE_THUMBNAIL_HEIGHT_TITLE' , 'H&ouml;he der Artikel-Thumbnails');
define('PRODUCT_IMAGE_THUMBNAIL_HEIGHT_DESC' , 'Maximale H&ouml;he der Artikel-Thumbnails in Pixel (Standard: 80)');

define('PRODUCT_IMAGE_INFO_WIDTH_TITLE' , 'Breite der Artikel-Info Bilder');
define('PRODUCT_IMAGE_INFO_WIDTH_DESC' , 'Maximale Breite der Artikel-Info Bilder in Pixel (Standard: 200).');
define('PRODUCT_IMAGE_INFO_HEIGHT_TITLE' , 'H&ouml;he der Artikel-Info Bilder');
define('PRODUCT_IMAGE_INFO_HEIGHT_DESC' , 'Maximale H&ouml;he der Artikel-Info Bilder in Pixel (Standard: 160)');

define('PRODUCT_IMAGE_POPUP_WIDTH_TITLE' , 'Breite der Artikel-Popup Bilder');
define('PRODUCT_IMAGE_POPUP_WIDTH_DESC' , 'Maximale Breite der Artikel-Popup Bilder in Pixel (Standard: 800)');
define('PRODUCT_IMAGE_POPUP_HEIGHT_TITLE' , 'H&ouml;he der Artikel-Popup Bilder');
define('PRODUCT_IMAGE_POPUP_HEIGHT_DESC' , 'Maximale H&ouml;he der Artikel-Popup Bilder in Pixel (Standard: 640)');

define('SMALL_IMAGE_WIDTH_TITLE' , 'Breite der Artikel Bilder');
define('SMALL_IMAGE_WIDTH_DESC' , 'Maximale Breite der Artikel Bilder in Pixel');
define('SMALL_IMAGE_HEIGHT_TITLE' , 'H&ouml;he der Artikel Bilder');
define('SMALL_IMAGE_HEIGHT_DESC' , 'Maximale H&ouml;he der Artikel Bilderin Pixel');

define('HEADING_IMAGE_WIDTH_TITLE' , 'Breite der &Uuml;berschrift Bilder');
define('HEADING_IMAGE_WIDTH_DESC' , 'Maximale Breite der &Uuml;berschrift Bilder in Pixel');
define('HEADING_IMAGE_HEIGHT_TITLE' , 'H&ouml;he der &Uuml;berschrift Bilder');
define('HEADING_IMAGE_HEIGHT_DESC' , 'Maximale H&ouml;he der &Uuml;berschriftbilder in Pixel');

define('SUBCATEGORY_IMAGE_WIDTH_TITLE' , 'Breite der Subkategorie-(Warengruppen-) Bilder');
define('SUBCATEGORY_IMAGE_WIDTH_DESC' , 'Maximale Breite der Subkategorie-(Warengruppen-) Bilder in Pixel');
define('SUBCATEGORY_IMAGE_HEIGHT_TITLE' , 'H&ouml;he der Subkategorie-(Warengruppen-) Bilder');
define('SUBCATEGORY_IMAGE_HEIGHT_DESC' , 'Maximale H&ouml;he der Subkategorie-(Warengruppen-) Bilder in Pixel');

define('CONFIG_CALCULATE_IMAGE_SIZE_TITLE' , 'Bildgr&ouml;sse berechnen');
define('CONFIG_CALCULATE_IMAGE_SIZE_DESC' , 'Sollen die Bildgr&ouml;ssen berechnet werden?');

define('IMAGE_REQUIRED_TITLE' , 'Bilder werden ben&ouml;tigt?');
define('IMAGE_REQUIRED_DESC' , 'Wenn Sie hier auf "1" setzen, werden nicht vorhandene Bilder als Rahmen angezeigt. Gut f&uuml;r Entwickler.');

define('MO_PICS_TITLE', 'Anzahl zus&auml;tzlicher Produktbilder');
define('MO_PICS_DESC', 'Anzahl der Produktbilder die zus&auml;tzlich zum Haupt-Produktbild zur Verf&uuml;gung stehen sollen.');

//This is for the Images showing your products for preview. All the small stuff.

define('PRODUCT_IMAGE_THUMBNAIL_BEVEL_TITLE' , 'Artikel-Thumbnails:Bevel<br /><img src="images/config_bevel.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_BEVEL_DESC' , 'Artikel-Thumbnails:Bevel<br /><br />Default Wert: (8,FFCCCC,330000)<br /><br />shaded bevelled edges<br />Verwendung:<br />(edge width,hex light colour,hex dark colour)');

define('PRODUCT_IMAGE_THUMBNAIL_GREYSCALE_TITLE' , 'Artikel-Thumbnails:Greyscale<br /><img src="images/config_greyscale.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_GREYSCALE_DESC' , 'Artikel-Thumbnails:Greyscale<br /><br />Default Wert: (32,22,22)<br /><br />basic black n white<br />Verwendung:<br />(int red,int green,int blue)');

define('PRODUCT_IMAGE_THUMBNAIL_ELLIPSE_TITLE' , 'Artikel-Thumbnails:Ellipse<br /><img src="images/config_eclipse.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_ELLIPSE_DESC' , 'Artikel-Thumbnails:Ellipse<br /><br />Default Wert: (FFFFFF)<br /><br />ellipse on bg colour<br />Verwendung:<br />(hex background colour)');

define('PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES_TITLE' , 'Artikel-Thumbnails:Round-edges<br /><img src="images/config_edge.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES_DESC' , 'Artikel-Thumbnails:Round-edges<br /><br />Default Wert: (5,FFFFFF,3)<br /><br />corner trimming<br />Verwendung:<br />(edge_radius,background colour,anti-alias width)');

define('PRODUCT_IMAGE_THUMBNAIL_MERGE_TITLE' , 'Artikel-Thumbnails:Merge<br /><img src="images/config_merge.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_MERGE_DESC' , 'Artikel-Thumbnails:Merge<br /><br />Default Wert: (overlay.gif,10,-50,60,FF0000)<br /><br />overlay merge image<br />Verwendung:<br />(merge image,x start [neg = from right],y start [neg = from base],opacity, transparent colour on merge image)');

define('PRODUCT_IMAGE_THUMBNAIL_FRAME_TITLE' , 'Artikel-Thumbnails:Frame<br /><img src="images/config_frame.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_FRAME_DESC' , 'Artikel-Thumbnails:Frame<br /><br />Default Wert: (FFFFFF,000000,3,EEEEEE)<br /><br />plain raised border<br />Verwendung:<br />(hex light colour,hex dark colour,int width of mid bit,hex frame colour [optional - defaults to half way between light and dark edges])');

define('PRODUCT_IMAGE_THUMBNAIL_DROP_SHADOW_TITLE' , 'Artikel-Thumbnails:Drop-Shadow<br /><img src="images/config_shadow.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_DROP_SHADOW_DESC' , 'Artikel-Thumbnails:Drop-Shadow<br /><br />Default Wert: (3,333333,FFFFFF)<br /><br />more like a dodgy motion blur [semi buggy]<br />Verwendung:<br />(shadow width,hex shadow colour,hex background colour)');

define('PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR_TITLE' , 'Artikel-Thumbnails:Motion-Blur<br /><img src="images/config_motion.gif">');
define('PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR_DESC' , 'Artikel-Thumbnails:Motion-Blur<br /><br />Default Wert: (4,FFFFFF)<br /><br />fading parallel lines<br />Verwendung:<br />(int number of lines,hex background colour)');

//And this is for the Images showing your products in single-view

define('PRODUCT_IMAGE_INFO_BEVEL_TITLE' , 'Artikel-Info Bilder:Bevel');
define('PRODUCT_IMAGE_INFO_BEVEL_DESC' , 'Artikel-Info Bilder:Bevel<br /><br />Default Wert: (8,FFCCCC,330000)<br /><br />shaded bevelled edges<br />Verwendung:<br />(edge width, hex light colour, hex dark colour)');

define('PRODUCT_IMAGE_INFO_GREYSCALE_TITLE' , 'Artikel-Info Bilder:Greyscale');
define('PRODUCT_IMAGE_INFO_GREYSCALE_DESC' , 'Artikel-Info Bilder:Greyscale<br /><br />Default Wert: (32,22,22)<br /><br />basic black n white<br />Verwendung:<br />(int red, int green, int blue)');

define('PRODUCT_IMAGE_INFO_ELLIPSE_TITLE' , 'Artikel-Info Bilder:Ellipse');
define('PRODUCT_IMAGE_INFO_ELLIPSE_DESC' , 'Artikel-Info Bilder:Ellipse<br /><br />Default Wert: (FFFFFF)<br /><br />ellipse on bg colour<br />Verwendung:<br />(hex background colour)');

define('PRODUCT_IMAGE_INFO_ROUND_EDGES_TITLE' , 'Artikel-Info Bilder:Round-edges');
define('PRODUCT_IMAGE_INFO_ROUND_EDGES_DESC' , 'Artikel-Info Bilder:Round-edges<br /><br />Default Wert: (5,FFFFFF,3)<br /><br />corner trimming<br />Verwendung:<br />( edge_radius, background colour, anti-alias width)');

define('PRODUCT_IMAGE_INFO_MERGE_TITLE' , 'Artikel-Info Bilder:Merge');
define('PRODUCT_IMAGE_INFO_MERGE_DESC' , 'Artikel-Info Bilder:Merge<br /><br />Default Wert: (overlay.gif,10,-50,60,FF0000)<br /><br />overlay merge image<br />Verwendung:<br />(merge image,x start [neg = from right],y start [neg = from base],opacity,transparent colour on merge image)');

define('PRODUCT_IMAGE_INFO_FRAME_TITLE' , 'Artikel-Info Bilder:Frame');
define('PRODUCT_IMAGE_INFO_FRAME_DESC' , 'Artikel-Info Bilder:Frame<br /><br />Default Wert: (FFFFFF,000000,3,EEEEEE)<br /><br />plain raised border<br />Verwendung:<br />(hex light colour,hex dark colour,int width of mid bit,hex frame colour [optional - defaults to half way between light and dark edges])');

define('PRODUCT_IMAGE_INFO_DROP_SHADOW_TITLE' , 'Artikel-Info Bilder:Drop-Shadow');
define('PRODUCT_IMAGE_INFO_DROP_SHADOW_DESC' , 'Artikel-Info Bilder:Drop-Shadow<br /><br />Default Wert: (3,333333,FFFFFF)<br /><br />more like a dodgy motion blur [semi buggy]<br />Verwendung:<br />(shadow width,hex shadow colour,hex background colour)');

define('PRODUCT_IMAGE_INFO_MOTION_BLUR_TITLE' , 'Artikel-Info Bilder:Motion-Blur');
define('PRODUCT_IMAGE_INFO_MOTION_BLUR_DESC' , 'Artikel-Info Bilder:Motion-Blur<br /><br />Default Wert: (4,FFFFFF)<br /><br />fading parallel lines<br />Verwendung:<br />(int number of lines,hex background colour)');

define('PRODUCT_IMAGE_POPUP_BEVEL_TITLE' , 'Artikel-Popup Bilder:Bevel');
define('PRODUCT_IMAGE_POPUP_BEVEL_DESC' , 'Artikel-Popup Bilder:Bevel<br /><br />Default Wert: (8,FFCCCC,330000)<br /><br />shaded bevelled edges<br />Verwendung:<br />(edge width,hex light colour,hex dark colour)');

define('PRODUCT_IMAGE_POPUP_GREYSCALE_TITLE' , 'Artikel-Popup Bilder:Greyscale');
define('PRODUCT_IMAGE_POPUP_GREYSCALE_DESC' , 'Artikel-Popup Bilder:Greyscale<br /><br />Default Wert: (32,22,22)<br /><br />basic black n white<br />Verwendung:<br />(int red,int green,int blue)');

define('PRODUCT_IMAGE_POPUP_ELLIPSE_TITLE' , 'Artikel-Popup Bilder:Ellipse');
define('PRODUCT_IMAGE_POPUP_ELLIPSE_DESC' , 'Artikel-Popup Bilder:Ellipse<br /><br />Default Wert: (FFFFFF)<br /><br />ellipse on bg colour<br />Verwendung:<br />(hex background colour)');

define('PRODUCT_IMAGE_POPUP_ROUND_EDGES_TITLE' , 'Artikel-Popup Bilder:Round-edges');
define('PRODUCT_IMAGE_POPUP_ROUND_EDGES_DESC' , 'Artikel-Popup Bilder:Round-edges<br /><br />Default Wert: (5,FFFFFF,3)<br /><br />corner trimming<br />Verwendung:<br />(edge_radius,background colour,anti-alias width)');

define('PRODUCT_IMAGE_POPUP_MERGE_TITLE' , 'Artikel-Popup Bilder:Merge');
define('PRODUCT_IMAGE_POPUP_MERGE_DESC' , 'Artikel-Popup Bilder:Merge<br /><br />Default Wert: (overlay.gif,10,-50,60,FF0000)<br /><br />overlay merge image<br />Verwendung:<br />(merge image,x start [neg = from right],y start [neg = from base],opacity,transparent colour on merge image)');

define('PRODUCT_IMAGE_POPUP_FRAME_TITLE' , 'Artikel-Popup Bilder:Frame');
define('PRODUCT_IMAGE_POPUP_FRAME_DESC' , 'Artikel-Popup Bilder:Frame<br /><br />Default Wert: (FFFFFF,000000,3,EEEEEE)<br /><br />plain raised border<br />Verwendung:<br />(hex light colour,hex dark colour,int width of mid bit,hex frame colour [optional - defaults to half way between light and dark edges])');

define('PRODUCT_IMAGE_POPUP_DROP_SHADOW_TITLE' , 'Artikel-Popup Bilder:Drop-Shadow');
define('PRODUCT_IMAGE_POPUP_DROP_SHADOW_DESC' , 'Artikel-Popup Bilder:Drop-Shadow<br /><br />Default Wert: (3,333333,FFFFFF)<br /><br />more like a dodgy motion blur [semi buggy]<br />Verwendung:<br />(shadow width,hex shadow colour,hex background colour)');

define('PRODUCT_IMAGE_POPUP_MOTION_BLUR_TITLE' , 'Artikel-Popup Bilder:Motion-Blur');
define('PRODUCT_IMAGE_POPUP_MOTION_BLUR_DESC' , 'Artikel-Popup Bilder:Motion-Blur<br /><br />Default Wert: (4,FFFFFF)<br /><br />fading parallel lines<br />Verwendung:<br />(int number of lines,hex background colour)');

define('IMAGE_MANIPULATOR_TITLE','GDlib processing');
define('IMAGE_MANIPULATOR_DESC','Image Manipulator f&uuml;r GD2 oder GD1<br /><br /><b>HINWEIS:</b> image_manipulator_GD2_advanced.php unterst&uuml;tzt transparente PNG\'s');


define('ACCOUNT_GENDER_TITLE' , 'Anrede');
define('ACCOUNT_GENDER_DESC' , 'Die Abfrage f&uuml;r die Anrede im Account benutzen');
define('ACCOUNT_DOB_TITLE' , 'Geburtsdatum');
define('ACCOUNT_DOB_DESC' , 'Die Abfrage f&uuml;r das Geburtsdatum im Account benutzen');
define('ACCOUNT_COMPANY_TITLE' , 'Firma');
define('ACCOUNT_COMPANY_DESC' , 'Die Abfrage f&uuml;r die Firma im Account benutzen');
define('ACCOUNT_SUBURB_TITLE' , 'Vorort');
define('ACCOUNT_SUBURB_DESC' , 'Die Abfrage f&uuml;r den Vorort im Account benutzen');
define('ACCOUNT_STATE_TITLE' , 'Bundesland');
define('ACCOUNT_STATE_DESC' , 'Die Abfrage f&uuml;r das Bundesland im Account benutzen');

define('DEFAULT_CURRENCY_TITLE' , 'Standard W&auml;hrung');
define('DEFAULT_CURRENCY_DESC' , 'W&auml;hrung die standardm&auml;ssig benutzt wird');
define('DEFAULT_LANGUAGE_TITLE' , 'Standard Sprache');
define('DEFAULT_LANGUAGE_DESC' , 'Sprache die standardm&auml;ssig benutzt wird');
define('DEFAULT_ORDERS_STATUS_ID_TITLE' , 'Standard Bestellstatus bei neuen Bestellungen');
define('DEFAULT_ORDERS_STATUS_ID_DESC' , 'Wenn eine neue Bestellung eingeht, wird dieser Status als Bestellstatus gesetzt.');

define('SHIPPING_ORIGIN_COUNTRY_TITLE' , 'Versandland');
define('SHIPPING_ORIGIN_COUNTRY_DESC' , 'W&auml;hlen Sie das Versandland aus, zur Berechnung korrekter Versandgeb&uuml;hren.');
define('SHIPPING_ORIGIN_ZIP_TITLE' , 'Postleitzahl des Versandstandortes');
define('SHIPPING_ORIGIN_ZIP_DESC' , 'Bitte geben Sie die Postleitzahl des Versandstandortes ein, der zur Berechnung der Versandkosten in Frage kommt.');
define('SHIPPING_MAX_WEIGHT_TITLE' , 'Maximalgewicht, das als ein Paket versendet werden kann');
define('SHIPPING_MAX_WEIGHT_DESC' , 'Versandpartner (Post/UPS etc.) haben ein maximales Paketgewicht. Geben Sie einen Wert daf&uuml;r ein.');
define('SHIPPING_BOX_WEIGHT_TITLE' , 'Paketleergewicht.');
define('SHIPPING_BOX_WEIGHT_DESC' , 'Wie hoch ist das Gewicht eines durchschnittlichen kleinen bis mittleren Leerpaketes?');
define('SHIPPING_BOX_PADDING_TITLE' , 'Bei gr&ouml;sseren Leerpaketen - Gewichtszuwachs in %.');
define('SHIPPING_BOX_PADDING_DESC' , 'F&uuml;r etwa 10% geben Sie 10 ein');
define('SHOW_SHIPPING_TITLE' , 'Versandkosten im Warenkorb');
define('SHOW_SHIPPING_DESC' , 'Verlinkte Anzeige von "zzgl. Versandkosten" im Warenkorb.');
define('SHIPPING_INFOS_TITLE' , 'Versandkosten ID');
define('SHIPPING_INFOS_DESC' , 'Sprachgruppen ID der Versandkosten (Default 1) f&uuml;r die Verlinkung.');
define('SHIPPING_DEFAULT_TAX_CLASS_METHOD_TITLE' , 'Berechnungsmethode der Default-Steuerklasse');
define('SHIPPING_DEFAULT_TAX_CLASS_METHOD_DESC' , 'keine: keine Versandkostensteuer ausweisen<br />auto proportional: Versandkostensteuer anteilig zur Bestellung ausweisen<br />auto max: Steuersatz der gr&ouml;&szlig;ten Umsatzgruppe als Versandkostensteuer ausweisen');

define('PRODUCT_LIST_FILTER_TITLE' , 'Anzeige der Sortierungsfilter in Artikellisten?');
define('PRODUCT_LIST_FILTER_DESC' , 'Anzeige der Sortierungsfilter f&uuml;r Warengruppen/Hersteller etc. Filter (false=inaktiv; true=aktiv)');

define('STOCK_CHECK_TITLE' , '&Uuml;berpr&uuml;fen des Warenbestandes');
define('STOCK_CHECK_DESC' , 'Pr&uuml;fen ob noch genug Ware zum Ausliefern von Bestellungen verf&uuml;gbar ist.');

define('ATTRIBUTE_STOCK_CHECK_TITLE' , '&Uuml;berpr&uuml;fen des Artikelattribut Bestandes');
define('ATTRIBUTE_STOCK_CHECK_DESC' , '&Uuml;berpr&uuml;fen des Bestandes an Ware mit bestimmten Artikelattributen');
define('STOCK_LIMITED_TITLE' , 'Warenmenge abziehen');
define('STOCK_LIMITED_DESC' , 'Warenmenge im Warenbestand abziehen, wenn die Ware bestellt wurde');
define('STOCK_ALLOW_CHECKOUT_TITLE' , 'Einkaufen nicht vorr&auml;tiger Ware erlauben');
define('STOCK_ALLOW_CHECKOUT_DESC' , 'M&ouml;chten Sie auch dann erlauben zu bestellen, wenn bestimmte Ware laut Warenbestand nicht verf&uuml;gbar ist?');
define('STOCK_MARK_PRODUCT_OUT_OF_STOCK_TITLE' , 'Kennzeichnung vergriffener Artikel');
define('STOCK_MARK_PRODUCT_OUT_OF_STOCK_DESC' , 'Dem Kunden kenntlich machen, welche Artikel nicht mehr verf&uuml;gbar sind.');
define('STOCK_REORDER_LEVEL_TITLE' , 'Meldung an den Admin dass ein Artikel nachbestellt werden muss');
define('STOCK_REORDER_LEVEL_DESC' , 'Ab welcher St&uuml;ckzahl soll diese Meldung erscheinen? (GEPLANTE FUNKTION)');
define('STORE_PAGE_PARSE_TIME_TITLE' , 'Speichern der Berechnungszeit des Shop-Seitenaufbaus');
define('STORE_PAGE_PARSE_TIME_DESC' , 'Speicher der Zeit die ben&ouml;tigt wird, um Skripte bis zum Output der Seite zu berechnen');
define('STORE_PAGE_PARSE_TIME_LOG_TITLE' , 'Speicherort der Logdatei');
define('STORE_PAGE_PARSE_TIME_LOG_DESC' , 'Hier den Speicherort/Dateiname eintragen, in dem die Berechnungszeit f&uuml;r den Seitenaufbau, sowie die Datenbank Abfragen gespeichert werden.<br/><strong>Achtung: Datei kann bei l&auml;ngerer Laufzeit sehr gro&szlig; werden!</strong>.<br/><br/>Standard-Datei "page_parse_time.log":<br/><strong>Frontend:</strong> Berechnungszeit und Datenbank Abfragen im Frontend des Shops werden im Root-Verzeichnis in der Datei "<strong>page_parse_time.log</strong>" gespeichert.<br/><strong>Administrationsbereich (Backend):</strong> Berechnungszeit und Datenbank Abfragen im Administrationsbereich des Shops werden automatisch in der Datei "<strong>admin/page_parse_time.log</strong>" gespeichert.');
define('STORE_PARSE_DATE_TIME_FORMAT_TITLE' , 'Datumsformat in der Log-Datei');
define('STORE_PARSE_DATE_TIME_FORMAT_DESC' , 'Das Datumsformat f&uuml;r Logging (Standard: %d/%m/%Y %H:%M:%S)');

define('DISPLAY_PAGE_PARSE_TIME_TITLE' , 'Berechnungszeiten der Seiten anzeigen');
define('DISPLAY_PAGE_PARSE_TIME_DESC' , 'Wenn das Speichern der Berechnungszeiten f&uuml;r Seiten eingeschaltet ist, k&ouml;nnen diese im Footer angezeigt werden.');
define('STORE_DB_TRANSACTIONS_TITLE' , 'Speichern der Datenbank Abfragen');
define('STORE_DB_TRANSACTIONS_DESC' , 'Speichern der einzelnen Datenbank Abfragen im Logfile f&uuml;r Berechnungszeiten');

define('USE_CACHE_TITLE' , 'Cache benutzen');
define('USE_CACHE_DESC' , 'Die Cache Features verwenden');

define('DB_CACHE_TITLE','DB Cache');
define('DB_CACHE_DESC','SELECT Abfragen k&ouml;nnen von xt:Commerce gecached werden, um die Datenbankabfragen zu verringern, und die Geschwindigkeit zu erh&ouml;hen');

define('DB_CACHE_EXPIRE_TITLE','DB Cache Lebenszeit');
define('DB_CACHE_EXPIRE_DESC','Zeit in Sekunden, bevor Cache Datein mit Daten aus der Datenbank automatisch &Uuml;berschrieben werden.');

define('DIR_FS_CACHE_TITLE' , 'Cache Ordner');
define('DIR_FS_CACHE_DESC' , 'Der Ordner, wo die gecachten Files gespeichert werden sollen');

define('ACCOUNT_OPTIONS_TITLE','Art der Kontoerstellung');
define('ACCOUNT_OPTIONS_DESC','Wie m&ouml;chten Sie die Anmeldeprozedur in Ihrem Shop gestallten?<br />Sie haben die Wahl zwischen regul&auml;ren Kundenkonten und "Einmalbestellungen" ohne Erstellung eines Kundenkontos (es wird ein Konto erstellt, aber dies ist f&uuml;r den Kunden nicht ersichtlich)');

define('EMAIL_TRANSPORT_TITLE' , 'E-Mail Transport Methode');
define('EMAIL_TRANSPORT_DESC' , '<b>Empfehlung: smtp</b> - Definiert ob der Server eine lokale Verbindung zum "Sendmail-Programm" benutzt oder ob er eine SMTP Verbindung &uuml;ber TCP/IP ben&ouml;tigt. Server die auf Windows oder MacOS laufen sollten SMTP verwenden.');

define('EMAIL_LINEFEED_TITLE' , 'E-Mail Linefeeds');
define('EMAIL_LINEFEED_DESC' , 'Definiert die Zeichen die benutzt werden sollen um die Mail Header zu trennen.');
define('EMAIL_USE_HTML_TITLE' , 'Benutzen von MIME HTML beim Versand von E-Mails');
define('EMAIL_USE_HTML_DESC' , 'E-Mails im HTML Format versenden');
define('ENTRY_EMAIL_ADDRESS_CHECK_TITLE' , '&Uuml;berpr&uuml;fen der E-Mail Adressen &uuml;ber DNS');
define('ENTRY_EMAIL_ADDRESS_CHECK_DESC' , 'Die E-Mail Adressen k&ouml;nnen &uuml;ber einen DNS Server gepr&uuml;ft werden');
define('SEND_EMAILS_TITLE' , 'Senden von E-Mails');
define('SEND_EMAILS_DESC' , 'E-Mails an Kunden versenden (bei Bestellungen etc)');
define('SENDMAIL_PATH_TITLE' , 'Der Pfad zu Sendmail');
define('SENDMAIL_PATH_DESC' , 'Wenn Sie Sendmail benutzen, geben Sie hier den Pfad zum Sendmail Programm an(normalerweise: /usr/bin/sendmail):');
define('SMTP_MAIN_SERVER_TITLE' , 'Adresse des SMTP Servers');
define('SMTP_MAIN_SERVER_DESC' , 'Geben Sie die Adresse Ihres Haupt SMTP Servers ein.');
define('SMTP_BACKUP_SERVER_TITLE' , 'Adresse des SMTP Backup Servers');
define('SMTP_BACKUP_SERVER_DESC' , 'Geben Sie die Adresse Ihres Backup SMTP Servers ein.');
define('SMTP_USERNAME_TITLE' , 'SMTP Username');
define('SMTP_USERNAME_DESC' , 'Bitte geben Sie hier den Usernamen Ihres SMTP Accounts ein.');
define('SMTP_PASSWORD_TITLE' , 'SMTP Passwort');
define('SMTP_PASSWORD_DESC' , 'Bitte geben Sie hier das Passwort Ihres SMTP Accounts ein.');
define('SMTP_AUTH_TITLE' , 'SMTP AUTH');
define('SMTP_AUTH_DESC' , 'Erfordert der SMTP Server eine sichere Authentifizierung?');
define('SMTP_PORT_TITLE' , 'SMTP Port');
define('SMTP_PORT_DESC' , 'Geben sie den SMTP Port Ihres SMTP Servers ein (default: 25)?');

//DokuMan - 2011-09-20 - E-Mail SQL errors
define('EMAIL_SQL_ERRORS_TITLE','SQL Fehlermeldungen als E-Mail versenden');
define('EMAIL_SQL_ERRORS_DESC','Bei "true" wird an die E-Mailadresse des Shop-Betreibers eine E-Mail mit der SQL-Fehlermeldung gesendet. Die SQL-Fehlermeldung dagegen wird vor dem Kunden versteckt.<br />Bei "false" wird die entsprechende Fehlermeldung direkt und f&uuml;r alle sichtbar ausgegeben (Standard).');

//Constants for contact_us
define('CONTACT_US_EMAIL_ADDRESS_TITLE' , 'Kontakt - E-Mail Adresse');
define('CONTACT_US_EMAIL_ADDRESS_DESC' , 'Bitte geben Sie eine korrekte Absender Adresse f&uuml;r das Versenden der E-Mails &uuml;ber das "Kontakt" Formular ein.');
define('CONTACT_US_NAME_TITLE' , 'Kontakt - E-Mail Adresse, Name');
define('CONTACT_US_NAME_DESC' , 'Bitte geben Sie einen Absender Namen f&uuml;r das Versenden der E-Mails &uuml;ber das "Kontakt" Formular ein.');
define('CONTACT_US_FORWARDING_STRING_TITLE' , 'Kontakt - Weiterleitungsadressen');
define('CONTACT_US_FORWARDING_STRING_DESC' , 'Geben Sie weitere Mailadressen ein, an welche die E-Mails des "Kontakt" Formulares noch versendet werden sollen (mit , getrennt)');
define('CONTACT_US_REPLY_ADDRESS_TITLE' , 'Kontakt - Antwortadresse');
define('CONTACT_US_REPLY_ADDRESS_DESC' , 'Bitte geben Sie eine E-Mailadresse ein, an die Ihre Kunden Antworten k&ouml;nnen.');
define('CONTACT_US_REPLY_ADDRESS_NAME_TITLE' , 'Kontakt - Antwortadresse, Name');
define('CONTACT_US_REPLY_ADDRESS_NAME_DESC' , 'Absendername f&uuml;r Antwortmails.');
define('CONTACT_US_EMAIL_SUBJECT_TITLE' , 'Kontakt - E-Mail Betreff');
define('CONTACT_US_EMAIL_SUBJECT_DESC' , 'Betreff f&uuml;r E-Mails vom Kontaktformular des Shops');

//Constants for support system
define('EMAIL_SUPPORT_ADDRESS_TITLE' , 'Technischer Support - E-Mail Adresse');
define('EMAIL_SUPPORT_ADDRESS_DESC' , 'Bitte geben Sie eine korrekte Absender Adresse f&uuml;r das Versenden der E-Mails &uuml;ber das <b>Support System</b> ein (Kontoerstellung, Passwort vergessen).');
define('EMAIL_SUPPORT_NAME_TITLE' , 'Technischer Support - E-Mail Adresse, Name');
define('EMAIL_SUPPORT_NAME_DESC' , 'Bitte geben Sie einen Absender Namen f&uuml;r das Versenden der mails &uuml;ber das <b>Support System</b> ein (Kontoerstellung, Passwort vergessen).');
define('EMAIL_SUPPORT_FORWARDING_STRING_TITLE' , 'Technischer Support - Weiterleitungsadressen');
define('EMAIL_SUPPORT_FORWARDING_STRING_DESC' , 'Geben Sie weitere E-Mailadressen ein, an welche die E-Mails des <b>Support Systems</b> noch versendet werden sollen (mit , getrennt)');
define('EMAIL_SUPPORT_REPLY_ADDRESS_TITLE' , 'Technischer Support - Antwortadresse');
define('EMAIL_SUPPORT_REPLY_ADDRESS_DESC' , 'Bitte geben Sie eine E-Mailadresse ein, an die Ihre Kunden Antworten k&ouml;nnen.');
define('EMAIL_SUPPORT_REPLY_ADDRESS_NAME_TITLE' , 'Technischer Support - Antwortadresse, Name');
define('EMAIL_SUPPORT_REPLY_ADDRESS_NAME_DESC' , 'Absendername f&uuml;r Antwortmails.');
define('EMAIL_SUPPORT_SUBJECT_TITLE' , 'Technischer Support - E-Mail Betreff');
define('EMAIL_SUPPORT_SUBJECT_DESC' , 'Betreff f&uuml;r E-Mails des <b>Support Systems</b>.');

//Constants for Billing system
define('EMAIL_BILLING_ADDRESS_TITLE' , 'Verrechnung - E-Mail Adresse');
define('EMAIL_BILLING_ADDRESS_DESC' , 'Bitte geben Sie eine korrekte Absenderadresse f&uuml;r das Versenden der mails &uuml;ber das <b>Verrechnungssystem</b> ein (Bestellbest&auml;tigung, Status&auml;nderungen,..).');
define('EMAIL_BILLING_NAME_TITLE' , 'Verrechnung - Mail Adresse, Name');
define('EMAIL_BILLING_NAME_DESC' , 'Bitte geben Sie einen Absendernamen f&uuml;r das Versenden der E-Mails &uuml;ber das <b>Verrechnungssystem</b> ein (Bestellbest&auml;tigung, Status&auml;nderungen,..).');
define('EMAIL_BILLING_FORWARDING_STRING_TITLE' , 'Verrechnung - Weiterleitungsadressen');
define('EMAIL_BILLING_FORWARDING_STRING_DESC' , 'Geben Sie weitere Mailadressen ein, wohin die E-Mails des <b>Verrechnungssystem</b> noch versendet werden sollen (mit , getrennt)');
define('EMAIL_BILLING_REPLY_ADDRESS_TITLE' , 'Verrechnung - Antwortadresse');
define('EMAIL_BILLING_REPLY_ADDRESS_DESC' , 'Bitte geben Sie eine E-Mailadresse ein, an die Ihre Kunden Antworten k&ouml;nnen.');
define('EMAIL_BILLING_REPLY_ADDRESS_NAME_TITLE' , 'Verrechnung - Antwortadresse, Name');
define('EMAIL_BILLING_REPLY_ADDRESS_NAME_DESC' , 'Absendername f&uuml;r Antwort E-Mails.');
define('EMAIL_BILLING_SUBJECT_TITLE' , 'Verrechnung - E-Mail Betreff Status&auml;nderungen');
define('EMAIL_BILLING_SUBJECT_DESC' , 'Geben Sie bitte einen E-Mailbetreff f&uuml;r E-Mails des <b>Verrechnungs-Systems</b> Ihres Shops ein (Status&auml;nderungen).');
define('EMAIL_BILLING_SUBJECT_ORDER_TITLE','Verrechnung - E-Mail Betreff f&uuml;r Bestellungen');
define('EMAIL_BILLING_SUBJECT_ORDER_DESC','Geben Sie bitte einen E-Mailbetreff f&uuml;r Ihre Bestellmails an. (zb: <b>Ihre Bestellung {$nr},am {$date}</b>) ps: folgende Variablen stehen zur Verf&uuml;gung, {$nr},{$date},{$firstname},{$lastname}');

define('DOWNLOAD_ENABLED_TITLE' , 'Download von Artikeln erlauben');
define('DOWNLOAD_ENABLED_DESC' , 'Die Artikel Download Funktionen einschalten (Software etc).');
define('DOWNLOAD_BY_REDIRECT_TITLE' , 'Download durch Redirection');
define('DOWNLOAD_BY_REDIRECT_DESC' , 'Browser-Umleitung f&uuml;r Artikeldownloads benutzen. Auf nicht Linux/Unix Systemen ausschalten.');
define('DOWNLOAD_MAX_DAYS_TITLE' , 'Verfallsdatum der Download Links(Tage)');
define('DOWNLOAD_MAX_DAYS_DESC' , 'Anzahl an Tagen, die ein Download Link f&uuml;r den Kunden aktiv bleibt. 0 bedeutet ohne Limit.');
define('DOWNLOAD_MAX_COUNT_TITLE' , 'Maximale Anzahl der Downloads eines gekauften Medienproduktes');
define('DOWNLOAD_MAX_COUNT_DESC' , 'Stellen Sie die maximale Anzahl an Downloads ein, die Sie dem Kunden erlauben, der einen Artikel dieser Art erworben hat. 0 bedeutet kein Download.');

define('GZIP_COMPRESSION_TITLE' , 'GZip Kompression einschalten');
define('GZIP_COMPRESSION_DESC' , 'Schalten Sie HTTP GZip Kompression ein um die Seitenaufbaugeschwindigkeit zu optimieren.');
define('GZIP_LEVEL_TITLE' , 'Kompressions Level');
define('GZIP_LEVEL_DESC' , 'W&auml;hlen Sie einen Kompressionslevel zwischen 0-9 (0 = Minimum, 9 = Maximum).');

define('SESSION_WARNING', '<br /><br /><font color="#FF0000"><strong>ACHTUNG:</strong></font>
Diese Funktion kann eventuell die Funktionsf&auml;higkeit des Shops
beeinflussen. Bitte nur &auml;ndern, wenn man sich &uuml;ber die m&ouml;glichen Folgen im Klaren ist und der Server diese Funktion auch wirklich unterst&uuml;tzt!');

define('SESSION_WRITE_DIRECTORY_TITLE' , 'Session Speicherort');
define('SESSION_WRITE_DIRECTORY_DESC' , 'Wenn Sessions als Files gespeichert werden sollen, benutzen Sie folgenden Ordner.');
define('SESSION_FORCE_COOKIE_USE_TITLE' , 'Cookie Benutzung bevorzugen');
define('SESSION_FORCE_COOKIE_USE_DESC' , 'Session starten falls Cookies vom Browser erlaubt werden. (Standard &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_CHECK_SSL_SESSION_ID_TITLE' , 'Checken der SSL Session ID');
define('SESSION_CHECK_SSL_SESSION_ID_DESC' , '&Uuml;berpr&uuml;fen der SSL_SESSION_ID bei jedem HTTPS Seitenaufruf. (Standard &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_CHECK_USER_AGENT_TITLE' , 'Checken des User Browsers');
define('SESSION_CHECK_USER_AGENT_DESC' , '&Uuml;berpr&uuml;fen des Browsers den der User benutzt, bei jedem Seitenaufruf. (Standard &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_CHECK_IP_ADDRESS_TITLE' , 'Checken der IP Adresse');
define('SESSION_CHECK_IP_ADDRESS_DESC' , '&Uuml;berpr&uuml;fen der IP Adresse des Users bei jedem Seitenaufruf. (Standard &quot;false&quot;)'.SESSION_WARNING);
define('SESSION_RECREATE_TITLE' , 'Session erneuern');
define('SESSION_RECREATE_DESC' , 'Erneuern der Session und Zuweisung einer neuen Session ID sobald ein User einloggt oder sich registriert (PHP >=4.1 needed). (Standard &quot;false&quot;)'.SESSION_WARNING);

define('DISPLAY_CONDITIONS_ON_CHECKOUT_TITLE' , 'Unterzeichnen der AGB');
define('DISPLAY_CONDITIONS_ON_CHECKOUT_DESC' , 'Anzeigen und Unterzeichnen der AGB beim Bestellvorgang');

define('META_MIN_KEYWORD_LENGTH_TITLE' , 'Minimum L&auml;nge Meta-Keywords');
define('META_MIN_KEYWORD_LENGTH_DESC' , 'Minimum L&auml;nge der automatisch erzeugten Meta-Keywords (Artikelbeschreibung)');
define('META_KEYWORDS_NUMBER_TITLE' , 'Anzahl der Meta-Keywords');
define('META_KEYWORDS_NUMBER_DESC' , 'Anzahl der Meta-Keywords');
define('META_AUTHOR_TITLE' , 'author');
define('META_AUTHOR_DESC' , '<meta name="author">');
define('META_PUBLISHER_TITLE' , 'publisher');
define('META_PUBLISHER_DESC' , '<meta name="publisher">');
define('META_COMPANY_TITLE' , 'company');
define('META_COMPANY_DESC' , '<meta name="company">');
define('META_TOPIC_TITLE' , 'page-topic');
define('META_TOPIC_DESC' , '<meta name="page-topic">');
define('META_REPLY_TO_TITLE' , 'reply-to');
define('META_REPLY_TO_DESC' , '<meta name="reply-to">');
define('META_REVISIT_AFTER_TITLE' , 'revisit-after');
define('META_REVISIT_AFTER_DESC' , '<meta name="revisit-after">');
define('META_ROBOTS_TITLE' , 'robots');
define('META_ROBOTS_DESC' , '<meta name="robots">');
define('META_DESCRIPTION_TITLE' , 'Description');
define('META_DESCRIPTION_DESC' , '<meta name="description">');
define('META_KEYWORDS_TITLE' , 'Keywords');
define('META_KEYWORDS_DESC' , '<meta name="keywords">');

define('MODULE_PAYMENT_INSTALLED_TITLE' , 'Installierte Zahlungsmodule');
define('MODULE_PAYMENT_INSTALLED_DESC' , 'Liste der Zahlungsmodul-Dateinamen (getrennt durch einen Strichpunkt (;)). Diese wird automatisch aktualisiert, daher ist es nicht notwendig diese zu editieren. (Beispiel: cc.php;cod.php;paypal.php)');
define('MODULE_ORDER_TOTAL_INSTALLED_TITLE' , 'Installierte Order Total-Module');
define('MODULE_ORDER_TOTAL_INSTALLED_DESC' , 'Liste der Order-Total-Modul-Dateinamen (getrennt durch einen Strichpunkt (;)). Diese wird automatisch aktualisiert, daher ist es nicht notwendig diese zu editieren. (Beispiel: ot_subtotal.php;ot_tax.php;ot_shipping.php;ot_total.php)');
define('MODULE_SHIPPING_INSTALLED_TITLE' , 'Installierte Versand Module');
define('MODULE_SHIPPING_INSTALLED_DESC' , 'Liste der Versandmodul-Dateinamen (getrennt durch einen Strichpunkt (;)). Diese wird automatisch aktualisiert, daher ist es nicht notwendig diese zu editieren. (Beispiel: ups.php;flat.php;item.php)');

define('CACHE_LIFETIME_TITLE','Cache Lebenszeit');
define('CACHE_LIFETIME_DESC','Zeit in Sekunden, bevor Cache Datein automatisch &uuml;berschrieben werden.');
define('CACHE_CHECK_TITLE','Pr&uuml;fe ob Cache modifiziert');
define('CACHE_CHECK_DESC','Wenn "true", dann werden If-Modified-Since headers bei ge-cache-tem Content ber&uuml;cksichtigt, und passende HTTP headers werden ausgegeben. Somit werden regelm&auml;ssig aufgerufene Seiten nicht jedesmal neu an den Client versandt.');

define('PRODUCT_REVIEWS_VIEW_TITLE','Bewertungen in Artikeldetails');
define('PRODUCT_REVIEWS_VIEW_DESC','Anzahl der angezeigten Bewertungen in der Artikeldetailansicht');

define('DELETE_GUEST_ACCOUNT_TITLE','L&ouml;schen von Gast-Konten');
define('DELETE_GUEST_ACCOUNT_DESC','Sollen Gast-Konten nach erfolgter Bestellung gel&ouml;scht werden? (Bestelldaten bleiben erhalten)');

define('USE_WYSIWYG_TITLE','WYSIWYG-Editor aktivieren');
define('USE_WYSIWYG_DESC','WYSIWYG-Editor f&uuml;r CMS und Artikel aktivieren ?');

define('PRICE_IS_BRUTTO_TITLE','Brutto Admin');
define('PRICE_IS_BRUTTO_DESC','Erm&ouml;glicht die Eingabe der Bruttopreise im Admin');

define('PRICE_PRECISION_TITLE','Brutto/Netto Dezimalstellen');
define('PRICE_PRECISION_DESC','Umrechnungsgenauigkeit (Hat keinen Einfluss auf die Anzeige im Shop, dort werden immer 2 Nachkommastellen angezeigt.)');

define('CHECK_CLIENT_AGENT_TITLE','Spider Sessions vermeiden?');
define('CHECK_CLIENT_AGENT_DESC','Bekannte Suchmaschinen Spider ohne Session auf die Seite lassen.');
define('SHOW_IP_LOG_TITLE','IP-Log im Checkout?');
define('SHOW_IP_LOG_DESC','Text "Ihre IP wird aus Sicherheitsgr&uuml;nden gespeichert", beim Checkout anzeigen?');

define('ACTIVATE_GIFT_SYSTEM_TITLE','Gutscheinsystem aktivieren?');
define('ACTIVATE_GIFT_SYSTEM_DESC','Gutscheinsystem aktivieren?');

define('ACTIVATE_SHIPPING_STATUS_TITLE','Versandstatusanzeige aktivieren?');
define('ACTIVATE_SHIPPING_STATUS_DESC','Versandstatusanzeige aktivieren? (Verschiedene Versandzeiten k&ouml;nnen f&uuml;r einzelne Artikel festgelegt werden. Nach Aktivierung erscheint ein neuer Punkt <b>Lieferstatus</b> bei der Artikeleingabe)');

define('SECURITY_CODE_LENGTH_TITLE','L&auml;nge des Sicherheitscodes');
define('SECURITY_CODE_LENGTH_DESC','L&auml;nge des Sicherheitscodes (Geschenk-Gutschein)');

define('IMAGE_QUALITY_TITLE','Bildqualit&auml;t');
define('IMAGE_QUALITY_DESC','Bildqualit&auml;t (0= h&ouml;chste Kompression, 100=beste Qualit&auml;t)');

define('GROUP_CHECK_TITLE','Kundengruppencheck');
define('GROUP_CHECK_DESC','Nur bestimmten Kundengruppen Zugang zu einzelnen Kategorien, Produkten, Contentelementen erlauben? (Nach Aktivierung erscheinen Eingabem&ouml;glichkeiten bei Artikeln, Kategorien und im Contentmanager)');

define('ACTIVATE_NAVIGATOR_TITLE','Artikelnavigator aktivieren?');
define('ACTIVATE_NAVIGATOR_DESC','Artikelnavigator in der Artikeldetailansicht aktivieren/deaktivieren (aus Performancegr&uuml;nden bei hoher Artikelanzahl)');

define('QUICKLINK_ACTIVATED_TITLE','Multilink/Kopierfunktion aktivieren');
define('QUICKLINK_ACTIVATED_DESC','Die Multilink/Kopierfunktion erleichtert das Kopieren/Verlinken eines Artikels in mehrere Kategorien, durch die M&ouml;glichkeit einzelne Kategorien per Checkbox zu selektieren');

define('ACTIVATE_REVERSE_CROSS_SELLING_TITLE','Reverse Cross-Marketing');
define('ACTIVATE_REVERSE_CROSS_SELLING_DESC','Reverse Cross-Marketing Funktion aktivieren?');

define('DOWNLOAD_UNALLOWED_PAYMENT_TITLE', 'Unerlaubte Download-Zahlungsmodule');
define('DOWNLOAD_UNALLOWED_PAYMENT_DESC', '<strong>NICHT</strong> Erlaubte Zahlungsweisen f&uuml;r Downloadprodukte durch Komma getrennt. Z.B. {banktransfer,cod,invoice,moneyorder}');
define('DOWNLOAD_MIN_ORDERS_STATUS_TITLE', 'Min. Bestellstatus');
define('DOWNLOAD_MIN_ORDERS_STATUS_DESC', 'Min. Bestellstatus, ab dem bestellte Downloads freigegeben sind.');

// Vat ID
define('STORE_OWNER_VAT_ID_TITLE' , 'UST ID des Shopbetreibers');
define('STORE_OWNER_VAT_ID_DESC' , 'Die Umsatzsteuer ihres Unternehmens');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_TITLE' , 'Kundenstatus f&uuml;r UST ID gepr&uuml;fte Kunden (Ausland)');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_DESC' , 'W&auml;hlen Sie den Kundenstatus(Gruppe) f&uuml;r UST ID gepr&uuml;fte Kunden aus!');
define('ACCOUNT_COMPANY_VAT_CHECK_TITLE' , 'Umsatzsteuer ID abfragen');
define('ACCOUNT_COMPANY_VAT_CHECK_DESC' , 'Die Umsatzsteuer ID soll durch Kunden eingegeben werden k&ouml;nnen. Bei false wird das Eingabefeld nicht mehr angezeigt.');
define('ACCOUNT_COMPANY_VAT_LIVE_CHECK_TITLE' , 'Umsatzsteuer ID online auf Plausibilit&auml;t &uuml;berpr&uuml;fen');
define('ACCOUNT_COMPANY_VAT_LIVE_CHECK_DESC' , 'Die Umsatzsteuer ID wird online auf Plausibilit&auml;t &uuml;berpr&uuml;ft. Dazu wird der Webservice des Steuerportals der EU (<a href="http://ec.europa.eu/taxation_customs" style="font-style:italic">http://ec.europa.eu/taxation_customs</a>).<br/>Ben&ouml;tigt PHP5 mit aktivierter "SOAP" Unterst&uuml;tztung! <strong><span class="messageStackSuccess">Die "PHP5 SOAP"-Unterst&uuml;tztung ist derzeit '.(in_array ('soap', get_loaded_extensions()) ? '' : '<span class="messageStackError">NICHT</span>').' aktiviert!</span></strong>');
define('ACCOUNT_COMPANY_VAT_GROUP_TITLE' , 'Kundengruppe nach UST ID Check anpassen?');
define('ACCOUNT_COMPANY_VAT_GROUP_DESC' , 'Durch Einschalten dieser Option wird die Kundengruppe nach einen postiven UST ID Check ge&auml;ndert');
define('ACCOUNT_VAT_BLOCK_ERROR_TITLE' , 'Eintragung falscher oder ungepr&uuml;fter USt-IdNr. Nummern sperren?');
define('ACCOUNT_VAT_BLOCK_ERROR_DESC' , 'Durch Einschalten dieser Option werden nur gepr&uuml;fte und richtige USt-IdNr. eingetragen');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL_TITLE','Kundenstatus f&uuml;r UST ID Gepr&uuml;fte Kunden (Inland)');
define('DEFAULT_CUSTOMERS_VAT_STATUS_ID_LOCAL_DESC','W&auml;hlen Sie den Kundenstatus(Gruppe) f&uuml;r UST ID gepr&uuml;fte Kunden aus!');

// Google Conversion
define('GOOGLE_CONVERSION_TITLE','Google Conversion-Tracking');
define('GOOGLE_CONVERSION_DESC','Die Aufzeichnung von Conversions-Keywords bei Bestellungen');
define('GOOGLE_CONVERSION_ID_TITLE','Conversion ID');
define('GOOGLE_CONVERSION_ID_DESC','Ihre Google Conversion ID');
define('GOOGLE_LANG_TITLE','Google Sprache');
define('GOOGLE_LANG_DESC','ISO Code der verwendeten Sprache');

// Afterbuy
define('AFTERBUY_ACTIVATED_TITLE','Aktiv');
define('AFTERBUY_ACTIVATED_DESC','Afterbuyschnittstelle aktivieren');
define('AFTERBUY_PARTNERID_TITLE','Partner ID');
define('AFTERBUY_PARTNERID_DESC','Ihre Afterbuy Partner ID');
define('AFTERBUY_PARTNERPASS_TITLE','Partner Passwort');
define('AFTERBUY_PARTNERPASS_DESC','Ihr Partner Passwort f&uuml;r die Afterbuy XML Schnittstelle');
define('AFTERBUY_USERID_TITLE','User ID');
define('AFTERBUY_USERID_DESC','Ihre Afterbuy User ID');
define('AFTERBUY_ORDERSTATUS_TITLE','Bestellstatus');
define('AFTERBUY_ORDERSTATUS_DESC','Bestellstatus nach erfolgreicher &Uuml;betragung der Bestelldaten');
define('AFTERBUY_URL','Eine Beschreibung von Afterbuy finden Sie hier: <a href="http://www.afterbuy.de" target="new">http://www.afterbuy.de</a>');
define('AFTERBUY_DEALERS_TITLE', 'Als H&auml;ndler markieren');
define('AFTERBUY_DEALERS_DESC', 'geben Sie hier die Gruppen IDs der H&auml;ndler ein, die in Afterbuy als H&auml;ndler eingehen sollen.<br />Beispiel: <em>6,5,8</em>. Es d&uuml;rfen keine Leerzeichen enhalten sein!');
define('AFTERBUY_IGNORE_GROUPE_TITLE', 'Kundengruppe ignorieren');
define('AFTERBUY_IGNORE_GROUPE_DESC', 'welche Kundengruppen sollen ignoiert werden?<br />Beispiel: <em>6,5,8</em>. Es d&uuml;rfen keine Leerzeichen enhalten sein!');

// Search-Options
define('SEARCH_IN_DESC_TITLE','Suche in Produktbeschreibungen');
define('SEARCH_IN_DESC_DESC','Aktivieren um die Suche in den Produktbeschreibungen (Kurz + Lang) zu erm&ouml;glichen');
define('SEARCH_IN_ATTR_TITLE','Suche in Produkt- Attributen');
define('SEARCH_IN_ATTR_DESC','Aktivieren um die Suche in den Produktattributen (z.B. Farbe, L&auml;nge) zu erm&ouml;glichen');

// changes for 3.0.4 SP2
define('REVOCATION_ID_TITLE','Widerrufsrecht ID');
define('REVOCATION_ID_DESC','Content ID des Widerrufrechts');
define('DISPLAY_REVOCATION_ON_CHECKOUT_TITLE','Anzeige Widerrufrecht?');
define('DISPLAY_REVOCATION_ON_CHECKOUT_DESC','Widerrufrecht auf checkout_confirmation anzeigen?');

// BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
define('PAYPAL_MODE_TITLE','PayPal-Modus:');
define('PAYPAL_MODE_DESC','Live (Normal) oder Testbetrieb (Sandbox). Je nach Modus muss bei PayPal zun&auml;chst ein API-Zugriff erstellt werden: <br/>Link: <a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" target="_blank"><strong>API-Zugriff f&uuml;r live-Modus einrichten</strong></a><br/>Link: <a href="https://www.sandbox.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" target="_blank"><strong>API-Zugriff f&uuml;r sandbox-Modus einrichten</strong></a><br/>Sie haben noch gar kein PayPal Konto? <a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=_registration-run" target="_blank"><strong>Klicken Sie hier, um eins zu erstellen.</strong></a>');
define('PAYPAL_API_USER_TITLE','PayPal-API-Benutzer (Live)');
define('PAYPAL_API_USER_DESC','Tragen Sie hier den Benutzernamen ein.');
define('PAYPAL_API_PWD_TITLE','PayPal-API-Passwort (Live)');
define('PAYPAL_API_PWD_DESC','Tragen Sie hier das Passwort ein.');
define('PAYPAL_API_SIGNATURE_TITLE','PayPal-API-Signatur (Live)');
define('PAYPAL_API_SIGNATURE_DESC','Tragen Sie hier die API Signatur ein.');
define('PAYPAL_API_SANDBOX_USER_TITLE','PayPal-API-Benutzer (Sandbox)');
define('PAYPAL_API_SANDBOX_USER_DESC','Tragen Sie hier den Benutzernamen ein.');
define('PAYPAL_API_SANDBOX_PWD_TITLE','PayPal-API-Passwort (Sandbox)');
define('PAYPAL_API_SANDBOX_PWD_DESC','Tragen Sie hier das Passwort ein.');
define('PAYPAL_API_SANDBOX_SIGNATURE_TITLE','PayPal-API-Signatur (Sandbox)');
define('PAYPAL_API_SANDBOX_SIGNATURE_DESC','Tragen Sie hier die API Signatur ein.');
define('PAYPAL_API_VERSION_TITLE','PayPal-API-Version');
define('PAYPAL_API_VERSION_DESC','Tragen Sie hier die aktuelle PayPal API Version ein - z.B.: 84.0');
define('PAYPAL_API_IMAGE_TITLE','PayPal Shop-Logo');
define('PAYPAL_API_IMAGE_DESC','Tragen Sie hier die Logo-Datei ein, die bei PayPal angezeigt werden soll.<br />Achtung: Wird nur &uuml;bertragen, wenn der Shop mit SSL arbeitet.<br />Das Bild darf max. 750px breit und 90px hoch sein.<br />Aufgerufen wird die Datei aus: '.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
define('PAYPAL_API_CO_BACK_TITLE','PayPal Hintergrund-Farbe');
define('PAYPAL_API_CO_BACK_DESC','Tragen Sie hier die Hintergrundfarbe ein, die bei PayPal angezeigt werden soll. z.B. FEE8B9');
define('PAYPAL_API_CO_BORD_TITLE','PayPal Rahmen-Farbe');
define('PAYPAL_API_CO_BORD_DESC','Tragen Sie hier die Rahmenfarbe ein, die bei PayPal angezeigt werden soll. z.B. E4C558');
define('PAYPAL_ERROR_DEBUG_TITLE','PayPal Fehler Anzeige');
define('PAYPAL_ERROR_DEBUG_DESC','Soll der original PayPal Fehler angezeigt werden? Normal=false');
define('PAYPAL_ORDER_STATUS_TMP_ID_TITLE','Bestellstatus "abgebrochen"');
define('PAYPAL_ORDER_STATUS_TMP_ID_DESC','W&auml;hlen Sie den Bestellstatus f&uuml;r eine abgebrochene Aktion aus (z.B. PayPal Abbruch)');
define('PAYPAL_ORDER_STATUS_SUCCESS_ID_TITLE','Bestellstatus OK');
define('PAYPAL_ORDER_STATUS_SUCCESS_ID_DESC','W&auml;hlen Sie den Bestellstatus f&uuml;r eine erfolgreiche Transaktion aus (z.B. Offen PP bezahlt)');
define('PAYPAL_ORDER_STATUS_PENDING_ID_TITLE','Bestellstatus "in Bearbeitung"');
define('PAYPAL_ORDER_STATUS_PENDING_ID_DESC','W&auml;hlen Sie den Bestellstatus f&uuml;r eine Transaktion aus, die noch nicht von PayPal bearbeitet wurde (z.B. Offen PP wartend)');
define('PAYPAL_ORDER_STATUS_REJECTED_ID_TITLE','Bestellstatus "abgewiesen"');
define('PAYPAL_ORDER_STATUS_REJECTED_ID_DESC','W&auml;hlen Sie den Bestellstatus f&uuml;r eine abgelehnte Transaktion aus (z.B. PayPal abgelehnt)');
define('PAYPAL_COUNTRY_MODE_TITLE','PayPal-L&auml;ndermodus');
define('PAYPAL_COUNTRY_MODE_DESC','W&auml;hlen Sie hier die Einstellung f&uuml;r den L&auml;ndermodus. Verschiedene Funktionen von PayPal sind nur in UK m&ouml;glich (z.b. DirectPayment )');
define('PAYPAL_EXPRESS_ADDRESS_CHANGE_TITLE','PayPal-Express-Adressdaten');
define('PAYPAL_EXPRESS_ADDRESS_CHANGE_DESC','Erlaubt das &Auml;ndern der von PayPal &uuml;bermittelten Adressdaten');
define('PAYPAL_EXPRESS_ADDRESS_OVERRIDE_TITLE','Lieferadresse &uuml;berschreiben');
define('PAYPAL_EXPRESS_ADDRESS_OVERRIDE_DESC','Erlaubt das &Auml;ndern der von PayPal &uuml;bermittelten Adressdaten (bestehendes Konto)');
define('PAYPAL_INVOICE_TITLE','Shop-Kenner f&uuml;r PayPal Rg.Nr.');
define('PAYPAL_INVOICE_DESC','Buchstabe(n), die vor die Order-Nr. als Shop-Kenner gesetzt werden und als Rechnungs-Nr. von PayPal benutzt werden.<br />Durch unterschiedliche Shop-Kenner k&ouml;nnen mehrere Shops mit einem PayPal Konto arbeiten, ohne dass es bei gleichen Order-Nummern zu gleichen Rechnungs-Nummern im PayPal Konto kommt.');
// EOF - Tomcraft - 2009-10-03 - Paypal Express Modul

// BOF - Tomcraft - 2009-11-02 - New admin top menu
define('USE_ADMIN_TOP_MENU_TITLE' , 'Admin Top Navigation');
define('USE_ADMIN_TOP_MENU_DESC' , 'Admin Top Navigation aktivieren? Ansonsten wird das Men&uuml; am linken Rand angezeigt (klassisch)');
// EOF - Tomcraft - 2009-11-02 - New admin top menu

// BOF - Tomcraft - 2009-11-02 - Admin language tabs
define('USE_ADMIN_LANG_TABS_TITLE' , 'Sprachtabs bei Kategorien/Artikel');
define('USE_ADMIN_LANG_TABS_DESC' , 'Sprachtabs bei den Eingabefeldern f&uuml;r Kategorien/Artikel aktivieren?');
// EOF - Tomcraft - 2009-11-02 - Admin language tabs

// BOF - Hendrik - 2010-08-11 - Thumbnails in admin products list
define('USE_ADMIN_THUMBS_IN_LIST_TITLE' , 'Artikelliste Bilder');
define('USE_ADMIN_THUMBS_IN_LIST_DESC' , 'In der Admin Artikelliste eine zus&auml;tzliche Spalte mit Bildern der Kategorien / Artikel anzeigen?');
define('USE_ADMIN_THUMBS_IN_LIST_STYLE_TITLE', 'Artikelliste Bilder CSS-Style');
define('USE_ADMIN_THUMBS_IN_LIST_STYLE_DESC', 'Hier k&ouml;nnen einfache CSS Style Angaben eingegeben werden - z.B. f&uuml;r die maximale Breite: max-width:90px;');
// EOF - Hendrik - 2010-08-11 - Thumbnails in admin products list

// BOF - Tomcraft - 2009-11-05 - Advanced contact form
define('USE_CONTACT_EMAIL_ADDRESS_TITLE' , 'Kontaktformular - Sendeoption');
define('USE_CONTACT_EMAIL_ADDRESS_DESC' , '"Kontakt E-Mail" Adresse des Shops zum Versenden des Kontaktformulars verwenden (wichtig f&uuml;r einige Provider z.B Hosteurope)');
// EOF - Tomcraft - 2009-11-05 - Advanced contact form

// BOF - Dokuman - 2010-02-04 - delete cache files in admin section
define('DELETE_CACHE_SUCCESSFUL', 'Cache erfolgreich geleert.');
define('DELETE_TEMP_CACHE_SUCCESSFUL', 'Templatecache erfolgreich geleert.');
// EOF - Dokuman - 2010-02-04 - delete cache files in admin section

// BOF - DokuMan - 2010-08-13 - set Google RSS Feed in admin section
define('GOOGLE_RSS_FEED_REFID_TITLE' , 'Google RSS Feed - refID');
define('GOOGLE_RSS_FEED_REFID_DESC' , 'Tragen Sie hier die Kampagnen-ID ein. Diese wird jedem Link des Google RSS Feeds automatisch hinzugef&uuml;gt.');
// EOF - DokuMan - 2010-08-13 - set Google RSS Feed in admin section

// BOF - web28 - 2010-08-17 -  Bildgroessenberechnung kleinerer Bilder
define('PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT_TITLE','Skalierung von Bildern mit geringer Aufl&ouml;sung');
define('PRODUCT_IMAGE_NO_ENLARGE_UNDER_DEFAULT_DESC','Aktivieren Sie die Einstellung <strong>false</strong> um zu verhindern, dass Produktbilder geringerer Aufl&ouml;sung auf die eingestellten default Werte f&uuml;r Breite und H&ouml;he skaliert werden. Aktivieren Sie die Einstellung <strong>true</strong>, werden auch Bilder geringerer Aufl&ouml;sung auf die eingestellten default Bildgr&ouml;&szlig;enwerte skaliert. In diesem Fall k&ouml;nnen diese Bilder aber sehr unscharf und pixelig dargestellt werden.');
// EOF - web28 - 2010-08-17 -  Bildgroessenberechnung kleinerer Bilder

//BOF - hendrik - 2011-05-14 - independent invoice number and date
define('IBN_BILLNR_TITLE', 'N&auml;chste Rechnungsnummer');
define('IBN_BILLNR_DESC', 'Beim Zuweisung einer Bestellung wird diese Nummer als n&auml;chstes vergeben.');
define('IBN_BILLNR_FORMAT_TITLE', 'Rechnungsnummer Format');       //ibillnr
define('IBN_BILLNR_FORMAT_DESC', 'Aufbauschema Rechn.Nr.: {n}=laufende Nummer, {d}=Tag, {m}=Monat, {y}=Jahr, <br>z.B. "100{n}-{d}-{m}-{y}" ergibt "10099-28-02-2007"');
//EOF - hendrik - 2011-05-14 - independent invoice number and date

//BOC - h-h-h - 2011-12-23 - Button "Buy Now" optional - default off
define('SHOW_BUTTON_BUY_NOW_TITLE', 'Zeige "Jetzt Kaufen" in den Produktlisten');
define('SHOW_BUTTON_BUY_NOW_DESC', '<b>Achtung:</b><br /> Dies kann zu Abmahnungen f&uuml;hren, wenn dem Kunden nicht alle wichtigen Informationen schon in den Listenseiten gezeigt werden!');
//EOC - h-h-h - 2011-12-23 - Button "Buy Now" optional - default off

//split page results
define('MAX_DISPLAY_ORDER_RESULTS_TITLE', 'Anzahl der Bestellungen pro Seite');
define('MAX_DISPLAY_ORDER_RESULTS_DESC', 'Maximum Anzahl der Bestellungen die in der &Uuml;bersicht pro Seite angezeigt werden sollen.');
define('MAX_DISPLAY_LIST_PRODUCTS_TITLE', 'Anzahl der Artikel pro Seite');
define('MAX_DISPLAY_LIST_PRODUCTS_DESC', 'Maximum Anzahl der Artikel die in der &Uuml;bersicht pro Seite angezeigt werden sollen.');
define('MAX_DISPLAY_LIST_CUSTOMERS_TITLE', 'Anzahl der Kunden pro Seite');
define('MAX_DISPLAY_LIST_CUSTOMERS_DESC', 'Maximum Anzahl der Kunden die in der &Uuml;bersicht pro Seite angezeigt werden sollen.');
define('MAX_ROW_LISTS_ATTR_OPTIONS_TITLE', 'Artikelmerkmale: Anzahl der Artikelmerkmale pro Seite');
define('MAX_ROW_LISTS_ATTR_OPTIONS_DESC', 'Maximum Anzahl der Artikelmerkmale (Optionen) die pro Seite angezeigt werden sollen.');
define('MAX_ROW_LISTS_ATTR_VALUES_TITLE', 'Artikelmerkmale: Anzahl der Optionswerte pro Seite');
define('MAX_ROW_LISTS_ATTR_VALUES_DESC', 'Maximum Anzahl der Optionswerte die pro Seite angezeigt werden sollen.');

//whos online
define('WHOS_ONLINE_TIME_LAST_CLICK_TITLE', 'Wer ist Online - Anzeigezeitraum in Sek.');
define('WHOS_ONLINE_TIME_LAST_CLICK_DESC', 'Anzeigedauer der Online User in der "Wer ist Online" Tabelle, nach dieser Zeit werden die Eintr&auml;ge gel&ouml;scht. (min. Wert: 900)');

//sessions
define('SESSION_LIFE_ADMIN_TITLE', 'Session Lebenszeit Admin: ');
define('SESSION_LIFE_ADMIN_DESC', 'Zeitdauer in Sekunden nach der die Sessionzeit f&uuml;r Admins abl&auml;uft (wird ausgeloggt) - Standard 7200 ');
define('SESSION_LIFE_CUSTOMERS_TITLE', 'Session Lebenszeit Kunden:');
define('SESSION_LIFE_CUSTOMERS_DESC', 'Zeitdauer in Sekunden nach der die Sessionzeit f&uuml;r Kunden abl&auml;uft (wird ausgeloggt) - Standard 1440 ');

//checkout confirmation options
define('CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION_TITLE','Bestellbest&auml;tigungsseite: Kurzbeschreibung');
define('CHECKOUT_USE_PRODUCTS_SHORT_DESCRIPTION_DESC','Soll auf der Bestellbest&auml;tigungsseite die Artikel-Kurzbeschreibung angezeigt werden? Hinweis: Die Kurzbeschreibung wird dann angezeigt, wenn es KEINE Artikel-Bestellbeschreibung gibt. Mit False wird die Kurzbeschreibung grunds&auml;tzlich nicht angezeigt!');
define('CHECKOUT_SHOW_PRODUCTS_IMAGES_TITLE','Bestellbest&auml;tigungsseite: Produktbilder');
define('CHECKOUT_SHOW_PRODUCTS_IMAGES_DESC','Sollen auf der Bestellbest&auml;tigungsseite die Artikelbilder angezeigt werden?');
define('CHECKOUT_SHOW_PRODUCTS_MODEL_TITLE','Bestellbest&auml;tigungsseite: Artikel-Nr.');
define('CHECKOUT_SHOW_PRODUCTS_MODEL_DESC','Sollen auf der Bestellbest&auml;tigungsseite die Artikel-Nr. angezeigt werden?');

// email billing attachments
define('EMAIL_BILLING_ATTACHMENTS_TITLE', 'Verrechnung - E-Mail Anh&auml;nge f&uuml;r Bestellungen ');
define('EMAIL_BILLING_ATTACHMENTS_DESC', 'Beispiel f&uuml;r Anh&auml;nge - die Dateien befinden sich im Shopverzeichnis <b>pdf</b>. Mehrere Anh&auml;nge mit Komma ohne Leerzeichen trennen:<br /> pdf/agb.pdf,pdf/widerruf.pdf');

// email images
define('SHOW_IMAGES_IN_EMAIL_TITLE', 'Artikelbilder in Bestell - E-Mail einf&uuml;gen');
define('SHOW_IMAGES_IN_EMAIL_DESC', 'Artikelbilder in die HTML-Bestellbest&auml;tigungs - E-Mail einf&uuml;gen (erh&ouml;ht die Gefahr, das die E-Mail als SPAM eingestuft wird)');
define('SHOW_IMAGES_IN_EMAIL_DIR_TITLE', 'E-Mail Bilderordner ');
define('SHOW_IMAGES_IN_EMAIL_DIR_DESC', 'Auswahl E-Mail Bilderordner');
define('SHOW_IMAGES_IN_EMAIL_STYLE_TITLE', 'E-Mail Bilder CSS-Style');
define('SHOW_IMAGES_IN_EMAIL_STYLE_DESC', 'Hier k&ouml;nnen einfache CSS Style Angaben eingegeben werden - z.B. f&uuml;r die maximale Breite: max-width:90px;');

//popup windows configuration
define('POPUP_SHIPPING_LINK_PARAMETERS_TITLE', 'Versandkosten Popup Fenster URL-Parameter');
define('POPUP_SHIPPING_LINK_PARAMETERS_DESC', 'Hier k&ouml;nnen die URL-Parameter eingegeben werden - Standard: &KeepThis=true&TB_iframe=true&height=400&width=600');
define('POPUP_SHIPPING_LINK_CLASS_TITLE', 'Versandkosten Popup Fenster CSS-Klasse');
define('POPUP_SHIPPING_LINK_CLASS_DESC', 'Hier k&ouml;nnen CSS-Klassen eingegeben werden - Standard: thickbox');
define('POPUP_CONTENT_LINK_PARAMETERS_TITLE', 'Content-Seiten Popup Fenster URL-Parameter');
define('POPUP_CONTENT_LINK_PARAMETERS_DESC', 'Hier k&ouml;nnen die URL-Parameter eingegeben werden - Standard: &KeepThis=true&TB_iframe=true&height=400&width=600');
define('POPUP_CONTENT_LINK_CLASS_TITLE', 'Content-Seiten Popup Fenster CSS-Klasse');
define('POPUP_CONTENT_LINK_CLASS_DESC', 'Hier k&ouml;nnen CSS-Klassen eingegeben werden - Standard: thickbox');
define('POPUP_PRODUCT_LINK_PARAMETERS_TITLE', 'Produkt-Seiten Popup Fenster URL-Parameter');
define('POPUP_PRODUCT_LINK_PARAMETERS_DESC', 'Hier k&ouml;nnen die URL-Parameter eingegeben werden - Standard: &KeepThis=true&TB_iframe=true&height=450&width=750');
define('POPUP_PRODUCT_LINK_CLASS_TITLE', 'Produkt-Seiten Popup Fenster CSS-Klasse');
define('POPUP_PRODUCT_LINK_CLASS_DESC', 'Hier k&ouml;nnen CSS-Klassen eingegeben werden - Standard: thickbox');
define('POPUP_COUPON_HELP_LINK_PARAMETERS_TITLE', 'Coupon Hilfe Popup Fenster URL-Parameter');
define('POPUP_COUPON_HELP_LINK_PARAMETERS_DESC', 'Hier k&ouml;nnen die URL-Parameter eingegeben werden - Standard: &KeepThis=true&TB_iframe=true&height=400&width=600');
define('POPUP_COUPON_HELP_LINK_CLASS_TITLE', 'Coupon Hilfe Popup Fenster CSS-Klasse');
define('POPUP_COUPON_HELP_LINK_CLASS_DESC', 'Hier k&ouml;nnen CSS-Klassen eingegeben werden - Standard: thickbox');

define('POPUP_PRODUCT_PRINT_SIZE_TITLE', 'Produkt Druckansicht Fenstergr&ouml;&szlig;e');
define('POPUP_PRODUCT_PRINT_SIZE_DESC', 'Hier kann die Gr&ouml;&szlig;e des Popup-Fensters definiert werden - Standard: width=640, height=600');
define('POPUP_PRINT_ORDER_SIZE_TITLE', 'Bestellung Druckansicht Fenstergr&ouml;&szlig;e');
define('POPUP_PRINT_ORDER_SIZE_DESC', 'Hier kann die Gr&ouml;&szlig;e des Popup-Fensters definiert werden - Standard: width=640, height=600');

// BOF - Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking
define('TRACKING_COUNT_ADMIN_ACTIVE_TITLE' , 'Seitenaufrufe des Shopbetreibers mitz&auml;hlen');
define('TRACKING_COUNT_ADMIN_ACTIVE_DESC' , 'Wird diese Option aktiviert, so werden auch alle Zugriffe des Administrator-Users des Shopbetreibers mitgez&auml;hlt, die (durch die h&auml;ufigeren  Zugriffe auf den Shop) die Besucherstatistik verf&auml;lschen k&ouml;nnen.');
define('TRACKING_GOOGLEANALYTICS_ACTIVE_TITLE' , 'Google Analytics Tracking aktivieren');
define('TRACKING_GOOGLEANALYTICS_ACTIVE_DESC' , 'Wird diese Option aktiviert, so werden alle Seitenaufrufe an Google Analytics &uuml;bermittelt und k&ouml;nnen sp&auml;ter ausgewertet werden. Dazu ist vorher die Anlage eines Kontos bei <a href="http://www.google.com/analytics/" target="_blank"><b>Google Analytics</b></a> erforderlich.');
define('TRACKING_GOOGLEANALYTICS_ID_TITLE' , 'Google Analytics Kontonummer');
define('TRACKING_GOOGLEANALYTICS_ID_DESC' , 'Tragen Sie hier die Google Analytics Kontonummer im Format "UA-XXXXXXXX-X" ein, die Sie nach einer erfolgreichen Kontoerstellen bekommen haben.');
define('TRACKING_PIWIK_ACTIVE_TITLE' , 'PIWIK Web-Analytics Tracking aktivieren');
define('TRACKING_PIWIK_ACTIVE_DESC' , 'Um PIWIK nutzen zu k&ouml;nnen, m&uuml;ssen Sie es zun&auml;chst herunterladen und auf Ihrem Webspace installieren, siehe auch <a href="http://http://de.piwik.org/" target="_blank"><b>PIWIK Web-Analytics</b></a>. Im Gegensatz zu Google Analytics werden die Daten lokal gespeichert, d.h. Sie als Shopbetreiber haben die Datenhoheit.');
define('TRACKING_PIWIK_LOCAL_PATH_TITLE' , 'PIWIK Installationsverzeichnis (ohne "http://")');
define('TRACKING_PIWIK_LOCAL_PATH_DESC' , 'Tragen Sie hier das Verzeichnis ein, nachdem PIWIK erfolgreich installiert worden ist. Als Pfad ist hier der komplette Domainname ohne "http://" einzutragen, z.B. "www.domain.de/piwik".');
define('TRACKING_PIWIK_ID_TITLE' , 'PIWIK Seiten-ID');
define('TRACKING_PIWIK_ID_DESC' , 'In der PIWIK Administrationsoberfl&auml;che wird pro angelegter Domain wird eine ID vergeben (meist "1")');
define('TRACKING_PIWIK_GOAL_TITLE' , 'PIWIK Kampagnen-Nummer (optional)');
define('TRACKING_PIWIK_GOAL_DESC' , 'Tragen Sie hier eine Kampagnen-Nummer ein, wenn Sie vordefinierte Ziele nachverfolgen m&ouml;chten. Details siehe <a href="http://piwik.org/docs/tracking-goals-web-analytics/" target="_blank"><b>PIWIK: Tracking Goal Conversions</b></a>');
// EOF - Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking

define('CONFIRM_SAVE_ENTRY_TITLE', 'Best&auml;tigungsabfrage beim Speichern von Artikeln/Kategorien');
define('CONFIRM_SAVE_ENTRY_DESC', 'Soll eine Best&auml;tigungsabfrage beim Speichern von Artikeln/Kategorien erfolgen? Standard: true (ja)');

define('WHOS_ONLINE_IP_WHOIS_SERVICE_TITLE', 'Wer ist Online - Whois Lookup URL');
define('WHOS_ONLINE_IP_WHOIS_SERVICE_DESC', 'http://www.utrace.de/?query= oder http://whois.domaintools.com/');

define('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS_TITLE', 'Bestellabschlu&szlig; - Ausverkaufte Artikel deaktivieren');
define('STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS_DESC', 'Soll ein ausverkaufter Artikel (Lagermenge 0) am Ende der Bestellung automatisch deaktiviert werden? Der Artikel ist dann nicht mehr im Shop sichtbar!<br />Bei Artikeln die in K&uuml;rze wieder lieferbar sind, sollte die Option auf "false" gesetzt werden');

define('SHIPPING_STATUS_INFOS_TITLE', 'Lieferzeit ID');
define('SHIPPING_STATUS_INFOS_DESC', 'Content ID f&uuml;r Lieferzeiten Hinweis');