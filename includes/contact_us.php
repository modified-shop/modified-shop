<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  //included by shop_content.php

  //use contact_us.php language file
  require_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/contact_us.php');
  
  $error = false;
  if (isset ($_GET['action']) && ($_GET['action'] == 'send')) {

    //jedes Feld kann hier auf die gewünschte Bedingung getestet und eine Fehlermeldung zugeordnet werden
    $err_msg = '';
    if (!xtc_validate_email(trim($_POST['email']))) {
      $err_msg .= ERROR_EMAIL;
    }
    if ((strtoupper($_POST['vvcode']) != $_SESSION['vvcode']) || $_SESSION['vvcode']=='') {
      $err_msg .= ERROR_VVCODE;
    }
    if (trim($_POST['message_body']) == '') {
      $err_msg .= ERROR_MSG_BODY;
    }

    $smarty->assign('error_message', ERROR_MAIL . $err_msg);
    unset($_SESSION['vvcode']);

    if ($err_msg != '') $error = true;

    //Wenn kein Fehler Email formatieren und absenden
    if (!$error) {
      // Datum und Uhrzeit
      $datum = date("d.m.Y");
      $uhrzeit = date("H:i");

      $additional_fields = '';
      if (isset($_POST['company']))  $additional_fields =  EMAIL_COMPANY. $_POST['company'] . "\n" ;
      if (isset($_POST['street']))   $additional_fields .= EMAIL_STREET . $_POST['street'] . "\n" ;
      if (isset($_POST['postcode'])) $additional_fields .= EMAIL_POSTCODE . $_POST['postcode'] . "\n" ;
      if (isset($_POST['city']))     $additional_fields .= EMAIL_CITY . $_POST['city'] . "\n" ;
      if (isset($_POST['phone']))    $additional_fields .= EMAIL_PHONE . $_POST['phone'] . "\n" ;
      if (isset($_POST['fax']))      $additional_fields .= EMAIL_FAX . $_POST['fax'] . "\n" ;

      $use_contact_email_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'USE_CONTACT_EMAIL_ADDRESS'");
      $use_contact_email = xtc_db_fetch_array($use_contact_email_query);
      if ($use_contact_email['configuration_value'] == 'true') {
          $email = trim(CONTACT_US_EMAIL_ADDRESS);
        $name = CONTACT_US_NAME;
        $notify =  EMAIL_NOTIFY . "\n\n";
      } else {
        $email = trim($_POST['email']);
        $name = $_POST['name'];
        $notify =  '';
      }

      $email_layout = sprintf(EMAIL_SENT_BY, CONTACT_US_NAME, CONTACT_US_EMAIL_ADDRESS, $datum , $uhrzeit) . "\n" .
              "--------------------------------------------------------------" . "\n" . $notify .
              EMAIL_NAME. $_POST['name'] . "\n" .
              EMAIL_EMAIL. trim($_POST['email']) . "\n" .
              $additional_fields .
              "\n".EMAIL_MESSAGE."\n ". $_POST['message_body'] . "\n";

      xtc_php_mail($email,
             $name,
             CONTACT_US_EMAIL_ADDRESS,
             CONTACT_US_NAME,
             CONTACT_US_FORWARDING_STRING,
             $email,
             $name,
             '',
             '',
             CONTACT_US_EMAIL_SUBJECT,
             nl2br($email_layout),
             $email_layout
             );

      if (!isset ($mail_error)) {
        xtc_redirect(xtc_href_link(FILENAME_CONTENT, 'action=success&coID='.(int) $_GET['coID']));
      } else {
        $smarty->assign('error_message', $mail_error);
      }
    }
  }

  $smarty->assign('CONTACT_HEADING', $shop_content_data['content_heading']);
  if (isset ($_GET['action']) && ($_GET['action'] == 'success')) {
    $smarty->assign('success', '1');
    $smarty->assign('BUTTON_CONTINUE', '<a href="'.xtc_href_link(FILENAME_DEFAULT).'">'.xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');

  } else {
    if ($shop_content_data['content_file'] != '') {
      ob_start();
      if (strpos($shop_content_data['content_file'], '.txt'))
        echo '<pre>';
      include (DIR_FS_CATALOG.'media/content/'.$shop_content_data['content_file']);
      if (strpos($shop_content_data['content_file'], '.txt'))
        echo '</pre>';
      $contact_content = ob_get_contents();
      ob_end_clean();
    } else {
      $contact_content = $shop_content_data['content_text'];
    }
    
    require (DIR_WS_INCLUDES.'header.php');

    if (isset ($_SESSION['customer_id']) && !$error) {
      $customers_name = $_SESSION['customer_first_name'].' '.$_SESSION['customer_last_name'];
      $c_query = xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS." WHERE customers_id='".(int)$_SESSION['customer_id']."'");
      $c_data  = xtc_db_fetch_array($c_query);
      $email_address = stripslashes($c_data['customers_email_address']);
      $phone = stripslashes($c_data['customers_telephone']);
      $fax = stripslashes($c_data['customers_fax']);
      $address_query = xtc_db_query("select
                        entry_company,
                        entry_street_address,
                        entry_city,
                        entry_postcode
                        from " . TABLE_ADDRESS_BOOK . "
                        where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                        and address_book_id = '" . (int)$_SESSION['customer_default_address_id'] . "'");
      $address_data = xtc_db_fetch_array($address_query);
      $company  = stripslashes($address_data['entry_company']);
      $street   = stripslashes($address_data['entry_street_address']);
      $postcode = stripslashes($address_data['entry_postcode']);
      $city     = stripslashes($address_data['entry_city']);
    } elseif (!$error) {
    	$customers_name = '';
    	$email_address = '';
    	$phone = '';
    	$company = '';
    	$street = '';
    	$postcode = '';
    	$city = '';
    	$fax = '';
    }

    $products_info = '';
    if (!empty($_GET['products_name'])) {$products_info = trim($_GET['products_name']);}
    if (!empty($_GET['products_model'])) {$products_info = trim($products_info . ' - ' . trim($_GET['products_model']));}
    if (!empty($_GET['question'])) {$products_question = trim($_GET['question'])."\n";}
    if ($products_info != '') {$products_info = $products_question . $products_info . "\n"; }
    if (!$error) $message_body = $products_info . "\n";

    $smarty->assign('CONTACT_CONTENT', $contact_content);
    $smarty->assign('FORM_ACTION', xtc_draw_form('contact_us', xtc_href_link(FILENAME_CONTENT, 'action=send&coID='.(int) $_GET['coID'], 'SSL')));
    $smarty->assign('VVIMG', '<img src="'.xtc_href_link(FILENAME_DISPLAY_VVCODES, '', 'SSL').'" alt="Captcha" />');
    $smarty->assign('INPUT_CODE', xtc_draw_input_field('vvcode', '', 'size="8" maxlength="6"', 'text', false));
    $smarty->assign('INPUT_NAME', xtc_draw_input_field('name', ($error ? $_POST['name'] : $customers_name), 'size="30"'));
    $smarty->assign('INPUT_EMAIL', xtc_draw_input_field('email', ($error ? $_POST['email'] : $email_address), 'size="30"'));
    $smarty->assign('INPUT_PHONE', xtc_draw_input_field('phone', ($error ? $_POST['phone'] : $phone), 'size="30"'));
    $smarty->assign('INPUT_COMPANY', xtc_draw_input_field('company', ($error ? $_POST['company'] : $company), 'size="30"'));
    $smarty->assign('INPUT_STREET', xtc_draw_input_field('street', ($error ? $_POST['street'] : $street), 'size="30"'));
    $smarty->assign('INPUT_POSTCODE', xtc_draw_input_field('postcode', ($error ? $_POST['postcode'] : $postcode), 'size="30"'));
    $smarty->assign('INPUT_CITY', xtc_draw_input_field('city', ($error ? $_POST['city'] : $city), 'size="30"'));
    $smarty->assign('INPUT_FAX', xtc_draw_input_field('fax', ($error ? $_POST['fax'] : $fax), 'size="30"'));
    $smarty->assign('INPUT_TEXT', xtc_draw_textarea_field('message_body', 'soft', 45, 15, ($error ? $_POST['message_body'] : $message_body)));
    $smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_send.gif', IMAGE_BUTTON_SEND));
    $smarty->assign('FORM_END', '</form>');
  }

  $smarty->assign('language', $_SESSION['language']);
  $smarty->caching = 0;
  $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/contact_us.html');
?>