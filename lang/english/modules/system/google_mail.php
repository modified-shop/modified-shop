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
  define('MODULE_GOOGLE_MAIL_TEXT_DESCRIPTION', 'Sends outgoing e-mails via a Google/Gmail account using OAuth2 (XOAUTH2) instead of a plain SMTP password. Requires an OAuth Client ID and Client Secret from the <a href="https://console.cloud.google.com/" target="_blank"><b>Google Cloud Console</b></a> with the Gmail API enabled.');

  define('MODULE_GOOGLE_MAIL_STATUS_TITLE', 'Status');
  define('MODULE_GOOGLE_MAIL_STATUS_DESC', 'Module status');

  define('MODULE_GOOGLE_MAIL_CLIENT_ID_TITLE', 'OAuth Client ID');
  define('MODULE_GOOGLE_MAIL_CLIENT_ID_DESC', 'The OAuth 2.0 Client ID from your Google Cloud project.');

  define('MODULE_GOOGLE_MAIL_CLIENT_SECRET_TITLE', 'OAuth Client Secret');
  define('MODULE_GOOGLE_MAIL_CLIENT_SECRET_DESC', 'The OAuth 2.0 Client Secret from your Google Cloud project.');

  define('MODULE_GOOGLE_MAIL_SENDER_EMAIL_TITLE', 'Gmail address');
  define('MODULE_GOOGLE_MAIL_SENDER_EMAIL_DESC', 'The Gmail/Google Workspace mailbox that will be used to send e-mail. Must match the account used in the "Connect with Google" step.');

  define('MODULE_GOOGLE_MAIL_TEXT_CONNECTED_AS', 'Connected as <b>%s</b>.');
  define('MODULE_GOOGLE_MAIL_TEXT_NOT_CONNECTED', 'Not connected yet.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_BUTTON', 'Connect with Google');
  define('MODULE_GOOGLE_MAIL_TEXT_FROM_ADDRESS_HINT', 'Note: Gmail expects the "From" address of outgoing mails to match the connected mailbox (or a configured "Send As" alias of it).');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_SUCCESS', 'Successfully connected to Google.');
  define('MODULE_GOOGLE_MAIL_TEXT_CONNECT_ERROR', 'Connecting to Google failed. Please try again.');
  define('MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR', 'The OAuth authorization could not be verified (state mismatch). Please try connecting again.');
  define('MODULE_GOOGLE_MAIL_TEXT_MISSING_CREDENTIALS', 'Please enter and save the OAuth Client ID and Client Secret before connecting.');
