<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com
   (c) 2003	 nextcommerce www.nextcommerce.org
   (c) 2005 xt:Commerce

   XTC-NEWSLETTER_RECIPIENTS RC1 - Contribution for XT-Commerce http://www.xt-commerce.com
   by Matthias Hinsche http://www.gamesempire.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('NL_REG_MAIL_ADMIN', false);

// include needed function
require_once (DIR_FS_INC.'ip_clearing.inc.php');

class newsletter {
  var $message, $message_class;


  function __construct() {
    $this->auto = false;
  }


  function RemoveFromList($key, $mail) {
    if (!xtc_not_null($key)) {
      $this->message = TEXT_EMAIL_ACTIVE_ERROR;
      $this->message_class = 'error';
    } else {
      $check_mail_query = xtc_db_query("SELECT customers_email_address,
                                               customers_id,
                                               mail_key
                                          FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                         WHERE customers_email_address = '".xtc_db_input($mail)."'
                                           AND mail_key = '".xtc_db_input($key)."'
                                       ");
      if (xtc_db_num_rows($check_mail_query) > 0) {
        // extern Mailer
        $this->_externmailer($mail, 'unsubscribe');
        $del_query = xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                         WHERE customers_email_address ='".xtc_db_input($mail)."'
                                           AND mail_key = '".xtc_db_input($key)."'
                                  ");
        $this->message = TEXT_EMAIL_DEL;
        $this->message_class = 'info';
      } else {
        $this->message = TEXT_EMAIL_DEL_ERROR;
        $this->message_class = 'error';
      }
    }
  }


  function ActivateAddress($key, $email) {
    if (!xtc_not_null($key)) {
      $this->message = TEXT_EMAIL_ACTIVE_ERROR;
      $this->message_class = 'error';
    } else {
      $check_mail_query = xtc_db_query("SELECT mail_key,
                                               mail_status
                                          FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                         WHERE customers_email_address = '".xtc_db_input($email)."'
                                       ");
      if (xtc_db_num_rows($check_mail_query) > 0) {

        $check_mail = xtc_db_fetch_array($check_mail_query);
        if($check_mail['mail_status'] == '1') {
          $this->message = TEXT_EMAIL_EXIST_NEWSLETTER;
          $this->message_class = 'error';
        } elseif ($check_mail['mail_key'] != $_GET['key']) {
          $this->message = TEXT_EMAIL_ACTIVE_ERROR;
          $this->message_class = 'error';
        } else {
          $sql_data_array = array('mail_status' => '1',
                                  'date_confirmed' => 'now()',
                                  'ip_date_confirmed' => ip_clearing($_SESSION['tracking']['ip'])
                                  );
          xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($email)."'");
          // extern Mailer
          $this->_externmailer($email, 'subscribe');
          $this->message = TEXT_EMAIL_ACTIVE;
          $this->message_class = 'info';
        }
      } else {
        $this->message = TEXT_EMAIL_NOT_EXIST;
        $this->message_class = 'error';
      }
    }
  }


  function AddUserAuto($mail) {
    $this->auto = true;
    $this->AddUser('inp', '', $mail);
  }


  function AddUser($check, $postCode, $mail) {
    require_once (DIR_FS_INC.'xtc_validate_email.inc.php');

    if ($check != 'inp' && $check != 'del') {
      $this->message = ERROR_MAIL;
      $this->message_class = 'error';
    } elseif (xtc_validate_email($mail) == false) {
      $this->message = ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
      $this->message_class = 'error';
    } else {

      $this->generateCode();

      if ((strtoupper($postCode) == $_SESSION['vvcode'] && $_SESSION['vvcode']!='') || $this->auto==true) {

        if ($check == 'inp') {
          // Check if email exists
          $check_mail_query = xtc_db_query("SELECT customers_email_address,
                                                   mail_status 
                                              FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                             WHERE customers_email_address = '".xtc_db_input($mail)."'
                                           ");
          if (xtc_db_num_rows($check_mail_query) > 0) {

            $check_mail = xtc_db_fetch_array($check_mail_query);

            if ($check_mail['mail_status'] == '0') {

              $this->message = TEXT_EMAIL_INPUT;
              $this->message_class = 'info';

              if (SEND_EMAILS_DOUBLE_OPT_IN == 'true' && SEND_EMAILS == 'true') {
                $sql_data_array = array('mail_key' => $this->vlCode);
                xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($mail)."'");
                $this->sendRequestMail($mail);
              } else {
                $sql_data_array = array('mail_status' => '1',
                                        'date_confirmed' => 'now()',
                                        'mail_key' => $this->vlCode,
                                        'ip_date_confirmed' => ip_clearing($_SESSION['tracking']['ip'])
                                        );
                xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($mail)."'");
                // extern Mailer
                $this->_externmailer($mail, 'subscribe');
                $this->message = TEXT_EMAIL_ACTIVE;
                $this->message_class = 'info';
              }

            } else {
              $this->message = TEXT_EMAIL_EXIST_NEWSLETTER;
              $this->message_class = 'error';
            }
          } else {
            $check_customer_mail_query = xtc_db_query("SELECT customers_id,
                                                              customers_status,
                                                              customers_firstname,
                                                              customers_lastname,
                                                              customers_email_address
                                                         FROM ".TABLE_CUSTOMERS."
                                                        WHERE customers_email_address = '".xtc_db_input($mail)."'
                                                      ");
            if (xtc_db_num_rows($check_customer_mail_query) > 0) {
              $check_customer = xtc_db_fetch_array($check_customer_mail_query);
              $customers_id = $check_customer['customers_id'];
              $customers_status = $check_customer['customers_status'];
              $customers_firstname = $check_customer['customers_firstname'];
              $customers_lastname = $check_customer['customers_lastname'];
            } else {
              $customers_id = '0';
              $customers_status = '1';
              $customers_firstname = TEXT_CUSTOMER_GUEST;
              $customers_lastname = '';
            }

            $sql_data_array = array ('customers_email_address' => $mail,
                                     'customers_id' => $customers_id,
                                     'customers_status' => $customers_status,
                                     'customers_firstname' => $customers_firstname,
                                     'customers_lastname' => $customers_lastname,
                                     'mail_status' => '0',
                                     'mail_key' => $this->vlCode,
                                     'date_added' => 'now()',
                                     'ip_date_added' => ip_clearing($_SESSION['tracking']['ip'])
                                     );
            xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array);

            $this->message = TEXT_EMAIL_INPUT;
            $this->message_class = 'info';

            if (SEND_EMAILS_DOUBLE_OPT_IN == 'true' && SEND_EMAILS == 'true') {
              $this->sendRequestMail($mail);
            } else {
              $sql_data_array = array('mail_status' => '1',
                                      'date_confirmed' => 'now()',
                                      'ip_date_confirmed' => ip_clearing($_SESSION['tracking']['ip'])
                                      );
              xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($mail)."'");
              // extern Mailer
              $this->_externmailer($mail, 'subscribe');
              $this->message = TEXT_EMAIL_ACTIVE;
              $this->message_class = 'info';
            }
          }
        }

        if ($check == 'del') {

          $check_mail_query = xtc_db_query("SELECT customers_email_address
                                              FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                             WHERE customers_email_address = '".xtc_db_input($mail)."'
                                           ");
          if (xtc_db_num_rows($check_mail_query) > 0) {
            // extern Mailer
            $this->_externmailer($mail, 'unsubscribe');
            $del_query = xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                             WHERE customers_email_address ='".xtc_db_input($mail)."'
                                      ");
            $this->message = TEXT_EMAIL_DEL;
            $this->message_class = 'info';
          } else {
            $this->message = TEXT_EMAIL_NOT_EXIST;
            $this->message_class = 'error';
          }
        }

      } else {
        $this->message = TEXT_WRONG_CODE;
        $this->message_class = 'error';
      }
    }
    unset($_SESSION['vvcode']);
  }


