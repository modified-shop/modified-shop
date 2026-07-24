<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_GOOGLE_MAIL_TEXT_TITLE', 'Google Mail (OAuth2)');
  define('MODULE_GOOGLE_MAIL_TEXT_DESCRIPTION', 'Versendet ausgehende E-Mails &uuml;ber ein Google/Gmail-Konto per OAuth2 (XOAUTH2) anstelle eines herk&ouml;mmlichen SMTP-Passworts. Ben&ouml;tigt eine OAuth Client-ID und ein Client-Secret aus der <a href="https://console.cloud.google.com/" target="_blank"><b>Google Cloud Console</b></a> mit aktivierter Gmail API.');

  define('MODULE_GOOGLE_MAIL_STATUS_TITLE', 'Status');
  define('MODULE_GOOGLE_MAIL_STATUS_DESC', 'Modulstatus');

  define('MODULE_GOOGLE_MAIL_CLIENT_ID_TITLE', 'OAuth Client-ID');
  define('MODULE_GOOGLE_MAIL_CLIENT_ID_DESC', 'Die OAuth 2.0 Client-ID aus Ihrem Google-Cloud-Projekt.');

  define('MODULE_GOOGLE_MAIL_CLIENT_SECRET_TITLE', 'OAuth Client-Secret');
  define('MODULE_GOOGLE_MAIL_CLIENT_SECRET_DESC', 'Das OAuth 2.0 Client-Secret aus Ihrem Google-Cloud-Projekt.');

  define('MODULE_GOOGLE_MAIL_SENDER_EMAIL_TITLE', 'Gmail-Adresse');
  define('MODULE_GOOGLE_MAIL_SENDER_EMAIL_DESC', 'Das Gmail-/Google-Workspace-Postfach, &uuml;ber das E-Mails versendet werden. Muss mit dem beim "Mit Google verbinden"-Schritt verwendeten Konto &uuml;bereinstimmen.');

  define('MODULE_GOOGLE_MAIL_TEXT_CONNECTED_AS', 'Verbunden als <b>%s</b>.');
  define('MODULE_GOOGLE_MAIL_TEXT_NOT_CONNECTED', 'Noch nicht verbunden.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_BUTTON', 'Mit Google verbinden');
  define('MODULE_GOOGLE_MAIL_TEXT_FROM_ADDRESS_HINT', 'Hinweis: Gmail erwartet, dass die Absenderadresse ausgehender Mails mit dem verbundenen Postfach &uuml;bereinstimmt (oder einem dort konfigurierten "Senden als"-Alias).');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_SUCCESS', 'Erfolgreich mit Google verbunden.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_ERROR', 'Die Verbindung zu Google ist fehlgeschlagen. Bitte erneut versuchen.');
  define('MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR', 'Die OAuth-Autorisierung konnte nicht verifiziert werden (state stimmt nicht &uuml;berein). Bitte erneut verbinden.');
  define('MODULE_GOOGLE_MAIL_TEXT_MISSING_CREDENTIALS', 'Bitte zuerst OAuth Client-ID und Client-Secret eintragen und speichern, bevor Sie sich verbinden.');
