<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  

  require_once ('includes/application_top.php');
  
  // Database
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');

  // make a connection to the database... now
  xtc_db_connect() or die('Unable to connect to database server!');

  // load configuration
  $configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
  }
  
  // language
  require_once(DIR_FS_INSTALLER.'lang/'.$_SESSION['language'].'.php');

  // include needed functions
  require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');
  require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
  require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');

  require_once (DIR_FS_EXTERNAL.'password_policy/password_policy.php');

  // smarty
  $smarty = new Smarty();
  $smarty->setTemplateDir(__DIR__.'/templates')
         ->registerResource('file', new EvaledFileResource())
         ->setConfigDir(__DIR__.'/lang')
         ->SetCaching(0);
  
  $country = 81;
  if (isset($_POST['action']) && $_POST['action'] == 'process') {
    $valid_params = array(
      'firstname',
      'lastname',
      'company',
      'street_address',
      'postcode',
      'city',
      'country',
      'password',
      'confirm_password',
      'email_address',
      'confirm_email_address',
    );

    // prepare variables
    foreach ($_POST as $key => $value) {
      if (!is_object(${$key}) && in_array($key , $valid_params)) {
        ${$key} = addslashes($value);
      }
    }
    
    $error = false;
    
    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_LAST_NAME_ERROR);
    }

    // email check
    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (xtc_validate_email($email_address) == false) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    } elseif ($email_address != $confirm_email_address) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_EMAIL_ERROR_NOT_MATCHING);
    } else {
      $check_email_query = xtc_db_query("SELECT count(*) as total
                                           FROM ".TABLE_CUSTOMERS."
                                          WHERE customers_email_address = '".xtc_db_input($email_address)."'
                                            AND account_type = '0'");
      $check_email = xtc_db_fetch_array($check_email_query);
      if ($check_email['total'] > 0) {
        $error = true;
        $messageStack->add('install_step2', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
      }
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_CITY_ERROR);
    }

    if (is_numeric($country) == false) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_COUNTRY_ERROR);
    } else {
      $check_country_query = xtc_db_query("SELECT countries_id
                                             FROM ".TABLE_COUNTRIES."
                                            WHERE countries_id = '".(int)$country."'
                                              AND status = '1'");
      if (xtc_db_num_rows($check_country_query) < 1) {
        $error = true;
        $messageStack->add('install_step2', ENTRY_COUNTRY_ERROR);
      }
    }

    $policy = new password_policy();
    if (!$policy->validate($password)) {
      $error = true;
      foreach ($policy->get_errors() as $k => $pwd_error) {
        $messageStack->add('install_step2', $pwd_error);
      }
    }
    elseif ($password != $confirm_password) {
      $error = true;
      $messageStack->add('install_step2', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }
    
    if ($error === false) {
      // set charset
      xtc_db_set_charset('utf8');
      
      $sql_data_array = array(
        'delete_user' => '0',
        'customers_status' => '0',
        'customers_firstname' => $firstname,
        'customers_lastname' => $lastname,
        'customers_email_address' => $email_address,
        'customers_password' => xtc_encrypt_password($password),
        'customers_date_added' => 'now()',
        'customers_last_modified' => 'now()',
      );
      xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);
      
      $customer_id = xtc_db_insert_id();    
      $sql_data_array = array('customers_id' => $customer_id,
                              'entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_postcode' => $postcode,
                              'entry_city' => $city,
                              'entry_country_id' => (int)$country,
                              'address_date_added' => 'now()',
                              'address_last_modified' => 'now()'
                              );
      xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
      
      $address_id = xtc_db_insert_id();
      xtc_db_query("UPDATE ".TABLE_CUSTOMERS." 
                       SET customers_default_address_id = '".(int)$address_id."' 
                     WHERE customers_id = '".(int)$customer_id."'");
    
      $sql_data_array = array('customers_info_id' => (int)$customer_id,
                              'customers_info_number_of_logons' => '1',
                              'customers_info_date_account_created' => 'now()'
                              );
      xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);
      
      $store_name = $company;
      $email_from = $email_address;
      xtc_db_query("UPDATE " .TABLE_COUNTRIES . " SET status='0'");
      xtc_db_query("UPDATE " .TABLE_COUNTRIES . " SET status='1' WHERE countries_id = '". (int)$country ."'");

      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_address). "' WHERE configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($store_name). "' WHERE configuration_key = 'STORE_NAME'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_from). "' WHERE configuration_key = 'EMAIL_FROM'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($country). "' WHERE configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($postcode). "' WHERE configuration_key = 'SHIPPING_ORIGIN_ZIP'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($company). "' WHERE configuration_key = 'STORE_OWNER'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". (int)$country. "' WHERE configuration_key = 'STORE_COUNTRY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". (int)$zone_id. "' WHERE configuration_key = 'STORE_ZONE'");

      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". /*xtc_db_input($store_name) . '\n' .*/ xtc_db_input($company) . '\n' . xtc_db_input($firstname) . ' ' . xtc_db_input($lastname) . '\n' . xtc_db_input($street_address) . '\n' . xtc_db_input($postcode) . ' ' . xtc_db_input($city) . '\n\n' . /*xtc_db_input($telephone) . '\n' .*/ xtc_db_input($email_address)."' WHERE configuration_key = 'STORE_NAME_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_address). "' WHERE configuration_key = 'META_REPLY_TO'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($company). "' WHERE configuration_key = 'META_COMPANY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($store_name). "' WHERE configuration_key = 'META_PUBLISHER'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($firstname) . ' ' . xtc_db_input($lastname). "' WHERE configuration_key = 'META_AUTHOR'");

      $multilanguage_email = 'DE::'.$email_from.'||EN::'.$email_from;
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'CONTACT_US_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'CONTACT_US_REPLY_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_SUPPORT_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_SUPPORT_REPLY_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_BILLING_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_BILLING_REPLY_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_BILLING_FORWARDING_STRING'");

      if (DB_SERVER_CHARSET == 'utf8') {
        xtc_db_query("UPDATE " .TABLE_LANGUAGES . " SET language_charset='utf-8'");
      }

      // tax rates
      xtc_db_query("TRUNCATE `tax_rates`");
      xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (3, 6, 1, 1, '0.0000', 'EU-AUS-UST 0%', NULL, now())");
      xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (4, 6, 2, 1, '0.0000', 'EU-AUS-UST 0%', NULL, now())");
      
      $tax_text = 'UST %s%%';
      $sql_file = 'tax_zones_standard.sql';
      
      switch ($country) {
        case '14':
          // Austria
          $tax_normal = '20.0000';
          $tax_special = '10.0000';
          break;
        case '21':
          // Belgium
          $tax_normal = '21.0000';
          $tax_special = '6.0000';
          break;
        case '57':
          // Denmark
          $tax_normal = '25.0000';
          $tax_special = '25.0000';
          break;
        case '72':
          // Finnland
          $tax_normal = '22.0000';
          $tax_special = '8.0000';
          break;
        case '73':
          // French
          $tax_normal = '19.6000';
          $tax_special = '2.1000';
           break;
        case '81':
          // Germany
          $tax_text = 'MwSt. %s%%';
          $tax_normal = '19.0000';
          $tax_special = '7.0000';
          break;
        case '84':
          // Greece
          $tax_normal = '18.0000';
          $tax_special = '4.0000';
          break;
        case '103':
          // Irland
          $tax_normal = '21.0000';
          $tax_special = '4.2000';
          break;
        case '105':
          // Italy
          $tax_normal = '20.0000';
          $tax_special = '4.0000';
          break;
        case '124':
          // Luxemborg
          $tax_normal = '15.0000';
          $tax_special = '3.0000';
          break;
        case '150':
          // Niederlande
          $tax_normal = '19.0000';
          $tax_special = '6.0000';
          break;
        case '171':
          // Portugal
          $tax_normal = '17.0000';
          $tax_special = '5.0000';
          break;
        case '195':
          // Spain
          $tax_normal = '16.0000';
          $tax_special = '4.0000';
          break;
        case '203':
          // Schweden
          $tax_normal = '25.0000';
          $tax_special = '6.0000';
          break;
        case '204':
          // Switzerland
          $tax_normal = '7.7000';
          $tax_special = '2.5000';

          $tax_zero = '0.0000';
          $tax_germany_normal = '19.0000';
          $tax_germany_special = '7.0000';
          
          $sql_file = 'tax_zones_switzerland.sql';
          break;
        case '222':
          // UK
          $tax_normal = '17.5000';
          $tax_special = '5.0000';
          break;
      }
      
      $tax_normal_text = sprintf($tax_text, round($tax_normal, 2));
      $tax_special_text = sprintf($tax_text, round($tax_special, 2));
      
      // switzerland
      if ($country == '204') {
        $tax_zero_text = sprintf($tax_text, $tax_zero);
        $tax_germany_normal = sprintf($tax_text, $tax_germany_normal);
        $tax_germany_special = sprintf($tax_text, $tax_germany_special);
        
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (5, 8, 1, 1, '".$tax_normal."', '".$tax_normal_text."', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (6, 8, 2, 1, '".$tax_special."', '".$tax_special_text."', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (1, 5, 1, 1, '".$tax_zero."', '".$tax_zero_text."', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (2, 5, 2, 1, '".$tax_zero."', '".$tax_zero_text."', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (7, 9, 1, 1, '".$tax_germany_normal."', '".$tax_germany_normal_text."', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (8, 9, 2, 1, '".$tax_germany_special."', '".$tax_germany_special_text."', NULL, now())");
      } else {  
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (1, 5, 1, 1, '".$tax_normal."', '".$tax_normal_text."', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (2, 5, 2, 1, '".$tax_special."', '".$tax_special_text."', NULL, now())");
      }
      
      // set charset
      xtc_db_set_charset(DB_SERVER_CHARSET);

      $sql_array = parse_sql_file(DIR_FS_INSTALLER.'includes/sql/'.$sql_file);

      for ($i=0, $n=count($sql_array); $i<$n; $i++) {
        if (DB_SERVER_CHARSET == 'utf8') {
          $sql_array[$i] = encode_utf8($sql_array[$i], '', true);
        }
        xtc_db_query($sql_array[$i]);
      }

      // redirect
      xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.'install_finished.php', '', $request_type));
    }
  } else {
    xtc_db_query("DROP TABLE IF EXISTS `engine`");
  }
  
  if ($messageStack->size('install_step2') > 0) {
    $smarty->assign('error', $messageStack->output('install_step2'));
  }
  
  // account
  $smarty->assign('INPUT_FIRSTNAME', xtc_draw_input_fieldNote(array ('name' => 'firstname')));
  $smarty->assign('INPUT_LASTNAME', xtc_draw_input_fieldNote(array ('name' => 'lastname')));
  $smarty->assign('INPUT_COMPANY', xtc_draw_input_fieldNote(array ('name' => 'company')));
  $smarty->assign('INPUT_STREET', xtc_draw_input_fieldNote(array ('name' => 'street_address')));
  $smarty->assign('INPUT_CODE', xtc_draw_input_fieldNote(array ('name' => 'postcode')));
  $smarty->assign('INPUT_CITY', xtc_draw_input_fieldNote(array ('name' => 'city')));
  $smarty->assign('INPUT_PASSWORD', xtc_draw_password_fieldNote(array ('name' => 'password')));
  $smarty->assign('INPUT_CONFIRM_PASSWORD', xtc_draw_password_fieldNote(array ('name' => 'confirm_password')));
  $smarty->assign('INPUT_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'email_address')));
  $smarty->assign('INPUT_CONFIRM_EMAIL', xtc_draw_input_fieldNote(array ('name' => 'confirm_email_address')));
  $smarty->assign('INPUT_COUNTRY', xtc_get_country_list(array ('name' => 'country'), (int)$country));
  
  // form
  $smarty->assign('FORM_ACTION', xtc_draw_form('install_step2', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type), 'post').xtc_draw_hidden_field('action', 'process'));
  $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
  $smarty->assign('FORM_END', '</form>');

  $smarty->assign('language', $_SESSION['language']);
  $module_content = $smarty->fetch('install_step2.html');

  require ('includes/header.php');
  $smarty->assign('module_content', $module_content);
  $smarty->assign('logo', xtc_href_link(DIR_WS_INSTALLER.'images/logo_head.png', '', $request_type));

  if (!defined('RM')) {
    $smarty->load_filter('output', 'note');
  }
  $smarty->display('index.html');
  require_once ('includes/application_bottom.php');
?>