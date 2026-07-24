<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Google Mail (OAuth2/XOAUTH2) system module - overrides the generic SMTP_*
// configuration on $mail when connected. See admin/includes/modules/system/google_mail.php
if (EMAIL_TRANSPORT == 'smtp'
    && defined('MODULE_GOOGLE_MAIL_STATUS') && MODULE_GOOGLE_MAIL_STATUS == 'true'
    && defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN') && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != ''
    )
{
  require_once (DIR_FS_EXTERNAL.'phpmailer/classes/oauth_token_provider.php');

  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 587;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = true;
  $mail->AuthType = 'XOAUTH2';
  $mail->Username = MODULE_GOOGLE_MAIL_SENDER_EMAIL;
  $mail->oauth = new oauth_token_provider(array(
    'token_endpoint' => 'https://oauth2.googleapis.com/token',
    'client_id' => MODULE_GOOGLE_MAIL_CLIENT_ID,
    'client_secret' => MODULE_GOOGLE_MAIL_CLIENT_SECRET,
    'refresh_token' => MODULE_GOOGLE_MAIL_REFRESH_TOKEN,
    'user_email' => MODULE_GOOGLE_MAIL_SENDER_EMAIL,
  ));
}
