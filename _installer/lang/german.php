<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  // buttons
  define('BUTTON_BACK', 'Zur&uuml;ck');
  define('BUTTON_SUBMIT', 'Best&auml;tigen');
  define('BUTTON_INSTALL', 'Neu installieren');
  define('BUTTON_UPDATE', 'Update');
  define('BUTTON_SHOP', 'Zum Shop');

  define('BUTTON_CONFIGURE', 'ausf&uuml;hren <i class="fa fa-caret-right"></i>');
  define('BUTTON_DB_UPDATE', 'ausf&uuml;hren <i class="fa fa-caret-right"></i>');
  define('BUTTON_SQL_UPDATE', 'ausf&uuml;hren <i class="fa fa-caret-right"></i>');
  define('BUTTON_SQL_MANUELL', 'ausf&uuml;hren <i class="fa fa-caret-right"></i>');
  define('BUTTON_DB_BACKUP', 'ausf&uuml;hren <i class="fa fa-caret-right"></i>');
  define('BUTTON_DB_RESTORE', 'ausf&uuml;hren <i class="fa fa-caret-right"></i>');
  define('BUTTON_PAYMENT_INSTALL', 'installieren <i class="fa fa-caret-right"></i>');
  
  // text
  define('TEXT_SQL_SUCCESS', '%s');
  define('TEXT_INFO_DONATIONS_IMG_ALT','Unterst&uuml;tzen Sie dieses Projekt mit Ihrer Spende');
  define('BUTTON_DONATE','<a href="https://www.modified-shop.org/spenden" target="_blank"><img src="https://www.paypal.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" alt="' . TEXT_INFO_DONATIONS_IMG_ALT . '" border="0" /></a>');
  define('TEXT_START', '<b>Willkommen zur modified eCommerce Shopsoftware Installation</b><br /><br />Die modified eCommerce Shopsoftware ist eine Open-Source e-commerce L&ouml;sung, die st&auml;ndig vom modified eCommerce Shopsoftware Team und einer grossen Gemeinschaft weiterentwickelt wird.<br /> Seine out-of-the-box Installation erlaubt es dem Shop-Besitzer seinen Online-Shop mit einem Minimum an Aufwand und Kosten zu installieren, zu betreiben und zu verwalten.<br /><br />Die modified eCommerce Shopsoftware ist auf jedem System lauff&auml;hig, welches eine PHP Umgebung (ab PHP '.PHP_VERSION_MIN.') und MySQL (ab MySQL 5.0.0) zur Verf&uuml;gung stellt, wie zum Beispiel Linux, Solaris, BSD, und Microsoft Windows.<br /><br />Die modified eCommerce Shopsoftware ist ein OpenSource-Projekt &ndash; wir stecken jede Menge Arbeit und Freizeit in dieses Projekt und w&uuml;rden uns daher &uuml;ber eine <b>Spende</b> als kleine Anerkennung freuen.<br /><br />' . BUTTON_DONATE);
  define('TEXT_UPDATER_HEADING', 'Bitte ausw&auml;hlen');
  define('TEXT_UPDATER', 'Willkommen beim neuen Updater der modified eCommerce Shopsoftware.');
  define('TEXT_UPDATE_CONFIG', 'Konfigurations-Datei (configure.php) aktualisieren');
  
  define('TEXT_CONFIGURE', 'Konfigurations-Datei (configure.php) aktualisieren');
  define('TEXT_CONFIGURE_DESC', 'Hier k&ouml;nnen Sie die configure.php Datei aktualisieren um sicher zu gehen, dass sie dem aktuelle Stand entspricht.');
  define('TEXT_CONFIGURE_SUCCESS', 'configure.php geschrieben!');
  
  define('TEXT_SQL_UPDATE', 'Datenbank Update');
  define('TEXT_SQL_UPDATE_HEADING', 'SQL Update ausw&auml;hlen');
  define('TEXT_SQL_UPDATE_DESC', 'Bitte w&auml;hlen Sie hier nur die Update-Dateien aus, die f&uuml;r Ihre derzeitige Shopversion notwendig sind.');
  define('TEXT_EXECUTED_SUCCESS', '<b>Erfolgreich ausgef&uuml;hrt:</b>');
  define('TEXT_EXECUTED_ERROR', '<b>Mit Fehlern ausgef&uuml;hrt:</b>');
  
  define('TEXT_SQL_MANUELL', 'Manuelle SQL-Eingabe');
  define('TEXT_SQL_MANUELL_HEADING', 'SQL Befehl eingeben:');
  define('TEXT_SQL_MANUELL_DESC', 'SQL-Befehle m&uuml;ssen mit einem Semikolon ( ; ) abgeschlossen werden!');

  define('TEXT_DB_RESTORE', 'Datenbank Wiederherstellung');
  define('TEXT_DB_RESTORE_DESC', 'Sie k&ouml;nnen hier Ihre Datenbank aus einem vorhandenen Backup wiederherstellen.');
  define('TEXT_INFO_DO_RESTORE', 'Die Datenbank wird wiederhergestellt!');
  define('TEXT_INFO_DO_RESTORE_OK', 'Die Datenbank wurde erfolgreich wiederhergestellt!');
  
  define('TEXT_DB_BACKUP', 'Datenbank-Backup');
  define('TEXT_DB_BACKUP_DESC', 'Sie k&ouml;nnen hier Ihre Datenbank sichern.');
  define('TEXT_DB_COMPRESS', 'Backup komprimieren');
  define('TEXT_DB_REMOVE_COLLATE', 'Ohne Zeichenkodierung \'COLLATE\' und \'DEFAULT CHARSET\'');
  define('TEXT_DB_REMOVE_ENGINE', 'Ohne Speicherengines \'ENGINE\'');
  define('TEXT_DB_COMPLETE_INSERTS', 'Vollst&auml;ndige \'INSERT\'s');
  define('TEXT_DB_UFT8_CONVERT', 'Datenbank auf UTF-8 konvertieren');
  define('TEXT_DB_COMPRESS_GZIP', 'Mit GZIP');
  define('TEXT_DB_COMPRESS_RAW', 'Keine Komprimierung (Raw SQL)');
  define('TEXT_DB_SIZE', 'Gr&ouml;&szlig;e');
  define('TEXT_DB_DATE', 'Datum');
  
  define('TEXT_INFO_DO_UPDATE_OK', 'Die Datenbank wurde erfolgreich aktualisiert!');
  define('TEXT_INFO_DO_UPDATE', 'Die Datenbank wird aktualisiert!');

  define('TEXT_INFO_DO_BACKUP_OK', 'Die Datenbank wurde erfolgreich gesichert!');
  define('TEXT_INFO_DO_BACKUP', 'Die Datenbank wird gesichert!');
  define('TEXT_INFO_WAIT', 'Bitte warten!');
  define('TEXT_INFO_UPDATE', 'Tabellen aktualisiert: ');
  define('TEXT_INFO_RESTORE', 'Tabellen wiederhergestellt: ');
  define('TEXT_INFO_BACKUP', 'Tabellen gesichert: ');
  define('TEXT_INFO_LAST', 'Zuletzt bearbeitet: ');
  define('TEXT_INFO_CALLS', 'Seitenaufrufe: ');
  define('TEXT_INFO_TIME', 'Scriptlaufzeit: ');
  define('TEXT_INFO_FROM', ' von ');
  define('TEXT_INFO_MAX_RELOADS', 'Maximale Seitenreloads wurden erreicht: ');
  
  define('TEXT_DB_UPDATE', 'Datenbankstruktur Update');
  define('TEXT_DB_UPDATE_DESC', 'Hier k&ouml;nnen Sie die Datenbank Ihrer Shopinstallation auf den aktuellen Stand bringen.');
  define('TEXT_DB_UPDATE_FINISHED', 'DB Update erfolgreich abgesclossen!');
  define('TEXT_FROM', ' von ');
  //define('TEXT_DB_UPDATE_BEFORE', 'Text davor'); // Not used yet
  //define('TEXT_DB_UPDATE_AFTER', 'Text danach'); // Not used yet

  define('TEXT_DB_HEADING', 'Angaben zur Datenbank:');
  define('TEXT_DB_SERVER', 'Server:');
  define('TEXT_DB_USERNAME', 'Benutzername:');
  define('TEXT_DB_PASSWORD', 'Passwort:');
  define('TEXT_DB_DATABASE', 'Datenbank:');
  define('TEXT_DB_MYSQL_TYPE', 'Typ:');
  define('TEXT_DB_CHARSET', 'Zeichensatz:');
  define('TEXT_DB_PCONNECT', 'Persistent:');
  define('TEXT_DB_EXISTS', 'Datenbank existiert bereits');
  define('TEXT_DB_EXISTS_DESC', 'Wenn Sie "Best&auml;tigen" klicken werden alle Tabellen dieser Datenbank &uuml;berschrieben! Wenn Sie dies nicht m&ouml;chten, dann klicken Sie auf "Zur&uuml;ck" und geben eine andere Datenbank an. Andersfalls klicken Sie auf "Best&auml;tigen".');
  
  define('TEXT_SERVER_HEADING', 'Angaben zum Shop:');
  define('TEXT_SERVER_HTTP_SERVER', 'HTTP:');
  define('TEXT_SERVER_HTTPS_SERVER', 'HTTPS:');
  define('TEXT_SERVER_USE_SSL', 'SSL:');
  define('TEXT_SERVER_SESSION', 'Session:');

  define('TEXT_ACCOUNT','Der Installer richtet den Admin-Account ein und schreibt noch diverse Daten in die Datenbank.<br />Die angegebenen Daten f&uuml;r <b>Land</b> und <b>PLZ</b> werden f&uuml;r die Versand- und Steuerberechnungen genutzt.');
  define('TEXT_ACCOUNT_HEADING', 'Angaben zum Account:');
  define('TEXT_ACCOUNT_FIRSTNAME', 'Vorname:');
  define('TEXT_ACCOUNT_LASTNAME', 'Nachname:');
  define('TEXT_ACCOUNT_COMPANY', 'Firma:');
  define('TEXT_ACCOUNT_STREET', 'Stra&szlig;e/Nr.:');
  define('TEXT_ACCOUNT_CODE', 'PLZ:');
  define('TEXT_ACCOUNT_CITY', 'Stadt:');
  define('TEXT_ACCOUNT_COUNTRY', 'Land:');
  define('TEXT_ACCOUNT_EMAIL', 'E-Mail:');
  define('TEXT_ACCOUNT_CONFIRM_EMAIL', 'E-Mail best&auml;tigen:');
  define('TEXT_ACCOUNT_PASSWORD', 'Passwort:');
  define('TEXT_ACCOUNT_CONFIRMATION', 'Passwort best&auml;tigen:');
  
  define('TEXT_FINISHED', 'Hier k&ouml;nnen Sie bereits die beliebten Zahlungsweisen von PayPal installieren.');
  define('TEXT_MODULES_INSTALLED', 'Installiert:');
  define('TEXT_MODULES_UNINSTALLED', 'Nicht installiert:');
  define('TEXT_INFO_DO_INSTALL', 'Die Datenbank wird installiert.');
  
  define('TEXT_ERROR_PERMISSION_FILES', 'Die folgenden Dateien ben&ouml;tigen Schreibrechte (CHMOD 777):');
  define('TEXT_ERROR_PERMISSION_FOLDER', 'Die folgenden Ordner ben&ouml;tigen Schreibrechte (CHMOD 777):');
  define('TEXT_ERROR_PERMISSION_RFOLDER', 'Folgende Ordner inklusive aller Dateien und Unterordner ben&ouml;tigen rekursive Schreibrechte (CHMOD 777):');
  define('TEXT_ERROR_REQUIREMENTS', 'Voraussetzungen');
  define('TEXT_ERROR_REQUIREMENTS_NAME', 'Name');
  define('TEXT_ERROR_REQUIREMENTS_VERSION', 'Version');
  define('TEXT_ERROR_REQUIREMENTS_MIN', 'Min');
  define('TEXT_ERROR_REQUIREMENTS_MAX', 'Max');
  define('TEXT_ERROR_FTP', 'Rechte per FTP &auml;ndern:');
  define('TEXT_ERROR_FTP_HOST', 'FTP Host:');
  define('TEXT_ERROR_FTP_PORT', 'FTP Port:');
  define('TEXT_ERROR_FTP_PATH', 'FTP Pfad:');
  define('TEXT_ERROR_FTP_USER', 'FTP Benutzer:');
  define('TEXT_ERROR_FTP_PASS', 'FTP Passwort:');
  define('TEXT_ERROR_UNLINK_FILES', 'Folgende Dateien m&uuml;ssen gel&ouml;scht werden:');
  define('TEXT_ERROR_UNLINK_FOLDER', 'Folgende Ordner m&uuml;ssen gel&ouml;scht werden:');
  
  // errors
  define('ERROR_DATABASE_CONNECTION', 'Bitte DB Daten pr&uuml;fen');
  define('ERROR_DATABASE_NOT_EMPTY', 'ACHTUNG: Die angegebene Datenbank enth&auml;lt bereits Tabellen!');
  define('ERROR_MODULES_PAYMENT', 'Leider konnten wir diese Zahlart nicht finden...');
  define('ERROR_SQL_UPDATE_NO_FILE', 'Leider konnten wir keine SQL-Update-Datei finden...');
  define('ERROR_FTP_LOGIN_NOT_POSSIBLE', 'FTP-Zugangsdaten fehlerhaft, Host nicht erreichbar');
  define('ERROR_FTP_CHMOD_WAS_NOT_SUCCESSFUL', '&Auml;ndern der Verzeichnisrechte war nicht erfolgreich');

  define('ENTRY_FIRST_NAME_ERROR', 'Ihr Vorname muss aus mindestens ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_LAST_NAME_ERROR', 'Ihr Nachname muss aus mindestens ' . ENTRY_LAST_NAME_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_EMAIL_ADDRESS_ERROR', 'Ihre E-Mail-Adresse muss aus mindestens ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Ihre eingegebene E-Mail-Adresse ist fehlerhaft oder bereits registriert.');
  define('ENTRY_EMAIL_ERROR_NOT_MATCHING', 'Ihre E-Mail-Adressen stimmen nicht &uuml;berein.');
  define('ENTRY_STREET_ADDRESS_ERROR', 'Stra&szlig;e/Nr. muss aus mindestens ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_POST_CODE_ERROR', 'Ihre Postleitzahl muss aus mindestens ' . ENTRY_POSTCODE_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_CITY_ERROR', 'Ort muss aus mindestens ' . ENTRY_CITY_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_PASSWORD_ERROR', 'Ihr Passwort muss aus mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen bestehen.');
  define('ENTRY_PASSWORD_ERROR_MIN_LOWER', 'Ihr Passwort muss mindestens %s Kleinbuchstaben enthalten.');
  define('ENTRY_PASSWORD_ERROR_MIN_UPPER', 'Ihr Passwort muss mindestens %s Grossbuchstaben enthalten.');
  define('ENTRY_PASSWORD_ERROR_MIN_NUM', 'Ihr Passwort muss mindestens %s Zahl enthalten.');
  define('ENTRY_PASSWORD_ERROR_MIN_CHAR', 'Ihr Passwort muss mindestens %s Sonderzeichen enthalten.');
  define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'Ihre Passw&ouml;rter stimmen nicht &uuml;berein.');
  define('ENTRY_PASSWORD_CURRENT_ERROR', 'Ihr Passwort muss aus mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen bestehen.');
  
?>