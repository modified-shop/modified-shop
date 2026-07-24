<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (defined('MODULE_OFFICE365_MAIL_STATUS') && MODULE_OFFICE365_MAIL_STATUS == 'true'
    && defined('MODULE_OFFICE365_MAIL_REFRESH_TOKEN') && MODULE_OFFICE365_MAIL_REFRESH_TOKEN != ''
    )
{
  require_once(DIR_FS_EXTERNAL . 'phpmailer/SMTP.php');
  require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/xoauth_smtp.php');
  require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/oauth_token_provider.php');

  $mail->isSMTP();
  $mail->setSMTPInstance(new xoauth_smtp());
  $mail->SMTPKeepAlive = true;
  $mail->Host = 'smtp.office365.com';
  $mail->Port = 587;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = true;
  $mail->AuthType = 'XOAUTH2';
  $mail->Username = MODULE_OFFICE365_MAIL_SENDER_EMAIL;
  $mail->SMTPDebug = (defined('SMTP_DEBUG') ? (int)SMTP_DEBUG : 0);
  $mail->SMTPOptions = array(
    'ssl' => array(
      'verify_peer' => true,
      'verify_peer_name' => true,
      'allow_self_signed' => false,
    ),
  );
  $mail->setOAuth(new oauth_token_provider(array(
    'token_endpoint' => 'https://login.microsoftonline.com/' .
      rawurlencode(MODULE_OFFICE365_MAIL_TENANT) .
      '/oauth2/v2.0/token',
    'client_id' => MODULE_OFFICE365_MAIL_CLIENT_ID,
    'client_secret' => MODULE_OFFICE365_MAIL_CLIENT_SECRET,
    'refresh_token' => MODULE_OFFICE365_MAIL_REFRESH_TOKEN,
    'user_email' => MODULE_OFFICE365_MAIL_SENDER_EMAIL,
    'scope' => 'https://outlook.office.com/SMTP.Send',
    'refresh_token_configuration_key' => 'MODULE_OFFICE365_MAIL_REFRESH_TOKEN',
    'oauth_error_configuration_key' => 'MODULE_OFFICE365_MAIL_OAUTH_ERROR',
    'oauth_error' => MODULE_OFFICE365_MAIL_OAUTH_ERROR,
  )));
}
