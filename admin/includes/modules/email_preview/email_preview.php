<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  if ($email_preview) {
    require_once (DIR_FS_CATALOG.'includes/classes/main.php');
    $main = new main($order->info['languages_id']);

    // load the signatures only, if the appropriate file(s) exists
    $html_signatur = '';
    $txt_signatur = '';
    if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/signatur.html')) {
      $shop_content_data = $main->getContentData(EMAIL_SIGNATURE_ID, $order->info['languages_id']);    
      $smarty->assign('SIGNATURE_HTML', $shop_content_data['content_text']);
      $html_signatur = $smarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/signatur.html'); 
    }
    if (file_exists(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/signatur.txt')) {
      $shop_content_data = $main->getContentData(EMAIL_SIGNATURE_ID, $order->info['languages_id']);
      $smarty->assign('SIGNATURE_TXT', $shop_content_data['content_text']);
      $txt_signatur = $smarty->fetch(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/signatur.txt'); 
    }

    //Platzhalter [NOSIGNATUR] falls keine Signatir notwendig (zB Newsletter)
    if (strpos($html_mail,'[NOSIGNATUR]') !== false) {
      $html_mail = str_replace('[NOSIGNATUR]', '', $html_mail);
      $txt_mail = str_replace('[NOSIGNATUR]', '', $txt_mail);
      $html_signatur = '';
      $txt_signatur = '';
    }

    $html_mail = str_replace('[SIGNATUR]', $html_signatur, $html_mail);
    $txt_mail = str_replace('[SIGNATUR]', $txt_signatur, $txt_mail);

    $email_div = email_preview_tabs();
    $email_div .= '<div id="email_preview_html">'.$html_mail.'</div>'.PHP_EOL;
    $email_div .= '<div id="email_preview_txt" style="display:none">'.nl2br($txt_mail).'</div>'.PHP_EOL;

    echo $email_div;
    exit;
  }