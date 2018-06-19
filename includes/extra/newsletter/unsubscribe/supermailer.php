<?php    
  if (defined('MODULE_SUPERMAILER_STATUS') && MODULE_SUPERMAILER_STATUS == 'True') {
    $txt_mail_arr = array(
      'EMail' => $mail,
      'RG' => MODULE_SUPERMAILER_GROUP,
    );
  
    $txt_mail = '';
    foreach(array_keys($txt_mail_arr) as $key){    
      $txt_mail .= $key . ': ' . $txt_mail_arr[$key] . "\n";
    }
    $txt_mail .= '[NOSIGNATUR]';
  
    xtc_php_mail($mail,
                 '',
                 MODULE_SUPERMAILER_EMAIL_ADDRESS,
                 '',
                 '',
                 $mail,
                 '',
                 '',
                 '',
                 'UNSUBSCRIBE',
                 $txt_mail,
                 nl2br($txt_mail)
                 );
  }
?>