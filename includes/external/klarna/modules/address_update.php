<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($klarna_data['billing_address'])
    && is_array($klarna_data['billing_address'])
    && count($klarna_data['billing_address']) > 0
    )
{
  $sql_data_array = array(
    'customers_firstname' => $klarna_data['billing_address']['given_name'],
    'customers_lastname' => $klarna_data['billing_address']['family_name'],
    'customers_email_address' => $klarna_data['billing_address']['email'],
    'customers_telephone' => $klarna_data['billing_address']['phone'],
    'customers_last_modified' => 'now()',
  );

  if (!isset($_SESSION['customer_id'])) {
    $sql_data_add_array = array(
      'customers_status' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
      'customers_password' => xtc_create_password(8),
      'account_type' => 1,
      'customers_date_added' => 'now()',
    );
    $sql_data_array = array_merge($sql_data_array, $sql_data_add_array);
    xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array);

    $_SESSION['customer_id'] = xtc_db_insert_id();
    $_SESSION['account_type'] = '1';
    $_SESSION['customer_gender'] = $gender;
    $_SESSION['customer_first_name'] = $sql_data_array['customers_firstname'];
    $_SESSION['customer_last_name'] = $sql_data_array['customers_lastname'];
    $_SESSION['customer_email_address'] = $sql_data_array['customers_email_address'];
    $_SESSION['customer_vat_id'] = '';

    xtc_write_user_info($_SESSION['customer_id']);    
  } else {
    xtc_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '".(int)$_SESSION['customer_id']."'");
  }

  $sql_data_array = array(
    'customers_id' => (int)$_SESSION['customer_id'],
    'entry_firstname' => $klarna_data['billing_address']['given_name'],
    'entry_lastname' => $klarna_data['billing_address']['family_name'],
    'entry_street_address' => $klarna_data['billing_address']['street_address'],
    'entry_postcode' => $klarna_data['billing_address']['postal_code'],
    'entry_city' => $klarna_data['billing_address']['city'],
    'entry_country_id' => get_country_id($klarna_data['billing_address']['country']),
    'address_type' => 1,
    'address_date_added' => 'now()',
    'address_last_modified' => 'now()'
  );

  $check_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_ADDRESS_BOOK."
                                WHERE address_type = '1'
                                  AND customers_id = '".(int)$_SESSION['customer_id']."'");
  if (xtc_db_num_rows($check_query) > 0) {
    $check = xtc_db_fetch_array($check_query);
    $_SESSION['billto'] = $check['address_book_id'];
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '".$check['address_book_id']."'");
  } else {
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
    $_SESSION['billto'] = xtc_db_insert_id();
    $_SESSION['customer_default_address_id'] = $_SESSION['billto'];
    $_SESSION['customer_country_id'] = $sql_data_array['entry_country_id'];
    $_SESSION['customer_zone_id'] = 0;
  
    xtc_db_query("UPDATE " . TABLE_CUSTOMERS . " 
                     SET customers_default_address_id = '" . (int)$_SESSION['billto'] . "' 
                   WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'");
    
    $sql_data_array = array('customers_info_id' => (int)$_SESSION['customer_id'],
                            'customers_info_number_of_logons' => '1',
                            'customers_info_date_account_created' => 'now()',
                            'customers_info_date_of_last_logon' => 'now()'
                            );
    xtc_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);
  }

}
