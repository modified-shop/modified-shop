<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  if (defined('MODULE_WITHDRAW_STATUS')
      && MODULE_WITHDRAW_STATUS == 'true'
      )
  {
    defined('DISPLAY_PRIVACY_CHECK') or define('DISPLAY_PRIVACY_CHECK', 'true');
    defined('MODULE_WITHDRAW_TOKEN_HOURS') or define('MODULE_WITHDRAW_TOKEN_HOURS', 24);
  
    // include needed functions
    require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
    require_once (DIR_FS_INC.'parse_multi_language_value.inc.php');
    require_once (DIR_FS_INC.'secure_form.inc.php');
    require_once (DIR_FS_INC.'xtc_date_long.inc.php');
    require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
  
    // include needed classes
    require_once(DIR_WS_CLASSES.'modified_captcha.php');
    
    $mod_captcha = $_mod_captcha_class::getInstance();
      
    // captcha
    $use_captcha = array();
    if (defined('MODULE_WITHDRAW_CAPTCHA') 
        && MODULE_WITHDRAW_CAPTCHA == 'true'
        )
    {
      $use_captcha = array('withdrawal');
    }
    defined('MODULE_CAPTCHA_CODE_LENGTH') or define('MODULE_CAPTCHA_CODE_LENGTH', 6);
    defined('MODULE_CAPTCHA_LOGGED_IN') or define('MODULE_CAPTCHA_LOGGED_IN', 'True');
    
    $action = isset($_GET['action']) && $_GET['action'] != '' ? $_GET['action'] : '';
    $privacy = isset($_POST['privacy']) && $_POST['privacy'] == 'privacy' ? true : false;
    
    if (!isset($smarty) || !is_object($smarty)) {
      $smarty = new Smarty();
    }
    
    if (!isset($main) || !is_object($main)) {
      $main = new main();
    }
    
    $error = false;
    
    switch ($action) {
      case 'auth':
        $valid_params = array(
          'orders_id',
          'email_address',
          'name',
        );
    
        // prepare variables
        foreach ($_POST as $key => $value) {
          if ((!isset(${$key}) || !is_object(${$key})) && in_array($key , $valid_params)) {
            ${$key} = xtc_db_prepare_input($value);
          }
        }
    
        if (!isset($name) || mb_strlen($name, $_SESSION['language_charset']) < ENTRY_LAST_NAME_MIN_LENGTH) {
          $error = true;
          $messageStack->add('withdraw', ENTRY_LAST_NAME_ERROR);
        }

        if (!isset($email_address) || !xtc_validate_email(trim($email_address))) {
          $messageStack->add('withdraw', ENTRY_EMAIL_ADDRESS_ERROR);
          $error = true;
        }
  
        if (!isset($orders_id) || strlen($orders_id) < 1) {
          $messageStack->add('withdraw', ENTRY_ORDERS_ID_ERROR);
          $error = true;
        }
        
        if (in_array('withdrawal', $use_captcha) && (!isset($_SESSION['customer_id']) || MODULE_CAPTCHA_LOGGED_IN == 'True')) {
          if ($mod_captcha->validate((isset($_POST['vvcode'])) ? $_POST['vvcode'] : '') !== true) {
            $messageStack->add('withdraw', strip_tags(ERROR_VVCODE, '<b><strong>'));
            $error = true;
          }
        }

        if (DISPLAY_PRIVACY_CHECK == 'true' && empty($privacy)) {
          $messageStack->add('withdraw', ENTRY_PRIVACY_ERROR);
          $error = true;
        }
        
        if (check_secure_form($_POST) === false) {
          $messageStack->add('withdraw', ENTRY_TOKEN_ERROR);
          $error = true;
        }
        
        if ($error === false) {
          $orders_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_ORDERS."
                                         WHERE orders_id = '".(int)$orders_id."'
                                           AND customers_email_address = '".xtc_db_input($email_address)."'
                                           AND (customers_lastname = '".xtc_db_input($name)."' 
                                                OR delivery_lastname = '".xtc_db_input($name)."' 
                                                OR billing_lastname = '".xtc_db_input($name)."'
                                                )");
          if (xtc_db_num_rows($orders_query) < 1) {          
            $messageStack->add('withdraw', ENTRY_TOKEN_ERROR);
          } else {
            $_SESSION['withdraw'][(int)$orders_id] = array(
              'valid' => true,
              'success' => false,
              'verified' => false,
              'orders_id' => $orders_id,
              'email_address' => $email_address,
              'name' => ((isset($name)) ? $name : ''),
              'orders' => xtc_db_fetch_array($orders_query),
            );
            xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'oID')).'action=validate&oID='.(int)$orders_id, 'SSL'));
          }
        }
        break;
        
      case 'customer':
        // a logged in customer opening his own order is already identified, no double opt-in needed
        if (isset($_REQUEST['oID'])
            && isset($_SESSION['customer_id'])
            && $_SESSION['customers_status']['customers_status_id'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST
            )
        {
          $orders_query = xtc_db_query("SELECT *
                                          FROM ".TABLE_ORDERS."
                                         WHERE orders_id = '".(int)$_REQUEST['oID']."'
                                           AND customers_id = '".(int)$_SESSION['customer_id']."'");
          if (xtc_db_num_rows($orders_query) > 0) {
            $orders = xtc_db_fetch_array($orders_query);

            $_SESSION['withdraw'][(int)$_REQUEST['oID']] = array(
              'valid' => true,
              'success' => false,
              'verified' => true,
              'orders_id' => (int)$_REQUEST['oID'],
              'email_address' => $orders['customers_email_address'],
              'name' => $orders['customers_lastname'],
              'orders' => $orders,
            );

            xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=validate', 'SSL'));
          }
        }

        $messageStack->add_session('withdraw', ENTRY_TOKEN_ERROR);
        xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'oID'))));
        break;

      case 'validate':
        // the link is opened wherever the mail was read, so a token has to work without an existing session
        if (isset($_REQUEST['key']) && is_string($_REQUEST['key'])) {
          xtc_db_query("DELETE FROM ".TABLE_ORDERS_WITHDRAW_TOKEN."
                         WHERE date_expires < now()");

          $token_query = xtc_db_query("SELECT orders_id
                                         FROM ".TABLE_ORDERS_WITHDRAW_TOKEN."
                                        WHERE token = '".xtc_db_input($_REQUEST['key'])."'
                                        LIMIT 1");
          if (xtc_db_num_rows($token_query) > 0) {
            $token = xtc_db_fetch_array($token_query);
            $orders_id = (int)$token['orders_id'];

            // single use
            xtc_db_query("DELETE FROM ".TABLE_ORDERS_WITHDRAW_TOKEN."
                           WHERE orders_id = '".$orders_id."'");

            $orders_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_ORDERS."
                                           WHERE orders_id = '".$orders_id."'");
            if (xtc_db_num_rows($orders_query) > 0) {
              $orders = xtc_db_fetch_array($orders_query);

              $_SESSION['withdraw'][$orders_id] = array(
                'valid' => true,
                'success' => false,
                'verified' => true,
                'orders_id' => $orders_id,
                'email_address' => $orders['customers_email_address'],
                'name' => $orders['customers_lastname'],
                'orders' => $orders,
              );

              // drop the token from the url so it does not linger in history or referrers
              xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key', 'oID')).'action=validate&oID='.$orders_id, 'SSL'));
            }
          }

          $messageStack->add_session('withdraw', ENTRY_TOKEN_ERROR);
          xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key', 'oID'))));
        }

        if (!isset($_REQUEST['oID'])
            || !isset($_SESSION['withdraw'][(int)$_REQUEST['oID']])
            || $_SESSION['withdraw'][(int)$_REQUEST['oID']]['valid'] !== true
            )
        {
          $messageStack->add_session('withdraw', ENTRY_TOKEN_ERROR);
          xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key'))));
        }
        break;

      case 'verify':
        if (isset($_REQUEST['oID'])
            && isset($_SESSION['withdraw'][(int)$_REQUEST['oID']])
            && $_SESSION['withdraw'][(int)$_REQUEST['oID']]['valid'] === true
            )
        {
          $withdraw_array = $_SESSION['withdraw'][(int)$_REQUEST['oID']];

          $orders_id = (int)$withdraw_array['orders_id'];
          $orders = $withdraw_array['orders'];

          // one pending token per order, expired ones are cleared on the way
          xtc_db_query("DELETE FROM ".TABLE_ORDERS_WITHDRAW_TOKEN."
                         WHERE orders_id = '".$orders_id."'
                            OR date_expires < now()");

          $key = xtc_random_charcode(32);
          xtc_db_query("INSERT INTO ".TABLE_ORDERS_WITHDRAW_TOKEN." (orders_id, token, date_added, date_expires)
                             VALUES ('".$orders_id."', '".xtc_db_input($key)."', now(), now() + interval ".(int)MODULE_WITHDRAW_TOKEN_HOURS." hour)");

          $link = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key', 'oID')).'action=validate&key='.$key, 'SSL');
    
          // action send
          $smarty->assign('language', $_SESSION['language']);
          $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

          $smarty->assign('NAME', $orders['customers_name']);
          $smarty->assign('EMAIL', $orders['customers_email_address']);
          $smarty->assign('ORDERS_ID', $orders['orders_id']);
          $smarty->assign('LINK', $link);

          $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/withdraw_mail.html');
          $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/withdraw_mail.txt');

          $withdraw_subject = str_replace('{$nr}', $orders['orders_id'], EMAIL_WITHDRAW_SUBJECT);
          $withdraw_subject = str_replace('{$date}', xtc_date_long($orders['date_purchased']), $withdraw_subject);
         
          xtc_php_mail(EMAIL_BILLING_ADDRESS,
                       EMAIL_BILLING_NAME,
                       trim($orders['customers_email_address']),
                       $orders['customers_name'],
                       '',
                       EMAIL_BILLING_REPLY_ADDRESS,
                       EMAIL_BILLING_REPLY_ADDRESS_NAME,
                       '',
                       '',
                       $withdraw_subject,
                       $html_mail,
                       $txt_mail,
                       1
                       );
          
          $messageStack->add_session('withdraw', TEXT_WITHDRAW_VERIFY_INFO, 'success');
          xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key')).'action=validate'));
        } else {
          $messageStack->add_session('withdraw', ENTRY_TOKEN_ERROR);
          xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key'))));
        }
        break;
        
      case 'withdraw':
        // this case writes to the database and sends mails, so verify the token before touching anything
        if (check_secure_form($_POST) === false) {
          $messageStack->add('withdraw', ENTRY_TOKEN_ERROR);
          break;
        }

        if (isset($_REQUEST['oID'])
            && isset($_SESSION['withdraw'][(int)$_REQUEST['oID']])
            && $_SESSION['withdraw'][(int)$_REQUEST['oID']]['valid'] === true
            )
        {
          $withdraw_array = $_SESSION['withdraw'][(int)$_REQUEST['oID']];
          
          $orders_id = (int)$withdraw_array['orders_id'];
          $orders = $withdraw_array['orders'];

          $products_qty = ((isset($_POST['products_qty']) && is_array($_POST['products_qty'])) ? $_POST['products_qty'] : array());

          $sql_array = array();
          foreach ($products_qty as $orders_products_id => $orders_withdraw_products_qty) {
            $orders_withdraw_products_query = xtc_db_query("SELECT op.*,
                                                                   o.currency,
                                                                   o.customers_status,
                                                                   SUM(owp.products_quantity) as withdraw_quantity
                                                              FROM ".TABLE_ORDERS." o
                                                              JOIN ".TABLE_ORDERS_PRODUCTS." op
                                                                   ON o.orders_id = op.orders_id 
                                                         LEFT JOIN ".TABLE_ORDERS_WITHDRAW_PRODUCTS." owp
                                                                   ON op.orders_products_id = owp.orders_products_id
                                                             WHERE o.orders_id = '".(int)$orders_id."'
                                                               AND op.orders_products_id = '".(int)$orders_products_id."'
                                                          GROUP BY op.orders_products_id");
            $orders_withdraw_products = xtc_db_fetch_array($orders_withdraw_products_query);

            // without the double opt-in the form only offers the full amount, so ignore any posted quantity
            if ($withdraw_array['verified'] !== true) {
              $orders_withdraw_products_qty = (int)$orders_withdraw_products['products_quantity'] - (int)$orders_withdraw_products['withdraw_quantity'];
            }

            if ((int)$orders_withdraw_products['withdraw_quantity'] < (int)$orders_withdraw_products['products_quantity']
                && (int)$orders_withdraw_products_qty <= (int)$orders_withdraw_products['products_quantity']
                && ((int)$orders_withdraw_products_qty + (int)$orders_withdraw_products['withdraw_quantity']) <= (int)$orders_withdraw_products['products_quantity']
                && (int)$orders_withdraw_products_qty > 0
                )
            {
              $sql_array[] = array(
                'orders_id' => $orders_withdraw_products['orders_id'],
                'orders_products_id' => $orders_withdraw_products['orders_products_id'],
                'products_id' => $orders_withdraw_products['products_id'],
                'products_quantity' => (int)$orders_withdraw_products_qty,
              );
            }
          }

          if (count($sql_array) > 0) {
            $sql_data_array = array(
              'orders_id' => $orders_id,
              'date_added' => 'now()',
            );
            xtc_db_perform(TABLE_ORDERS_WITHDRAW, $sql_data_array);
            
            $orders_withdraw_id = xtc_db_insert_id();
            $_SESSION['withdraw'][(int)$_REQUEST['oID']]['orders_withdraw_id'] = $orders_withdraw_id;

            foreach ($sql_array as $sql_data_array) {
              $sql_data_array['orders_withdraw_id'] = $orders_withdraw_id;
              xtc_db_perform(TABLE_ORDERS_WITHDRAW_PRODUCTS, $sql_data_array);
            }
            
            // action send
            $smarty->assign('language', $_SESSION['language']);
            $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

            $smarty->assign('NAME', $orders['customers_name']);
            $smarty->assign('EMAIL', $orders['customers_email_address']);
            $smarty->assign('ORDERS_ID', $orders['orders_id']);

            $orders_withdraw_products_array = array();
            $orders_withdraw_products_query = xtc_db_query("SELECT op.*,
                                                                   o.currency,
                                                                   o.customers_status,
                                                                   owp.products_quantity
                                                              FROM ".TABLE_ORDERS." o
                                                              JOIN ".TABLE_ORDERS_PRODUCTS." op
                                                                   ON o.orders_id = op.orders_id 
                                                              JOIN ".TABLE_ORDERS_WITHDRAW_PRODUCTS." owp
                                                                   ON owp.orders_products_id = op.orders_products_id
                                                             WHERE owp.orders_withdraw_id = '".(int)$orders_withdraw_id."'
                                                          GROUP BY owp.orders_products_id");
            while ($orders_withdraw_products = xtc_db_fetch_array($orders_withdraw_products_query)) {
              $order_xtPrice = new xtcPrice($orders_withdraw_products['currency'], $orders_withdraw_products['customers_status']);
      
              $attributes_array = array();
              $attributes_query = xtc_db_query("SELECT *
                                                  FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                                 WHERE orders_id = '".(int)$orders_withdraw_products['orders_id']."'
                                                   AND orders_products_id = '".$orders_withdraw_products['orders_products_id']."'");
              if (xtc_db_num_rows($attributes_query) > 0) {
                while ($attribtues = xtc_db_fetch_array($attributes_query)) {
                  $attributes_array[] = array(
                    'OPTIONS_NAME' => $attribtues['products_options'],
                    'VALUES_NAME' => $attribtues['products_options_values'],
                  );
                }
              }
        
              $orders_withdraw_products_array[] = array(
                'PRODUCTS_NAME' => $orders_withdraw_products['products_name'],
                'PRODUCTS_MODEL' => $orders_withdraw_products['products_model'],
                'PRODUCTS_PRICE' => $order_xtPrice->xtcFormat($orders_withdraw_products['products_price'], true),
                'PRODUCTS_QUANTITY' => $orders_withdraw_products['products_quantity'],
                'PRODUCTS_ATTRIBUTES' => $attributes_array,
              );
            }
            $smarty->assign('PRODUCTS', $orders_withdraw_products_array);

            $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/withdraw_mail.html');
            $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/withdraw_mail.txt');

            $withdraw_subject = str_replace('{$nr}', $orders_id, EMAIL_WITHDRAW_SUBJECT);
            $withdraw_subject = str_replace('{$date}', xtc_date_long($orders['date_purchased']), $withdraw_subject);
           
            xtc_php_mail(EMAIL_BILLING_ADDRESS,
                         EMAIL_BILLING_NAME,
                         EMAIL_BILLING_ADDRESS,
                         STORE_NAME,
                         EMAIL_BILLING_FORWARDING_STRING,
                         trim($orders['customers_email_address']),
                         $orders['customers_name'],
                         '',
                         '',
                         $withdraw_subject,
                         $html_mail,
                         $txt_mail,
                         4
                         );

            // send mail to customer
            if (SEND_EMAILS == 'true') {
              xtc_php_mail(EMAIL_BILLING_ADDRESS,
                           EMAIL_BILLING_NAME,
                           trim($orders['customers_email_address']),
                           $orders['customers_name'],
                           '',
                           EMAIL_BILLING_REPLY_ADDRESS,
                           EMAIL_BILLING_REPLY_ADDRESS_NAME,
                           '',
                           '',
                           $withdraw_subject,
                           $html_mail,
                           $txt_mail,
                           2
                           );
            }
                            
            $messageStack->add_session('withdraw', ENTRY_WITHDRAW_SUCCESS, 'success');
            xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key')).'action=success'));
          }
 
          $messageStack->add('withdraw', ENTRY_TOKEN_ERROR);
                    
        } else {
          $messageStack->add_session('withdraw', ENTRY_TOKEN_ERROR);
          xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key'))));
        }
        break;
      
      case 'success':
        // valid alone only proves the login step, so require a booked withdrawal before confirming one
        if (isset($_REQUEST['oID'])
            && isset($_SESSION['withdraw'][(int)$_REQUEST['oID']])
            && $_SESSION['withdraw'][(int)$_REQUEST['oID']]['valid'] === true
            && isset($_SESSION['withdraw'][(int)$_REQUEST['oID']]['orders_withdraw_id'])
            )
        {
          $_SESSION['withdraw'][(int)$_REQUEST['oID']]['success'] = true;
        } else {
          $messageStack->add_session('withdraw', ENTRY_TOKEN_ERROR);
          xtc_redirect(xtc_href_link(basename($PHP_SELF), 'coID='.(int)$_GET['coID'], 'SSL'));
        }
        break;
      
      default:
        $_SESSION['withdraw'] = array();
        
    }
      
    $smarty->assign('WITHDRAW_HEADING', ((!empty($shop_content_data['content_heading'])) ? $shop_content_data['content_heading'] : $shop_content_data['content_title']));
    $smarty->assign('WITHDRAW_CONTENT', $shop_content_data['content_text']);
  
    if (isset($_REQUEST['oID'])
        && isset($_SESSION['withdraw'][(int)$_REQUEST['oID']])
        )
    {
      $withdraw_array = $_SESSION['withdraw'][(int)$_REQUEST['oID']];

      $orders_id = (int)$withdraw_array['orders_id'];
      $orders = $withdraw_array['orders'];
      
      if ($withdraw_array['success'] === true) {
        $orders_withdraw_id = $withdraw_array['orders_withdraw_id'];
        
        $orders_withdraw_products_array = array();
        $orders_withdraw_products_query = xtc_db_query("SELECT op.*,
                                                               o.currency,
                                                               o.customers_status,
                                                               SUM(owp.products_quantity) as withdraw_quantity
                                                          FROM ".TABLE_ORDERS." o
                                                          JOIN ".TABLE_ORDERS_PRODUCTS." op
                                                               ON o.orders_id = op.orders_id 
                                                          JOIN ".TABLE_ORDERS_WITHDRAW_PRODUCTS." owp
                                                               ON op.orders_products_id = owp.orders_products_id
                                                         WHERE owp.orders_withdraw_id = '".(int)$orders_withdraw_id."'
                                                      GROUP BY op.orders_products_id");
        while ($orders_withdraw_products = xtc_db_fetch_array($orders_withdraw_products_query)) {
          $order_xtPrice = new xtcPrice($orders_withdraw_products['currency'], $orders_withdraw_products['customers_status']);
  
          $attributes_array = array();
          $attributes_query = xtc_db_query("SELECT *
                                              FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                             WHERE orders_id = '".(int)$orders_withdraw_products['orders_id']."'
                                               AND orders_products_id = '".$orders_withdraw_products['orders_products_id']."'");
          if (xtc_db_num_rows($attributes_query) > 0) {
            while ($attribtues = xtc_db_fetch_array($attributes_query)) {
              $attributes_array[] = array(
                'OPTIONS_NAME' => $attribtues['products_options'],
                'VALUES_NAME' => $attribtues['products_options_values'],
              );
            }
          }
    
          $orders_withdraw_products_array[] = array(
            'PRODUCTS_NAME' => $orders_withdraw_products['products_name'],
            'PRODUCTS_MODEL' => $orders_withdraw_products['products_model'],
            'PRODUCTS_PRICE' => $order_xtPrice->xtcFormat($orders_withdraw_products['products_price'], true),
            'PRODUCTS_QUANTITY' => $orders_withdraw_products['withdraw_quantity'],
            'PRODUCTS_ATTRIBUTES' => $attributes_array,
          );
        }
        $smarty->assign('PRODUCTS', $orders_withdraw_products_array);
        $smarty->assign('BUTTON_CONTINUE', '<a href="'.xtc_href_link(basename($PHP_SELF), 'coID='.(int)$_GET['coID'], 'SSL').'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
        $smarty->assign('FORM_TYPE', 'success');
        $smarty->assign('VERIFIED', $withdraw_array['verified']);
      } elseif ($withdraw_array['valid'] === true) {
        $smarty->assign('FORM_ACTION', xtc_draw_form('withdraw', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action', 'key')).'action=withdraw', 'SSL')).secure_form('withdraw'));
        
        $hidden_data_array = array();
        $orders_withdraw_products_array = array();
        $orders_withdraw_products_query = xtc_db_query("SELECT op.*,
                                                               o.currency,
                                                               o.customers_status,
                                                               SUM(owp.products_quantity) as withdraw_quantity
                                                          FROM ".TABLE_ORDERS." o
                                                          JOIN ".TABLE_ORDERS_PRODUCTS." op
                                                               ON o.orders_id = op.orders_id 
                                                     LEFT JOIN ".TABLE_ORDERS_WITHDRAW_PRODUCTS." owp
                                                               ON op.orders_products_id = owp.orders_products_id
                                                         WHERE o.orders_id = '".(int)$orders_id."'
                                                      GROUP BY op.orders_products_id");
        while ($orders_withdraw_products = xtc_db_fetch_array($orders_withdraw_products_query)) {
          $order_xtPrice = new xtcPrice($orders_withdraw_products['currency'], $orders_withdraw_products['customers_status']);
  
          if ((int)$orders_withdraw_products['withdraw_quantity'] < (int)$orders_withdraw_products['products_quantity']) {        
            $attributes_array = array();
            $attributes_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES."
                                               WHERE orders_id = '".(int)$orders_id."'
                                                 AND orders_products_id = '".$orders_withdraw_products['orders_products_id']."'");
            if (xtc_db_num_rows($attributes_query) > 0) {
              while ($attribtues = xtc_db_fetch_array($attributes_query)) {
                $attributes_array[] = array(
                  'OPTIONS_NAME' => $attribtues['products_options'],
                  'VALUES_NAME' => $attribtues['products_options_values'],
                );
              }
            }
    
            $orders_withdraw_products_array[] = array(
              'PRODUCTS_NAME' => $orders_withdraw_products['products_name'],
              'PRODUCTS_MODEL' => $orders_withdraw_products['products_model'],
              'PRODUCTS_PRICE' => $order_xtPrice->xtcFormat($orders_withdraw_products['products_price'], true),
              'PRODUCTS_QUANTITY' => ((int)$orders_withdraw_products['products_quantity'] - (int)$orders_withdraw_products['withdraw_quantity']),
              'WITHDRAW_QUANTITY' => (int)$orders_withdraw_products['withdraw_quantity'],
              'PRODUCTS_QTY' => xtc_draw_input_field('products_qty['.$orders_withdraw_products['orders_products_id'].']', ((isset($_POST['products_qty'][$orders_withdraw_products['orders_products_id']])) ? (int)$_POST['products_qty'][$orders_withdraw_products['orders_products_id']] : 0)),
              'PRODUCTS_ATTRIBUTES' => $attributes_array,
            );
            
            $hidden_data_array[] = xtc_draw_hidden_field('products_qty['.$orders_withdraw_products['orders_products_id'].']', ((int)$orders_withdraw_products['products_quantity'] - (int)$orders_withdraw_products['withdraw_quantity']));
          }
        }
  
        $smarty->assign('WITHDRAW_INFO', sprintf(TEXT_WITHDRAW_INFO, $orders_id, xtc_date_long($orders['date_purchased'])));
        
        $smarty->assign('PRODUCTS', $orders_withdraw_products_array);
  
        if (count($orders_withdraw_products_array) > 0) {
          if ($withdraw_array['verified'] === false) {   
            $smarty->assign('TEXT_WITHDRAW_PARTLY', sprintf(TEXT_WITHDRAW_PARTLY, xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=verify', 'SSL')));
            $smarty->assign('HIDDEN_DATA', $hidden_data_array);
          }
          $smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_send.gif', IMAGE_BUTTON_WITHDRAW));
        }
        $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(basename($PHP_SELF), 'coID='.(int)$_GET['coID'], 'SSL').'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
        $smarty->assign('FORM_END', '</form>');
        $smarty->assign('FORM_TYPE', 'products');    
        $smarty->assign('VERIFIED', $withdraw_array['verified']);    
      }
    } else {    
      $smarty->assign('FORM_ACTION', xtc_draw_form('withdraw', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=auth', 'SSL')).secure_form('withdraw'));
      if (in_array('withdrawal', $use_captcha) && (!isset($_SESSION['customer_id']) || MODULE_CAPTCHA_LOGGED_IN == 'True')) {
        $smarty->assign('VVIMG', $mod_captcha->get_image_code());
        $smarty->assign('INPUT_CODE', $mod_captcha->get_input_code());
      }
      if (DISPLAY_PRIVACY_CHECK == 'true') {
        $smarty->assign('PRIVACY_CHECKBOX', xtc_draw_checkbox_field('privacy', 'privacy', $privacy, 'id="privacy"'));
      }
      $smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO, $request_type));      
      $smarty->assign('INPUT_EMAIL', xtc_draw_input_fieldNote(array('name' => 'email_address', 'text' => (xtc_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : '')), '', 'autocomplete="email"'));
      $smarty->assign('INPUT_ORDER', xtc_draw_input_fieldNote(array('name' => 'orders_id', 'text' => (xtc_not_null(ENTRY_ORDERS_ID_TEXT) ? '<span class="inputRequirement">'.ENTRY_ORDERS_ID_TEXT.'</span>' : ''))));
      $smarty->assign('INPUT_NAME', xtc_draw_input_fieldNote(array('name' => 'name', 'text' => (xtc_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : '')), '', 'autocomplete="family-name"'));
      $smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
      $smarty->assign('FORM_END', '</form>');
      $smarty->assign('FORM_TYPE', 'auth');
    }
  
    if ($messageStack->size('withdraw') > 0) {
      $smarty->assign('error_message', $messageStack->output('withdraw'));
    }
    if ($messageStack->size('withdraw', 'success') > 0) {
      $smarty->assign('success_message', $messageStack->output('withdraw', 'success'));
    }
  
    $smarty->assign('language', $_SESSION['language']);
    $smarty->caching = 0;
    $smarty->display(CURRENT_TEMPLATE.'/module/withdraw.html');
    $display_mode = 'contactus';
    
    // clear variables
    $smarty->clear_assign('BUTTON_CONTINUE');
    $smarty->clear_assign('CONTENT_HEADING');
    $content_body = '';
    unset($email);
    
    // disable cache
    $disable_smarty_cache = true;
  }