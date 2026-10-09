<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function send_email_verify_mail($customers_id, $email_address, $token, $valid_time) {
    $smarty = new Smarty();
    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
    $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
    $smarty->assign('EMAIL', $email_address);
    // the link must not carry the session of the browser that requested it
    $smarty->assign('LINK', xtc_href_link(FILENAME_CREATE_ACCOUNT, 'action=verify_email&customers_id='.(int)$customers_id.'&key='.$token, 'SSL', false));
    $smarty->assign('VALID_REQUEST_TIME', ($valid_time / 3600));
    $smarty->caching = 0;

    $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/email_verify_mail.html');
    $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/email_verify_mail.txt');

    xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                 EMAIL_SUPPORT_NAME,
                 $email_address,
                 '',
                 '',
                 EMAIL_SUPPORT_REPLY_ADDRESS,
                 EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                 '',
                 '',
                 TEXT_EMAIL_VERIFY_SUBJECT,
                 $html_mail,
                 $txt_mail,
                 1
                 );
  }
?>
