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
  define('MODULE_GOOGLE_MAIL_STATUS_DESC', 'Modulstatus. Beim Aktivieren wird Microsoft 365 Mail (OAuth2) deaktiviert.');

  define('MODULE_GOOGLE_MAIL_CLIENT_ID_TITLE', 'OAuth Client-ID');
  define('MODULE_GOOGLE_MAIL_CLIENT_ID_DESC', 'Die OAuth 2.0 Client-ID aus Ihrem Google-Cloud-Projekt.');

  define('MODULE_GOOGLE_MAIL_CLIENT_SECRET_TITLE', 'OAuth Client-Secret');
  define('MODULE_GOOGLE_MAIL_CLIENT_SECRET_DESC', 'Das OAuth 2.0 Client-Secret aus Ihrem Google-Cloud-Projekt.');

  define('MODULE_GOOGLE_MAIL_SENDER_EMAIL_TITLE', 'Gmail-Adresse');
  define('MODULE_GOOGLE_MAIL_SENDER_EMAIL_DESC', 'Das Gmail-/Google-Workspace-Postfach, &uuml;ber das E-Mails versendet werden. Muss mit dem beim "Mit Google verbinden"-Schritt verwendeten Konto &uuml;bereinstimmen.');

  define('MODULE_GOOGLE_MAIL_TEXT_CONNECTED_AS', 'Verbunden als <b>%s</b>.');
  define('MODULE_GOOGLE_MAIL_TEXT_NOT_CONNECTED', 'Noch nicht verbunden.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECTION_ERROR', 'OAuth-Verbindungsfehler: <b>%s</b>. Bitte erneut verbinden.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_BUTTON', 'Mit Google verbinden');
  define('MODULE_GOOGLE_MAIL_TEXT_FROM_ADDRESS_HINT', 'Hinweis: Gmail erwartet, dass die Absenderadresse ausgehender Mails mit dem verbundenen Postfach &uuml;bereinstimmt (oder einem dort konfigurierten "Senden als"-Alias).');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_SUCCESS', 'Erfolgreich mit Google verbunden.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_ERROR', 'Die Verbindung zu Google ist fehlgeschlagen. Bitte erneut versuchen.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_CANCELLED', 'Die Verbindung mit Google wurde abgebrochen.');
  define('MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR', 'Die OAuth-Autorisierung konnte nicht verifiziert werden (state stimmt nicht &uuml;berein). Bitte erneut verbinden.');
  define('MODULE_GOOGLE_MAIL_TEXT_ACCOUNT_MISMATCH', 'Das bei Google ausgew&auml;hlte Konto stimmt nicht mit der eingetragenen Gmail-Adresse &uuml;berein.');
  define('MODULE_GOOGLE_MAIL_TEXT_SCOPE_MISSING', 'Die erforderliche Gmail-Berechtigung wurde nicht erteilt. Bitte erneut verbinden und den Zugriff auf Gmail ausdr&uuml;cklich zulassen.');
  define('MODULE_GOOGLE_MAIL_TEXT_MISSING_CREDENTIALS', 'Bitte zuerst OAuth Client-ID, Client-Secret und Gmail-Adresse vollst&auml;ndig eintragen und speichern.');

  define('MODULE_GOOGLE_MAIL_TEXT_SETUP_GUIDE', 'So erstellen Sie die ben&ouml;tigte OAuth Client-ID/Secret:<br>
<ol>
<li>In der <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> ein Projekt anlegen oder ausw&auml;hlen, dann die <b>Gmail API</b> aktivieren (APIs &amp; Services &rarr; Library).</li>
<li>Unter <b>APIs &amp; Services &rarr; OAuth consent screen</b>: <b>Internal</b> w&auml;hlen bei einem Google-Workspace-Konto (keine Verifizierung n&ouml;tig), oder <b>External</b> bei einem privaten @gmail.com-Konto. Als Scope <code>https://mail.google.com/</code> hinzuf&uuml;gen. Bei External + Status "Testing" die Gmail-Adresse als Testnutzer eintragen - Achtung: Refresh-Tokens im Testing-Status laufen nach 7 Tagen ab. Der f&uuml;r SMTP erforderliche Scope ist eingeschr&auml;nkt; Google kann f&uuml;r eine &ouml;ffentliche External-App eine Pr&uuml;fung verlangen und f&uuml;r reine Versand-Apps stattdessen die Gmail API mit <code>gmail.send</code> voraussetzen. F&uuml;r den internen Betrieb empfiehlt sich ein Workspace-/Internal-Konto.</li>
<li>Unter <b>APIs &amp; Services &rarr; Credentials &rarr; Create Credentials &rarr; OAuth client ID</b>: Anwendungstyp <b>Web application</b>, und genau diese Authorized redirect URI eintragen:<br><code>%s</code></li>
<li>Die erhaltene Client-ID und das Client-Secret oben eintragen, speichern, dann auf "Mit Google verbinden" klicken.</li>
</ol>');
