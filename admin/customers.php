<?php
  /* --------------------------------------------------------------
   $Id: customers.php 5140 2013-07-18 15:09:39Z web28 $

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

  // split page results
  if(!defined('MAX_DISPLAY_LIST_CUSTOMERS')) {
    define('MAX_DISPLAY_LIST_CUSTOMERS', 100);
  }

  // customers totals
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $customers_statuses_array = xtc_get_customers_statuses();
  // changes all $customers_statuses_array[xx] to $customers_statuses_id_array[xx]  in html section
  $customers_statuses_id_array = array();
  for ($i=0;$n=sizeof($customers_statuses_array),$i<$n;$i++) {
    $customers_statuses_id_array[$customers_statuses_array[$i]['id']] = $customers_statuses_array[$i];
  }

  $processed = false;
  $error = false;
  $entry_vat_error_text = '';
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $customers_id = (int)$_GET['cID'];

  if (isset($_GET['special']) && $_GET['special'] == 'remove_memo') {
    $mID = xtc_db_prepare_input($_GET['mID']);
    xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_MEMO." WHERE memo_id = '".(int)$mID."'");
    xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action', 'special')).'cID='.$customers_id.'&action=edit'));
  }

  if (($action == 'edit' || $action == 'update') && !(($customers_id == 1 && $_SESSION['customer_id'] == 1) || $customers_id != 1)) {
    xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, ''));
  }

  if ($action) {
    switch ($action) {
      case 'new_order' :
        // customers
        $customers1_query = xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".$customers_id."'");
        $customers1 = xtc_db_fetch_array($customers1_query);

        // customers default address
        $customers_query = xtc_db_query("SELECT * FROM ".TABLE_ADDRESS_BOOK."
                                          WHERE customers_id = '".$customers_id."'
                                            AND address_book_id =  '".$customers1['customers_default_address_id']."'");
        $customers = xtc_db_fetch_array($customers_query);

        // countries
        $country_query = xtc_db_query("SELECT countries_name, countries_iso_code_2, address_format_id
                                         FROM ".TABLE_COUNTRIES."
                                        WHERE countries_id = '".$customers['entry_country_id']."'");
        $country = xtc_db_fetch_array($country_query);

        // customers status
        $stat_query = xtc_db_query("SELECT * FROM ".TABLE_CUSTOMERS_STATUS." WHERE customers_status_id = '".(int)$customers1['customers_status']."' ");
        $stat = xtc_db_fetch_array($stat_query);

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
            'customers_gender' => xtc_db_prepare_input($customers['entry_gender']),
            'customers_company' => xtc_db_prepare_input($customers['entry_company']),
            'customers_street_address' => xtc_db_prepare_input($customers['entry_street_address']),
            'customers_suburb' => xtc_db_prepare_input($customers['entry_suburb']),
            'customers_city' => xtc_db_prepare_input($customers['entry_city']),
            'customers_postcode' => xtc_db_prepare_input($customers['entry_postcode']),
            'customers_state' => xtc_db_prepare_input($customers['entry_state']),
            'customers_country' => xtc_db_prepare_input($country['countries_name']),
            'customers_telephone' => xtc_db_prepare_input($customers1['customers_telephone']),
            'customers_email_address' => xtc_db_prepare_input($customers1['customers_email_address']),
            'customers_address_format_id' => xtc_db_prepare_input($country['address_format_id']),
            'delivery_name' => xtc_db_prepare_input($customers['entry_firstname'].' '.$customers['entry_lastname']),
            'delivery_lastname' => xtc_db_prepare_input($customers['entry_lastname']),
            'delivery_firstname' => xtc_db_prepare_input($customers['entry_firstname']),
            'delivery_gender' => xtc_db_prepare_input($customers['entry_gender']),
            'delivery_company' => xtc_db_prepare_input($customers['entry_company']),
            'delivery_street_address' => xtc_db_prepare_input($customers['entry_street_address']),
            'delivery_suburb' => xtc_db_prepare_input($customers['entry_suburb']),
            'delivery_city' => xtc_db_prepare_input($customers['entry_city']),
            'delivery_postcode' => xtc_db_prepare_input($customers['entry_postcode']),
            'delivery_state' => xtc_db_prepare_input($customers['entry_state']),
            'delivery_country' => xtc_db_prepare_input($country['countries_name']),
            'delivery_country_iso_code_2' => xtc_db_prepare_input($country['countries_iso_code_2']),
            'delivery_address_format_id' => xtc_db_prepare_input($country['address_format_id']),
            'billing_name' => xtc_db_prepare_input($customers['entry_firstname'].' '.$customers['entry_lastname']),
            'billing_lastname' => xtc_db_prepare_input($customers['entry_lastname']),
            'billing_firstname' => xtc_db_prepare_input($customers['entry_firstname']),
            'billing_gender' => xtc_db_prepare_input($customers['entry_gender']),
            'billing_company' => xtc_db_prepare_input($customers['entry_company']),
            'billing_street_address' => xtc_db_prepare_input($customers['entry_street_address']),
            'billing_suburb' => xtc_db_prepare_input($customers['entry_suburb']),
            'billing_city' => xtc_db_prepare_input($customers['entry_city']),
            'billing_postcode' => xtc_db_prepare_input($customers['entry_postcode']),
            'billing_state' => xtc_db_prepare_input($customers['entry_state']),
            'billing_country' => xtc_db_prepare_input($country['countries_name']),
            'billing_country_iso_code_2' => xtc_db_prepare_input($country['countries_iso_code_2']),
            'billing_address_format_id' => xtc_db_prepare_input($country['address_format_id']),
            'payment_method' => 'cod',
            'comments' => '',
            'last_modified' => 'now()',
            'date_purchased' => 'now()',
            'orders_status' => '1',
            'orders_date_finished' => '',
            'currency' => DEFAULT_CURRENCY,
            'currency_value' => '1.0000',
            'account_type' => '0',
            'payment_class' => 'cod',
            'shipping_method' => MODULE_SHIPPING_FLAT_TEXT_TITLE,
            'shipping_class' => 'flat_flat',
            'customers_ip' => '',
            'language' => $_SESSION['language']
          );

        xtc_db_perform(TABLE_ORDERS, $sql_data_array);
        $orders_id = xtc_db_insert_id();

        require_once (DIR_FS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_total.php');
        $sql_data_array = array(
            'orders_id' => (int)$orders_id,
            'title' => MODULE_ORDER_TOTAL_TOTAL_TITLE.':',
            'text' => '0',
            'value' => '0',
            'class' => 'ot_total',
            'sort_order' => MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER
          );
        xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

        require_once (DIR_FS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_subtotal.php');
        $sql_data_array = array(
            'orders_id' => (int)$orders_id,
            'title' => '<b>'.MODULE_ORDER_TOTAL_SUBTOTAL_TITLE.'</b>:',
            'text' => '0',
            'value' => '0',
            'class' => 'ot_subtotal',
            'sort_order' => MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER
          );
        xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);

        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID='.(int)$orders_id.'&action=edit'));
        break;

      case 'delete_confirm_adressbook' :
          xtc_db_query("-- admin/customers.php
                        DELETE FROM ".TABLE_ADDRESS_BOOK."
                              WHERE address_book_id = '".(int) $_GET['address_book_id']."'
                                AND customers_id = '".$customers_id."'"
                                    );
          xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'delete_confirm_adressbook')).'cID='.(int)$customers_id));
          break;

       case 'update_default_adressbook' :
          $address_book_query = xtc_db_query("-- admin/customers.php
                                         SELECT entry_gender AS customers_gender,
                                                entry_firstname AS customers_firstname,
                                                entry_lastname AS customers_lastname
                                           FROM ".TABLE_ADDRESS_BOOK."
                                          WHERE address_book_id = '".(int) $_GET['default']."'
                                            AND customers_id = '".$customers_id."'"
                                             );
          $address_book_array = xtc_db_fetch_array($address_book_query);

          if (ACCOUNT_GENDER != 'true') {
            unset($address_book_array['customers_gender']);
          }

          $sql_data_array = array (
              'customers_default_address_id' => (int) $_GET['default'],
              'customers_last_modified' => 'now()'
            );
          $sql_data_array = array_merge($address_book_array,$sql_data_array);
          xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '". $customers_id ."'");

          xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action', 'update_default_adressbook', 'default')).'cID='.$customers_id.'&action=address_book'));
          break;

      case 'statusconfirm' :
        $error = false;
        $check_status_query = xtc_db_query("SELECT customers_firstname,
                                                   customers_lastname,
                                                   customers_email_address,
                                                   customers_status,
                                                   member_flag
                                              FROM ".TABLE_CUSTOMERS."
                                             WHERE customers_id = '".$customers_id."'");
        $check_status = xtc_db_fetch_array($check_status_query);
        if ($check_status['customers_status'] != (int)$_POST['customers_status']) {
          $sql_data_array = array('customers_status' => (int)$_POST['customers_status']);
          
          $sql_add_data_array['account_type'] = '1';                        
          if ($_POST['customers_status'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
            $sql_add_data_array['account_type'] = '0';
          }
          
          // check existing account
          if ($sql_add_data_array['account_type'] == '0') {
            $check_existing_customer_query = xtc_db_query("SELECT customers_id
                                                             FROM ".TABLE_CUSTOMERS."
                                                            WHERE customers_email_address = '".xtc_db_input($check_status['customers_email_address'])."'
                                                              AND account_type = '0'
                                                              AND customers_id != '".$customers_id."'");
            if (xtc_db_num_rows($check_existing_customer_query) > 0) {
              $error = true;
              $messageStack->add_session(WARNING_CUSTOMER_ALREADY_EXISTS, 'warning');
            }
          }
          
          if ($error === false) {
            xtc_db_perform(TABLE_CUSTOMERS, array_merge($sql_data_array, $sql_add_data_array), 'update', "customers_id = '".$customers_id."'"); 

            // update customers status in newsletters_recipients
            xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array, 'update', "customers_id = '".$customers_id."'"); 
                    
            // create insert for admin access table if customers status is set to 0
            if ($_POST['customers_status'] == 0) {
              xtc_db_query("INSERT INTO  ".TABLE_ADMIN_ACCESS." (customers_id,start) VALUES ('".$customers_id."','1')");
            } else {
              xtc_db_query("DELETE FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".$customers_id."'");
            }
            $sql_data_array = array('customers_id' => $customers_id,
                                    'new_value' => (int)$_POST['customers_status'],
                                    'old_value' => $check_status['customers_status'],
                                    'date_added' => 'now()',
                                    'customer_notified' => '0');
            xtc_db_perform(TABLE_CUSTOMERS_STATUS_HISTORY, $sql_data_array);  
          }        
        }
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID', 'action')).'cID='.$customers_id));
        break;

      case 'update' :
        $customers_cid = xtc_db_prepare_input($_POST['csID']);
        $customers_vat_id = xtc_db_prepare_input($_POST['customers_vat_id']);
        $customers_vat_id_status = (isset($_POST['customers_vat_id_status']) ? xtc_db_prepare_input($_POST['customers_vat_id_status']) : '');
        $customers_firstname = xtc_db_prepare_input($_POST['customers_firstname']);
        $customers_lastname = xtc_db_prepare_input($_POST['customers_lastname']);
        $customers_email_address = xtc_db_prepare_input($_POST['customers_email_address']);
        $customers_telephone = xtc_db_prepare_input($_POST['customers_telephone']);
        $customers_fax = xtc_db_prepare_input($_POST['customers_fax']);
        $customers_newsletter = (isset($_POST['customers_newsletter']) ? xtc_db_prepare_input($_POST['customers_newsletter']) : '');
        if (ACCOUNT_GENDER == 'true') $customers_gender = xtc_db_prepare_input($_POST['customers_gender']);
        if (ACCOUNT_DOB == 'true') $customers_dob = xtc_db_prepare_input($_POST['customers_dob']);
        $customers_default_address_id = xtc_db_prepare_input($_POST['customers_default_address_id']);
        $address_book_id = xtc_db_prepare_input($_POST['address_book_id']);
        $entry_street_address = xtc_db_prepare_input($_POST['entry_street_address']);
        if (ACCOUNT_SUBURB == 'true') $entry_suburb = xtc_db_prepare_input($_POST['entry_suburb']);
        $entry_postcode = xtc_db_prepare_input($_POST['entry_postcode']);
        $entry_city = xtc_db_prepare_input($_POST['entry_city']);
        $entry_country_id = xtc_db_prepare_input($_POST['entry_country_id']);
        if (ACCOUNT_COMPANY == 'true') $entry_company = xtc_db_prepare_input($_POST['entry_company']);
        if (ACCOUNT_STATE == 'true') $entry_state = xtc_db_prepare_input($_POST['entry_state']);
        if (ACCOUNT_STATE == 'true') $entry_zone_id = xtc_db_prepare_input($_POST['entry_zone_id']);
        $memo_title = xtc_db_prepare_input($_POST['memo_title']);
        $memo_text = xtc_db_prepare_input($_POST['memo_text']);
        $payment_unallowed = implode(',', (is_array($_POST['payment_unallowed']) ? $_POST['payment_unallowed'] : array()));
        $shipping_unallowed = implode(',', (is_array($_POST['shipping_unallowed']) ? $_POST['shipping_unallowed'] : array()));
        $password = xtc_db_prepare_input($_POST['entry_password']);
        /*
        $amount = xtc_db_prepare_input($_POST['amount']);
        if ($amount != '') {
          $sql_data_array = array('customer_id' => $customers_id,
                                  'amount' => $amount
                                  );
          $check_gv_query = xtc_db_query("SELECT * FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE customer_id = '".$customers_id."'");
          if (xtc_db_num_rows($check_gv_query) > 0) {
            xtc_db_perform(TABLE_COUPON_GV_CUSTOMER, $sql_data_array, 'update', "customer_id = '".$customers_id."'");
          } else {
            xtc_db_perform(TABLE_COUPON_GV_CUSTOMER, $sql_data_array);
          }
        }*/

        // reset error flag
        $error = false;
        
        if ($memo_text != '' || $memo_title != '') {
          if ($memo_text != '' && $memo_title == '') {
            $error = true;
            $entry_memo_title_error = true;
          }
          if ($memo_text == '' && $memo_title != '') {
            $error = true;
            $entry_memo_text_error = true;
          }
          if ($error === false) {
            $sql_data_array = array ('customers_id' => $customers_id,
                                     'memo_date' => date("Y-m-d"),
                                     'memo_title' => $memo_title,
                                     'memo_text' => $memo_text,
                                     'poster_id' => (int)$_SESSION['customer_id']
                                    );
            xtc_db_perform(TABLE_CUSTOMERS_MEMO, $sql_data_array);
          }
        }
        
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

        if (ACCOUNT_GENDER == 'true') {
          if (($customers_gender != 'm') && ($customers_gender != 'f')) {
            $error = true;
            $entry_gender_error = true;
          } else {
            $entry_gender_error = false;
          }
        }

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
          // display correct error code of VAT ID check
          switch ($customers_vat_id_status) {
            case '0' :// 'VAT invalid'
              $entry_vat_error_text = TEXT_VAT_FALSE;
              break;
            case '1' :// 'VAT valid'
              $entry_vat_error_text = TEXT_VAT_TRUE;
              break;
            case '2' :// 'SOAP ERROR: Connection to host not possible, europe.eu down?'
              $entry_vat_error_text = TEXT_VAT_CONNECTION_NOT_POSSIBLE;
              break;
            case '8' :// 'unknown country'
              $entry_vat_error_text = TEXT_VAT_UNKNOWN_COUNTRY;
              break;
            case '94' :// 'INVALID_INPUT' => 'The provided CountryCode is invalid or the VAT number is empty'
              $entry_vat_error_text = TEXT_VAT_INVALID_INPUT;
              break;
            case '95' :// 'SERVICE_UNAVAILABLE' => 'The SOAP service is unavailable, try again later'
              $entry_vat_error_text = TEXT_VAT_SERVICE_UNAVAILABLE;
              break;
            case '96' :// 'MS_UNAVAILABLE' => 'The Member State service is unavailable, try again later or with another Member State'
              $entry_vat_error_text = TEXT_VAT_MS_UNAVAILABLE;
              break;
            case '97' :// 'TIMEOUT' => 'The Member State service could not be reached in time, try again later or with another Member State',
              $entry_vat_error_text = TEXT_VAT_TIMEOUT;
              break;
            case '98' :// 'SERVER_BUSY' => 'The service cannot process your request. Try again later.'
              $entry_vat_error_text = TEXT_VAT_SERVER_BUSY;
              break;
            case '99' :// 'no PHP5 SOAP support'
              $entry_vat_error_text = TEXT_VAT_NO_PHP5_SOAP_SUPPORT;
              break;
            default:
              $entry_vat_error_text = '';
              break;
          }
          if (isset($vatID->vat_info['error']) && $vatID->vat_info['error']==1){
            $entry_vat_error = true;
            $error = true;
          }
        }

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
                $zone_query = xtc_db_query("SELECT zone_id FROM ".TABLE_ZONES." WHERE zone_country_id = '".xtc_db_input($entry_country_id)."' AND zone_code = '".xtc_db_input($entry_state)."'");
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

        if (ACCOUNT_TELEPHONE_OPTIONAL == 'false' && strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
          $error = true;
          $entry_telephone_error = true;
        } else {
          $entry_telephone_error = false;
        }

        if (strlen($password) > 0 && strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
          $error = true;
          $entry_password_error = true;
        } else {
          $entry_password_error = false;
        }

        $check_email = xtc_db_query("SELECT customers_email_address
                                      FROM ".TABLE_CUSTOMERS."
                                     WHERE customers_email_address = '".xtc_db_input($customers_email_address)."'
                                       AND account_type = '0'
                                       AND customers_id != '".$customers_id."'");
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

          if ($password != "") {
            $sql_data_array['customers_password'] = xtc_encrypt_password($password);
          }
          if (ACCOUNT_GENDER == 'true') {
            $sql_data_array['customers_gender'] = $customers_gender;
          }
          if (ACCOUNT_DOB == 'true') {
            $sql_data_array['customers_dob'] = xtc_date_raw($customers_dob);
          }

          xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".$customers_id."' AND customers_default_address_id = '".$address_book_id."'");

          xtc_db_query("UPDATE ".TABLE_CUSTOMERS_INFO."
                           SET customers_info_date_account_last_modified = now()
                         WHERE customers_info_id = '".$customers_id."'");

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

          if (ACCOUNT_GENDER == 'true') {
            $sql_data_array['entry_gender'] = $customers_gender;
          }
          if (ACCOUNT_COMPANY == 'true') {
            $sql_data_array['entry_company'] = $entry_company;
          }
          if (ACCOUNT_SUBURB == 'true') {
            $sql_data_array['entry_suburb'] = $entry_suburb;
          }
          if (ACCOUNT_STATE == 'true') {
            $sql_data_array['entry_zone_id'] = (int)$entry_zone_id;
            $sql_data_array['entry_state'] = $entry_state;
          }
          if ($address_book_id == 0) {
            $sql_data_array['address_date_added'] = 'now()';
            $sql_data_array['customers_id'] = $customers_id;
            xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'insert');
          } else {
            xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '".$customers_id."' AND address_book_id = '".xtc_db_input($address_book_id)."'");
          }
          xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$customers_id));

        }  elseif ($error == true) {

          // unset memo to avoid duplicate entry
          if ($entry_memo_title_error === false && $entry_memo_text_error === false) {
            unset($_POST['memo_title']);
            unset($_POST['memo_text']);
          }
          
          // unallowd payment/shipping must be comma separated
          $_POST['payment_unallowed'] = $payment_unallowed;
          $_POST['shipping_unallowed'] = $shipping_unallowed;

          $cInfo = new objectInfo($_POST);
          $processed = true;
        }
        break;

      case 'deleteconfirm' :
        if ($_POST['delete_reviews'] == 'on') {
          $reviews_query = xtc_db_query("SELECT reviews_id FROM ".TABLE_REVIEWS." WHERE customers_id = '".$customers_id."'");
          while ($reviews = xtc_db_fetch_array($reviews_query)) {
            xtc_db_query("DELETE FROM ".TABLE_REVIEWS_DESCRIPTION." WHERE reviews_id = '".$reviews['reviews_id']."'");
          }
          xtc_db_query("DELETE FROM ".TABLE_REVIEWS." WHERE customers_id = '".$customers_id."'");
        } else {
          xtc_db_query("UPDATE ".TABLE_REVIEWS." SET customers_id = null WHERE customers_id = '".$customers_id."'");
        }
        xtc_db_query("DELETE FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_INFO." WHERE customers_info_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_BASKET_ATTRIBUTES." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_PRODUCTS_NOTIFICATIONS." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_WHOS_ONLINE." WHERE customer_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_STATUS_HISTORY." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_IP." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_ADMIN_ACCESS." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_NEWSLETTER_RECIPIENTS." WHERE customers_id = '".$customers_id."'");
        xtc_db_query("DELETE FROM ".TABLE_COUPON_GV_CUSTOMER." WHERE customer_id = '".$customers_id."'");
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array ('cID', 'action'))));
        break;
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
  <?php if (ACCOUNT_GENDER == 'true') { ?>
  if (document.customers.customers_gender[0].checked || document.customers.customers_gender[1].checked) {
  } else {
    error_message = error_message + "<?php echo xtc_js_lang(JS_GENDER); ?>";
    error = 1;
  }
  <?php } ?>

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
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL;
        echo '</td>'.PHP_EOL;
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
      <?php
      if ($action == 'edit' || $action == 'update') {
        include (DIR_WS_MODULES.'customers_edit.php'); // ACTION EDIT - UPDATE
      } else {
        include (DIR_WS_MODULES.'customers_listing.php'); // ACTION EDIT - UPDATE
      }
      ?>
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