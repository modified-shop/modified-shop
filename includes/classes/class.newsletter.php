<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

// include needed classes
require_once (DIR_FS_CATALOG.'includes/classes/modified_captcha.php');


class newsletter {
  var $message, $message_class;


  function __construct() {
    $this->auto = false;
    $this->remove = false;
    
    $captcha_class = CAPTCHA_MOD_CLASS;
    $this->mod_captcha = $captcha_class::getInstance();
  }


  function RemoveFromList($key, $mail) {
    if (!xtc_not_null($key) && $this->remove === false) {
      $this->message = TEXT_EMAIL_ACTIVE_ERROR;
      $this->message_class = 'error';
    } else {
      $where = '';
      if ($this->remove === false) {
        $where = " AND mail_key = '".xtc_db_input($key)."' ";
      }
      $check_mail_query = xtc_db_query("SELECT customers_email_address,
                                               customers_id,
                                               mail_key
                                          FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                         WHERE MD5(customers_email_address) = '".xtc_db_input($mail)."'
                                               ".$where);
      if (xtc_db_num_rows($check_mail_query) > 0) {
        $check_mail = xtc_db_fetch_array($check_mail_query);
        $this->sendRequestMail($check_mail['customers_email_address'], 'unsubscribe');

        $sql_data_array = array (
          'mail_status' => '2',
          'mail_key' => '',
          'date_added' => 'null',
          'ip_date_added' => '',
          'date_confirmed' => 'null',
          'ip_date_confirmed' => '',
        );
        xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($check_mail['customers_email_address'])."'".$where);

        $this->message = TEXT_EMAIL_DEL;
        $this->message_class = 'info';
      } else {
        $this->message = TEXT_EMAIL_DEL_ERROR;
        $this->message_class = 'error';
      }
    }
  }


