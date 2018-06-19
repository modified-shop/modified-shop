<?php    
  if (defined('MODULE_SUPERMAILER_STATUS') && MODULE_SUPERMAILER_STATUS == 'True') {
    $newsletter_query = xtc_db_query("SELECT * 
                                        FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                       WHERE customers_email_address ='".xtc_db_input($mail)."'");
    $newsletter = xtc_db_fetch_array($newsletter_query);

    $txt_mail_arr = array(
      'EMail' => $newsletter['customers_email_address'],
      'RG' => MODULE_SUPERMAILER_GROUP,
      'Name' => $newsletter['customers_firstname'] . ' ' . $newsletter['customers_lastname'],
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
                 'SUBSCRIBE',
                 $txt_mail,
                 nl2br($txt_mail)
                 );
  }
?>