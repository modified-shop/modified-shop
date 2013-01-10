<?php
  /* --------------------------------------------------------------
   $Id: german.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ----------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (german.php,v 1.8 2003/08/13); www.nextcommerce.com
   (c) 2006 xt:Commerce (german.php 1213 2005-09-14); www.xtcommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  // Global
  define('TEXT_FOOTER','<a style="text-decoration:none;" href="http://www.modified-shop.org" target="_blank"><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></a><span style="color:#555555;">' . '&nbsp;' . '&copy;2009-' . date('Y') . '&nbsp;' . 'provides no warranty and is redistributable under the <a style="color:#555555;text-decoration:none;" href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License (Version 2)</a><br />eCommerce Engine 2006 based on <a style="text-decoration:none; color:#555555;" href="http://www.xt-commerce.com/" rel="nofollow" target="_blank">xt:Commerce</a></span>');

  // Box names
  define('BOX_LANGUAGE','Sprache');
  define('BOX_DB_CONNECTION','DB Verbindung') ;
  define('BOX_WEBSERVER_SETTINGS','Webserver Einstellungen');
  define('BOX_DB_IMPORT','DB Import');
  define('BOX_WRITE_CONFIG','Schreiben der Konfigurationsdatei');
  define('BOX_ADMIN_CONFIG','Administrator Konfiguration');
  define('BOX_USERS_CONFIG','User Konfiguration');
  define('PULL_DOWN_DEFAULT','Bitte W&auml;hlen Sie ein Land');

  // Error messages
  // index.php
  define('SELECT_LANGUAGE_ERROR','Bitte w&auml;hlen Sie eine Sprache!');
  // install_step2,5.php
  define('TEXT_CONNECTION_ERROR','Eine Testverbindung zur Datenbank war nicht erfolgreich.');
  define('TEXT_CONNECTION_SUCCESS','Eine Testverbindung zur Datenbank war erfolgreich.');
  define('TEXT_DB_ERROR','Folgender Fehler wurde zur&uuml;ckgegeben:');
  define('TEXT_DB_ERROR_1','Bitte klicken Sie auf <i>Back</i> um Ihre Datenbankeinstellungen zu &uuml;berpr&uuml;fen.');
  define('TEXT_DB_ERROR_2','Wenn Sie Hilfe zu Ihrer Datenbank ben&ouml;tigen, wenden Sie sich bitte an Ihren Provider.');
  // BOF - web28 - 2010.12.13 - NEW db-upgrade
  define('TEXT_DB_UPGRADE','<strong>Nur Datenbank Upgrade der bereits installierten modified eCommerce Shopsoftware durchf&uuml;hren!</strong>');
  // BOF - web28 - 2010.12.13 - NEW db-upgrade
  // BOF - vr - 2010-01-14 - check MySQL *server* version
  define('TEXT_DB_SERVER_VERSION_ERROR','Ihre MySQL-Version ist zu alt. Der Shop ben&ouml;tigt mindestens die Version: ');
  define('TEXT_DB_SERVER_VERSION','Ihre MySQL-Version: ');
  // EOF - vr - 2010-01-14 - check MySQL *server* version
  // BOF - vr - 2010-01-14 - check MySQL *client* version
  define('TEXT_DB_CLIENT_VERSION_WARNING','Ihre MySQL-Client-Version ist zu alt. Der Shop ben&ouml;tigt mindestens die Version: 4.1.2 </br></br>Sie k&ouml;nnen die Installation aber fortf&uuml;hren.</br>Wenn sich die Installation nicht fehlerfrei durchf&uuml;hren l&auml;sst, bitten Sie Ihren Provider um ein Update!');
  define('TEXT_DB_CLIENT_VERSION','Ihre MySQL-Client-Version: ');
  // EOF - vr - 2010-01-14 - check MySQL *client* version
  // BOF - web28 - 2010-02-1014 - check FILE PATH
  define('TEXT_PATH_ERROR','<h1>URL oder Dateipfad ung&uuml;ltig</h1>');
  define('TEXT_PATH_ERROR2','Achtung! Sie haben eine ung&uuml;ltige URL oder einen ung&uuml;ltigen Dateipfad eingegeben!');
  define('TEXT_PATH_ERROR3','Bitte &uuml;berpr&uuml;fen Sie Ihre Einstellungen!');
  // EOF - web28 - 2010-02-1014 - check FILE PATH
  // BOF - DokuMan - 2010-08-16 - language dependent definitions for index.php
  define('TEXT_WRONG_FILE_PERMISSION','FALSCHE DATEIRECHTE ');
  define('TEXT_WRONG_FOLDER_PERMISSION','FALSCHE VERZEICHNISRECHTE ');
  define('TEXT_FILE_PERMISSION_STATUS','DATEIRECHTE ');
  define('TEXT_FOLDER_PERMISSION_STATUS','VERZEICHNISRECHTE ');
  define('TEXT_ERROR','FEHLER');
  define('TEXT_WARNING','WARNUNG');
  define('TEXT_PHPVERSION_TOO_OLD','ACHTUNG! Ihre PHP-Version ist zu alt. Der Shop setzt mindestens die Version %s voraus.<br /><br />Ihre PHP-Version: ');
  define('TEXT_ERROR_PHP_MAX','ACHTUNG! Ihre PHP-Version ist zu neu. Der Shop funktioniert nur bis Version %s.<br /><br />Ihre PHP-Version: ');
  // BOF - h-h-h - 2011-04-14 - check fsockopen
  define('TEXT_FSOCKOPEN_NOT_SUPPORTED','FSOCKOPEN WIRD NICHT UNTERST&Uuml;TZT. Einige Dritt-Module verwenden fsockopen. Diese werden ohne fsockopen-Unterst&uuml;tzung nicht funktionieren!');
  // EOF - h-h-h - 2011-04-14 - check fsockopen
  // BOF - DokuMan - 2012-06-11 - check cURL
  define('TEXT_CURL_NOT_SUPPORTED','CURL WIRD NICHT UNTERST&Uuml;TZT. Einige Dritt-Module verwenden cURL. Diese werden ohne cURL-Unterst&uuml;tzung nicht funktionieren!');
  // EOF - DokuMan - 2012-06-11 - check cURL
  define('TEXT_NO_GDLIB_FOUND',': KEINE GDLIB GEFUNDEN!');
  define('TEXT_GDLIBV2_SUPPORT','falls GDlib Version &lt; 2+ , wenden Sie sich bitte an den Support!');
  define('TEXT_GDLIB_MISSING_GIF_SUPPORT','Sie haben keine GIF-Unterst&uuml;tzung innerhalb der GDlib, so dass Sie im Shop keine GIF-Bilder und GIF-Wasserzeichen-Funktionen nutzen k&ouml;nnen!');
  define('TEXT_GDLIB_GIF_VERSION','GDlib GIF-Unterst&uuml;tzung');
  define('TEXT_CHMOD_REMARK_HEADLINE','Achtung');
  define('TEXT_CHMOD_REMARK','Die folgenden Dateien und Ordner ben&ouml;tigen Schreibrechte ( CHMOD 0777 )');
  define('TEXT_CHECKING','&Uuml;berpr&uuml;fung');
  define('TEXT_INSTALLATION_NOT_POSSIBLE','Die Installation kann wegen fehlender Voraussetzungen nicht fortgesetzt werden! Bitte beheben Sie die Fehler und versuchen Sie es dann erneuet!');
  // EOF - DokuMan - 2010-08-16 - language dependent definitions for index.php

  // index.php
  define('TITLE_SELECT_LANGUAGE','W&auml;hlen Sie eine Sprache aus!');
  define('TEXT_WELCOME_INDEX','<b>Willkommen zur modified eCommerce Shopsoftware</b><br /><br />Die modified eCommerce Shopsoftware ist eine Open-Source e-commerce L&ouml;sung, die st&auml;ndig vom modified eCommerce Shopsoftware Team und einer grossen Gemeinschaft weiterentwickelt wird.<br /> Seine out-of-the-box Installation erlaubt es dem Shop-Besitzer seinen Online-Shop mit einem Minimum an Aufwand und Kosten zu installieren, zu betreiben und zu verwalten.<br /><br />Die modified eCommerce Shopsoftware ist auf jedem System lauff&auml;hig, welches eine PHP Umgebung (ab PHP 5.0) und MySQL zur Verf&uuml;gung stellt, wie zum Beispiel Linux, Solaris, BSD, und Microsoft Windows.<br /><br />Die modified eCommerce Shopsoftware ist ein OpenSource-Projekt &ndash; wir stecken jede Menge Arbeit und Freizeit in dieses Projekt und w&uuml;rden uns daher &uuml;ber eine Spende als kleine Anerkennung freuen.');
  define('TEXT_INFO_DONATIONS_IMG_ALT','Unterst&uuml;tzen Sie dieses Projekt mit Ihrer Spende');
  define('TEXT_WELCOME_STEP1','<b>Datenbank- und Webservereinstellungen</b><br /><br />Der Installer ben&ouml;tigt hier einige Informationen bez&uuml;glich Ihrer Datenbank und Ihrer Verzeichnisstruktur.');
  define('TEXT_WELCOME_STEP2','<b>Datenbank Installation</b><br /><br />Der modified eCommerce Shopsoftware Installer installiert automatisch die modified eCommerce Shopsoftware Datenbank.');
  // BOF - web28 - 2010.02.20 - NEW STEP2-4 Handling
  define('TEXT_WELCOME_STEP2A','<b>Datenbank Installation wurde deaktiviert</b><br /><br />Die Installation der modified eCommerce Shopsoftware Datenbank in Step3 wird &uuml;bersprungen!.');
  // EOF - web28 - 2010.02.20 - NEW STEP2-4 Handling
  define('TEXT_WELCOME_STEP3','<b>Datenbank Import.</b><br /><br />Die Daten der modified eCommerce Shopsoftware Datenbank werden automatisch in die Datenbank importiert.');
  define('TEXT_WELCOME_STEP4','<b>Erstellen der modified eCommerce Shopsoftware Konfigurations-Dateien</b><br /><br /><b>Wenn bereits configure Dateien aus einer fr&uuml;heren Installation vorhanden sind, wird die modified eCommerce Shopsoftware diese L&ouml;schen.</b><br /><br />Der Installer schreibt automatisch die Konfigurationsdateien f&uuml;r die Dateistruktur und die Datenbankanbindung.<br /><br />Sie k&ouml;nnen zwischen verschiedenen Session-Handling_systemen w&auml;hlen.');
  define('TEXT_WELCOME_STEP5','<b>Webserver Konfiguration</b><br /><br />');
  // BOF - web28 - 2010-02-1014 - CORRECT TO GERMAN
  define('TEXT_WELCOME_STEP6','<b>Grunds&auml;tzliche Shopkonfiguration</b><br /><br />Der Installer richtet den Admin-Account ein und schreibt noch diverse Daten in die Datenbank.<br />Die angegebenen Daten f&uuml;r <b>Land</b> und <b>PLZ</b> werden f&uuml;r die Versand und Steuerberechnungen genutzt.<br /><br />Wenn Sie w&uuml;nschen, kann die modified eCommerce Shopsoftware automatisch die Zonen, Steuers&auml;tze und Steuerklassen f&uuml;r Versand und Verkauf innerhalb der EU einrichten.<br />Markieren Sie nur <b>automatisches Einstellen der Steuerzonen</b> - <b>Ja</b>.');
  // EOF - web28 - 2010-02-1014 - CORRECT TO GERMAN
  define('TEXT_WELCOME_STEP7','<b>Setup f&uuml;r G&auml;ste und Standardkunden</b><br /><br />Das modified eCommerce Shopsoftware Gruppen und Preissystem bietet Ihnen unbegrenzte M&ouml;glichkeiten der Preisgebung.<br /><br />
    <b>% Rabatt auf ein einzelnes Produkt</b><br />
    %max kann f&uuml;r jedes einzelne Produkt und f&uuml;r jede einzelne Kundengruppe gesetzt werden.<br />
    wenn %max f&uuml;r Produkt = 10.00% jedoch %max f&uuml;r Gruppe = 5% -> 5% Rabatt auf das Produkt<br />
    wenn %max f&uuml;r Produkt = 10.00% jedoch %max f&uuml;r Gruppe = 15% -> 10% Rabatt auf das Produkt<br /><br />
    <b>% Rabatt auf die Gesamte Bestellung</b><br />
    % Rabatt des Bestellwertes (nach Steuer und W&auml;hrungsberechnung)<br /><br />
    <b>Staffelpreise</b><br />
    Sie k&ouml;nnen ebenfalls beliebig viele Staffelpreise f&uuml;r einzelne Produkte und einzelne Kundengruppen einrichten.<br />
    Sie k&ouml;nnen auch jedes dieser Systeme beliebig kombinieren, wie zum Beispiel:<br />
    Kundengruppe 1 -> Staffelpreise auf das Produkt Y<br />
    Kundengruppe 2 -> 10% Rabatt auf Produkt Y<br />
    Kundengruppe 3 -> ein spezielle Gruppenpreis f&uuml;r Produkt Y<br />
    Kundengruppe 4 -> Nettopreis f&uuml;r Produkt Y<br />
  ');
  define('TEXT_WELCOME_FINISHED','<b>Die modified eCommerce Shopsoftware Installation war erfolgreich!</b><br /><br />Der Installer hat nun die Grundfunktionen Ihres Shops eingerichtet. Melden Sie sich im Shop mit Ihrem Admin-Account an und wechseln in den Adminbereich, um die komplette Konfiguration Ihres Shops vorzunehmen.');

  // install_step1.php
  define('TITLE_CUSTOM_SETTINGS','Installations Optionen');
  define('TEXT_IMPORT_DB','modified eCommerce Shopsoftware Datenbank Installation');
  define('TEXT_IMPORT_DB_LONG','Installiert die modified eCommerce Shopsoftware Datenbankstruktur mit den ben&ouml;tigten Tabellen. <b>(Zwingend bei Erstinstallation! Bestehende Tabellen werden dabei geleert!)</b>');
  define('TEXT_AUTOMATIC','Konfigurations-Dateien erstellen');
  define('TEXT_AUTOMATIC_LONG','Ihre Informationen bez&uuml;glich Webserver und Datenbank werden automatisch in die ben&ouml;tigten Catalog und Admin Konfigurations-Dateien geschrieben, bestehende Dateien werden dabei &uuml;berschrieben!');
  define('TITLE_DATABASE_SETTINGS','Datenbank Informationen');
  define('TEXT_DATABASE_SERVER','Datenbankserver');
  define('TEXT_DATABASE_SERVER_LONG','Der Datenbankserver kann entweder in Form eines Hostnamens, wie zum Beispiel <i>db1.myserver.com</i> oder <i>localhost</i>, oder als IP-Adresse, wie <i>192.168.0.1</i> angegeben werden.');
  define('TEXT_USERNAME','Benutzername');
  define('TEXT_USERNAME_LONG','Der Benutzername, der zum konnektieren der Datenbank ben&ouml;tigt wird, wie zum Beispiel <i>mysql_10</i>.<br /><br />Bemerkung: Wenn die modified eCommerce Shopsoftware Datenbank importiert werden soll (wenn oben ausgew&auml;hlt), muss der Benutzer CREATE und DROP Rechte f&uuml;r die Datenbank haben. Sollten hier Probleme auftreten, kann Ihnen Ihr Provider weiterhelfen.');
  define('TEXT_PASSWORD','Passwort');
  define('TEXT_PASSWORD_LONG','Das Passwort wird zusammen mit dem Benutzernamen zum Verbindungsaufbau zur Datenbank benutzt.');
  define('TEXT_DATABASE','Datenbank');
  define('TEXT_DATABASE_LONG','Der Name der Datenbank, in die die Tabellen eingef&uuml;gt werden sollen.<br /><b>ACHTUNG:</b> Es muss bereits eine leere Datenbank vorhanden sein, falls nicht -> leere Datenbank mit phpMyAdmin erstellen!');
  define('TITLE_WEBSERVER_SETTINGS','Webserver Informationen');
  define('TEXT_WS_ROOT','Webserver Root Verzeichnis');
  define('TEXT_WS_ROOT_LONG','Das Verzeichnis, in das die Webseiten gespeichert werden, zum Beispiel <b>/home/myname/htdocs</b>.');
  define('TEXT_WS_XTC','Webserver Shop Verzeichnis');
  define('TEXT_WS_XTC_LONG','Das Verzeichnis, in das der Shop geladen wurde, relativ zum Webserver Root Verzeichnis, bspw. <b>/modified-shop/</b>.<br /><br />Webserver Root Verzeichnis + Webserver Shop Verzeichnis ergeben den vollst&auml;ndigen Pfad zum Shop.');
  define('TEXT_WS_ADMIN','Webserver Admin Verzeichnis');
  define('TEXT_WS_ADMIN_LONG','Das Verzeichnis, in welchem sich die Admin-Werkzeuge Ihres Shops befinden (vom Webserver root Verzeichnis), beispielsweise <i>/home/myname/public_html<b>/modified-shop/admin/</b></i>.');
  define('TEXT_WS_CATALOG','WWW Catalog Verzeichnis');
  define('TEXT_WS_CATALOG_LONG','Das virtuelle Verzeichnis, in dem sich die modified eCommerce Shopsoftware befindet, relativ zum HTTP Server, bspw. <b>/</b> oder <b>/modified-shop/</b>.<br /><br />HTTP Server + WWW Catalog Verzeichnis ergeben die Shop-URL.');
  define('TEXT_WS_ADMINTOOL','WWW Admin Verzeichnis');
  define('TEXT_WS_ADMINTOOL_LONG','Das virtuelle Verzeichnis, in dem sich die modified eCommerce Shopsoftware Admin-Module befinden, beispielsweise <i>http://www.Ihre-Domain.de<b>/modified-shop/admin/</b></i>');
  //BOF WEBSERVER INFO
  define('TITLE_WEBSERVER_INFO','Die vorgegebenen Pfade sind nur in Ausnahmef&auml;llen zu &auml;ndern!');
  define('TEXT_WS_ROOT_INFO','Der Pfad wird automatisch ermittelt!');
  //EOF WEBSERVER INFO

  // install_step2.php
  define('TEXT_PROCESS_1','Bitte setzten Sie die Installation nun fort, um die Datenbank zu Importieren.');
  define('TEXT_PROCESS_2','Dieser Vorgang nimmt einige Zeit in Anspruch. Es ist wichtig, dass Sie den Vorgang nun nicht unterbrechen, weil sonst die Datenbank m&ouml;glicherweise nicht korrekt installiert wird.');
  define('TEXT_PROCESS_3','Die zu importierende Datei muss sich an folgendem Ort befinden. Diese befindet sich bei einem Standard-Upload dort.');

  // install_step3.php
  define('TEXT_TITLE_ERROR','Der folgende Fehler ist aufgetreten:');
  define('TEXT_TITLE_SUCCESS','Der Datenbank-Import war erfolgreich.');

  // install_step4.php
  define('TITLE_WEBSERVER_CONFIGURATION','Webserver Informationen:');
  define('TITLE_STEP4_ERROR','Der folgende Fehler ist aufgetreten:');
  define('TEXT_STEP4_ERROR','<b>Die Konfigurationsdateien existieren nicht, oder deren Rechte sind nicht richtig gesetzt.</b><br /><br />Bitte f&uuml;hren Sie folgende Aktionen durch: ');
  define('TEXT_STEP4_ERROR_1','Wenn <i>chmod 706</i> nicht funktioniert, versuchen Sie <i>chmod 777</i>.');
  define('TEXT_STEP4_ERROR_2','Wenn Sie diese Installationsroutine in einer Windows Umgebung ausf&uuml;hren, versuchen Sie das Umbenennen der entsprechenden Dateien.');
  define('TEXT_VALUES','Die Konfigurations-Werte werden nun in die folgenden Dateien geschrieben:');
  define('TITLE_CHECK_CONFIGURATION','Bitte pr&uuml;fen Sie Ihre Webserver Informationen');
  define('TEXT_HTTP','HTTP Server');
  define('TEXT_HTTP_LONG','Der Webserver kann als Hostname, bspw. <b>http://myshop.com</b>, oder als IP-Adresse angegeben werden.');
  define('TEXT_HTTPS','HTTPS Server');
  define('TEXT_HTTPS_LONG','Der gesicherte Webserver kann als Hostname, bspw. <b>https://myshop.com</b>, oder als IP-Adresse angegeben werden.');
  define('TEXT_SSL','Benutze SSL-Verbindung');
  define('TEXT_SSL_LONG','Erm&ouml;glicht die Nutzung einer gesicherten Verbindung mittels SSL (HTTPS)');
  define('TITLE_CHECK_DATABASE','Bitte pr&uuml;fen Sie Ihre Datenbank Informationen');
  define('TEXT_PERSIST','Benutze Persistente Verbindung');
  define('TEXT_PERSIST_LONG','H&auml;lt eine Verbindung zur Datenbank f&uuml;r l&auml;ngere Zeit aufrecht. Auf den meisten geteilten Servern ist diese Funktion nicht m&ouml;glich.');
  define('TEXT_SESS_FILE','Speichere Sessions in Dateien.');
  define('TEXT_SESS_DB','Speichere Sessions in der Datenbank');
  define('TEXT_SESS_LONG','Das Verzeichnis, in welches PHP die Session-Dateien speichert.');
  define('TITLE_CHECK_FILES','Bitte pr&uuml;fen Sie Ihre Datei Informationen');
  //BOF - web28 - 2010-03-02 - New SSL-PROXY info
  define('TEXT_SSL_PROXY_LONG','<b>* Benutze SSL Proxy: </b><br />Bei Verwendung eines SLL Proxys ist der Pfad bei <b>HTTPS Server</b> anzupassen!');
  //BOF - GTB - 2010-08-31 - Layout correction
  define('TEXT_SSL_PROXY_EXP','<b>SSL Proxy Beispiele f&uuml;r einige Provider: </b><br /><br /><div class="prov">Hosteurope: </div><div class="proxy">https://ssl.webpack.de/nureinbeispiel.de</div><div class="clear">&nbsp;</div><div class="prov">ALL-INKL.COM: </div><div class="proxy">https://ssl-account.com/nureinbeispiel.de</div><div class="clear">&nbsp;</div><div class="prov">1und1: </div><div class="proxy">https://ssl.kundenserver.de/nureinbeispiel.de</div><div class="clear">&nbsp;</div><div class="prov">Strato: </div><div class="proxy">https://www.ssl-id.de/nureinbeispiel.de</div><div class="clear">&nbsp;</div><div class="prov">domainFactory: </div><div class="proxy">https://sslsites.de/nureinbeispiel.de</div><div class="clear">&nbsp;</div>');
  //EOF - GTB - 2010-08-31 - Layout correction
  //EOF - web28 - 2010-03-02 - New SSL-PROXY info

  // install_step5.php
  define('TEXT_WS_CONFIGURATION_SUCCESS','Die <strong>modified eCommerce Shopsoftware</strong> Webserver Konfiguration war erfolgreich');

  // install_step6.php
  define('TITLE_ADMIN_CONFIG','Administrator Konfiguration');
  define('TEXT_REQU_INFORMATION','* erforderliche Information');
  define('TEXT_FIRSTNAME','Vorname:');
  define('TEXT_LASTNAME','Nachname:');
  define('TEXT_EMAIL','E-Mail Adresse:');
  define('TEXT_EMAIL_LONG','E-Mail Adresse, an die eine separate Mail bei Bestellungen gesendet werden soll.');
  define('TEXT_STREET','Stra&szlig;e:');
  define('TEXT_POSTCODE','PLZ:');
  define('TEXT_CITY','Stadt:');
  define('TEXT_STATE','Bundesland/Province:');
  define('TEXT_COUNTRY','Land:');
  define('TEXT_COUNTRY_LONG','Wird benutzt f&uuml;r Versand und Steuern.');
  define('TEXT_TEL','Telefonnummer:');
  define('TEXT_PASSWORD_CONF','Passwort Best&auml;tigung:');
  define('TITLE_SHOP_CONFIG','Shop Konfiguration');
  define('TEXT_STORE','Shop Name:');
  define('TEXT_STORE_LONG','Der Name des Shops.');
  define('TEXT_EMAIL_FROM','E-Mail von');
  define('TEXT_EMAIL_FROM_LONG','Die E-Mail Adresse, die in den Bestellungen als From benutzt wird.');
  define('TITLE_ZONE_CONFIG','Zonen Konfiguration');
  define('TEXT_ZONE','automatisches Einstellen der Steuerzonen?');
  define('TITLE_ZONE_CONFIG_NOTE','*Hinweis: modified eCommerce Shopsoftware kann die Zonen automatisch aufsetzten, sofern Sich Ihr Shop in der EU befindet.');
  define('TITLE_SHOP_CONFIG_NOTE','*Hinweis: Information for grundlegende Shopeinstellungen');
  define('TITLE_ADMIN_CONFIG_NOTE','*Hinweis: Informationen f&uuml;r Admin/Superuser');
  define('TEXT_ZONE_NO','Nein');
  define('TEXT_ZONE_YES','Ja');
  define('TEXT_COMPANY','Firmenname');
  define('ENTRY_FIRST_NAME_ERROR','Vorname ist zu kurz');
  define('ENTRY_LAST_NAME_ERROR','Nachname ist zu kurz');
  define('ENTRY_EMAIL_ADDRESS_ERROR','E-Mail Adresse ist zu kurz');
  define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR','Bitte &uuml;berpr&uuml;fen Sie Ihre E-Mail Adresse');
  define('ENTRY_STREET_ADDRESS_ERROR','Stra&szlig;e ist zu kurz');
  define('ENTRY_POST_CODE_ERROR','Postleitzahl ist zu kurz');
  define('ENTRY_CITY_ERROR','Stadt ist zu kurz');
  define('ENTRY_COUNTRY_ERROR','Bitte &uuml;berpr&uuml;fen Sie das Bundesland');
  define('ENTRY_STATE_ERROR','Bitte &uuml;berpr&uuml;fen Sie das Land');
  define('ENTRY_TELEPHONE_NUMBER_ERROR','Telefonnummer ist zu kurz');
  define('ENTRY_PASSWORD_ERROR','Bitte &uuml;berpr&uuml;fen Sie das Passwort');
  define('ENTRY_STORE_NAME_ERROR','Shop-Name ist zu kurz');
  define('ENTRY_COMPANY_NAME_ERROR','Firmenname ist zu kurz');
  define('ENTRY_EMAIL_ADDRESS_FROM_ERROR','E-Mail-From ist zu kurz');
  define('ENTRY_EMAIL_ADDRESS_FROM_CHECK_ERROR','Bitte &uuml;berpr&uuml;fen Sie den E-Mail-From');
  define('SELECT_ZONE_SETUP_ERROR','W&auml;hlen Sie Zone-Setup');

  // install_step7
  define('TITLE_GUEST_CONFIG','Gast Konfiguration');
  define('TITLE_GUEST_CONFIG_NOTE','*Hinweis; Gast-User Einstellungen (nicht-registrierter Benutzer)');
  define('TITLE_CUSTOMERS_CONFIG','Standard-Kunde Konfiguration');
  define('TITLE_CUSTOMERS_CONFIG_NOTE','*Hinweis; Standard-Kunde Einstellungen (registrierter Kunde)');
  define('TEXT_STATUS_DISCOUNT','Rabatt auf Produkte');
  define('TEXT_STATUS_DISCOUNT_LONG','Rabatt auf Produkte <i>(in Prozent, z.B. 10.00 , 20.00)</i>');
  define('TEXT_STATUS_OT_DISCOUNT_FLAG','Rabatt auf Bestellung');
  define('TEXT_STATUS_OT_DISCOUNT_FLAG_LONG',' Erlaubt den Rabatt auf den kompletten Bestellwert');
  define('TEXT_STATUS_OT_DISCOUNT','Rabatth&ouml;he auf Bestellung');
  define('TEXT_STATUS_OT_DISCOUNT_LONG','H&ouml;he des Rabattes auf den Bestellwert <i>(in Prozent, z.B. 10.00 , 20.00)</i>');
  define('TEXT_STATUS_GRADUATED_PRICE','Staffelpreise');
  define('TEXT_STATUS_GRADUATED_PRICE_LONG','Erlaubt es dem entsprechenden User die Staffelpreise zu sehen.');
  define('TEXT_STATUS_SHOW_PRICE','Preise');
  define('TEXT_STATUS_SHOW_PRICE_LONG','Erlaubt es dem User, normale Preise zu sehen.');
  define('TEXT_STATUS_SHOW_TAX','inkl. Steuer');
  define('TEXT_STATUS_SHOW_TAX_LONG','Zeigt die angegebenen Preise mit (Ja) oder ohne (Nein) Steuer.');
  define('TEXT_STATUS_COD_PERMISSION','Per Nachnahme');
  define('TEXT_STATUS_COD_PERMISSION_LONG','Erlaubt dem Kunden per Nachnahme zu bestellen.');
  define('TEXT_STATUS_CC_PERMISSION','Kreditkarten.');
  define('TEXT_STATUS_CC_PERMISSION_LONG','Erlaubt dem Kunden &uuml;ber ihre Kreditkartenzahlsysteme zu bestellen.');
  define('TEXT_STATUS_BT_PERMISSION','Bankeinzug');
  define('TEXT_STATUS_BT_PERMISSION_LONG','Erlaubt dem Kunden per Bankeinzug zu bestellen.');
  define('ENTRY_DISCOUNT_ERROR','Product discount -Guest');
  define('ENTRY_OT_DISCOUNT_ERROR','Discount on ot -Guest');
  define('SELECT_OT_DISCOUNT_ERROR','Discount on ot -Guest');
  define('SELECT_GRADUATED_ERROR','Graduated Prices -Guest');
  define('SELECT_PRICE_ERROR','Show Price -Guest');
  define('SELECT_TAX_ERROR','Show Tax -Guest');
  define('ENTRY_DISCOUNT_ERROR2','Product discount -Default');
  define('ENTRY_OT_DISCOUNT_ERROR2','Discount on ot -Default');
  define('SELECT_OT_DISCOUNT_ERROR2','Discount on ot -Default');
  define('SELECT_GRADUATED_ERROR2','Graduated Prices -Default');
  define('SELECT_PRICE_ERROR2','Show Price -Default');
  define('SELECT_TAX_ERROR2','Show Tax -Default');

  // install_fnished.php
  define('TEXT_SHOP_CONFIG_SUCCESS','Die <strong>modified eCommerce Shopsoftware</strong> Konfiguration war erfolgreich');
  define('TEXT_TEAM','Vielen Dank, dass Sie sich f&uuml;r die modified eCommerce Shopsoftware entschieden haben. Besuchen Sie uns auf der <a href="http://www.modified-shop.org">modified eCommerce Shopsoftware Supportseite</a>.<br /><br />Alles Gute und viel Erfolg w&uuml;nscht Ihnen das gesamte modified eCommerce Shopsoftware Team.<br /><br />Wenn Sie uns unterst&uuml;tzen wollen, w&uuml;rden wir uns &uuml;ber eine kleine Spende freuen.<br />');

  // BOC flth new permission change system
  define('FTP_CHANGE_PERM_EXPLAIN', 'Um die Datei- und Verzeichnisrechte zu setzen, benutzen Sie entweder Ihr FTP-Programm oder geben Sie Ihre FTP-Daten hier ein, um die ben&ouml;tigten Dateirechte automatisch setzen zu lassen.');
          //Je nach Serverkonfiguration k&ouml;nnen Sie es auch direkt &uuml;ber PHP probieren, klicken Sie dazu bitte <a href="index.php?action=fixperms">hier</a>.<br />
          //Falls die Rechte danach immer noch nicht stimmen, hilft nur die Methode &uuml;ber FTP rechts.');
  define('LOGIN_NOT_POSSIBLE', 'FTP-Pfad fehlerhaft, Host nicht erreichbar');
  define('CHMOD_WAS_NOT_SUCCESSFUL', '&auml;ndern der Verzeichnisrechte war NICHT erfolgreich');
  define('FTP_HOST', 'FTP Host (automatisch erkannt)');
  define('FTP_PORT', 'FTP-Port (Standard: 21)');
  define('FTP_PATH', 'FTP-Pfad (meist /httpdocs oder /htdocs, evtl. Unterverzeichnis)');
  define('FTP_LOGIN', 'FTP-Benutzername');
  define('FTP_PASSWORD', 'FTP-Passwort');
  define('CONNECT_FTP', 'FTP verbinden');
  // EOC flth new permission change system