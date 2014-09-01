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

  require('../includes/configure.php');
  require('includes/application.php');

  require_once(DIR_FS_INC . 'xtc_encrypt_password.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_connect.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_query.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_fetch_array.inc.php');
  require_once(DIR_FS_INC .'xtc_validate_email.inc.php');
  require_once(DIR_FS_INC .'xtc_db_input.inc.php');
  require_once(DIR_FS_INC .'xtc_db_num_rows.inc.php');
  require_once(DIR_FS_INC .'xtc_redirect.inc.php');
  require_once(DIR_FS_INC .'xtc_href_link.inc.php');
  require_once(DIR_FS_INC . 'xtc_draw_pull_down_menu.inc.php');
  require_once(DIR_FS_INC . 'xtc_draw_input_field.inc.php');
  require_once(DIR_FS_INC . 'xtc_get_country_list.inc.php');

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

  $messageStack = new messageStack();
  $process = false;

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $process = true;

    $firstname = xtc_db_prepare_input($_POST['FIRST_NAME']);
    $lastname = xtc_db_prepare_input($_POST['LAST_NAME']);
    $email_address = xtc_db_prepare_input($_POST['EMAIL_ADRESS']);
    $street_address = xtc_db_prepare_input($_POST['STREET_ADRESS']);
    $postcode = xtc_db_prepare_input($_POST['POST_CODE']);
    $city = xtc_db_prepare_input($_POST['CITY']);
    $zone_id = xtc_db_prepare_input($_POST['zone_id']);
    $state = xtc_db_prepare_input($_POST['STATE']);
    $country = xtc_db_prepare_input($_POST['COUNTRY']);
    $telephone = xtc_db_prepare_input($_POST['TELEPHONE']);
    $password = xtc_db_prepare_input($_POST['PASSWORD']);
    $confirmation = xtc_db_prepare_input($_POST['PASSWORD_CONFIRMATION']);
    $store_name = xtc_db_prepare_input($_POST['STORE_NAME']);
    $email_from = xtc_db_prepare_input($_POST['EMAIL_ADRESS_FROM']);
    $zone_setup = xtc_db_prepare_input($_POST['ZONE_SETUP']);
    $company = xtc_db_prepare_input($_POST['COMPANY']);

    $error = false;

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_LAST_NAME_ERROR);
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (xtc_validate_email($email_address) == false) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_CITY_ERROR);
    }

    if (is_numeric($country) == false) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_COUNTRY_ERROR);
    }

    // BOF - Tomcraft - 2009-10-14 - removed option to select state as is is not needed in germany
    /*
    if (ACCOUNT_STATE == 'true') {
      $zone_id = 0;
      $check_query = xtc_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
      $check = xtc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
        $zone_query = xtc_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and (zone_name like '" . xtc_db_input($state) . "%' or zone_code like '%" . xtc_db_input($state) . "%')");
        if (xtc_db_num_rows($zone_query) > 0) {
          $zone = xtc_db_fetch_array($zone_query);
          $zone_id = $zone['zone_id'];
        } else {
          $error = true;

          $messageStack->add('install_step6', ENTRY_STATE_ERROR_SELECT);
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('install_step6', ENTRY_STATE_ERROR);
        }
      }
    }
    */
    // EOF - Tomcraft - 2009-10-14 - removed option to select state as is is not needed in germany
    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_PASSWORD_ERROR);
    } elseif ($password != $confirmation) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }

    if (strlen($store_name) < '3') {
      $error = true;
      $messageStack->add('install_step6', ENTRY_STORE_NAME_ERROR);
    }

    if (strlen($company) < '2') {
      $error = true;
      $messageStack->add('install_step6', ENTRY_COMPANY_NAME_ERROR);
    }

    if (strlen($email_from) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_EMAIL_ADDRESS_FROM_ERROR);
    } elseif (xtc_validate_email($email_from) == false) {
      $error = true;
      $messageStack->add('install_step6', ENTRY_EMAIL_ADDRESS_FROM_CHECK_ERROR);
    }

    if ( ($zone_setup != 'yes') && ($zone_setup != 'no') ) {
        $error = true;
        $messageStack->add('install_step6', SELECT_ZONE_SETUP_ERROR);
    }

    if ($error == false) {
      xtc_db_query("insert into " . TABLE_CUSTOMERS . " (
                                customers_id,
                                customers_status,
                                customers_firstname,
                                customers_lastname,
                                customers_gender,
                                customers_email_address,
                                customers_default_address_id,
                                customers_telephone,
                                customers_password,
                                delete_user) VALUES
                                ('1',
                                '0',
                                '".xtc_db_input($firstname)."',
                                '".xtc_db_input($lastname)."','m',
                                '".xtc_db_input($email_address)."',
                                '1',
                                '".xtc_db_input($telephone)."',
                                '".xtc_encrypt_password($password)."',
                                '0')");

      xtc_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (
                                customers_info_id,
                                customers_info_date_of_last_logon,
                                customers_info_number_of_logons,
                                customers_info_date_account_created,
                                customers_info_date_account_last_modified,
                                global_product_notifications) VALUES
                                ('1','','','now()','','')");
      xtc_db_query("insert into " .TABLE_ADDRESS_BOOK . " (
                                customers_id,
                                entry_company,
                                entry_firstname,
                                entry_lastname,
                                entry_street_address,
                                entry_postcode,
                                entry_city,
                                entry_state,
                                entry_country_id,
                                entry_zone_id) VALUES
                                ('1',
                                '".xtc_db_input($company)."',
                                '".xtc_db_input($firstname)."',
                                '".xtc_db_input($lastname)."',
                                '".xtc_db_input($street_address)."',
                                '".xtc_db_input($postcode)."',
                                '".xtc_db_input($city)."',
                                '".xtc_db_input($state)."',
                                '".xtc_db_input($country)."',
                                '".xtc_db_input($zone_id)."'
                                )");

      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($email_address). "' WHERE configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($store_name). "' WHERE configuration_key = 'STORE_NAME'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($email_from). "' WHERE configuration_key = 'EMAIL_FROM'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($country). "' WHERE configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($postcode). "' WHERE configuration_key = 'SHIPPING_ORIGIN_ZIP'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($company). "' WHERE configuration_key = 'STORE_OWNER'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($email_from). "' WHERE configuration_key = 'EMAIL_BILLING_FORWARDING_STRING'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($email_from). "' WHERE configuration_key = 'EMAIL_BILLING_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($email_from). "' WHERE configuration_key = 'CONTACT_US_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". ($email_from). "' WHERE configuration_key = 'EMAIL_SUPPORT_ADDRESS'");

      if ($zone_setup == 'yes') {
        // Steuersätze des jeweiligen Landes einstellen!
        $tax_normal='';
        $tax_normal_text='';
        $tax_special='';
        $tax_special_text='';
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
            // Dänemark
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
            $tax_normal='7.6000';
            $tax_normal_text='UST 7,6%';
            $tax_special='2.4000';
            $tax_special_text='UST 2,4%';
            break;
          case '222':
            // UK
            $tax_normal='17.5000';
            $tax_normal_text='UST 17.5%';
            $tax_special='5.0000';
            $tax_special_text='UST 5%';
            break;
        }

        // Steuersätze / tax_rates
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (1, 5, 1, 1, '".$tax_normal."', '".$tax_normal_text."', '', '')");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (2, 5, 2, 1, '".$tax_special."', '".$tax_special_text."', '', '')");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (3, 6, 1, 1, '0.0000', 'EU-AUS-UST 0%', '', '')");
        xtc_db_query("INSERT INTO tax_rates (tax_rates_id, tax_zone_id, tax_class_id, tax_priority, tax_rate, tax_description, last_modified, date_added) VALUES (4, 6, 2, 1, '0.0000', 'EU-AUS-UST 0%', '', '')");

        // Steuerklassen
        xtc_db_query("INSERT INTO tax_class (tax_class_id, tax_class_title, tax_class_description, last_modified, date_added) VALUES (1, 'Standardsatz', '', '', now())");
        xtc_db_query("INSERT INTO tax_class (tax_class_id, tax_class_title, tax_class_description, last_modified, date_added) VALUES (2, 'erm&auml;&szlig;igter Steuersatz', '', NULL, now())");

        // Steuersätze
        xtc_db_query("INSERT INTO geo_zones (geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added) VALUES (6, 'Steuerzone EU-Ausland', '', '', now())");
        xtc_db_query("INSERT INTO geo_zones (geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added) VALUES (5, 'Steuerzone EU', 'Steuerzone f&uuml;r die EU', '', now())");
        xtc_db_query("INSERT INTO geo_zones (geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added) VALUES (7, 'Steuerzone B2B', '', NULL, now())");

        // EU-Steuerzonen Stand 01.01.2007
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (14, 14, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (21, 21, 0, 5, NULL, now())");
        //BOF - Dokuman 2009-08-20 - Added Bulgaria to EU Zones (since 01.01.2007)
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (33, 33, 0, 5, NULL, now())");
        //EOF - Dokuman 2009-08-20 - Added Bulgaria to EU Zones (since 01.01.2007)
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (55, 55, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (56, 56, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (57, 57, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (67, 67, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (72, 72, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (73, 73, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (81, 81, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (84, 84, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (97, 97, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (103, 103, 0, 5, NULL,now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (105, 105, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (117, 117, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (123, 123, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (124, 124, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (132, 132, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (150, 150, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (170, 170, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (171, 171, 0, 5, NULL, now())");
        //BOF - Dokuman 2009-08-20 - Added Romania to EU Zones (since 01.01.2007)
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (175, 175, 0, 5, NULL, now())");
        //EOF - Dokuman 2009-08-20 - Added Romania to EU Zones (since 01.01.2007)
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (189, 189, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (190, 190, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (195, 195, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (203, 203, 0, 5, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (222, 222, 0, 5, NULL, now())");

        // Rest der Welt
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (1, 1, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (2, 2, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (3, 3, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (4, 4, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (5, 5, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (6, 6, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (7, 7, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (8, 8, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (9, 9, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (10, 10, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (11, 11, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (12, 12, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (13, 13, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (15, 15, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (16, 16, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (17, 17, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (18, 18, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (19, 19, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (20, 20, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (22, 22, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (23, 23, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (24, 24, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (25, 25, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (26, 26, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (27, 27, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (28, 28, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (29, 29, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (30, 30, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (31, 31, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (32, 32, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (34, 34, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (35, 35, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (36, 36, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (37, 37, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (38, 38, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (39, 39, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (40, 40, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (41, 41, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (42, 42, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (43, 43, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (44, 44, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (45, 45, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (46, 46, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (47, 47, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (48, 48, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (49, 49, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (50, 50, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (51, 51, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (52, 52, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (53, 53, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (54, 54, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (58, 58, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (59, 59, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (60, 60, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (61, 61, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (62, 62, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (63, 63, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (64, 64, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (65, 65, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (66, 66, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (68, 68, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (69, 69, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (70, 70, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (71, 71, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (74, 74, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (75, 75, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (76, 76, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (77, 77, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (78, 78, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (79, 79, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (80, 80, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (82, 82, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (83, 83, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (85, 85, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (86, 86, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (87, 87, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (88, 88, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (89, 89, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (90, 90, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (91, 91, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (92, 92, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (93, 93, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (94, 94, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (95, 95, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (96, 96, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (98, 98, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (99, 99, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (100, 100, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (101, 101, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (102, 102, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (104, 104, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (106, 106, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (107, 107, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (108, 108, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (109, 109, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (110, 110, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (111, 111, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (112, 112, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (113, 113, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (114, 114, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (115, 115, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (116, 116, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (118, 118, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (119, 119, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (120, 120, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (121, 121, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (122, 122, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (125, 125, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (126, 126, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (127, 127, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (128, 128, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (129, 129, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (130, 130, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (131, 131, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (133, 133, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (134, 134, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (135, 135, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (136, 136, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (137, 137, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (138, 138, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (139, 139, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (140, 140, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (141, 141, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (142, 142, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (143, 143, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (144, 144, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (145, 145, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (146, 146, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (147, 147, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (148, 148, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (149, 149, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (151, 151, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (152, 152, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (153, 153, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (154, 154, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (155, 155, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (156, 156, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (157, 157, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (158, 158, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (159, 159, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (160, 160, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (161, 161, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (162, 162, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (163, 163, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (164, 164, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (165, 165, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (166, 166, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (167, 167, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (168, 168, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (169, 169, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (172, 172, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (173, 173, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (174, 174, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (176, 176, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (177, 177, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (178, 178, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (179, 179, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (180, 180, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (181, 181, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (182, 182, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (183, 183, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (184, 184, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (185, 185, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (186, 186, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (187, 187, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (188, 188, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (191, 191, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (192, 192, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (193, 193, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (194, 194, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (196, 196, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (197, 197, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (198, 198, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (199, 199, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (200, 200, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (201, 201, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (202, 202, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (204, 204, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (205, 205, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (206, 206, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (207, 207, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (208, 208, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (209, 209, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (210, 210, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (211, 211, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (212, 212, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (213, 213, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (214, 214, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (215, 215, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (216, 216, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (217, 217, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (218, 218, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (219, 219, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (220, 220, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (221, 221, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (223, 223, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (224, 224, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (225, 225, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (226, 226, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (227, 227, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (228, 228, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (229, 229, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (230, 230, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (231, 231, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (232, 232, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (233, 233, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (234, 234, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (235, 235, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (236, 236, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (237, 237, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (238, 238, 0, 6, NULL, now())");
        xtc_db_query("INSERT INTO zones_to_geo_zones VALUES (239, 239, 0, 6, NULL, now())");
      }
      xtc_redirect(xtc_href_link(DIR_MODIFIED_INSTALLER.'/install_step7.php', '', 'NONSSL'));
    }
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>modified eCommerce Shopsoftware Installer - STEP 6 / Shopinformation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
    <?php require('includes/form_check.js.php'); ?>
    <style type="text/css">
      body { background: #eee; font-family: Arial, sans-serif; font-size: 12px;}
      table,td,div { font-family: Arial, sans-serif; font-size: 12px;}
      h1 { font-size: 18px; margin: 0; padding: 0; margin-bottom: 10px; }
      <!--
        .messageBox {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 1;
      }
      .messageStackError, .messageStackWarning { font-family: Verdana, Arial, sans-serif; font-weight: bold; font-size: 10px; background-color: #; }
      -->
    </style>
  </head>
  <body>
    <table width="800" style="border:30px solid #fff;" bgcolor="#f3f3f3" height="80%" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="95" colspan="2" >
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td><img src="images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
            </tr>
          </table>
      </tr>
      <tr>
        <td align="center" valign="top">
          <br />
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <img src="images/step6.gif" width="705" height="180" border="0"><br />
                <br />
                <br />
                <div style="border:1px solid #ccc; background:#fff; padding:10px;"><?php echo TEXT_WELCOME_STEP6; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <?php
            if ($messageStack->size('install_step6') > 0) {
          ?>
            <table width="95%" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td colspan="3">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="color:#ffffff">
                        <div style="border:1px solid #c10000; background:#ff0000; color:#ffffff; padding:10px;"><?php echo $messageStack->output('install_step6'); ?></div>
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
          <table width="95%" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                 <form name="install" action="install_step6.php" method="post" onSubmit="return check_form(install_step6);">
                   <?php echo $input_lang; ?>
                   <input name="action" type="hidden" value="process" />
                   <table width="100%" border="0" cellpadding="0" cellspacing="0">
                     <tr>
                       <td>
                         <h1><?php echo TITLE_ADMIN_CONFIG; ?></h1>
                         <?php echo TEXT_REQU_INFORMATION; ?>
                       </td>
                     </tr>
                   </table>
                   <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                     <table width="100%" border="0">
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
                         <td><?php echo xtc_draw_input_field_installer('EMAIL_ADRESS'); ?>*<strong><?php echo TEXT_EMAIL_LONG; ?></strong></td>
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
                       <?php // BOF - Tomcraft - 2009-10-14 - removed option to select state as is is not needed in germany ?>
                       <!--
                         <tr>
                           <td><strong><?php //echo TEXT_STATE; ?></strong></td>
                         <td>
                         <?php
                       /*
                       if ($process == true) {
                       if ($entry_state_has_zones == true) {
                               $zones_array = array();
                               $zones_query = xtc_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' order by zone_name");
                               while ($zones_values = xtc_db_fetch_array($zones_query)) {
                                 $zones_array[] = array('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
                               }
                               echo xtc_draw_pull_down_menu('STATE', $zones_array);
                             } else {
                               echo xtc_draw_input_field('STATE');
                             }
                           } else {
                             echo xtc_draw_input_field('STATE');
                           }
                       */
                         ?>
                         *</td>
                       </tr>
                       //-->
                       <?php // EOF - Tomcraft - 2009-10-14 - removed option to select state as is is not needed in germany ?>
                       <tr>
                         <td><strong><?php echo TEXT_COUNTRY; ?></strong></td>
                         <?php // BOF - Tomcraft - 2009-10-14 - changed default country to germany ?>
                         <td><?php echo xtc_get_country_list('COUNTRY',81); ?>&nbsp;*<strong><?php echo TEXT_COUNTRY_LONG; ?></strong></td>
                         <?php // EOF - Tomcraft - 2009-10-14 - changed default country to germany ?>
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
                   <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                     <table width="100%" border="0">
                       <tr>
                         <td width="26%"><strong><?php echo  TEXT_STORE; ?></strong></td>
                         <td width="74%"><?php echo xtc_draw_input_field_installer('STORE_NAME'); ?>*<strong><?php echo  TEXT_STORE_LONG; ?></strong></td>
                       </tr>
                       <tr>
                         <td><strong><?php echo  TEXT_COMPANY; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('COMPANY'); ?>*</td>
                       </tr>
                       <tr>
                         <td><strong><?php echo  TEXT_EMAIL_FROM; ?></strong></td>
                         <td><?php echo xtc_draw_input_field_installer('EMAIL_ADRESS_FROM'); ?>*<strong><?php echo  TEXT_EMAIL_FROM_LONG; ?></strong></td>
                       </tr>
                     </table>
                   </div>
                   <br />
                   <h1><?php echo TITLE_ZONE_CONFIG; ?> </h1>
                   <div style="border:1px solid #ccc; background:#fff; padding:10px;">
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
                    <input name="image" type="image" src="buttons/<?php echo $lang;?>/button_continue.gif" alt="Continue" align="right">
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
</html>
