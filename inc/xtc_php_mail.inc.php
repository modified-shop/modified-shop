<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_php_mail.inc.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (xtc_php_mail.inc.php,v 1.17 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_php_mail.inc.php)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include the mail classes
function xtc_php_mail($from_email_address, $from_email_name,
                      $to_email_address, $to_name, $forwarding_to,
                      $reply_address, $reply_address_name,
                      $path_to_attachments, $path_to_more_attachments,
                      $email_subject, $message_body_html, $message_body_plain
                     )
{
  global $order, $main;

  // include needed function
  require_once(DIR_FS_INC.'xtc_not_null.inc.php');
  require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');
  
  // includes main class
  if (!is_object($main)) {
    require_once(DIR_FS_CATALOG.'includes/classes/main.php');
    $main = new main();
  }

  if (!class_exists('Smarty')) {
    require (DIR_FS_EXTERNAL.'smarty/smarty_2/Smarty.class.php');
  }
  $mailsmarty= new Smarty;
  $mailsmarty->compile_dir = DIR_FS_CATALOG.'templates_c';
  
  //set language parameters
  $lang_data = array();
  $lang_data['directory'] = isset($_SESSION['language']) ? $_SESSION['language'] : '';
  $lang_data['language_charset'] = isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : '';
  $lang_data['code'] = isset($_SESSION['language_code']) ? $_SESSION['language_code'] : '';
  $lang_data['languages_id'] = isset($_SESSION['languages_id']) ? $_SESSION['languages_id'] : '';
  $where= '';
  if (empty($lang_data['directory']) || empty($lang_data['language_charset']) || empty($lang_data['code'])) {
     $where = " WHERE code = '".DEFAULT_LANGUAGE."'";
  }
  if (isset($order) && is_object($order)) {
    $where = " WHERE directory = '".$order->info['language']."'";
    $customers_status = $order->info['status'];
  }

  if ($where) {
    $lang_query = xtc_db_query("SELECT * 
                                  FROM ".TABLE_LANGUAGES." 
                                  ".$where."
                               ");
    $lang_data = xtc_db_fetch_array($lang_query);
  }
  
  // set parameters
  $from_email_address = parse_multi_language_value($from_email_address, $lang_data['code']);
  $from_email_name = parse_multi_language_value($from_email_name, $lang_data['code']);
  $to_email_address = parse_multi_language_value($to_email_address, $lang_data['code']);
  $to_name = parse_multi_language_value($to_name, $lang_data['code']);
  $forwarding_to = parse_multi_language_value($forwarding_to, $lang_data['code']);
  $reply_address = parse_multi_language_value($reply_address, $lang_data['code']);
  $reply_address_name = parse_multi_language_value($reply_address_name, $lang_data['code']);
  $path_to_attachments = parse_multi_language_value($path_to_attachments, $lang_data['code']);
  $email_subject = parse_multi_language_value($email_subject, $lang_data['code']);
      
  // load the signatures only, if the appropriate file(s) exists
  $html_signatur = '';
  $txt_signatur = '';
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/signatur.html')) {
    $shop_content_data = $main->getContentData(EMAIL_SIGNATURE_ID, $lang_data['languages_id'], ((isset($customers_status)) ? $customers_status : DEFAULT_CUSTOMERS_STATUS_ID_GUEST));    
    $mailsmarty->assign('SIGNATURE_HTML', $shop_content_data['content_text']);
    $html_signatur = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/signatur.html'); 
  }
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/signatur.txt')) {
    $shop_content_data = $main->getContentData(EMAIL_SIGNATURE_ID, $lang_data['languages_id'], ((isset($customers_status)) ? $customers_status : DEFAULT_CUSTOMERS_STATUS_ID_GUEST));
    $mailsmarty->assign('SIGNATURE_TXT', $shop_content_data['content_text']);
    $txt_signatur = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/signatur.txt'); 
  }

  $html_widerruf = '';
  $txt_widerruf = '';
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/widerruf.html')) {
    $html_widerruf = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/widerruf.html'); 
  }
  if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/widerruf.txt')) {
    $txt_widerruf = $mailsmarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$lang_data['directory'].'/widerruf.txt'); 
  }  

  //Platzhalter [WIDERRUF] durch Widerruf Text ersetzen
  if (strpos($message_body_html,'[WIDERRUF]') !== false) {
    $message_body_html = str_replace('[WIDERRUF]', $html_widerruf, $message_body_html);
  } elseif ($html_widerruf != '') {
    $html_widerruf = '<br />'.$html_widerruf;
  }
  if (strpos($message_body_plain,'[WIDERRUF]') !== false) {
    $message_body_plain = str_replace('[WIDERRUF]', $txt_widerruf, $message_body_plain);
  } elseif ($txt_widerruf != '') {
    $txt_widerruf = "\n".$txt_widerruf;  
  }

  //Platzhalter [NOSIGNATUR] falls keine Signatir notwendig (zB Newsletter)
  if (strpos($message_body_html,'[NOSIGNATUR]') !== false) {
    $message_body_html = str_replace('[NOSIGNATUR]', '', $message_body_html);
    $message_body_plain = str_replace('[NOSIGNATUR]', '', $message_body_plain);
    $html_signatur = '';
    $txt_signatur = '';
  }

  //Platzhalter [SIGNATUR] durch Signatur Text ersetzen
  if (strpos($message_body_html,'[SIGNATUR]') !== false) {
    $message_body_html = str_replace('[SIGNATUR]', $html_signatur, $message_body_html);
    $html_signatur = '';
  } elseif ($html_signatur != '') {
    $html_signatur = '<br />'.$html_signatur;
  }
  if (strpos($message_body_plain,'[SIGNATUR]') !== false) {
    $message_body_plain = str_replace('[SIGNATUR]', $txt_signatur, $message_body_plain);
    $txt_signatur = '';
  } elseif ($txt_signatur != '') {
    $txt_signatur = "\n".$txt_signatur;  
  }

  require_once (DIR_FS_EXTERNAL.'phpmailer/PHPMailerAutoload.php');

  $mail = new PHPMailer();
  $mail->PluginDir = DIR_FS_EXTERNAL.'phpmailer/';
  $mail->CharSet = $lang_data['language_charset'];
  $mail->SetLanguage($lang_data['code'], DIR_FS_EXTERNAL.'phpmailer/language/');

  if (EMAIL_TRANSPORT == 'smtp') {
    $mail->IsSMTP();
    $mail->SMTPKeepAlive = true; // set mailer to use SMTP
    $mail->SMTPAuth = (SMTP_AUTH == 'true') ? true : false; // turn on SMTP authentication true/false
    $mail->SMTPSecure = (defined('SMTP_SECURE') && SMTP_SECURE != 'none') ? SMTP_SECURE : ''; // turn on SMTP secure ssl or tls
    $mail->Port = SMTP_PORT; // SMTP port
    $mail->Username = SMTP_USERNAME; // SMTP username
    $mail->Password = SMTP_PASSWORD; // SMTP password
    $mail->Host = SMTP_MAIN_SERVER.';'.SMTP_BACKUP_SERVER; // specify main and backup server "smtp1.example.com;smtp2.example.com"
  }

  if (EMAIL_TRANSPORT == 'sendmail') { // set mailer to use SMTP
    $mail->IsSendmail();
    $mail->Sendmail = SENDMAIL_PATH;
  }
  
  if (EMAIL_TRANSPORT == 'mail') {
    $mail->IsMail();
  }

  // decode html2txt
  $html_array = array('<br />', '<br/>', '<br>');
  $txt_array = array(" \n", " \n", " \n");
  $message_body_plain = str_replace($html_array, $txt_array, $message_body_plain.$txt_signatur);//DPW Signatur ergänzt.
  
  // remove html tags
  $message_body_plain = strip_tags($message_body_plain);
  $message_body_plain = html_entity_decode($message_body_plain, ENT_NOQUOTES, $lang_data['language_charset']);

  if (EMAIL_USE_HTML == 'true') { // set email format to HTML
    $mail->IsHTML(true);
    $mail->Body = $message_body_html.$html_signatur;//DPW Signatur ergänzt.
    $mail->AltBody = $message_body_plain;
  } else {
    $mail->IsHTML(false);
    $mail->Body = $message_body_plain;
  }

  $mail->From = $from_email_address;
  $mail->Sender = $from_email_address;
  $mail->FromName = $from_email_name;
  $mail->AddAddress($to_email_address, $to_name);
  if ($forwarding_to != '') {
    $forwarding = explode(',', $forwarding_to);
    foreach ($forwarding as $forwarding_address) {
      $mail->AddBCC(trim($forwarding_address));
    }
  }
  $mail->AddReplyTo($reply_address, $reply_address_name);

  $mail->WordWrap = (int)EMAIL_WORD_WRAP; // set word wrap
  //create attachments array for better handling
  $attachments = attachments_array($path_to_attachments,$path_to_more_attachments);
  // add attachments
  for( $i = 0, $n = count($attachments); $i < $n; $i++) {
    $mail->AddAttachment($attachments[$i]);
  }
  $mail->Subject = $email_subject;

  if (!$mail->Send()) {
    trigger_error('Mailer Error - '.$mail->ErrorInfo, E_USER_WARNING);
  }
}

function attachments_array($path_to_attachments,$path_to_more_attachments)
{
  $attachments = array();
  $attachments = check_attachments($attachments,$path_to_attachments);
  $attachments = check_attachments($attachments,$path_to_more_attachments);
  return $attachments;
}

function check_attachments($attachments, $path_to_attachments)
{
  if ($path_to_attachments != '') {
    $path_to_attachments = is_array($path_to_attachments) ? $path_to_attachments : explode(',',$path_to_attachments);
    $num = count($path_to_attachments);
    for($i=0; $i <$num; $i++) {
      $path_to_attachments[$i] = ((strpos($path_to_attachments[$i], DIR_FS_DOCUMENT_ROOT)===false) ? DIR_FS_DOCUMENT_ROOT:'') . trim($path_to_attachments[$i]);
      if (file_exists($path_to_attachments[$i])) {
        $attachments[] = $path_to_attachments[$i];
      }
    }
  }
  return $attachments;
}
?>