<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_address_store.php 3783 2012-10-17 11:29:42Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
     Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

    $process = true;

    if (ACCOUNT_GENDER == 'true') {
      $gender = xtc_db_prepare_input($_POST['gender']);
    }
    if (ACCOUNT_COMPANY == 'true') {
      $company = xtc_db_prepare_input($_POST['company']);
    }
    $firstname = xtc_db_prepare_input($_POST['firstname']);
    $lastname = xtc_db_prepare_input($_POST['lastname']);
    $street_address = xtc_db_prepare_input($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') {
      $suburb = xtc_db_prepare_input($_POST['suburb']);
    }
    $postcode = xtc_db_prepare_input($_POST['postcode']);
    $city = xtc_db_prepare_input($_POST['city']);
    $country = xtc_db_prepare_input($_POST['country']);
    if (ACCOUNT_STATE == 'true') {
      $zone_id = xtc_db_prepare_input($_POST['zone_id']);
      $state = xtc_db_prepare_input($_POST['state']);
    }

    if (ACCOUNT_GENDER == 'true') {
      if (($gender != 'm') && ($gender != 'f')) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('checkout_address', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('checkout_address', ENTRY_LAST_NAME_ERROR);
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('checkout_address', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('checkout_address', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('checkout_address', ENTRY_CITY_ERROR);
    }

    if (ACCOUNT_STATE == 'true') {
      $zone_id = 0;
      $check_query = xtc_db_query("select count(*) as total from ".TABLE_ZONES." where zone_country_id = '".(int) $country."'");
      $check = xtc_db_fetch_array($check_query);
      $entry_state_has_zones = ($check['total'] > 0);
      if ($entry_state_has_zones == true) {
        //BOF - DokuMan - 2011-09-29 - change the logic of accepting state names
        //$zone_query = xtc_db_query("select distinct zone_id from ".TABLE_ZONES." where zone_country_id = '".(int) $country."' and (zone_name like '".xtc_db_input($state)."%' or zone_code like '%".xtc_db_input($state)."%')");
        // check for zone_id - zone_name, zone_code (submitted by input field)  - zone_id (submitted by dropdown field) 
        $zone_query = xtc_db_query("SELECT DISTINCT zone_id 
                                      FROM ".TABLE_ZONES." 
                                     WHERE zone_country_id = '".(int)$country ."' 
                                       AND (zone_name LIKE '" . xtc_db_input($state) . "%' OR zone_code = '" . xtc_db_input($state) . "' OR zone_id = '" .(int)$state ."')
                                  ");
        //if (xtc_db_num_rows($zone_query) > 1) {
        //  $zone_query = xtc_db_query("select distinct zone_id from ".TABLE_ZONES." where zone_country_id = '".(int) $country."' and zone_name = '".xtc_db_input($state)."'");
        //}
        //if (xtc_db_num_rows($zone_query) >= 1) {
        if (xtc_db_num_rows($zone_query) == 1) {
        //EOF - DokuMan - 2011-09-29 - change the logic of accepting state names
          $zone = xtc_db_fetch_array($zone_query);
          $zone_id = $zone['zone_id'];
        } else {
          $error = true;

          $messageStack->add('checkout_address', ENTRY_STATE_ERROR_SELECT);
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('checkout_address', ENTRY_STATE_ERROR);
        }
      }
    }

    if ((is_numeric($country) == false) || ($country < 1)) {
      $error = true;

      $messageStack->add('checkout_address', ENTRY_COUNTRY_ERROR);
    }

    if ($error == false) {
      $sql_data_array = array ('customers_id' => $_SESSION['customer_id'],
                               'entry_firstname' => $firstname,
                               'entry_lastname' => $lastname,
                               'entry_street_address' => $street_address,
                               'entry_postcode' => $postcode,
                               'entry_city' => $city,
                               'entry_country_id' => $country);

      if (ACCOUNT_GENDER == 'true') {
        $sql_data_array['entry_gender'] = $gender;
      }
      if (ACCOUNT_COMPANY == 'true') {
        $sql_data_array['entry_company'] = $company;
      }
      if (ACCOUNT_SUBURB == 'true') {
        $sql_data_array['entry_suburb'] = $suburb;
      }
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array['entry_zone_id'] = $zone_id;
          $sql_data_array['entry_state'] = '';
        } else {
          $sql_data_array['entry_zone_id'] = '0';
          $sql_data_array['entry_state'] = $state;
        }
      }

      xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);      
      
      //SWITCH shipping/payment
      switch ($checkout_page) {
        case 'shipping':
          $_SESSION['sendto'] = xtc_db_insert_id();
          xtc_redirect(xtc_href_link($link_checkout_shipping, '', 'SSL'));
          break;
        case 'payment':
          $_SESSION['billto'] = xtc_db_insert_id();
          if (isset ($_SESSION['payment'])) {
            unset ($_SESSION['payment']);
          } 
          xtc_redirect(xtc_href_link($link_checkout_payment, '', 'SSL'));          
          break;      
      }       
    }
