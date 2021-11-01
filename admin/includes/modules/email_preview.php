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

    //header
    $email_div = '<head>'.PHP_EOL ;
    $email_div .= '<meta http-equiv="Content-Type" content="text/html; charset='.$lang_charset.'" /> '.PHP_EOL ;
  
    //css
    $email_div .=
    '<style type="text/css">
      #tab_html,#tab_txt {
        font-family: Verdana, Arial, sans-serif;
        font-size:13px;
        padding: 2px 5px;
        border: 1px solid #a3a3a3;
        float:left;
        cursor:pointer;
        margin-left:-1px;
        margin-bottom: 15px;
      }
      .active {
        background: #FF6165;
      }
    </style>'. PHP_EOL;
  
    //script
    $email_div .=
    '<script type="text/javascript">
      function change_class(newId,oldId) {
        //alert (newId);
        var newEnd = newId.split("_");
        document.getElementById("tab_"+newEnd[2]).className += " active";
        var newElem = document.getElementById(newId);
        newElem.style.display="block";
        var oldEnd = oldId.split("_");
        var cssClassStr = document.getElementById("tab_"+oldEnd[2]).className;
        document.getElementById("tab_"+oldEnd[2]).className = cssClassStr.replace(" active","");
        var oldElem = document.getElementById(oldId);            
        oldElem.style.display="none";
      }
    </script>'.PHP_EOL;
  
    $email_div .= '</head>'.PHP_EOL ;
  
    //tabs
    $email_div .= '<div id="tab_html" class="tab active" onclick="change_class(\'email_preview_html\',\'email_preview_txt\')">HTML</div>'.PHP_EOL;
    $email_div .= '<div id="tab_txt" class="tab" onclick="change_class(\'email_preview_txt\',\'email_preview_html\')">TEXT</div>'.PHP_EOL;
    $email_div .= '<div style="clear:both;"></div>'.PHP_EOL;
    
    //content
    $email_div .= '<div id="email_preview_html">'.$html_mail.'</div>'.PHP_EOL;
    $email_div .= '<div id="email_preview_txt" style="display:none">'.nl2br($txt_mail).'</div>'.PHP_EOL;

    echo $email_div;
    exit;
  }