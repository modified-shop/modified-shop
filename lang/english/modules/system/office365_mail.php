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
  define('MODULE_OFFICE365_MAIL_TEXT_DESCRIPTION', 'Sends outgoing e-mail through Exchange Online using OAuth2 (XOAUTH2). Requires an app registration in the <a href="https://entra.microsoft.com/" target="_blank"><b>Microsoft Entra admin center</b></a>. An active and connected module selects SMTP transport automatically.');

  define('MODULE_OFFICE365_MAIL_STATUS_TITLE', 'Status');
  define('MODULE_OFFICE365_MAIL_STATUS_DESC', 'Module status. Enabling this module disables Google Mail (OAuth2).');

  define('MODULE_OFFICE365_MAIL_TENANT_TITLE', 'Tenant');
  define('MODULE_OFFICE365_MAIL_TENANT_DESC', 'Directory (tenant) ID or verified tenant domain. Multi-tenant app registrations can use <code>organizations</code>; enter the actual tenant ID for single-tenant apps.');

  define('MODULE_OFFICE365_MAIL_CLIENT_ID_TITLE', 'Application (client) ID');
  define('MODULE_OFFICE365_MAIL_CLIENT_ID_DESC', 'The Application (client) ID of the Microsoft Entra app registration.');

  define('MODULE_OFFICE365_MAIL_CLIENT_SECRET_TITLE', 'Client secret');
  define('MODULE_OFFICE365_MAIL_CLIENT_SECRET_DESC', 'The client secret <b>value</b>, not its Secret ID.');

  define('MODULE_OFFICE365_MAIL_SENDER_EMAIL_TITLE', 'Mailbox address');
  define('MODULE_OFFICE365_MAIL_SENDER_EMAIL_DESC', 'The Exchange Online mailbox used for XOAUTH2. For a shared mailbox, enter the shared mailbox address.');

  define('MODULE_OFFICE365_MAIL_TEXT_CONNECTED_AS', 'Connected for <b>%s</b>.');
  define('MODULE_OFFICE365_MAIL_TEXT_NOT_CONNECTED', 'Not connected yet.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_BUTTON', 'Connect with Microsoft');
  define('MODULE_OFFICE365_MAIL_TEXT_FROM_ADDRESS_HINT', 'The From address must match the mailbox or an address for which it has Send As permission. For shared mailboxes, sign in as an authorized user but keep the shared mailbox address configured here.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_SUCCESS', 'Successfully connected to Microsoft 365.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_ERROR', 'Connecting to Microsoft 365 failed. Check the tenant, client ID, client secret, redirect URI and permissions.');
  define('MODULE_OFFICE365_MAIL_TEXT_CONNECT_CANCELLED', 'Connecting to Microsoft was cancelled.');
  define('MODULE_OFFICE365_MAIL_TEXT_OAUTH_STATE_ERROR', 'The OAuth authorization could not be verified. Please try connecting again.');
  define('MODULE_OFFICE365_MAIL_TEXT_SCOPE_MISSING', 'The required SMTP.Send permission was not granted. Check the permissions and reconnect.');
  define('MODULE_OFFICE365_MAIL_TEXT_MISSING_CREDENTIALS', 'Please enter the tenant, client ID, client secret and mailbox address.');
  define('MODULE_OFFICE365_MAIL_TEXT_INVALID_TENANT', 'The configured Microsoft tenant is invalid.');

  define('MODULE_OFFICE365_MAIL_TEXT_SETUP_GUIDE', 'Microsoft Entra setup:<br>
<ol>
<li>In the <a href="https://entra.microsoft.com/" target="_blank">Microsoft Entra admin center</a>, register an app under <b>Identity &rarr; Applications &rarr; App registrations</b>. Select the appropriate account type.</li>
<li>Under <b>Authentication &rarr; Add a platform &rarr; Web</b>, add this exact redirect URI:<br><code>%s</code></li>
<li>Under <b>Certificates &amp; secrets</b>, create a client secret and immediately copy its <b>Value</b>.</li>
<li>Under <b>API permissions &rarr; Add a permission &rarr; APIs my organization uses &rarr; Office 365 Exchange Online &rarr; Delegated permissions</b>, add <code>SMTP.Send</code>. Grant admin consent if required by the tenant policy.</li>
<li><b>Authenticated SMTP</b> must be enabled for the Exchange Online mailbox. Microsoft recommends enabling SMTP AUTH only for mailboxes that require it.</li>
<li>Save the values above, then click "Connect with Microsoft".</li>
</ol>');
