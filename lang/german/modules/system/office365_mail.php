<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_OFFICE365_MAIL_TEXT_TITLE', 'Microsoft 365 Mail (OAuth2)');
  define('MODULE_OFFICE365_MAIL_TEXT_DESCRIPTION', 'Versendet ausgehende E-Mails &uuml;ber Exchange Online per OAuth2 (XOAUTH2). Ben&ouml;tigt eine App-Registrierung im <a href="https://entra.microsoft.com/" target="_blank"><b>Microsoft Entra Admin Center</b></a>. Bei aktivem und verbundenem Modul wird der SMTP-Transport automatisch gesetzt.');

  define('MODULE_OFFICE365_MAIL_STATUS_TITLE', 'Status');
  define('MODULE_OFFICE365_MAIL_STATUS_DESC', 'Modulstatus. Beim Aktivieren wird Google Mail (OAuth2) deaktiviert.');

  define('MODULE_OFFICE365_MAIL_TENANT_TITLE', 'Mandant');
  define('MODULE_OFFICE365_MAIL_TENANT_DESC', 'Directory (Tenant) ID oder verifizierte Mandantendom&auml;ne. F&uuml;r mandanten&uuml;bergreifende App-Registrierungen kann <code>organizations</code> verwendet werden; f&uuml;r Single-Tenant-Apps die konkrete Tenant-ID eintragen.');

  define('MODULE_OFFICE365_MAIL_CLIENT_ID_TITLE', 'Anwendungs-ID (Client-ID)');
  define('MODULE_OFFICE365_MAIL_CLIENT_ID_DESC', 'Die Application (client) ID der Microsoft-Entra-App-Registrierung.');

  define('MODULE_OFFICE365_MAIL_CLIENT_SECRET_TITLE', 'Client-Secret');
  define('MODULE_OFFICE365_MAIL_CLIENT_SECRET_DESC', 'Der <b>Wert</b> des Client-Secrets, nicht dessen Secret-ID.');

  define('MODULE_OFFICE365_MAIL_SENDER_EMAIL_TITLE', 'Postfachadresse');
  define('MODULE_OFFICE365_MAIL_SENDER_EMAIL_DESC', 'Das Exchange-Online-Postfach, das f&uuml;r XOAUTH2 verwendet wird. Bei einem freigegebenen Postfach hier dessen Adresse eintragen.');

  define('MODULE_OFFICE365_MAIL_TEXT_CONNECTED_AS', 'Verbunden f&uuml;r <b>%s</b>.');
  define('MODULE_OFFICE365_MAIL_TEXT_NOT_CONNECTED', 'Noch nicht verbunden.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_BUTTON', 'Mit Microsoft verbinden');
  define('MODULE_OFFICE365_MAIL_TEXT_FROM_ADDRESS_HINT', 'Die Absenderadresse muss dem Postfach oder einer dort erlaubten "Senden als"-Adresse entsprechen. Bei freigegebenen Postf&auml;chern melden Sie sich mit einem berechtigten Benutzer an, lassen hier aber die Adresse des freigegebenen Postfachs stehen.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_SUCCESS', 'Erfolgreich mit Microsoft 365 verbunden.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_ERROR', 'Die Verbindung zu Microsoft 365 ist fehlgeschlagen. Pr&uuml;fen Sie Mandant, Client-ID, Client-Secret, Redirect-URI und Berechtigungen.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_CANCELLED', 'Die Verbindung mit Microsoft wurde abgebrochen.');
  define('MODULE_OFFICE365_MAIL_TEXT_OAUTH_STATE_ERROR', 'Die OAuth-Autorisierung konnte nicht verifiziert werden. Bitte erneut verbinden.');
  define('MODULE_OFFICE365_MAIL_TEXT_SCOPE_MISSING', 'Die erforderliche SMTP.Send-Berechtigung wurde nicht erteilt. Bitte Berechtigungen pr&uuml;fen und erneut verbinden.');
  define('MODULE_OFFICE365_MAIL_TEXT_MISSING_CREDENTIALS', 'Bitte Mandant, Client-ID, Client-Secret und Postfachadresse vollst&auml;ndig eintragen.');
  define('MODULE_OFFICE365_MAIL_TEXT_INVALID_TENANT', 'Der eingetragene Microsoft-Mandant ist ung&uuml;ltig.');

  define('MODULE_OFFICE365_MAIL_TEXT_SETUP_GUIDE', 'Einrichtung in Microsoft Entra:<br>
<ol>
<li>Im <a href="https://entra.microsoft.com/" target="_blank">Microsoft Entra Admin Center</a> unter <b>Identity &rarr; Applications &rarr; App registrations</b> eine App registrieren. Den passenden Kontotyp ausw&auml;hlen.</li>
<li>Unter <b>Authentication &rarr; Add a platform &rarr; Web</b> genau diese Redirect-URI eintragen:<br><code>%s</code></li>
<li>Unter <b>Certificates &amp; secrets</b> ein Client-Secret anlegen und dessen <b>Value</b> sofort kopieren.</li>
<li>Unter <b>API permissions &rarr; Add a permission &rarr; APIs my organization uses &rarr; Office 365 Exchange Online &rarr; Delegated permissions</b> die Berechtigung <code>SMTP.Send</code> hinzuf&uuml;gen. Falls die Mandantenrichtlinie es verlangt, Administratorzustimmung erteilen.</li>
<li>F&uuml;r das verwendete Exchange-Online-Postfach muss <b>Authenticated SMTP</b> aktiviert sein. Microsoft empfiehlt, SMTP AUTH nur f&uuml;r die ben&ouml;tigten Postf&auml;cher freizuschalten.</li>
<li>Die Daten oben speichern und anschlie&szlig;end auf "Mit Microsoft verbinden" klicken.</li>
</ol>');
