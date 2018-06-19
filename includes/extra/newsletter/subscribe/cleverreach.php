<?php    
  if (defined('MODULE_CLEVERREACH_STATUS') && MODULE_CLEVERREACH_STATUS == 'true') {
    $api = new SoapClient('http://api.cleverreach.com/soap/interface_v5.1.php?wsdl');

    $newsletter_query = xtc_db_query("SELECT * 
                                        FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                       WHERE customers_email_address ='".xtc_db_input($mail)."'");
    $newsletter = xtc_db_fetch_array($newsletter_query);
  
    $user = array('email' => $mail,
                  'registered' => strtotime($newsletter['date_added']),
                  'activated' => time(),
                  'source' => MODULE_CLEVERREACH_NAME,
                  'attributes' => array(array('key' => 'firstname', 'value' => encode_utf8($newsletter['customers_firstname'], $_SESSION['language_charset'], true)),
                                        array('key' => 'lastname', 'value' => encode_utf8($newsletter['customers_lastname'], $_SESSION['language_charset'], true)))
                  );
    $result = $api->receiverAdd(MODULE_CLEVERREACH_APIKEY, MODULE_CLEVERREACH_GROUP, $user);

    // get unsubscribed
    $nl_unsubscribe_query = xtc_db_query("SELECT date_added
                                            FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                           WHERE mail_id < '".$newsletter['mail_id']."'
                                        ORDER BY mail_id DESC
                                           LIMIT 1");
                                         
    if (xtc_db_num_rows($nl_unsubscribe_query) > 0) {
      $nl_unsubscribe = xtc_db_fetch_array($nl_unsubscribe_query);
                                         
      $page = 0;
      do {
        $filter = array('page' => $page++,
                        'filter' => 'unsubscribed',
                        'range_start' => date('d.m.Y H:i', strtotime($nl_unsubscribe['date_added'])),
                        'range_end' => date('d.m.Y H:i', time())
                        );
        $return = $api->receiverGetByDate(MODULE_CLEVERREACH_APIKEY, MODULE_CLEVERREACH_GROUP, $filter);
        if ($return->status == "SUCCESS") {
          foreach ($return->data as $data) {
            xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                WHERE customers_email_address = '".xtc_db_input($data->email)."'");
          }
        }        
      } while ($return->status == "SUCCESS");
    }
  }
?>