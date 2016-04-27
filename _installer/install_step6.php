<?php
/* --------------------------------------------------------------
   $Id: install_step6.php 2999 2012-06-11 08:27:32Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (install_step6.php,v 1.29 2003/08/20); www.nextcommerce.org
   (c) 2006 xtCommerce (install_step6.php 941 2005-05-11); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (file_exists('../includes/local/configure.php')) {
    require('../includes/local/configure.php');
  } else {
    require('../includes/configure.php');
  }
  require('includes/application.php');

  // Database
  require_once(DIR_FS_INC . 'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once(DIR_FS_INC . 'db_functions.inc.php');

  require_once(DIR_FS_INC . 'xtc_encrypt_password.inc.php');
  require_once(DIR_FS_INC . 'xtc_validate_email.inc.php');
  require_once(DIR_FS_INC . 'xtc_redirect.inc.php');
  require_once(DIR_FS_INC . 'xtc_href_link.inc.php');
  require_once(DIR_FS_INC . 'xtc_draw_pull_down_menu.inc.php');
  require_once(DIR_FS_INC . 'xtc_draw_input_field.inc.php');
  require_once(DIR_FS_INC . 'xtc_get_country_list.inc.php');

  require_once (DIR_FS_EXTERNAL.'password_policy/password_policy.php');
  
  require_once(DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/includes/functions.php');
  
   //BOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php
  //include('language/'.$_SESSION['language'].'.php');
  include('language/'.$lang.'.php');
  //EOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php

  // connect do database
  xtc_db_connect() or die('Unable to connect to database server!');

  // get configuration data
  $configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }

  if (isset($_POST['action']) && ($_POST['action'] == 'get_states')) {
      $check_query = xtc_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$_POST['countryid'] . "'");
      $check = xtc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      
      $zone_name = isset($_POST['zone']) ? $_POST['zone'] : '';
      $zone_name =  $character_set == 'latin1' ? utf8_decode($zone_name) : $zone_name;

      if ($check['total'] > 0)
      {
        $zones_array = array();
        $zones_query = xtc_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$_POST['countryid'] . "' order by zone_name");
        while ($zones_values = xtc_db_fetch_array($zones_query)) {
          $zones_array[] = array('id' => ($zones_values['zone_name']), 'text' => ($zones_values['zone_name']));
        }
        $t_output =  xtc_draw_pull_down_menu('STATE', $zones_array, (isset($_POST['zone']) ? $zone_name : ''), 'class="select_states"' );
      }
      else
      {
        $t_output =  xtc_draw_input_field('STATE', (isset($_POST['zone']) && !isset($_POST['type']) ? $zone_name : ''));
      }
      $json_output = $t_output;
      echo $json_output;
      EXIT;
  }
  $messageStack = new messageStack();
  $process = false;

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $process = true;

    $gender = xtc_db_prepare_input($_POST['GENDER']);
    $firstname = xtc_db_prepare_input($_POST['FIRST_NAME']);
    $lastname = xtc_db_prepare_input($_POST['LAST_NAME']);
    $email_address = xtc_db_prepare_input($_POST['EMAIL_ADRESS']);
    $street_address = xtc_db_prepare_input($_POST['STREET_ADRESS']);
    $postcode = xtc_db_prepare_input($_POST['POST_CODE']);
    $city = xtc_db_prepare_input($_POST['CITY']);
    //$zone_id = xtc_db_prepare_input($_POST['zone_id']);
    $state = (isset($_POST['STATE']) ? xtc_db_prepare_input($_POST['STATE']) : '');
    $country = xtc_db_prepare_input($_POST['COUNTRY']);
    $telephone = xtc_db_prepare_input($_POST['TELEPHONE']);
    $password = xtc_db_prepare_input($_POST['PASSWORD']);
    $confirmation = xtc_db_prepare_input($_POST['PASSWORD_CONFIRMATION']);
    $store_name = xtc_db_prepare_input($_POST['STORE_NAME']);
    $email_from = xtc_db_prepare_input($_POST['EMAIL_ADRESS_FROM']);
    $zone_setup = xtc_db_prepare_input($_POST['ZONE_SETUP']);
    $company = xtc_db_prepare_input($_POST['COMPANY']);

    $error = false;

    if ($gender == '') {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_GENDER_ERROR);
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_LAST_NAME_ERROR);
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (xtc_validate_email($email_address) == false) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_CITY_ERROR);
    }

    if (is_numeric($country) == false) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_COUNTRY_ERROR);
    }

    $zone_id = 0;
    if ($state != '') {
      $check_query = xtc_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
      $check = xtc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
        $zone_query = xtc_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and (zone_name like '" . xtc_db_input($state) . "%' or zone_code like '%" . xtc_db_input($state) . "%')");
        if (xtc_db_num_rows($zone_query) > 0) {
          $zone = xtc_db_fetch_array($zone_query);
          $zone_id = $zone['zone_id'];
        } 
      }
    }
    
    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    $policy = new password_policy();
    if (!$policy->validate($password)) {
      $error = true;
      foreach ($policy->get_errors() as $k => $error) {
        $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.$error);
      }
    }
    elseif ($password != $confirmation) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }

    if (strlen($store_name) < '3') {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_STORE_NAME_ERROR);
    }

    if (strlen($company) < '2') {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_COMPANY_NAME_ERROR);
    }

    if (strlen($email_from) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_EMAIL_ADDRESS_FROM_ERROR);
    } elseif (xtc_validate_email($email_from) == false) {
      $error = true;
      $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.ENTRY_EMAIL_ADDRESS_FROM_CHECK_ERROR);
    }

    if ( ($zone_setup != 'yes') && ($zone_setup != 'no') ) {
        $error = true;
        $messageStack->add('install_step6', '<img src="images/icons/error.png" />&nbsp;'.SELECT_ZONE_SETUP_ERROR);
    }

    if ($error == false) {
      xtc_db_query("TRUNCATE `customers`");
      xtc_db_query("TRUNCATE `customers_info`");
      xtc_db_query("TRUNCATE `address_book`");
      xtc_db_query("TRUNCATE `tax_class`");
      xtc_db_query("TRUNCATE `geo_zones`");
      xtc_db_query("TRUNCATE `zones_to_geo_zones`");
      
      $sql_data_array = array('customers_id' => '1',
                              'customers_status' => '0',
                              'customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_gender' => $gender,
                              'customers_email_address' => $email_address,
                              'customers_default_address_id' => '1',
                              'customers_telephone' => $telephone,
                              'customers_password' => xtc_encrypt_password($password),
                              'delete_user' => '0',
                              'customers_date_added' => 'now()',
                              );
      xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);
      
      $sql_data_array = array('customers_info_id' => '1',
                              'customers_info_date_of_last_logon' => '',
                              'customers_info_number_of_logons' => '',
                              'customers_info_date_account_created' => 'now()', 
                              'customers_info_date_account_last_modified' => '',
                              'global_product_notifications' => '',
                              );
      xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);                                              

      $sql_data_array = array('customers_id' => '1',
                              'entry_gender' => $gender,
                              'entry_company' => $company,
                              'entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_postcode' => $postcode,
                              'entry_city' => $city,
                              'entry_state' => $state,
                              'entry_country_id' => (int)$country,
                              'entry_zone_id' => (int)$zone_id,
                              'address_date_added' => 'now()',
                              );
      xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);                                              

      xtc_db_query("UPDATE " .TABLE_COUNTRIES . " SET status='0'");
      xtc_db_query("UPDATE " .TABLE_COUNTRIES . " SET status='1' WHERE countries_id = ". (int)$country);

      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_address). "' WHERE configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($store_name). "' WHERE configuration_key = 'STORE_NAME'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_from). "' WHERE configuration_key = 'EMAIL_FROM'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($country). "' WHERE configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($postcode). "' WHERE configuration_key = 'SHIPPING_ORIGIN_ZIP'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($company). "' WHERE configuration_key = 'STORE_OWNER'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". (int)$country. "' WHERE configuration_key = 'STORE_COUNTRY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". (int)$zone_id. "' WHERE configuration_key = 'STORE_ZONE'");

      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($store_name) . '\n' . xtc_db_input($company) . '\n' . xtc_db_input($firstname) . ' ' . xtc_db_input($lastname) . '\n' . xtc_db_input($street_address) . '\n' . xtc_db_input($postcode) . ' ' . xtc_db_input($city) . '\n\n' . xtc_db_input($telephone) . '\n' . xtc_db_input($email_address)."' WHERE configuration_key = 'STORE_NAME_ADDRESS'");
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

      if ($zone_setup == 'yes') {
        
        // Steuers�tze des jeweiligen Landes einstellen!
        $tax_normal='';
        $tax_normal_text='';
        $tax_special='';
        $tax_special_text='';
        
        $sql_file = DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/includes/sql/tax_zones_standard.sql';
        
        switch ($country) {
          case '14':
            // Austria
            $tax_normal='20.0000';
            $tax_normal_text='UST 20%';
            $tax_special='10.0000';
            $tax_special_text='UST 10%';
            break;
          case '21':
            // Belgien
            $tax_normal='21.0000';
            $tax_normal_text='UST 21%';
            $tax_special='6.0000';
            $tax_special_text='UST 6%';
            break;
          case '57':
            // D�nemark
            $tax_normal='25.0000';
            $tax_normal_text='UST 25%';
            $tax_special='25.0000';
            $tax_special_text='UST 25%';
            break;
          case '72':
            // Finnland
            $tax_normal='22.0000';
            $tax_normal_text='UST 22%';
            $tax_special='8.0000';
            $tax_special_text='UST 8%';
            break;
          case '73':
            // Frankreich
            $tax_normal='19.6000';
            $tax_normal_text='UST 19.6%';
            $tax_special='2.1000';
            $tax_special_text='UST 2.1%';
             break;
          case '81':
            // Deutschland
            $tax_normal='19.0000';
            $tax_normal_text='MwSt. 19%';
            $tax_special='7.0000';
            $tax_special_text='MwSt. 7%';
            break;
          case '84':
            // Griechenland
            $tax_normal='18.0000';
            $tax_normal_text='UST 18%';
            $tax_special='4.0000';
            $tax_special_text='UST 4%';
            break;
          case '103':
            // Irland
            $tax_normal='21.0000';
            $tax_normal_text='UST 21%';
            $tax_special='4.2000';
            $tax_special_text='UST 4.2%';
            break;
          case '105':
            // Italien
            $tax_normal='20.0000';
            $tax_normal_text='UST 20%';
            $tax_special='4.0000';
            $tax_special_text='UST 4%';
            break;
          case '124':
            // Luxemburg
            $tax_normal='15.0000';
            $tax_normal_text='UST 15%';
            $tax_special='3.0000';
            $tax_special_text='UST 3%';
            break;
          case '150':
            // Niederlande
            $tax_normal='19.0000';
            $tax_normal_text='UST 19%';
            $tax_special='6.0000';
            $tax_special_text='UST 6%';
            break;
          case '171':
            // Portugal
            $tax_normal='17.0000';
            $tax_normal_text='UST 17%';
            $tax_special='5.0000';
            $tax_special_text='UST 5%';
            break;
          case '195':
            // Spain
            $tax_normal='16.0000';
            $tax_normal_text='UST 16%';
            $tax_special='4.0000';
            $tax_special_text='UST 4%';
            break;
          case '203':
            // Schweden
            $tax_normal='25.0000';
            $tax_normal_text='UST 25%';
            $tax_special='6.0000';
            $tax_special_text='UST 6%';
            break;
          case '204':
            // Schweiz
            $tax_normal='8.0000';
            $tax_normal_text='UST 8%';
            $tax_special='2.5000';
            $tax_special_text='UST 2,5%';

            $tax_zero='0.0000';
            $tax_zero_text='UST 0%';
            $tax_germany_normal='19.0000';
            $tax_germany_normal_text='UST 19%';
            $tax_germany_special='7.0000';
            $tax_germany_special_text='UST 7%';
            
            $sql_file = DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/includes/sql/tax_zones_switzerland.sql';
            break;
          case '222':
            // UK
            $tax_normal='17.5000';
            $tax_normal_text='UST 17.5%';
            $tax_special='5.0000';
            $tax_special_text='UST 5%';
            break;
        }

        // TODO - DUTY INFO

        // Steuers�tze / tax_rates
        xtc_db_query("TRUNCATE `tax_rates`");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (3, 6, 1, 1, '0.0000', 'EU-AUS-UST 0%', NULL, now())");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (4, 6, 2, 1, '0.0000', 'EU-AUS-UST 0%', NULL, now())");
        
        // Schweiz
        if ($country == '204') {        
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

        // Steuers�tze & Steuerzonen & Steuerklassen
        sql_update($sql_file);
      }
      
      // customers status
      sql_update(DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER . '/includes/sql/customers_status.sql');
      
      if (INSTALL_CHARSET == 'utf8') {
        xtc_db_query("update languages set language_charset='utf-8'");
      }

      //xtc_redirect(xtc_href_link(DIR_MODIFIED_INSTALLER.'/install_step7.php', 'lg='.$lang.'&char='.INSTALL_CHARSET, 'NONSSL'));
      xtc_redirect(xtc_href_link(DIR_MODIFIED_INSTALLER.'/install_finished.php', 'lg='.$lang.'&char='.INSTALL_CHARSET, 'NONSSL'));
    }
  }

  require ('includes/header.php')
?>
    <table width="803" style="border:10px solid #fff;" bgcolor="#ffffff" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="95" colspan="2" >
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td><img src="images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
            </tr>
          </table>
      </tr>
      <tr>
        <td align="left" valign="top">
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <ul id="navigation" class="cf">
                  <li class="inactive"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_INDEX; ?></span><br /><span class="description"><?php echo NAV_DESC_INDEX; ?></span></li>
                  <li class="inactive"><span class="number">1.</span> <span class="title"><?php echo NAV_TITLE_STEP1; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP1; ?></span></li>
                  <li class="inactive"><span class="number">2.</span> <span class="title"><?php echo NAV_TITLE_STEP2; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP2; ?></span></li>
                  <li class="inactive last"><span class="number">3.</span> <span class="title"><?php echo NAV_TITLE_STEP3; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP3; ?></span></li>
                  <li class="inactive second_line"><span class="number">4.</span> <span class="title"><?php echo NAV_TITLE_STEP4; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP4; ?></span></li>
                  <li class="inactive second_line"><span class="number">5.</span> <span class="title"><?php echo NAV_TITLE_STEP5; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP5; ?></span></li>
                  <li class="active second_line"><span class="number">6.</span> <span class="title"><?php echo NAV_TITLE_STEP6; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP6; ?></span></li>
                  <!--
                  <li class="inactive second_line"><span class="number">7.</span> <span class="title"><?php echo NAV_TITLE_STEP7; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP7; ?></span></li>
                  //-->
                  <li class="inactive second_line last"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_FINISHED; ?></span><br /><span class="description"><?php echo NAV_DESC_FINISHED; ?></span></li>
                </ul>
                <br />
                <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;"><?php echo TEXT_WELCOME_STEP6; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <?php
            if ($messageStack->size('install_step6') > 0) {
          ?>
            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td colspan="3">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="color:#ffffff">
                        <div style="background:#F2DEDE; color:#a94442; padding:10px; border:1px solid #DCA7A7" class="messageStackError"><?php echo $messageStack->output('install_step6'); ?></div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            <br />
          <?php
            }
          ?>
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                 <form name="install" action="install_step6.php" method="post" onSubmit="return check_form(install_step6);">
                <?php echo $input_lang; 
                      echo draw_hidden_fields(); ?>
                   <input name="action" type="hidden" value="process" />
                   <table width="100%" border="0" cellpadding="0" cellspacing="0">
                     <tr>
                       <td>
                         <h1><?php echo TITLE_ADMIN_CONFIG; ?></h1>
                         <?php echo TEXT_REQU_INFORMATION; ?>
                       </td>
                     </tr>
                   </table>
                   <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                     <table width="100%" border="0">
                       <tr>
                         <td width="26%"><strong><?php echo TEXT_GENDER; ?></strong></td>
                         <td width="74%">
                            <?php echo xtc_draw_radio_field_installer('GENDER', 'm', (($gender=='m')?true:false)) . TEXT_MALE; ?>
                            <?php echo xtc_draw_radio_field_installer('GENDER', 'f', (($gender=='f')?true:false)) . TEXT_FEMALE; ?>
                         </td>
                       </tr>
                       <tr>
                         <td width="26%"><strong><?php echo TEXT_FIRSTNAME; ?></strong></td>
                         <td width="74%"><?php echo xtc_draw_input_field_installer('FIRST_NAME'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_LASTNAME; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('LAST_NAME'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_EMAIL; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('EMAIL_ADRESS'); ?> * <?php echo TEXT_EMAIL_LONG; ?></td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_STREET; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('STREET_ADRESS'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_POSTCODE; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('POST_CODE'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_CITY; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('CITY'); ?>*</td>
                       </tr>
                        <tr>
                         <td><strong><?php echo TEXT_COUNTRY; ?></strong></td>
                         <td><?php echo xtc_get_country_list('COUNTRY',(isset($_POST['COUNTRY']) && $_POST['COUNTRY'] > 0 ? $_POST['COUNTRY'] : 81)); ?>&nbsp; * <?php echo TEXT_COUNTRY_LONG; ?></td>
                       </tr>
                       <tr>
                          <td><strong><?php echo TEXT_STATE; ?></strong></td>
                          <td id="states_container">
                            <?php echo xtc_draw_input_field_installer('STATE'); ?>
                          </td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_TEL; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('TELEPHONE'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_PASSWORD; ?></strong></td>
                         <td><?php echo xtc_draw_password_field_installer('PASSWORD'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo TEXT_PASSWORD_CONF; ?></strong></td>
                         <td><?php echo xtc_draw_password_field_installer('PASSWORD_CONFIRMATION'); ?>*</td>
                       </tr>
                     </table>
                   </div>
                   <br />
                   <table width="100%" border="0" cellpadding="0" cellspacing="0">
                     <tr>
                       <td>
                         <h1><?php echo TITLE_SHOP_CONFIG; ?> </h1>
                       </td>
                     </tr>
                   </table>
                   <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                     <table width="100%" border="0">
                       <tr>
                         <td width="26%"><strong><?php echo  TEXT_STORE; ?></strong></td>
                         <td width="74%"><?php echo xtc_draw_input_field_installer('STORE_NAME'); ?> * <?php echo  TEXT_STORE_LONG; ?></td>
                       </tr>
                       <tr>
                         <td><strong><?php echo  TEXT_COMPANY; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('COMPANY'); ?> *</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo  TEXT_EMAIL_FROM; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('EMAIL_ADRESS_FROM'); ?> * <?php echo  TEXT_EMAIL_FROM_LONG; ?></td>
                       </tr>
                     </table>
                   </div>
                   <br />
                   <h1><?php echo TITLE_ZONE_CONFIG; ?> </h1>
                   <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                      <table width="100%" border="0">
                        <tr>
                          <td width="26%"><strong><?php echo  TEXT_ZONE; ?></strong></td>
                          <td width="74%"><?php echo  TEXT_ZONE_YES; ?>
                            <?php echo xtc_draw_radio_field_installer('ZONE_SETUP', 'yes', 'true'); ?>
                            <?php echo  TEXT_ZONE_NO; ?>
                            <?php echo xtc_draw_radio_field_installer('ZONE_SETUP', 'no'); ?>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <p>
                      <br />
                    </p>
                    <input name="image" type="image" src="images/buttons/<?php echo $lang;?>/button_continue.gif" alt="Continue" align="right">
                    <br />
                  </form>
                </div>
              </td>
            </tr>
          </table>
          <br />
        </td>
      </tr>
    </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
  </body>
  <script type="text/javascript" src="includes/javascript/jquery-1.8.3.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function () {
      create_states($('select[name="COUNTRY"]').val());
      
      $('select[name="COUNTRY"]').change(function() {
        create_states($(this).val());
      });
    });
    
    function create_states(val) {
        var type = '';
        var zone = '&zone=' + $('[name="STATE"]').val();
        if ($('select[name="STATE"]').length) {
          type = '&type=select';
        }
        $('#states_container').html('<img src="images/loading.gif">');
        jQuery.ajax({
          data:     'action=get_states&countryid=' + val + type + zone ,
          url:      'install_step6.php',
          type:     "POST",
          async:    true,
          success:  function(t_states) {
            $('#states_container').html(t_states);
          }
        });
    }
  </script>
</html>