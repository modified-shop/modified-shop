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
      xtc_db_query("TRUNCATE `customers`");
      xtc_db_query("TRUNCATE `customers_info`");
      xtc_db_query("TRUNCATE `address_book`");
      xtc_db_query("TRUNCATE `tax_class`");
      xtc_db_query("TRUNCATE `geo_zones`");
      xtc_db_query("TRUNCATE `zones_to_geo_zones`");

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

      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_address). "' WHERE configuration_key = 'STORE_OWNER_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($store_name). "' WHERE configuration_key = 'STORE_NAME'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($email_from). "' WHERE configuration_key = 'EMAIL_FROM'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($country). "' WHERE configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($postcode). "' WHERE configuration_key = 'SHIPPING_ORIGIN_ZIP'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($company). "' WHERE configuration_key = 'STORE_OWNER'");
      
      $multilanguage_email = 'DE::'.$email_from.'||EN::'.$email_from;
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'CONTACT_US_EMAIL_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'CONTACT_US_REPLY_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_SUPPORT_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_SUPPORT_REPLY_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_BILLING_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_BILLING_REPLY_ADDRESS'");
      xtc_db_query("UPDATE " .TABLE_CONFIGURATION . " SET configuration_value='". xtc_db_input($multilanguage_email). "' WHERE configuration_key = 'EMAIL_BILLING_FORWARDING_STRING'");

      if ($zone_setup == 'yes') {
        
        // Steuersätze des jeweiligen Landes einstellen!
        $tax_normal='';
        $tax_normal_text='';
        $tax_special='';
        $tax_special_text='';
        
        $sql_file = DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/tax_zones_standard.sql';
        
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
            
            $sql_file = DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/tax_zones_switzerland.sql';
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

        // Steuersätze / tax_rates
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

        // Steuersätze & Steuerzonen & Steuerklassen
        sql_update($sql_file);
      }
      xtc_redirect(xtc_href_link(DIR_MODIFIED_INSTALLER.'/install_step7.php', 'lg='.$lang.'&char='.INSTALL_CHARSET, 'NONSSL'));
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