  function ActivateAddress($key, $mail) {
    if (!xtc_not_null($key)) {
      $this->message = TEXT_EMAIL_ACTIVE_ERROR;
      $this->message_class = 'error';
    } else {
      $check_mail_query = xtc_db_query("SELECT mail_key,
                                               mail_status,
                                               customers_email_address
                                          FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                         WHERE MD5(customers_email_address) = '".xtc_db_input($mail)."'
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
          xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_email_address = '".xtc_db_input($check_mail['customers_email_address'])."'");
          $this->sendRequestMail($check_mail['customers_email_address'], 'subscribe');
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
      if ($this->mod_captcha->validate($postCode) === true || $this->auto === true) {

        if ($check == 'inp') {
          $check_mail_query = xtc_db_query("SELECT customers_email_address,
                                                   mail_status 
                                              FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                             WHERE customers_email_address = '".xtc_db_input($mail)."'
                                           ");
          if (xtc_db_num_rows($check_mail_query) > 0) {

            $check_mail = xtc_db_fetch_array($check_mail_query);

            if ($check_mail['mail_status'] != '1') {

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
                $this->sendRequestMail($mail, 'subscribe');
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
              $this->sendRequestMail($mail, 'subscribe');
              $this->message = TEXT_EMAIL_ACTIVE;
              $this->message_class = 'info';
            }
          }
        }

        if ($check == 'del') {
          $this->remove = true;
          $this->RemoveFromList('', md5($mail));
        }

      } else {
        $this->message = TEXT_WRONG_CODE;
        $this->message_class = 'error';
      }
    }
  }


  function sendRequestMail($mail, $action = 'opt_in') {
    $sendmail = false;
    $smarty = new Smarty;
    
    $sql_data_array = array(
      'customers_email_address' => $mail,
      'customers_action' => $action,
      'ip_address' => ((defined('RUN_MODE_ADMIN')) ? 'Admin' : ip_clearing($_SESSION['tracking']['ip'])),
      'date_added' => 'now()'
    );
    xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS_HISTORY, $sql_data_array);
    
    switch ($action) {
      case 'opt_in':
        $sendmail = true;
        $link = xtc_href_link(FILENAME_NEWSLETTER, 'action=activate&email='.md5($mail).'&key='.$this->vlCode, 'NONSSL');
        $smarty->assign('EMAIL', xtc_db_input($mail));
        $smarty->assign('LINK', $link);
        
        foreach(auto_include(DIR_FS_CATALOG.'includes/extra/newsletter/opt_in/','php') as $file) require_once ($file);
        break;
      
      case 'unsubscribe':
        foreach(auto_include(DIR_FS_CATALOG.'includes/extra/newsletter/unsubscribe/','php') as $file) require_once ($file);
        break;
        
      case 'subscribe':
        if (ACTIVATE_GIFT_SYSTEM == 'true') {
          if (defined('MODULE_NEWSLETTER_VOUCHER_AMOUNT')
              && MODULE_NEWSLETTER_VOUCHER_AMOUNT > '0'
              && $this->check_gv_coupon_sendt($mail) === false
              ) 
          {
            $sendmail = true;
            require_once (DIR_FS_INC.'create_coupon_code.inc.php');

            $coupon_code = create_coupon_code();
            $sql_data_array = array('coupon_code' => $coupon_code,
                                    'coupon_type' => 'G',
                                    'coupon_amount' => MODULE_NEWSLETTER_VOUCHER_AMOUNT,
                                    'date_created' => 'now()'
                                    );
            xtc_db_perform(TABLE_COUPONS, $sql_data_array);

            $insert_id = xtc_db_insert_id();
            $sql_data_array = array('coupon_id' => $insert_id,
                                    'customer_id_sent' => '0',
                                    'sent_firstname' => 'Newsletter',
                                    'emailed_to' => $mail,
                                    'date_sent' => 'now()'
                                    );
            xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);

            $smarty->assign('SEND_GIFT', 'true');
            $smarty->assign('GIFT_AMMOUNT', $xtPrice->xtcFormat(MODULE_NEWSLETTER_VOUCHER_AMOUNT, true));
            $smarty->assign('GIFT_CODE', $coupon_code);
            $smarty->assign('GIFT_LINK', xtc_href_link(FILENAME_GV_REDEEM, 'gv_no='.$coupon_code, 'NONSSL', false));
          }
          
          if (defined('MODULE_NEWSLETTER_DISCOUNT_COUPON')
              && MODULE_NEWSLETTER_DISCOUNT_COUPON != ''
              && $this->check_gv_coupon_sendt($mail) === false
              ) 
          {
            $coupon_code = MODULE_NEWSLETTER_DISCOUNT_COUPON;
            $coupon_query = xtc_db_query("SELECT * 
                                            FROM ".TABLE_COUPONS." 
                                           WHERE coupon_code = '".xtc_db_input($coupon_code)."'");
            if (xtc_db_num_rows($coupon_query) > 0) {
              $sendmail = true;
              $coupon = xtc_db_fetch_array($coupon_query);
              $coupon_id = $coupon['coupon_id'];
              $coupon_desc_query = xtc_db_query("SELECT * 
                                                   FROM ".TABLE_COUPONS_DESCRIPTION." 
                                                  WHERE coupon_id = '".$coupon_id."' 
                                                    AND language_id = '".(int)$_SESSION['languages_id']."'");
              $coupon_desc = xtc_db_fetch_array($coupon_desc_query);
        
              $sql_data_array = array('coupon_id' => $coupon_id,
                                      'customer_id_sent' => '0',
                                      'sent_firstname' => 'Newsletter',
                                      'emailed_to' => $mail,
                                      'date_sent' => 'now()'
                                      );
              xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);
        
              $smarty->assign('SEND_COUPON', 'true');
              $smarty->assign('COUPON_DESC', $coupon_desc['coupon_description']);
              $smarty->assign('COUPON_CODE', $coupon['coupon_code']);
            }
          }
        }
        
        foreach(auto_include(DIR_FS_CATALOG.'includes/extra/newsletter/subscribe/','php') as $file) require_once ($file);
        break;
    }
    
    if ($sendmail === true) {
      $smarty->assign('language', $_SESSION['language']);
      $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
      $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
      
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
  }


  function generateCode() {
    require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
    $this->vlCode = xtc_random_charcode(32);
  }


  function RemoveLinkAdmin($key,$mail) {
    return HTTP_CATALOG_SERVER.DIR_WS_CATALOG.FILENAME_CATALOG_NEWSLETTER.'?action=remove&email='.md5($mail).'&key='.$key;
  }

  
  function check_gv_coupon_sendt($mail) {
    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_COUPON_EMAIL_TRACK."
                                  WHERE emailed_to = '".xtc_db_input($mail)."'
                                    AND sent_firstname = 'Newsletter'");
    if (xtc_db_num_rows($check_query) > 0) {
      return true;
    }
    return false;
  }

}
?>