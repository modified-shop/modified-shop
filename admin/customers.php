<?php
  /* --------------------------------------------------------------
   $Id: customers.php 4284 2013-01-12 13:21:54Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(customers.php,v 1.76 2003/05/04); www.oscommerce.com
   (c) 2003   nextcommerce (customers.php,v 1.22 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (customers.php 1296 2005-10-08)

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require ('includes/application_top.php');
  require_once (DIR_FS_INC.'xtc_validate_vatid_status.inc.php');
  require_once (DIR_FS_INC.'xtc_get_geo_zone_code.inc.php');
  require_once (DIR_FS_INC.'xtc_encrypt_password.inc.php');
  require_once (DIR_FS_INC.'xtc_js_lang.php');

  //split page results
  if(!defined('MAX_DISPLAY_LIST_CUSTOMERS')) {
    define('MAX_DISPLAY_LIST_CUSTOMERS', 100);
  }

  // BOF - JUNG GESTALTEN - 27.11.2008 - KUNDENUMSÄTZE
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  // EOF - JUNG GESTALTEN - 27.11.2008 - KUNDENUMSÄTZE

  $customers_statuses_array = xtc_get_customers_statuses();

  //BOC web28 2011-10-31 - FIX customer groups
  $customers_statuses_id_array = array();
  for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
    $customers_statuses_id_array[$customers_statuses_array[$i]['id']] = $customers_statuses_array[$i];
  }
  //changes all $customers_statuses_array[xx] to $customers_statuses_id_array[xx]  in html section
  //EOC web28 2011-10-31 - FIX customer groups

  $processed = false;
  $error = false;
  $entry_vat_error_text ='';
  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (isset($_GET['special']) && $_GET['special'] == 'remove_memo') {
    $mID = xtc_db_prepare_input($_GET['mID']);
    xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_MEMO." WHERE memo_id = '".(int)$mID."'");
    xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, 'cID='.(int) $_GET['cID'].'&action=edit'));
  }

  if ($action == 'edit' || $action == 'update') {
  if ((int)$_GET['cID'] == 1 && $_SESSION['customer_id'] == 1) {
  } else {
    if ((int)$_GET['cID'] != 1) {
    } else {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, ''));
    }
  }
}

  if ($action) {
    switch ($action) {
    case 'new_order' :
      $customers1_query = xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".(int)$_GET['cID']."'");
      $customers1 = xtc_db_fetch_array($customers1_query);
      //BOC - web28 - 2012-04-08 - set order addresses to customers default address
      $customers_query = xtc_db_query("SELECT * FROM ".TABLE_ADDRESS_BOOK."
                                               WHERE customers_id = '".(int)$_GET['cID']."'
                                                 AND address_book_id =  '".(int)$customers1['customers_default_address_id']."'
                                      ");
      //EOC - web28 - 2012-04-08 - set order addresses to customers default address

      //TODO set order addresses to last orders addresses (customers, delivery, billing)

      $customers = xtc_db_fetch_array($customers_query);
      //BOF - web28 - 2011-06-10 add missing iso_code2
      $country_query = xtc_db_query("SELECT countries_name,
                                            countries_iso_code_2,
                                            address_format_id
                                      FROM ".TABLE_COUNTRIES."
                                      WHERE countries_id = '".(int)$customers['entry_country_id']."'");
      //EOF - web28 - 2011-06-10 add missing iso_code2
      $country = xtc_db_fetch_array($country_query);
      $stat_query = xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS_STATUS." WHERE customers_status_id = '".(int)$customers1['customers_status']."' ");
      $stat = xtc_db_fetch_array($stat_query);

      // BOF - DokuMan - 2009-05-22 - BUGFIX: first and last name were not saved when creating manual orders
      $sql_data_array = array (
                              'customers_id' => xtc_db_prepare_input($customers['customers_id']),
                              'customers_cid' => xtc_db_prepare_input($customers1['customers_cid']),
                              'customers_vat_id' => xtc_db_prepare_input($customers1['customers_vat_id']),
                              'customers_status' => xtc_db_prepare_input($customers1['customers_status']),
                              'customers_status_name' => xtc_db_prepare_input($stat['customers_status_name']),
                              'customers_status_image' => xtc_db_prepare_input($stat['customers_status_image']),
                              'customers_status_discount' => xtc_db_prepare_input($stat['customers_status_discount']),
                              'customers_name' => xtc_db_prepare_input($customers['entry_firstname'].' '.$customers['entry_lastname']),
                              'customers_lastname' => xtc_db_prepare_input($customers['entry_lastname']),
                              'customers_firstname' => xtc_db_prepare_input($customers['entry_firstname']),
                              'customers_company' => xtc_db_prepare_input($customers['entry_company']),
                              'customers_street_address' => xtc_db_prepare_input($customers['entry_street_address']),
                              'customers_suburb' => xtc_db_prepare_input($customers['entry_suburb']),
                              'customers_city' => xtc_db_prepare_input($customers['entry_city']),
                              'customers_postcode' => xtc_db_prepare_input($customers['entry_postcode']),
                              'customers_state' => xtc_db_prepare_input($customers['entry_state']),
                              'customers_country' => xtc_db_prepare_input($country['countries_name']),
                              'customers_telephone' => xtc_db_prepare_input($customers1['customers_telephone']),
                              'customers_email_address' => xtc_db_prepare_input($customers1['customers_email_address']),
                              'customers_address_format_id' => xtc_db_prepare_input($country['address_format_id']), //web28 - 2012-04-08 fix country address_format_id
                              'delivery_name' => xtc_db_prepare_input($customers['entry_firstname'].' '.$customers['entry_lastname']),
                              'delivery_lastname' => xtc_db_prepare_input($customers['entry_lastname']),
                              'delivery_firstname' => xtc_db_prepare_input($customers['entry_firstname']),
                              'delivery_company' => xtc_db_prepare_input($customers['entry_company']),
                              'delivery_street_address' => xtc_db_prepare_input($customers['entry_street_address']),
                              'delivery_suburb' => xtc_db_prepare_input($customers['entry_suburb']),
                              'delivery_city' => xtc_db_prepare_input($customers['entry_city']),
                              'delivery_postcode' => xtc_db_prepare_input($customers['entry_postcode']),
                              'delivery_state' => xtc_db_prepare_input($customers['entry_state']),
                              'delivery_country' => xtc_db_prepare_input($country['countries_name']),
                              'delivery_country_iso_code_2' => xtc_db_prepare_input($country['countries_iso_code_2']), //web28 - 2011-06-10 add missing iso_code2
                              'delivery_address_format_id' => xtc_db_prepare_input($country['address_format_id']), //web28 - 2012-04-08 fix country address_format_id
                              'billing_name' => xtc_db_prepare_input($customers['entry_firstname'].' '.$customers['entry_lastname']),
                              'billing_lastname' => xtc_db_prepare_input($customers['entry_lastname']),
                              'billing_firstname' => xtc_db_prepare_input($customers['entry_firstname']),
                              'billing_company' => xtc_db_prepare_input($customers['entry_company']),
                              'billing_street_address' => xtc_db_prepare_input($customers['entry_street_address']),
                              'billing_suburb' => xtc_db_prepare_input($customers['entry_suburb']),
                              'billing_city' => xtc_db_prepare_input($customers['entry_city']),
                              'billing_postcode' => xtc_db_prepare_input($customers['entry_postcode']),
                              'billing_state' => xtc_db_prepare_input($customers['entry_state']),
                              'billing_country' => xtc_db_prepare_input($country['countries_name']),
                              'billing_country_iso_code_2' => xtc_db_prepare_input($country['countries_iso_code_2']), //web28 - 2011-06-10 add missing iso_code2
                              'billing_address_format_id' => xtc_db_prepare_input($country['address_format_id']), //web28 - 2012-04-08 fix country address_format_id
                              'payment_method' => 'cod',
                              'cc_type' => '',
                              'cc_owner' => '',
                              'cc_number' => '',
                              'cc_expires' => '',
                              'cc_start' => '',
                              'cc_issue' => '',
                              'cc_cvv' => '',
                              'comments' => '',
                              'last_modified' => 'now()',
                              'date_purchased' => 'now()',
                              'orders_status' => '1',
                              'orders_date_finished' => '',
                              'currency' => DEFAULT_CURRENCY, //Web28 - 2012-02-26 - BUGFIX: DEFAULT_CURRENCY
                              'currency_value' => '1.0000',
                              'account_type' => '0',
                              'payment_class' => 'cod',
                              'shipping_method' => MODULE_SHIPPING_FLAT_TEXT_TITLE, //Web28 - 2012-02-26 - BUGFIX: Use Session language
                              'shipping_class' => 'flat_flat',
                              'customers_ip' => '',
                              'language' => $_SESSION['language'] //Web28 - 2012-02-26 - BUGFIX: Use Session language
                              );
      // EOF - DokuMan - 2009-05-22 - BUGFIX: first and last name were not saved when creating manual orders
      xtc_db_perform(TABLE_ORDERS, $sql_data_array);
      $orders_id = xtc_db_insert_id();

      //BOC - Web28 - 2012-02-26 - BUGFIX: Use Session language
      require_once (DIR_FS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_total.php');
      $sql_data_array = array ('orders_id' => (int)$orders_id, 'title' => MODULE_ORDER_TOTAL_TOTAL_TITLE.':', 'text' => '0', 'value' => '0', 'class' => 'ot_total');
      //EOC - Web28 - 2012-02-26 - BUGFIX: Use Session language

      $insert_sql_data = array ('sort_order' => MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER);
      $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
      xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

      //BOC - Web28 - 2012-02-26 - BUGFIX: Use Session language
      require_once (DIR_FS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_subtotal.php');
      $sql_data_array = array ('orders_id' => (int)$orders_id, 'title' => '<b>'.MODULE_ORDER_TOTAL_SUBTOTAL_TITLE.'</b>:', 'text' => '0', 'value' => '0', 'class' => 'ot_subtotal');
      //EOC - Web28 - 2012-02-26 - BUGFIX: Use Session language

      $insert_sql_data = array ('sort_order' => MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER);
      $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
      xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID='.(int)$orders_id.'&action=edit'));
      break;
    case 'delete_confirm_adressbook' :
        $customers_id = xtc_db_prepare_input($_GET['cID']);

        xtc_db_query("-- admin/customers.php
                      DELETE FROM ".TABLE_ADDRESS_BOOK."
                            WHERE address_book_id = '".(int) $_GET['address_book_id']."'
                              AND customers_id = '".xtc_db_input($customers_id)."'"
                                  );
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete_confirm_adressbook')).'cID='.(int)$customers_id));
        break;
     case 'update_default_adressbook' :
        $customers_id = xtc_db_prepare_input($_GET['cID']);
        
        $address_book_query = xtc_db_query("-- admin/customers.php
                                       SELECT entry_gender AS customers_gender,
                                              entry_firstname AS customers_firstname,
                                              entry_lastname AS customers_lastname
                                         FROM ".TABLE_ADDRESS_BOOK."
                                        WHERE address_book_id = '".(int) $_GET['default']."'
                                          AND customers_id = '".xtc_db_input($customers_id)."'"
                                           );
        $address_book_array = xtc_db_fetch_array($address_book_query);  

        if (ACCOUNT_GENDER != 'true') {
          unset($address_book_array['customers_gender']);
        }
        
        $sql_data_array = array ('customers_default_address_id' => (int) $_GET['default'],
                                 'customers_last_modified' => 'now()'
                                );
        $sql_data_array = array_merge($address_book_array,$sql_data_array);
        xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '". xtc_db_input($customers_id) ."'");
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'update_default_adressbook', 'default')).'cID='.$customers_id.'&action=address_book'));
        break;
    case 'statusconfirm' :
      $customers_id = xtc_db_prepare_input($_GET['cID']);
      $customer_updated = false;
      $check_status_query = xtc_db_query("SELECT customers_firstname,
                                                 customers_lastname,
                                                 customers_email_address,
                                                 customers_status,
                                                 member_flag
                                            FROM ".TABLE_CUSTOMERS."
                                           WHERE customers_id = '".xtc_db_input($_GET['cID'])."'");
      $check_status = xtc_db_fetch_array($check_status_query);
      if ($check_status['customers_status'] != $status) {
        xtc_db_query("UPDATE ".TABLE_CUSTOMERS." SET customers_status = '".xtc_db_input($_POST['status'])."' WHERE customers_id = '".xtc_db_input($_GET['cID'])."'");
        // update customers status in newsletters_recipients
        xtc_db_query("UPDATE ".TABLE_NEWSLETTER_RECIPIENTS." SET customers_status = '".xtc_db_input($_POST['status'])."' WHERE customers_id = '".xtc_db_input($_GET['cID'])."'");
        // create insert for admin access table if customers status is set to 0
        if ($_POST['status'] == 0) {
          xtc_db_query("INSERT INTO  ".TABLE_ADMIN_ACCESS." (customers_id,start) VALUES ('".xtc_db_input($_GET['cID'])."','1')");
        } else {
          xtc_db_query("DELETE FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".xtc_db_input($_GET['cID'])."'");
        }
        //Temporarily set due to above commented lines
        $customer_notified = '0';
        xtc_db_query("INSERT INTO  ".TABLE_CUSTOMERS_STATUS_HISTORY." (customers_id, new_value, old_value, date_added, customer_notified) VALUES ('".xtc_db_input($_GET['cID'])."', '".xtc_db_input($_POST['status'])."', '".$check_status['customers_status']."', now(), '".$customer_notified."')");
        $customer_updated = true;
      }
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, 'page='.(int)$_GET['page'].'&cID='.(int)$_GET['cID']));
      break;
    case 'update' :
      $customers_id = xtc_db_prepare_input($_GET['cID']);
      $customers_cid = xtc_db_prepare_input($_POST['csID']);
      $customers_vat_id = xtc_db_prepare_input($_POST['customers_vat_id']);
      $customers_vat_id_status = (isset($_POST['customers_vat_id_status']) ? xtc_db_prepare_input($_POST['customers_vat_id_status']) : '');
      $customers_firstname = xtc_db_prepare_input($_POST['customers_firstname']);
      $customers_lastname = xtc_db_prepare_input($_POST['customers_lastname']);
      $customers_email_address = xtc_db_prepare_input($_POST['customers_email_address']);
      $customers_telephone = xtc_db_prepare_input($_POST['customers_telephone']);
      $customers_fax = xtc_db_prepare_input($_POST['customers_fax']);
      $customers_newsletter = (isset($_POST['customers_newsletter']) ? xtc_db_prepare_input($_POST['customers_newsletter']) : '');
      $customers_gender = xtc_db_prepare_input($_POST['customers_gender']);
      $customers_dob = xtc_db_prepare_input($_POST['customers_dob']);
      $default_address_id = xtc_db_prepare_input($_POST['default_address_id']);
      $address_book_id = xtc_db_prepare_input($_POST['address_book_id']);
      $entry_street_address = xtc_db_prepare_input($_POST['entry_street_address']);
      $entry_suburb = xtc_db_prepare_input($_POST['entry_suburb']);
      $entry_postcode = xtc_db_prepare_input($_POST['entry_postcode']);
      $entry_city = xtc_db_prepare_input($_POST['entry_city']);
      $entry_country_id = xtc_db_prepare_input($_POST['entry_country_id']);
      $entry_company = xtc_db_prepare_input($_POST['entry_company']);
      $entry_state = (isset($_POST['entry_state']) ? xtc_db_prepare_input($_POST['entry_state']) : '');
      $entry_zone_id = (isset($_POST['entry_zone_id']) ? xtc_db_prepare_input($_POST['entry_zone_id']) : '');
      $memo_title = xtc_db_prepare_input($_POST['memo_title']);
      $memo_text = xtc_db_prepare_input($_POST['memo_text']);
      $payment_unallowed = xtc_db_prepare_input($_POST['payment_unallowed']);
      $shipping_unallowed = xtc_db_prepare_input($_POST['shipping_unallowed']);
      $password = xtc_db_prepare_input($_POST['entry_password']);
      if ($memo_text != '' && $memo_title != '') {
        $sql_data_array = array ('customers_id' => (int)$_GET['cID'], 
                                 'memo_date' => date("Y-m-d"), 
                                 'memo_title' => $memo_title, 
                                 'memo_text' => $memo_text, 
                                 'poster_id' => (int)$_SESSION['customer_id']
                                );
        xtc_db_perform(TABLE_CUSTOMERS_MEMO, $sql_data_array);
      }
      $error = false; // reset error flag

      if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $entry_firstname_error = true;
      } else {
        $entry_firstname_error = false;
      }

      if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;
        $entry_lastname_error = true;
      } else {
        $entry_lastname_error = false;
      }

      //BOF - DokuMan - 2011-08-26 - error flag for $entry_gender_error was missing
      if (ACCOUNT_GENDER == 'true') {
        if (($customers_gender != 'm') && ($customers_gender != 'f')) {
          $error = true;
          $entry_gender_error = true;
        } else {
          $entry_gender_error = false;
        }
      }
      //EOF - DokuMan - 2011-08-26 - error flag for $entry_gender_error was missing

      if (ACCOUNT_DOB == 'true') {
        if (checkdate(substr(xtc_date_raw($customers_dob), 4, 2), substr(xtc_date_raw($customers_dob), 6, 2), substr(xtc_date_raw($customers_dob), 0, 4))) {
          $entry_date_of_birth_error = false;
        } else {
          $error = true;
          $entry_date_of_birth_error = true;
        }
      }

    // New VAT Check
      if (xtc_get_geo_zone_code($entry_country_id) != '6') {
        require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'vat_validation.php');
        $vatID = new vat_validation($customers_vat_id, $customers_id, '', $entry_country_id);
        $customers_vat_id_status = isset($vatID->vat_info['vat_id_status']) ? $vatID->vat_info['vat_id_status'] : '';

        // BOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
        switch ($customers_vat_id_status) {
          // 0 = 'VAT invalid'
          // 1 = 'VAT valid'
          // 2 = 'SOAP ERROR: Connection to host not possible, europe.eu down?'
          // 8 = 'unknown country'
          //94 = 'INVALID_INPUT'       => 'The provided CountryCode is invalid or the VAT number is empty',
          //95 = 'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later',
          //96 = 'MS_UNAVAILABLE'      => 'The Member State service is unavailable, try again later or with another Member State',
          //97 = 'TIMEOUT'             => 'The Member State service could not be reached in time, try again later or with another Member State',
          //98 = 'SERVER_BUSY'         => 'The service cannot process your request. Try again later.'
          //99 = 'no PHP5 SOAP support'
          case '0' :
            $entry_vat_error_text = TEXT_VAT_FALSE;
            break;
          case '1' :
            $entry_vat_error_text = TEXT_VAT_TRUE;
            break;
          case '2' :
            $entry_vat_error_text = TEXT_VAT_CONNECTION_NOT_POSSIBLE;
            break;
          case '8' :
            $entry_vat_error_text = TEXT_VAT_UNKNOWN_COUNTRY;
            break;
          case '94' :
            $entry_vat_error_text = TEXT_VAT_INVALID_INPUT;
            break;
          case '95' :
            $entry_vat_error_text = TEXT_VAT_SERVICE_UNAVAILABLE;
            break;
          case '96' :
            $entry_vat_error_text = TEXT_VAT_MS_UNAVAILABLE;
            break;
          case '97' :
            $entry_vat_error_text = TEXT_VAT_TIMEOUT;
            break;
          case '98' :
            $entry_vat_error_text = TEXT_VAT_SERVER_BUSY;
            break;
          case '99' :
            $entry_vat_error_text = TEXT_VAT_NO_PHP5_SOAP_SUPPORT;
            break;
          default:
            $entry_vat_error_text = '';
            break;
        }
        // EOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check

        if($vatID->vat_info['error']==1){
          $entry_vat_error = true;
          $error = true;
        }
      }
      // New VAT CHECK END

      if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
        $error = true;
        $entry_email_address_error = true;
      } else {
        $entry_email_address_error = false;
      }

      if (!xtc_validate_email($customers_email_address)) {
        $error = true;
        $entry_email_address_check_error = true;
      } else {
        $entry_email_address_check_error = false;
      }

      if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;
        $entry_street_address_error = true;
      } else {
        $entry_street_address_error = false;
      }

      if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;
        $entry_post_code_error = true;
      } else {
        $entry_post_code_error = false;
      }

      if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;
        $entry_city_error = true;
      } else {
        $entry_city_error = false;
      }

      if ($entry_country_id == false) {
        $error = true;
        $entry_country_error = true;
      } else {
        $entry_country_error = false;
      }

      if (ACCOUNT_STATE == 'true') {
        if ($entry_country_error == true) {
          $entry_state_error = true;
        } else {
          $zone_id = 0;
          $entry_state_error = false;
          $check_query = xtc_db_query("SELECT count(*) as total FROM ".TABLE_ZONES." WHERE zone_country_id = '".xtc_db_input($entry_country_id)."'");
          $check_value = xtc_db_fetch_array($check_query);
          $entry_state_has_zones = ($check_value['total'] > 0);
          if ($entry_state_has_zones == true) {
            $zone_query = xtc_db_query("SELECT zone_id FROM ".TABLE_ZONES." WHERE zone_country_id = '".xtc_db_input($entry_country_id)."' AND zone_name = '".xtc_db_input($entry_state)."'");
            if (xtc_db_num_rows($zone_query) == 1) {
              $zone_values = xtc_db_fetch_array($zone_query);
              $entry_zone_id = $zone_values['zone_id'];
            } else {
              $zone_query = xtc_db_query("SELECT zone_id FROM ".TABLE_ZONES." WHERE zone_country_id = '".xtc_db_input($entry_country)."' AND zone_code = '".xtc_db_input($entry_state)."'");
              if (xtc_db_num_rows($zone_query) >= 1) {
                $zone_values = xtc_db_fetch_array($zone_query);
                $zone_id = $zone_values['zone_id'];
              } else {
                $error = true;
                $entry_state_error = true;
              }
            }
          } else {
            if ($entry_state == false) {
              $error = true;
              $entry_state_error = true;
            }
          }
        }
      }

      if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $entry_telephone_error = true;
      } else {
        $entry_telephone_error = false;
      }

      // BOF - DokuMan - 2009-05-22 - Bugfix #0000218 - force to enter password when editing users
            if (strlen($password) > 0 && strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $entry_password_error = true;
      } else {
        $entry_password_error = false;
      }
      // EOF - DokuMan - 2009-05-22 - Bugfix #0000218 - force to enter password when editing users

      $check_email = xtc_db_query("SELECT customers_email_address
                                    FROM ".TABLE_CUSTOMERS."
                                   WHERE customers_email_address = '".xtc_db_input($customers_email_address)."'
                                     AND customers_id <> '".xtc_db_input($customers_id)."'");
      if (xtc_db_num_rows($check_email)) {
        $error = true;
        $entry_email_address_exists = true;
      } else {
        $entry_email_address_exists = false;
      }

      if ($error == false) {
        $sql_data_array = array (
                                  'customers_firstname' => $customers_firstname,
                                  'customers_cid' => $customers_cid,
                                  'customers_vat_id' => $customers_vat_id,
                                  'customers_vat_id_status' => $customers_vat_id_status,
                                  'customers_lastname' => $customers_lastname,
                                  'customers_email_address' => $customers_email_address,
                                  'customers_telephone' => $customers_telephone,
                                  'customers_fax' => $customers_fax,
                                  'payment_unallowed' => $payment_unallowed,
                                  'shipping_unallowed' => $shipping_unallowed,
                                  'customers_newsletter' => $customers_newsletter,
                                  'customers_last_modified' => 'now()'
                                  );

        // if new password is set
        if ($password != "") {
          $sql_data_array['customers_password'] = xtc_encrypt_password($password);          
        }

        if (ACCOUNT_GENDER == 'true')
          $sql_data_array['customers_gender'] = $customers_gender;
        if (ACCOUNT_DOB == 'true')
          $sql_data_array['customers_dob'] = xtc_date_raw($customers_dob);

        //xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".xtc_db_input($customers_id)."'");
        xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".xtc_db_input($customers_id)."' AND customers_default_address_id = '".$address_book_id."'");

          xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO."
                           SET customers_info_date_account_last_modified = now()
                         WHERE customers_info_id = '".xtc_db_input($customers_id)."'");

        if ($entry_zone_id > 0)
          $entry_state = '';

        $sql_data_array = array (
          'entry_firstname' => $customers_firstname,
          'entry_lastname' => $customers_lastname,
          'entry_street_address' => $entry_street_address,
          'entry_postcode' => $entry_postcode,
          'entry_city' => $entry_city,
          'entry_country_id' => $entry_country_id,
          'address_last_modified' => 'now()'
          );


          
        if (ACCOUNT_GENDER == 'true')
          $sql_data_array['entry_gender'] = $customers_gender;

        if (ACCOUNT_COMPANY == 'true')
          $sql_data_array['entry_company'] = $entry_company;

        if (ACCOUNT_SUBURB == 'true')
          $sql_data_array['entry_suburb'] = $entry_suburb;

        if (ACCOUNT_STATE == 'true') {
          if ($entry_zone_id > 0) {
            $sql_data_array['entry_zone_id'] = $entry_zone_id;
            $sql_data_array['entry_state'] = '';
          } else {
            $sql_data_array['entry_zone_id'] = '0';
            $sql_data_array['entry_state'] = $entry_state;
          }
        }
        if ($address_book_id == 0) {
          $sql_data_array['address_date_added'] = 'now()';
          $sql_data_array['customers_id'] = $customers_id;
          xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'insert');
        } else {
          //xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '".xtc_db_input($customers_id)."' AND address_book_id = '".xtc_db_input($default_address_id)."'");
          xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '".xtc_db_input($customers_id)."' AND address_book_id = '".xtc_db_input($address_book_id)."'");
        }   
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.(int)$customers_id));
      }  elseif ($error == true) {
        $cInfo = new objectInfo($_POST);
        $processed = true;
      }
      break;
    case 'deleteconfirm' :
      $customers_id = xtc_db_prepare_input($_GET['cID']);

      if ($_POST['delete_reviews'] == 'on') {
        $reviews_query = xtc_db_query("SELECT reviews_id FROM ".TABLE_REVIEWS." WHERE customers_id = '".xtc_db_input($customers_id)."'");
        while ($reviews = xtc_db_fetch_array($reviews_query)) {
          xtc_db_query("DELETE FROM ".TABLE_REVIEWS_DESCRIPTION." WHERE reviews_id = '".$reviews['reviews_id']."'");
        }
        xtc_db_query("DELETE FROM ".TABLE_REVIEWS." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      } else {
        xtc_db_query("UPDATE ".TABLE_REVIEWS." SET customers_id = null WHERE customers_id = '".xtc_db_input($customers_id)."'");
      }

      xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_NOTIFICATIONS." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_WHOS_ONLINE." WHERE customer_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_STATUS_HISTORY." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".xtc_db_input($customers_id)."'");
      xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS." WHERE customers_id = '".xtc_db_input($customers_id)."'"); // DokuMan - 2011-04-15 - also delete the newsletter entry of the customer
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action'))));
      break;
    default :
      $customers_query = xtc_db_query("
      -- admin/customers.php
      SELECT c.customers_id,
             c.customers_cid,
             c.customers_vat_id,
             c.customers_status, # DokuMan 2011-12-13 - Added missing customers_status
             c.customers_gender,
             c.customers_firstname,
             c.customers_lastname,
             c.customers_dob,
             c.customers_email_address,
             c.customers_default_address_id,
             c.customers_telephone,
             c.customers_fax,
             c.customers_newsletter,
             c.payment_unallowed, # Tomcraft 2011-03-18 - Added missing payment_unallowed
             c.shipping_unallowed, # Tomcraft 2011-03-18 - Added missing payment_unallowed
             a.entry_company,
             a.entry_street_address,
             a.entry_suburb,
             a.entry_postcode,
             a.entry_city,
             a.entry_state,
             a.entry_country_id,
             a.entry_zone_id
        FROM ".TABLE_CUSTOMERS." c
   LEFT JOIN ".TABLE_ADDRESS_BOOK." a
          ON c.customers_default_address_id = a.address_book_id
       WHERE a.customers_id = c.customers_id
         AND c.customers_id = ".(int)$_GET['cID']
         );
      $customers = xtc_db_fetch_array($customers_query);
      $cInfo = new objectInfo($customers);
  }
}
require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<?php
if ($action == 'edit' || $action == 'update') {
?>
<script type="text/javascript">
<!--
function check_form() {
  var error = 0;
  var error_message = "<?php echo xtc_js_lang(JS_ERROR); ?>";
  var customers_firstname = document.customers.customers_firstname.value;
  var customers_lastname = document.customers.customers_lastname.value;
  <?php
    if (ACCOUNT_COMPANY == 'true')
      echo 'var entry_company = document.customers.entry_company.value;' . "\n";
  ?>
  <?php
    if (ACCOUNT_DOB == 'true')
      echo 'var customers_dob = document.customers.customers_dob.value;' . "\n";
  ?>
  var customers_email_address = document.customers.customers_email_address.value;
  var entry_street_address = document.customers.entry_street_address.value;
  var entry_postcode = document.customers.entry_postcode.value;
  var entry_city = document.customers.entry_city.value;
  var customers_telephone = document.customers.customers_telephone.value;
  <?php
    if (ACCOUNT_GENDER == 'true') { ?>
      if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
      } else {
        error_message = error_message + "<?php echo xtc_js_lang(JS_GENDER); ?>";
        error = 1;
      }
      <?php
    }
  ?>

  if (customers_firstname == "" || customers_firstname.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_FIRST_NAME); ?>";
    error = 1;
  }

  if (customers_lastname == "" || customers_lastname.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_LAST_NAME); ?>";
    error = 1;
  }

  <?php
    if (ACCOUNT_DOB == 'true') { ?>
      if (customers_dob == "" || customers_dob.length < <?php echo ENTRY_DOB_MIN_LENGTH; ?>) {
        error_message = error_message + "<?php echo xtc_js_lang(JS_DOB); ?>";
        error = 1;
      }
      <?php
    }
  ?>

  if (customers_email_address == "" || customers_email_address.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_EMAIL_ADDRESS); ?>";
    error = 1;
  }

  if (entry_street_address == "" || entry_street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_ADDRESS); ?>";
    error = 1;
  }

  if (entry_postcode == "" || entry_postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_POST_CODE); ?>";
    error = 1;
  }

  if (entry_city == "" || entry_city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_CITY); ?>";
    error = 1;
  }

<?php
  if (ACCOUNT_STATE == 'true') {
?>
  if (document.customers.elements['entry_state'].type != "hidden") {
    if (document.customers.entry_state.value == '' || document.customers.entry_state.value.length < <?php echo ENTRY_STATE_MIN_LENGTH; ?> ) {
       error_message = error_message + "<?php echo xtc_js_lang(JS_STATE); ?>";
       error = 1;
    }
  }
<?php
  }
?>

  if (document.customers.elements['entry_country_id'].type != "hidden") {
    if (document.customers.entry_country_id.value == 0) {
      error_message = error_message + "<?php echo xtc_js_lang(JS_COUNTRY); ?>";
      error = 1;
    }
  }

  if (customers_telephone == "" || customers_telephone.length < <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>) {
    error_message = error_message + "<?php echo xtc_js_lang(JS_TELEPHONE); ?>";
    error = 1;
  }

  if (error == 1) {
    alert(unescape(error_message));
    return false;
  } else {
    return true;
  }
}
//-->
</script>
<?php
}
?>
</head>
<body onLoad="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
      <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
        <!-- left_navigation //-->
        <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
        <!-- left_navigation_eof //-->
      </table>
    </td>
    <!-- body_text //-->
    <td class="boxCenter" width="100%" valign="top">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <?php
        if ($action == 'edit' || $action == 'update') {
          if (isset($_GET['edit']) && $_GET['edit'] != '') {
            $check = "a.address_book_id = '". (int) $_GET['edit']."'";
            $customers_default_address_id_checkbox = xtc_draw_checkbox_field('primary', 'on', false);
          } else {
            $check = "c.customers_default_address_id = a.address_book_id";
            
          }

            //if (!is_object($cInfo)) { //DokuMan - 2010-10-01 - remove check if $cinfo is an object, otherwise customer status will be blank
            $customers_query = xtc_db_query("-- admin/customers.php
                                             SELECT c.customers_id,
                                                    c.customers_cid,
                                                    c.customers_vat_id,
                                                    c.customers_vat_id_status,
                                                    c.customers_status,
                                                    -- c.customers_gender, # web28 2012-01-06 - wrong use correctly a.entry_gender AS customers_gender
                                                    -- c.customers_firstname, # web28 2012-01-06 - wrong use correctly a.entry_firstname AS customers_firstname
                                                    -- c.customers_lastname, # web28 2012-01-06 - wrong use correctly a.entry_lastname AS customers_lastname
                                                    c.customers_dob,
                                                    c.customers_email_address,
                                                    c.customers_default_address_id,
                                                    c.customers_telephone,
                                                    c.customers_fax,
                                                    c.customers_newsletter,
                                                    c.member_flag,
                                                    c.payment_unallowed,
                                                    c.shipping_unallowed,
                                                    a.address_book_id,
                                                    a.entry_gender AS customers_gender,
                                                    a.entry_firstname AS customers_firstname,
                                                    a.entry_lastname AS customers_lastname,   
                                                    a.entry_company,
                                                    a.entry_street_address,
                                                    a.entry_suburb,
                                                    a.entry_postcode,
                                                    a.entry_city,
                                                    a.entry_state,
                                                    a.entry_country_id,
                                                    a.entry_zone_id
                                               FROM ".TABLE_CUSTOMERS." c
                                          LEFT JOIN ".TABLE_ADDRESS_BOOK." a
                                                 ON ".$check."
                                              WHERE a.customers_id = c.customers_id
                                                AND c.customers_id = '".(int)$_GET['cID']."'"
                                           );
            $customers = xtc_db_fetch_array($customers_query);
            if (xtc_db_num_rows($customers_query) != 0) {
              $cInfo = new objectInfo($customers);              
            }
           //} //DokuMan - 2010-10-01 - remove check if $cinfo is an object, otherwise customer status will be blank
          $newsletter_array = array (array ('id' => '1', 'text' => ENTRY_NEWSLETTER_YES), array ('id' => '0', 'text' => ENTRY_NEWSLETTER_NO));
        ?>
        <tr>
          <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
              <td class="pageHeading"><?php echo $cInfo->customers_lastname.' '.$cInfo->customers_firstname; ?></td>
            </tr>
            <tr>
              <td class="main" valign="top"><?php echo BOX_HEADING_CUSTOMERS; ?></td>
            </tr>
          </table>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td valign="middle" class="pageHeading"><?php if ($customers_statuses_id_array[$customers['customers_status']]['csa_image'] != '') { echo xtc_image(DIR_WS_ICONS . $customers_statuses_id_array[$customers['customers_status']]['csa_image'], ''); } ?></td><?php// web28 - 2011-10-31 - change  $customers_statuses_array  to $customers_statuses_id_array?>
              <td class="main"></td>
              <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            </tr>
            <tr>
              <td colspan="3" class="main"><?php echo HEADING_TITLE_STATUS  .': ' . $customers_statuses_id_array[$customers['customers_status']]['text'] ; ?></td><?php// web28 - 2011-10-31 - change  $customers_statuses_array  to $customers_statuses_id_array?>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <?php echo xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action')) . 'action=update', 'post', 'onSubmit="return check_form();"') . xtc_draw_hidden_field('default_address_id', $cInfo->customers_default_address_id) . xtc_draw_hidden_field('address_book_id', $cInfo->address_book_id); ?>
        <tr>
          <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
        </tr>
        <tr>
          <td class="formArea">
          <table border="0" cellspacing="2" cellpadding="2">
            <?php
              if (ACCOUNT_GENDER == 'true') {
            ?>
            <tr>
              <td class="main"><?php echo ENTRY_GENDER; ?></td>
              <td class="main">
              <?php
              if ($error == true) {
                if ($entry_gender_error == true) {
                  echo xtc_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.FEMALE.'&nbsp;'.ENTRY_GENDER_ERROR;
                } else {
                  echo ($cInfo->customers_gender == 'm') ? MALE : FEMALE;
                  echo xtc_draw_hidden_field('customers_gender');
                }
              } else {
                echo xtc_draw_radio_field('customers_gender', 'm', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.MALE.'&nbsp;&nbsp;'.xtc_draw_radio_field('customers_gender', 'f', false, $cInfo->customers_gender).'&nbsp;&nbsp;'.FEMALE;
              }
              ?>
              </td>
            </tr>
            <?php
              }
            echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>'; 
            ?>  
              <td class="main" bgcolor="#FFCC33"><?php echo ENTRY_CID; ?></td>
              <td class="main" width="100%" bgcolor="#FFCC33">
                <?php
                echo xtc_draw_input_field('csID', $cInfo->customers_cid, 'maxlength="32"', false);
                ?>
              </td>
            </tr>
            <tr>
              <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
              <td class="main">
                <?php
                //BOF - DokuMan - 2010-11-01 - enhance eror-reporting on firstname
                if ($error == true) {
                  if ($entry_firstname_error == true) {
                    echo xtc_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"').'&nbsp;'.ENTRY_FIRST_NAME_ERROR;
                  } else {
                    echo $cInfo->customers_lastname.xtc_draw_hidden_field('customers_firstname');
                  }
                } else {
                  echo xtc_draw_input_field('customers_firstname', $cInfo->customers_firstname, 'maxlength="32"', true);
                }
                //EOF - DokuMan - 2010-11-01 - enhance eror-reporting on firstname
                ?>
              </td>
            </tr>
            <tr>
              <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_lastname_error == true) {
                    echo xtc_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"').'&nbsp;'.ENTRY_LAST_NAME_ERROR;
                  } else {
                    echo $cInfo->customers_lastname.xtc_draw_hidden_field('customers_lastname');
                  }
                } else {
                  echo xtc_draw_input_field('customers_lastname', $cInfo->customers_lastname, 'maxlength="32"', true);
                }
                ?>
              </td>
            </tr>
            <?php
            if (ACCOUNT_DOB == 'true') {
              echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>';              
            ?>           
              <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
              <td class="main">
                 <?php
                if ($error == true) {
                  if ($entry_date_of_birth_error == true) {
                    echo xtc_draw_input_field('customers_dob', xtc_date_short($cInfo->customers_dob), 'maxlength="10"').'&nbsp;'.ENTRY_DATE_OF_BIRTH_ERROR;
                  } else {
                    echo $cInfo->customers_dob.xtc_draw_hidden_field('customers_dob');
                  }
                } else {
                  echo xtc_draw_input_field('customers_dob', xtc_date_short($cInfo->customers_dob), 'maxlength="10"', true);
                }
                ?>
              </td>
            </tr>
            <?php
            }
             echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>'; 
            ?>  
              <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_email_address_error == true) {
                    echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR;
                  } elseif ($entry_email_address_check_error == true) {
                    echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_CHECK_ERROR;
                  } elseif ($entry_email_address_exists == true) {
                    echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"').'&nbsp;'.ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
                  } else {
                    echo $customers_email_address.xtc_draw_hidden_field('customers_email_address');
                  }
                } else {
                  echo xtc_draw_input_field('customers_email_address', $cInfo->customers_email_address, 'maxlength="96"', true);
                }
                ?>
            </td>
            </tr>
          </table>
          </td>
        </tr>
        <?php
          if (ACCOUNT_COMPANY == 'true') {
        ?>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
          <td class="formAreaTitle"><?php echo CATEGORY_COMPANY; ?></td>
        </tr>
        <tr>
          <td class="formArea">
          <table border="0" cellspacing="2" cellpadding="2">
            <tr>
              <td class="main"><?php echo ENTRY_COMPANY; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_company_error == true) {
                    echo xtc_draw_input_field('entry_company', $cInfo->entry_company, 'maxlength="64"').'&nbsp;'.ENTRY_COMPANY_ERROR;
                  } else {
                    echo $cInfo->entry_company.xtc_draw_hidden_field('entry_company');
                  }
                } else {
                  echo xtc_draw_input_field('entry_company', $cInfo->entry_company, 'maxlength="64"');
                }
                ?>
              </td>
            </tr>
            <?php
            if(ACCOUNT_COMPANY_VAT_CHECK == 'true'){
              // BOF - Dokuman - 2011-08-26 - show error code of VAT ID check FROM DB (only in 'edit' process, not in 'update')  //web28 - 2012-04-08 - and only when customers_vat_id is not empty
              if ($action == 'edit' && $cInfo->customers_vat_id != '') {
                // BOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
                switch ($cInfo->customers_vat_id_status) {
                  case '0' :
                    $entry_vat_error_text = TEXT_VAT_FALSE;
                    break;
                  case '1' :
                    $entry_vat_error_text = TEXT_VAT_TRUE;
                    break;
                  case '8' :
                    $entry_vat_error_text = TEXT_VAT_UNKNOWN_COUNTRY;
                    break;
                  case '94' :
                    $entry_vat_error_text = TEXT_VAT_INVALID_INPUT;
                    break;
                  case '95' :
                    $entry_vat_error_text = TEXT_VAT_SERVICE_UNAVAILABLE;
                    break;
                  case '96' :
                    $entry_vat_error_text = TEXT_VAT_MS_UNAVAILABLE;
                    break;
                  case '97' :
                    $entry_vat_error_text = TEXT_VAT_TIMEOUT;
                    break;
                  case '98' :
                    $entry_vat_error_text = TEXT_VAT_SERVER_BUSY;
                    break;
                  case '99' :
                    $entry_vat_error_text = TEXT_VAT_NO_PHP5_SOAP_SUPPORT;
                    break;
                }
                // EOF - Dokuman - 2011-09-13 - display correct error code of VAT ID check
              }
              // EOF - Dokuman - 2011-08-26 - show error code of VAT ID check FROM DB (only in 'edit' process, not in 'update')
              echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>';
              ?>
                <td class="main"><?php echo ENTRY_VAT_ID; ?></td>
                <td class="main">
                  <?php
                    // BOF - Dokuman - 2011-07-28 - display correct error code of VAT ID check
                    echo xtc_draw_input_field('customers_vat_id', $cInfo->customers_vat_id, 'maxlength="32"').'&nbsp;'.$entry_vat_error_text;
                    /*
                                if ($error == true) {
                                  if ($entry_vat_error == true) {
                                    echo xtc_draw_input_field('customers_vat_id', $cInfo->customers_vat_id, 'maxlength="32"').'&nbsp;'.$entry_vat_error_text;
                                  } else {
                                    echo $cInfo->customers_vat_id.xtc_draw_hidden_field('customers_vat_id');
                                  }
                                } else {
                                  echo xtc_draw_input_field('customers_vat_id', $cInfo->customers_vat_id, 'maxlength="32"');
                                }
                                */
                                // EOF - Dokuman - 2011-07-28 - display correct error code of VAT ID check
                                ?>
                  </td>
                </tr>
              <?php
              }
              ?>
            </table>
          </td>
        </tr>
        <?php
          }
        ?>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
          <td class="formAreaTitle"><?php echo CATEGORY_ADDRESS; ?></td>
        </tr>
        <tr>
          <td class="formArea">
          <table border="0" cellspacing="2" cellpadding="2">
            <tr>
              <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_street_address_error == true) {
                    echo xtc_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"').'&nbsp;'.ENTRY_STREET_ADDRESS_ERROR;
                  } else {
                    echo $cInfo->entry_street_address.xtc_draw_hidden_field('entry_street_address');
                  }
                } else {
                  echo xtc_draw_input_field('entry_street_address', $cInfo->entry_street_address, 'maxlength="64"', true);
                }
                ?>
              </td>
            </tr>
            <?php
              if (ACCOUNT_SUBURB == 'true') {
            ?>
            <tr>
              <td class="main"><?php echo ENTRY_SUBURB; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_suburb_error == true) {
                    echo xtc_draw_input_field('suburb', $cInfo->entry_suburb, 'maxlength="32"').'&nbsp;'.ENTRY_SUBURB_ERROR;
                  } else {
                    echo $cInfo->entry_suburb.xtc_draw_hidden_field('entry_suburb');
                  }
                } else {
                  echo xtc_draw_input_field('entry_suburb', $cInfo->entry_suburb, 'maxlength="32"');
                }
                ?>
              </td>
            </tr>
            <?php
              }
            ?>
            <tr>
              <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_post_code_error == true) {
                    echo xtc_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"').'&nbsp;'.ENTRY_POST_CODE_ERROR;
                  } else {
                    echo $cInfo->entry_postcode.xtc_draw_hidden_field('entry_postcode');
                  }
                } else {
                  echo xtc_draw_input_field('entry_postcode', $cInfo->entry_postcode, 'maxlength="8"', true);
                }
              ?>
              </td>
            </tr>
            <tr>
              <td class="main"><?php echo ENTRY_CITY; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_city_error == true) {
                    echo xtc_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"').'&nbsp;'.ENTRY_CITY_ERROR;
                  } else {
                    echo $cInfo->entry_city.xtc_draw_hidden_field('entry_city');
                  }
                } else {
                  echo xtc_draw_input_field('entry_city', $cInfo->entry_city, 'maxlength="32"', true);
                }
                ?>
              </td>
            </tr>
            <?php
              if (ACCOUNT_STATE == 'true') {
            ?>
            <tr>
              <td class="main"><?php echo ENTRY_STATE; ?></td>
              <td class="main">
                <?php
                $entry_state = xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state);
                if ($error == true) {
                  if ($entry_state_error == true) {
                    if ($entry_state_has_zones == true) {
                      $zones_array = array ();
                      $zones_query = xtc_db_query("SELECT zone_name FROM ".TABLE_ZONES." WHERE zone_country_id = '".xtc_db_input($cInfo->entry_country_id)."' order by zone_name");
                      while ($zones_values = xtc_db_fetch_array($zones_query)) {
                        $zones_array[] = array ('id' => $zones_values['zone_name'], 'text' => $zones_values['zone_name']);
                      }
                      echo xtc_draw_pull_down_menu('entry_state', $zones_array).'&nbsp;'.ENTRY_STATE_ERROR;
                    } else {
                      echo xtc_draw_input_field('entry_state', xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state)).'&nbsp;'.ENTRY_STATE_ERROR;
                    }
                  } else {
                    echo $entry_state.xtc_draw_hidden_field('entry_zone_id').xtc_draw_hidden_field('entry_state');
                  }
                } else {
                  echo xtc_draw_input_field('entry_state', xtc_get_zone_name($cInfo->entry_country_id, $cInfo->entry_zone_id, $cInfo->entry_state));
                }
                ?>
              </td>
           </tr>
            <?php
              }
            ?>
            <tr>
              <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
              <td class="main">
                <?php
                if ($error == true) {
                  if ($entry_country_error == true) {
                    echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries('',1), $cInfo->entry_country_id).'&nbsp;'.ENTRY_COUNTRY_ERROR; //Web28 - 2012-04-17 - NEW: show only active language
                  } else {
                    echo xtc_get_country_name($cInfo->entry_country_id).xtc_draw_hidden_field('entry_country_id');
                  }
                } else {
                  echo xtc_draw_pull_down_menu('entry_country_id', xtc_get_countries('',1), $cInfo->entry_country_id); //Web28 - 2012-04-17 - NEW: show only active language
                }
                ?>
              </td>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <?php
        if ($cInfo->customers_default_address_id == $cInfo->address_book_id) {
        ?>
        <tr>
          <td class="formAreaTitle"><?php echo CATEGORY_CONTACT; ?></td>
        </tr>
        <?php
        }
        echo ($cInfo->customers_default_address_id != $cInfo->address_book_id) ? '<tr style="display:none;">' : '<tr>'; 
        ?>        
          <td class="formArea">
          <table border="0" cellspacing="2" cellpadding="2">
            <tr>
              <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
              <td class="main">
              <?php
                if ($error == true) {
                  if ($entry_telephone_error == true) {
                    echo xtc_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"').'&nbsp;'.ENTRY_TELEPHONE_NUMBER_ERROR;
                  } else {
                    echo $cInfo->customers_telephone.xtc_draw_hidden_field('customers_telephone');
                  }
                } else {
                  echo xtc_draw_input_field('customers_telephone', $cInfo->customers_telephone, 'maxlength="32"', true);
                }
              ?>
              </td>
            </tr>
            <tr>
              <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
              <td class="main">
              <?php
                if ($processed == true) {
                  echo $cInfo->customers_fax.xtc_draw_hidden_field('customers_fax');
                } else {
                  echo xtc_draw_input_field('customers_fax', $cInfo->customers_fax, 'maxlength="32"');
                }
              ?>
              </td>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <?php
        if ($cInfo->customers_default_address_id == $cInfo->address_book_id) {
        ?>
        <tr>
          <td class="formAreaTitle"><?php echo CATEGORY_OPTIONS; ?></td>
        </tr>
        <tr>
          <td class="formArea">
          <table border="0" cellspacing="2" cellpadding="2">
                    <tr>
              <td class="main"><?php echo ENTRY_PAYMENT_UNALLOWED; ?></td>
              <td class="main">
              <?php
                if ($processed == true) {
                  echo $cInfo->payment_unallowed.xtc_draw_hidden_field('payment_unallowed');
                } else {
                  echo xtc_draw_input_field('payment_unallowed', $cInfo->payment_unallowed, 'maxlength="255"');
                }
              ?>
              </td>
            </tr>
            <tr>
              <td class="main"><?php echo ENTRY_SHIPPING_UNALLOWED; ?></td>
              <td class="main">
              <?php
                if ($processed == true) {
                  echo $cInfo->shipping_unallowed.xtc_draw_hidden_field('shipping_unallowed');
                } else {
                  echo xtc_draw_input_field('shipping_unallowed', $cInfo->shipping_unallowed, 'maxlength="255"');
                }
              ?>
              </td>
           </tr>
           <tr>
              <td class="main" bgcolor="#FFCC33"><?php echo ENTRY_NEW_PASSWORD; ?></td>
              <td class="main" bgcolor="#FFCC33">
              <?php
                if ($error == true) {
                  if ($entry_password_error == true) {
                    echo xtc_draw_input_field('entry_password', $customers_password).'&nbsp;'.ENTRY_PASSWORD_ERROR;
                  } else {
                    echo xtc_draw_input_field('entry_password');
                  }
                } else {
                  echo xtc_draw_input_field('entry_password');
                }
                ?>
              </td>
              <?php
                 // BOF - Christian - 2009-06-26 - delete Newsletter Funktion...
                  /*
                        <tr>
                          <td class="main"><?php echo ENTRY_NEWSLETTER; ?></td>
                          <td class="main">
                          <?php
                if ($processed == true) {
                  if ($cInfo->customers_newsletter == '1') {
                    echo ENTRY_NEWSLETTER_YES;
                  } else {
                    echo ENTRY_NEWSLETTER_NO;
                  }
                  echo xtc_draw_hidden_field('customers_newsletter');
                } else {
                  echo xtc_draw_pull_down_menu('customers_newsletter', $newsletter_array, $cInfo->customers_newsletter);
                }
              ?>
              </td>
                        </tr>
                        */
              // EOF - Christian - 2009-06-26 - delete Newsletter Funktion...
           ?>
           </tr>
            <tr>
           <?php include(DIR_WS_MODULES . FILENAME_CUSTOMER_MEMO); ?>
            </tr>
          </table>
          </td>
        </tr>
        <?php
        }
        ?>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
          <td align="right" class="main"><input type="submit" class="button" onclick="this.blur();" value="<?php echo BUTTON_UPDATE; ?>"><?php echo ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action'))) .'">' . BUTTON_CANCEL . '</a>'; ?></td>
        </tr>
        </form>
        <?php
        } else {
        ?>
        <tr>
          <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
              <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            </tr>
            <tr>
              <td class="main" valign="top"><?php echo BOX_HEADING_CUSTOMERS; ?></td>
            </tr>
          </table>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
            <?php echo xtc_draw_form('search', FILENAME_CUSTOMERS, '', 'get'); ?>
              <td class="pageHeading"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CREATE_ACCOUNT) . '">' . BUTTON_CREATE_ACCOUNT . '</a>'; ?></td>
              <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
              <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . xtc_draw_input_field('search').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); ?></td>
            </form>
            </tr>
            <tr>
            <?php echo xtc_draw_form('status', FILENAME_CUSTOMERS, '', 'get');
            $select_data = array ();
              //BOF - GTB - 2011-02-03 - show selected customer group
              //$select_data = array (array ('id' => '99', 'text' => TEXT_SELECT), array ('id' => '100', 'text' => TEXT_ALL_CUSTOMERS));
              $select_data = array (array ('id' => '', 'text' => TEXT_SELECT), array ('id' => '100', 'text' => TEXT_ALL_CUSTOMERS));
              //<td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status',xtc_array_merge($select_data, $customers_statuses_array), '99', 'onChange="this.form.submit();"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); </td>
              //EOF - GTB - 2011-02-03 - show selected customer group
              ?>
              <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status',xtc_array_merge($select_data, $customers_statuses_array), isset($_GET['status']) ? $_GET['status'] : '', 'onChange="this.form.submit();"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); ?></td>
             </form>
            </tr>
          </table>
          </td>
        </tr>
        <tr>
          <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent" width="40"><?php echo TABLE_HEADING_ACCOUNT_TYPE; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERSCID.xtc_sorting(FILENAME_CUSTOMERS,'customers_cid'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME.xtc_sorting(FILENAME_CUSTOMERS,'customers_lastname'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME.xtc_sorting(FILENAME_CUSTOMERS,'customers_firstname'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_EMAIL.xtc_sorting(FILENAME_CUSTOMERS,'customers_email_address'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TEXT_INFO_COUNTRY.xtc_sorting(FILENAME_CUSTOMERS,'customers_country'); ?></td>
                   <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_UMSATZ; ?></td>
                  <td class="dataTableHeadingContent" align="left"><?php echo HEADING_TITLE_STATUS; ?></td>
                  <?php
                  if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
                  ?>
                  <td class="dataTableHeadingContent" align="left"><?php echo HEADING_TITLE_VAT; ?></td>
                  <?php
                  }
                  ?>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACCOUNT_CREATED.xtc_sorting(FILENAME_CUSTOMERS,'date_account_created'); ?></td>
                  <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                $search = '';
                if (isset($_GET['search']) && (xtc_not_null($_GET['search']))) {
                  $keywords = xtc_db_input(xtc_db_prepare_input($_GET['search']));
                  $search = "AND (c.customers_lastname LIKE '%".$keywords."%' 
                                  OR c.customers_firstname LIKE '%".$keywords."%' 
                                  OR c.customers_email_address LIKE '%".$keywords."%'
                                 )";
                  //BOF - web28 - 2010-05-29 added for ADMIN SEARCH BAR
                  if(isset($_GET['asb']) && $_GET['asb'] == 'asb') {
                    $search = "AND (c.customers_lastname LIKE '%".$keywords."%' 
                                    OR c.customers_firstname LIKE '%".$keywords."%'
                                   )";
                  }
                  //EOF - web28 - 2010-05-29 added for ADMIN SEARCH BAR
                }
                //BOF - web28 - 2010-05-29 added for ADMIN SEARCH BAR
                if (isset($_GET['search_email']) && (xtc_not_null($_GET['search_email']))) {
                  $keywords = xtc_db_input(xtc_db_prepare_input($_GET['search_email']));
                  $search = "AND (c.customers_email_address LIKE '%".$keywords."%')";
                }
                //EOF - web28 - 2010-05-29 added for ADMIN SEARCH BAR
                if (isset($_GET['status']) && ($_GET['status'] != '100' || $_GET['status'] == '0')) {
                  $status = xtc_db_prepare_input($_GET['status']);
                  $search = "AND c.customers_status = '".$status."'";
                }

                if (isset($_GET['sorting']) && xtc_not_null($_GET['sorting'])) {
                  switch ($_GET['sorting']) {
                    case 'customers_firstname' :
                      $sort = 'order by c.customers_firstname';
                      break;
                    case 'customers_firstname-desc' :
                      $sort = 'order by c.customers_firstname DESC';
                      break;
                    case 'customers_lastname' :
                      $sort = 'order by c.customers_lastname';
                      break;
                    case 'customers_lastname-desc' :
                      $sort = 'order by c.customers_lastname DESC';
                      break;
                    case 'customers_country' :
                      $sort = 'order by a.entry_country_id';
                      break;
                    case 'customers_country-desc' :
                      $sort = 'order by a.entry_country_id DESC';
                      break;
                    case 'date_account_created' :
                      $sort = 'order by ci.customers_info_date_account_created';
                      break;
                    case 'date_account_created-desc' :
                      $sort = 'order by ci.customers_info_date_account_created DESC';
                      break;
                      // BOF - DokuMan - 2012-02-06 - added customers_cid
                    case 'customers_cid' :
                      $sort = 'order by c.customers_cid';
                      break;
                    case 'customers_cid-desc' :
                      $sort = 'order by c.customers_cid DESC';
                      break;
                    case 'customers_email_address-desc' :
                      $sort = 'order by c.customers_email_address DESC';
                      break;
                    case 'customers_email_address' :
                      $sort = 'order by c.customers_email_address';
                      break;
                  }
                } else {
                  $sort = 'order by ci.customers_info_date_account_created DESC'; // vr - 2010-02-22 - default sort order
                }

                // BOF - vr - 2010-02-22 - removed group by part to prevent folding of customers records with the same creation timestamp
                $customers_query_raw = "-- admin/customers.php
                                         SELECT
                                               c.customers_id,
                                               c.customers_cid,
                                               c.customers_vat_id,
                                               c.customers_vat_id_status,
                                               c.customers_status,
                                               c.customers_firstname,
                                               c.customers_lastname,
                                               c.customers_email_address,
                                               c.member_flag,
                                               c.account_type,
                                               a.entry_company,
                                               a.entry_country_id,
                                               ci.customers_info_date_account_created as date_account_created,
                                               ci.customers_info_date_account_last_modified as date_account_last_modified,
                                               ci.customers_info_date_of_last_logon as date_last_logon,
                                               ci.customers_info_number_of_logons as number_of_logons
                                          FROM
                                               ".TABLE_CUSTOMERS." c ,
                                               ".TABLE_ADDRESS_BOOK." a,
                                               ".TABLE_CUSTOMERS_INFO." ci
                                         WHERE c.customers_id = a.customers_id
                                           AND c.customers_default_address_id = a.address_book_id
                                           AND ci.customers_info_id = c.customers_id
                                               ".$search."
                                               ".$sort;
                // EOF - vr - 2010-02-22 - removed group by part to prevent folding of customers records with the same creation timestamp
                $customers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_LIST_CUSTOMERS, $customers_query_raw, $customers_query_numrows);
                $customers_query = xtc_db_query($customers_query_raw);
                while ($customers = xtc_db_fetch_array($customers_query)) {
                  // vr - 2012-10-27 moved info query into raw query
                  // BOF - DokuMan - 2011-09-12 - optimize sql query for customers sales volume - thx to GTB
                  $umsatz_query = xtc_db_query("-- admin/customers.php
                                                SELECT SUM(op.final_price) as ordersum
                                                  FROM ".TABLE_ORDERS_PRODUCTS." op
                                                  JOIN ".TABLE_ORDERS." o ON o.orders_id = op.orders_id
                                                 WHERE '".(int)$customers['customers_id']."' = o.customers_id");
                  $umsatz = xtc_db_fetch_array($umsatz_query);
                  // EOF - DokuMan - 2011-09-12 - optimize sql query for customers sales volume - thx to GTB

                  if ((!isset($_GET['cID']) || (@$_GET['cID'] == $customers['customers_id'])) && !isset($cInfo)) {
                    $country_query = xtc_db_query("SELECT countries_name FROM ".TABLE_COUNTRIES." WHERE countries_id = '".(int)$customers['entry_country_id']."'");
                    $country = xtc_db_fetch_array($country_query);

                    $reviews_query = xtc_db_query("SELECT count(*) as number_of_reviews FROM ".TABLE_REVIEWS." WHERE customers_id = '".(int)$customers['customers_id']."'");
                    $reviews = xtc_db_fetch_array($reviews_query);

                    // vr - 2012-10-27 moved info query into raw query, $info is now part in $customers
                    $customer_info = xtc_array_merge($country, $reviews);

                    $cInfo_array = xtc_array_merge($customers, $customer_info);
                    $cInfo = new objectInfo($cInfo_array);
                  }

                  if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id)) {
                    echo '          <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=edit').'\'">'."\n";
                  } else {
                    echo '          <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID')).'cID='.$customers['customers_id']).'\'">'."\n";
                  }

                  $account_type = ($customers['account_type'] == 1) ? TEXT_GUEST : TEXT_ACCOUNT;
                  ?>
                  </td>
                  <td class="dataTableContent" style="width:40px;"><?php echo $account_type; ?></td>
                  <td class="dataTableContent" style="width:80px;"><?php echo $customers['customers_cid']; ?>&nbsp;</td>
                  <td class="dataTableContent"><?php echo $customers['customers_lastname']; ?></td>
                  <td class="dataTableContent"><?php echo $customers['customers_firstname']; ?></td>
                  <td class="dataTableContent"><?php echo $customers['customers_email_address']; ?></td>
                  <td class="dataTableContent"><?php echo xtc_get_country_name($customers['entry_country_id']); ?></td>
                  <?php
                  if ($umsatz['ordersum'] !='') {
                  ?>
                  <td class="dataTableContent"><?php if ($umsatz['ordersum']>0) { echo $currencies->format($umsatz['ordersum']);} ?></td>
                  <?php
                  } else {
                  ?>
                  <td class="dataTableContent"> --- </td>
                  <?php
                  }
                  ?>
                  <td class="dataTableContent" align="left"><?php echo $customers_statuses_id_array[$customers['customers_status']]['text'] . ' (' . $customers['customers_status'] . ')' ; ?></td><?php// web28 - 2011-10-31 - change  $customers_statuses_array  to $customers_statuses_id_array?>
                  <?php
                  if (ACCOUNT_COMPANY_VAT_CHECK == 'true') {
                    ?>
                    <td class="dataTableContent" align="left">
                      <?php
                      if ($customers['customers_vat_id']) {
                        if (xtc_not_null(xtc_validate_vatid_status($customers['customers_id']))) {
                          echo $customers['customers_vat_id'].'<br /><span style="font-size:8pt"><nobr>('.xtc_validate_vatid_status($customers['customers_id']).')</nobr></span>';
                        } else {
                          echo $customers['customers_vat_id'];
                        }
                      }
                    ?>
                    </td>
                    <?php
                  }
                  ?>
                  <td class="dataTableContent" align="right"><?php echo xtc_date_short($customers['date_account_created']); ?>&nbsp;</td>
                  <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($customers['customers_id'] == $cInfo->customers_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID')) . 'cID=' . $customers['customers_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                  }
                ?>
                <tr>
                  <td colspan="7">
                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td class="smallText" valign="top"><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_LIST_CUSTOMERS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                        <td class="smallText" align="right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_LIST_CUSTOMERS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
                      </tr>
                      <?php
                        if (isset($_GET['search'])) {
                      ?>
                      <tr>
                        <td align="right" colspan="2"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CUSTOMERS) . '">' . BUTTON_RESET . '</a>'; ?></td>
                      </tr>
                      <?php
                        }
                      ?>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
              <?php
                $heading = array ();
                $contents = array ();
                switch ($action) {
                  case 'confirm' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_DELETE_CUSTOMER.'</b>');

                    $contents = array ('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=deleteconfirm'));
                    $contents[] = array ('text' => TEXT_DELETE_INTRO.'<br /><br /><b>'.$cInfo->customers_firstname.' '.$cInfo->customers_lastname.'</b>');
                    if ($cInfo->number_of_reviews > 0)
                      $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('delete_reviews', 'on', true).' '.sprintf(TEXT_DELETE_REVIEWS, $cInfo->number_of_reviews));
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_DELETE.'"><a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                    break;

                  case 'address_book' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_ADRESS_BOOK.'</b>');

                    $contents = array ();
                    require_once (DIR_FS_INC.'xtc_get_address_format_id.inc.php');
                    require_once (DIR_FS_INC.'xtc_count_customer_address_book_entries.inc.php');
                    
                    $addresses_query = xtc_db_query("-- admin/customers.php
                                                     select address_book_id,
                                                            entry_firstname as firstname,
                                                            entry_lastname as lastname,
                                                            entry_company as company,
                                                            entry_street_address as street_address,
                                                            entry_suburb as suburb,
                                                            entry_city as city,
                                                            entry_postcode as postcode,
                                                            entry_state as state,
                                                            entry_zone_id as zone_id,
                                                            entry_country_id as country_id
                                                       FROM ".TABLE_ADDRESS_BOOK."
                                                      WHERE customers_id = '".(int) $cInfo->customers_id."'
                                                   ORDER BY address_book_id
                                                   ");
                                                     
                    while ($addresses = xtc_db_fetch_array($addresses_query)) {
                      $format_id = xtc_get_address_format_id($addresses['country_id']);
                      
                      if (isset($_GET['delete']) && $_GET['delete'] != '') {                              
                          if ($addresses['address_book_id'] == $_GET['delete']) {
                            if ($_GET['delete'] != $cInfo->customers_default_address_id) {
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => TEXT_INFO_DELETE);
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('text' =>  '<table style="font-size:11px; margin-left:20px;"><tr><td>' . xtc_address_format($format_id, $addresses, true, ' ', '<br />') . '</td></tr></table>');      
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>&nbsp;<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete')).'cID='.$cInfo->customers_id.'&action=delete_confirm_adressbook&address_book_id='.$addresses['address_book_id']).'">'.BUTTON_DELETE.'</a>');      
                              $contents[] = array ('text' => '<br/>');
                            } else {
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => TEXT_INFO_DELETE_DEFAULT);
                              $contents[] = array ('text' => '<br/>');
                              $contents[] = array ('align' => 'left', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_BACK.'</a>');      
                              $contents[] = array ('text' => '<br/>');
                            }
                          }    
                      } else {
                        $contents[] = array ('text' => '<br/>');
                        $contents[] = array ('text' =>  '<table style="font-size:11px; margin-left:20px;"><tr><td>' . xtc_address_format($format_id, $addresses, true, ' ', '<br />') . '</td></tr></table>');      
                        $contents[] = array ('text' => '<br/>');
                        $contents[] = array ('align' => 'left', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'edit')).'cID='.$cInfo->customers_id.'&action=edit&edit='.$addresses['address_book_id']).'">'.BUTTON_EDIT.'</a>&nbsp;<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete', 'edit')).'cID='.$cInfo->customers_id.'&action=address_book&delete='.$addresses['address_book_id']).'">'.BUTTON_DELETE.'</a>'. (($cInfo->customers_default_address_id!=$addresses['address_book_id'])?'&nbsp;<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete', 'default')).'cID='.$cInfo->customers_id.'&action=update_default_adressbook&default='.$addresses['address_book_id']).'">'.TEXT_SET_DEFAULT.'</a>':'') );
                        $contents[] = array ('text' =>  '<hr size="1"/>');
                      }
                      
                    }                          
                    if (!isset($_GET['delete'])) {
                      $contents[] = array ('align' => 'right', 'text' => (xtc_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) ? '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'edit')).'cID='.$cInfo->customers_id.'&action=edit&edit=0').'">'.BUTTON_INSERT.'</a>&nbsp;<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>' : '<a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                      $contents[] = array ('text' => '<br/>');
                    }
                    break;
                  case 'editstatus' :
                    if ($_GET['cID'] != 1) {
                      $customers_history_query = xtc_db_query("SELECT new_value, old_value, date_added, customer_notified FROM ".TABLE_CUSTOMERS_STATUS_HISTORY." WHERE customers_id = '".xtc_db_input($_GET['cID'])."' order by customers_status_history_id desc");
                      $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_STATUS_CUSTOMER.'</b>');
                      $contents = array ('form' => xtc_draw_form('customers', FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=statusconfirm'));
                      $contents[] = array ('text' => '<br />'.xtc_draw_pull_down_menu('status', $customers_statuses_array, $cInfo->customers_status));
                      $contents[] = array ('text' => '<table nowrap border="0" cellspacing="0" cellpadding="0"><tr><td style="border-bottom: 1px solid; border-color: #000000;" nowrap class="smallText" align="center"><b>'.TABLE_HEADING_NEW_VALUE.' </b></td><td style="border-bottom: 1px solid; border-color: #000000;" nowrap class="smallText" align="center"><b>'.TABLE_HEADING_DATE_ADDED.'</b></td></tr>');

                      if (xtc_db_num_rows($customers_history_query)) {
                        while ($customers_history = xtc_db_fetch_array($customers_history_query)) {
                          $contents[] = array ('text' => '<tr>'."\n".'<td class="smallText">'.$customers_statuses_id_array[$customers_history['new_value']]['text'].'</td>'."\n".'<td class="smallText" align="center">'.xtc_datetime_short($customers_history['date_added']).'</td>'."\n".'<td class="smallText" align="center">');// web28 - 2011-10-31 - change  $customers_statuses_array  to $customers_statuses_id_array
                          $contents[] = array ('text' => '</tr>'."\n");
                        }
                      } else {
                        $contents[] = array ('text' => '<tr>'."\n".' <td class="smallText" colspan="2">'.TEXT_NO_CUSTOMER_HISTORY.'</td>'."\n".' </tr>'."\n");
                      }
                      $contents[] = array ('text' => '</table>');
                      $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'.BUTTON_UPDATE.'"><a class="button" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_CANCEL.'</a>');
                      $status = isset($_POST['status']) ? xtc_db_prepare_input($_POST['status']) : ''; // maybe this line not needed to recheck...
                    }
                    break;

                  default :
                  //BOF - DokuMan - 2010-11-01 - seems to be for debugging porpoises only
                                        /*
                                        if (isset($_GET['cID'])) {
                    $customer_status = xtc_get_customer_status($_GET['cID']);
                    $cs_id = $customer_status['customers_status'];
                    $cs_member_flag = $customer_status['member_flag'];
                    $cs_name = $customer_status['customers_status_name'];
                    $cs_image = $customer_status['customers_status_image'];
                    $cs_discount = $customer_status['customers_status_discount'];
                    $cs_ot_discount_flag = $customer_status['customers_status_ot_discount_flag'];
                    $cs_ot_discount = $customer_status['customers_status_ot_discount'];
                    $cs_staffelpreise = $customer_status['customers_status_staffelpreise'];
                    $cs_payment_unallowed = $customer_status['customers_status_payment_unallowed'];
                                       }
                                        */
                    //echo 'customer_status ' . $_GET['cID'] . 'variables = ' . $cs_id . $cs_member_flag . $cs_name .  $cs_discount .  $cs_image . $cs_ot_discount;
                    //EOF - DokuMan - 2010-11-01 - seems to be for debugging porpoises only
                    if (isset($cInfo) && is_object($cInfo)) {
                      $heading[] = array ('text' => '<b>'.$cInfo->customers_firstname.' '.$cInfo->customers_lastname.'</b>');
                      if ($cInfo->customers_id != 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=edit').'">'.BUTTON_EDIT.'</a>');
                      }
                      if ($cInfo->customers_id == 1 && $_SESSION['customer_id'] == 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=edit').'">'.BUTTON_EDIT.'</a>');
                      }
                      if ($cInfo->customers_id != 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=confirm').'">'.BUTTON_DELETE.'</a>');
                      }
                      if ($cInfo->customers_id != 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=editstatus').'">'.BUTTON_STATUS.'</a>');
                      }
                      // elari cs v3.x changed for added accounting module
                      if ($cInfo->customers_id != 1 && $cInfo->customers_status == 0) { // h-h-h - 2011-10-06 - show only if customer is admin - thx to Webkiste
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ACCOUNTING, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id).'">'.BUTTON_ACCOUNTING.'</a>');
                      }
                      if ($cInfo->customers_id != 1) {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=address_book').'">'.TEXT_INFO_HEADING_ADRESS_BOOK.'</a>');
                      }
                      // elari cs v3.x changed for added iplog module
                      $contents[] = array (
                                           'align' => 'center',
                                           'text' => '<table>
                                                        <tr>
                                                          <td style="text-align: center;">
                                                            <a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_ORDERS, 'cID='.$cInfo->customers_id).'">'.BUTTON_ORDERS.'</a>
                                                          </td>
                                                          <td style="text-align: center;">
                                                            <a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_MAIL, 'selected_box=tools&customer='.$cInfo->customers_email_address).'">'.BUTTON_EMAIL.'</a>
                                                          </td>
                                                        </tr>
                                                        <tr>
                                                          <td style="text-align: center;">
                                                            <a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=iplog').'">'.BUTTON_IPLOG.'</a></td>
                                                          <td style="text-align: center;">
                                                            <a class="button" onclick="this.blur();" href="'.xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$cInfo->customers_id.'&action=new_order').'" onclick="return confirm(\''.NEW_ORDER.'\')">'.BUTTON_NEW_ORDER.'</a>
                                                          </td>
                                                        </tr>
                                                      </table>'
                                          );
                      //BOF - DokuMan - 2010-11-02 - Workaround for customer details not showing on iplog-Box
                      if ($action == 'iplog') {
                        $info_query = xtc_db_query("SELECT
                                                          customers_info_date_account_created as date_account_created,
                                                          customers_info_date_account_last_modified as date_account_last_modified,
                                                          customers_info_date_of_last_logon as date_last_logon,
                                                          customers_info_number_of_logons as number_of_logons
                                                     FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".$cInfo->customers_id."'");
                        $info = xtc_db_fetch_array($info_query);

                        $country_query = xtc_db_query("SELECT countries_name FROM ".TABLE_COUNTRIES." WHERE countries_id = '".(int)$cInfo->entry_country_id."'");
                        $country = xtc_db_fetch_array($country_query);

                        $reviews_query = xtc_db_query("SELECT COUNT(*) as number_of_reviews FROM ".TABLE_REVIEWS." WHERE customers_id = '".(int)$cInfo->customers_id."'");
                        $reviews = xtc_db_fetch_array($reviews_query);

                        $contents[] = array ('text' => '<br />'.TEXT_DATE_ACCOUNT_CREATED.' '.xtc_date_short($info['date_account_created']));
                        $contents[] = array ('text' => '<br />'.TEXT_DATE_ACCOUNT_LAST_MODIFIED.' '.xtc_date_short($info['date_account_last_modified']));
                        $contents[] = array ('text' => '<br />'.TEXT_INFO_DATE_LAST_LOGON.' '.xtc_date_short($info['date_last_logon']));
                        $contents[] = array ('text' => '<br />'.TEXT_INFO_NUMBER_OF_LOGONS.' '.$info['number_of_logons']);
                        $contents[] = array ('text' => '<br />'.TEXT_INFO_COUNTRY.' '.$country['countries_name']);
                        $contents[] = array ('text' => '<br />'.TEXT_INFO_NUMBER_OF_REVIEWS.' '.$reviews['number_of_reviews']);
                      } else {
                        //EOF - DokuMan - 2010-11-02 - Workaround for customer details not showing on iplog-Box
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ACCOUNT_CREATED.' '.xtc_date_short($cInfo->date_account_created));
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ACCOUNT_LAST_MODIFIED.' '.xtc_date_short($cInfo->date_account_last_modified));
                      // BOF - Tomcraft - 2011-01-16 - Additionally show time for customers last logon time
                      //$contents[] = array ('text' => '<br />'.TEXT_INFO_DATE_LAST_LOGON.' '.xtc_date_short($cInfo->date_last_logon));
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_DATE_LAST_LOGON.' '.xtc_datetime_short($cInfo->date_last_logon));
                      // EOF - Tomcraft - 2011-01-16 - Additionally show time for customers last logon time
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_NUMBER_OF_LOGONS.' '.$cInfo->number_of_logons);
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_COUNTRY.' '.$cInfo->countries_name);
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_NUMBER_OF_REVIEWS.' '.$cInfo->number_of_reviews);
                        //BOF - DokuMan - 2010-11-02 - Workaround for customer details not showing on iplog-Box
                      }
                      //EOF - DokuMan - 2010-11-02 - Workaround for customer details not showing on iplog-Box
                    }

                     if ($action == 'iplog') {
                      if (isset ($_GET['cID'])) {
                        $contents[] = array ('text' => '<br /><b>IPLOG :');
                        $customers_id = xtc_db_prepare_input($_GET['cID']);
                        $customers_log_info_array = xtc_get_user_info($customers_id);
                        if (xtc_db_num_rows($customers_log_info_array)) {
                          while ($customers_log_info = xtc_db_fetch_array($customers_log_info_array)) {
                            $contents[] = array ('text' => '<tr>'."\n".'<td class="smallText">'.$customers_log_info['customers_ip_date'].' '.$customers_log_info['customers_ip'].' '.$customers_log_info['customers_advertiser']);
                          }
                        }
                      }
                    }
                    break;
                }
                if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
                  echo '            <td width="25%" valign="top">'."\n";
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '            </td>'."\n";
                }
              ?>
            </tr>
          </table>
        </td>
      </tr>
      <?php
      }
      ?>
      </table>
    </td>
    <!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