  function sendRequestMail($mail) {

    $smarty = new Smarty;
    $link = xtc_href_link(FILENAME_NEWSLETTER, 'action=activate&email='.xtc_db_input($mail).'&key='.$this->vlCode, 'NONSSL');

    // assign language to template for caching
    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
    $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

    // assign vars
    $smarty->assign('EMAIL', xtc_db_input($mail));
    $smarty->assign('LINK', $link);

    // dont allow cache
    $smarty->caching = false;

    $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/newsletter_mail.html');
    $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/newsletter_mail.txt');

    xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                 EMAIL_SUPPORT_NAME,
                 xtc_db_input($mail),
                 '',
                 '',
                 EMAIL_SUPPORT_REPLY_ADDRESS,
                 EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                 NL_REG_MAIL_ADMIN === true ? EMAIL_SUPPORT_ADDRESS : '',
                 NL_REG_MAIL_ADMIN === true ? EMAIL_SUPPORT_NAME : '',
                 TEXT_EMAIL_SUBJECT,
                 $html_mail,
                 $txt_mail
                 );
  }


  function generateCode() {
    require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
    $this->vlCode = xtc_random_charcode(32);
  }


  function RemoveLinkAdmin($key,$mail) {
    return HTTP_CATALOG_SERVER.DIR_WS_CATALOG.FILENAME_CATALOG_NEWSLETTER.'?action=remove&email='.$mail.'&key='.$key;
  }


  function _externmailer($mail, $type) {
    $newsletter_query = xtc_db_query("SELECT * 
                                        FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                       WHERE customers_email_address ='".xtc_db_input($mail)."'");
    $newsletter = xtc_db_fetch_array($newsletter_query);
    
    if (defined('MODULE_SUPERMAILER_STATUS') && MODULE_SUPERMAILER_STATUS == 'True') {
      $txt_mail_std_arr = array('EMail' => $newsletter['customers_email_address'],
                                'RG' => MODULE_SUPERMAILER_GROUP);
    
      if ($type == 'subscribe') {
        $txt_mail_add_arr['Name'] = $newsletter['customers_firstname'] . ' ' . $newsletter['customers_lastname'];
      }
    
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
                   strtoupper($type),
                   $txt_mail,
                   nl2br($txt_mail)
                   );
    }

    if (defined('MODULE_CLEVERREACH_STATUS') && MODULE_CLEVERREACH_STATUS == 'true') {
      $api = new SoapClient('http://api.cleverreach.com/soap/interface_v5.1.php?wsdl');
      
      switch ($type) {
        case 'subscribe':
          $user = array('email' => $mail,
                        'registered' => strtotime($newsletter['date_added']),
                        'activated' => time(),
                        'source' => MODULE_CLEVERREACH_NAME,
                        'attributes' => array(array('key' => 'firstname', 'value' => utf8_decode($newsletter['customers_firstname'])),
                                              array('key' => 'lastname', 'value' => utf8_decode($newsletter['customers_lastname'])))
                        );
          $result = $api->receiverAdd(MODULE_CLEVERREACH_APIKEY, MODULE_CLEVERREACH_GROUP, $user);
          break; 
        case 'unsubscribe':
          $result = $api->receiverDelete(MODULE_CLEVERREACH_APIKEY, MODULE_CLEVERREACH_GROUP, $mail);
          break;
      }
      
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
            xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS." 
                                WHERE customers_email_address = '".xtc_db_input($return->data['email'])."'");
          }        
        } while ($return->status == "SUCCESS");
      }
    }
  }

}
?>