<?php

/* -----------------------------------------------------------------------------------------
   $Id: shopgate_xtc_php_mail.inc.php 1129 2005-08-05 11:46:11Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2003	 nextcommerce (shopgate_xtc_php_mail.inc.php,v 1.17 2003/08/24); www.nextcommerce.org


   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
// include the mail classes
function shopgate_php_mail($from_email_address, $from_email_name, $to_email_address, $to_name, $forwarding_to, $reply_address, $reply_address_name, $path_to_attachement, $path_to_more_attachements, $email_subject, $message_body_html, $message_body_plain) {
	global $mail_error;

//*********************************************************************************************
// Signatur f�r E-Mails
// by Dipl.-Ing. Daniel Wallas f�r www.tuvino.de
//*********************************************************************************************
$mailsmarty= new Smarty;
$mailsmarty->compile_dir = DIR_FS_DOCUMENT_ROOT.'templates_c';

//BOF - Dokuman - 20091030 - Check for existing signature files
//$html_signatur = $mailsmarty->fetch(DIR_FS_DOCUMENT_ROOT.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/signatur.html');
//$txt_signatur = $mailsmarty->fetch(DIR_FS_DOCUMENT_ROOT.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/signatur.txt');

// load the signatures only, if the appropriate file(s) exists
	$html_signatur = '';
	$txt_signatur = '';
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/signatur.html')) {
        $html_signatur = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/signatur.html');
  }
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/signatur.txt')) {
        $txt_signatur = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/signatur.txt');
  }
//EOF - Dokuman - 20091030 - Check for existing signature files

  //BOF - web28 - 2010-06-05 - Widerruf in Email
  $html_widerruf = '';
  $txt_widerruf = '';
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/widerruf.html')) {
        $html_widerruf = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/widerruf.html');
  }
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/widerruf.txt')) {
        $txt_widerruf = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/widerruf.txt');
  }
  
  //Platzhalter [WIDERRUF] durch Widerruf Text ersetzen
  if (strpos($message_body_html,'[WIDERRUF]') !== false) {
	$message_body_html = str_replace('[WIDERRUF]', $html_widerruf, $message_body_html);
  }
  if (strpos($message_body_plain,'[WIDERRUF]') !== false) {
	$message_body_plain = str_replace('[WIDERRUF]', $txt_widerruf, $message_body_plain);
  }
  
  //Platzhalter [SIGNATUR] durch Signatur Text ersetzen
  if (strpos($message_body_html,'[SIGNATUR]') !== false) {
	$message_body_html = str_replace('[SIGNATUR]', $html_signatur, $message_body_html);
	$html_signatur = '';
  }
  if (strpos($message_body_plain,'[SIGNATUR]') !== false) {
	$message_body_plain = str_replace('[SIGNATUR]', $txt_signatur, $message_body_plain);
	$txt_signatur = '';
  }
  //EOF - web28 - 2010-06-05 - Widerruf in Email

//**********************************************************************************************

	$mail = new PHPMailer();
	$mail->PluginDir = DIR_FS_DOCUMENT_ROOT.'includes/classes/';

	if (isset ($_SESSION['language_charset'])) {
		$mail->CharSet = $_SESSION['language_charset'];
		//BOF  - web28 - 2010-07-15 - needed for html_entity_decode
		$charset = $_SESSION['language_charset'];
		//EOF  - web28 - 2010-07-15 - needed for html_entity_decode
	} else {
		$lang_query = "SELECT * FROM ".TABLE_LANGUAGES." WHERE code = '".DEFAULT_LANGUAGE."'";
		$lang_query = xtc_db_query($lang_query);
		$lang_data = xtc_db_fetch_array($lang_query);
		$mail->CharSet = $lang_data['language_charset'];
		//BOF  - web28 - 2010-07-15 - needed for html_entity_decode
		$charset = $lang_data['language_charset'];
		//EOF  - web28 - 2010-07-15 - needed for html_entity_decode		
	}
	//BOF  - web28 - 2010-06-05 - SetLanguage Multilanguage
	/*
	if ($_SESSION['language'] == 'german') {
		$mail->SetLanguage("de", DIR_WS_CLASSES);
	} else {
		$mail->SetLanguage("en", DIR_WS_CLASSES);
	}
	*/
    if (isset (	$_SESSION['language_code'])) {		
		$lang_code = $_SESSION['language_code'];
	} else $lang_code = DEFAULT_LANGUAGE;	
	
	$mail->SetLanguage($lang_code, DIR_WS_CLASSES);	
	//EOF - web28 - 2010-06-05 - SetLanguage Multilanguage
	
	if (EMAIL_TRANSPORT == 'smtp') {
		$mail->IsSMTP();
		$mail->SMTPKeepAlive = true; // set mailer to use SMTP
		$mail->SMTPAuth = SMTP_AUTH; // turn on SMTP authentication true/false
		$mail->Username = SMTP_USERNAME; // SMTP username
		$mail->Password = SMTP_PASSWORD; // SMTP password
		$mail->Host = SMTP_MAIN_SERVER.';'.SMTP_Backup_Server; // specify main and backup server "smtp1.example.com;smtp2.example.com"
	}

	if (EMAIL_TRANSPORT == 'sendmail') { // set mailer to use SMTP
		$mail->IsSendmail();
		$mail->Sendmail = SENDMAIL_PATH;
	}
	if (EMAIL_TRANSPORT == 'mail') {
		$mail->IsMail();
	}

	if (EMAIL_USE_HTML == 'true') // set email format to HTML
		{
		$mail->IsHTML(true);
		$mail->Body = $message_body_html.$html_signatur;//DPW Signatur erg�nzt.
		// remove html tags
		$message_body_plain = str_replace('<br />', " \n", $message_body_plain.$txt_signatur);//DPW Signatur erg�nzt.
		$message_body_plain = strip_tags($message_body_plain);
		//BOF  - web28 - 2010-07-15 -  html_entity_decode
		$message_body_plain = html_entity_decode($message_body_plain, ENT_NOQUOTES, $charset);
		//EOF  - web28 - 2010-07-15 -  html_entity_decode
		$mail->AltBody = $message_body_plain;
	} else {
		$mail->IsHTML(false);
		//remove html tags
		$message_body_plain = str_replace('<br />', " \n", $message_body_plain.$txt_signatur);//DPW Signatur erg�nzt.
		$message_body_plain = strip_tags($message_body_plain);
		//BOF  - web28 - 2010-07-15 -  html_entity_decode
		$message_body_plain = html_entity_decode($message_body_plain, ENT_NOQUOTES, $charset);
		//EOF  - web28 - 2010-07-15 -  html_entity_decode
		$mail->Body = $message_body_plain;
	}

	$mail->From = $from_email_address;
	$mail->Sender = $from_email_address;
	$mail->FromName = $from_email_name;
	$mail->AddAddress($to_email_address, $to_name);
	if ($forwarding_to != '')
		$mail->AddBCC($forwarding_to);
	$mail->AddReplyTo($reply_address, $reply_address_name);

	$mail->WordWrap = 50; // set word wrap to 50 characters
	//$mail->AddAttachment($path_to_attachement);                     // add attachments
	//$mail->AddAttachment($path_to_more_attachements);               // optional name                                          

	$mail->Subject = $email_subject;

	if (!$mail->Send()) {
		return false;
		/*echo "Message was not sent <p>";
		echo "Mailer Error: ".$mail->ErrorInfo."</p>";
		exit;*/
	}
	return true;
}
?>
