<?php 

if ($email_preview) {
    $html_signatur = '<br />' . $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/signatur.html');
    $html_mail = str_replace('[SIGNATUR]', $html_signatur, $html_mail);
    $txt_signatur = "\n" . $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/signatur.txt');
    $txt_mail = str_replace('[SIGNATUR]', $txt_signatur, $txt_mail);
    $email_div = email_preview_tabs();
    $email_div .= '<div id="email_preview_html">'.$html_mail.'</div>'.PHP_EOL;
    $email_div .= '<div id="email_preview_txt" style="display:none">'.nl2br($txt_mail).'</div>'.PHP_EOL;
    //xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID='.$order_id.'&action=edit'));
    echo $email_div;
    exit;
}