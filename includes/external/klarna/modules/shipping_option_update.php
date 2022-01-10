<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (isset($klarna_data['shipping_address'])
    && is_array($klarna_data['shipping_address'])
    && count($klarna_data['shipping_address']) > 0
    )
{
  $sql_data_array = array(
    'customers_id' => (int)$_SESSION['customer_id'],
    'entry_firstname' => $klarna_data['shipping_address']['given_name'],
    'entry_lastname' => $klarna_data['shipping_address']['family_name'],
    'entry_street_address' => $klarna_data['shipping_address']['street_address'],
    'entry_postcode' => $klarna_data['shipping_address']['postal_code'],
    'entry_city' => $klarna_data['shipping_address']['city'],
    'entry_country_id' => get_country_id($klarna_data['shipping_address']['country']),
    'address_type' => 2,
    'address_date_added' => 'now()',
    'address_last_modified' => 'now()'
  );

  $check_query = xtc_db_query("SELECT *
                                 FROM ".TABLE_ADDRESS_BOOK."
                                WHERE address_type = '2'
                                  AND customers_id = '".(int)$_SESSION['customer_id']."'");
  if (xtc_db_num_rows($check_query) > 0) {
    $check = xtc_db_fetch_array($check_query);
    $_SESSION['sendto'] = $check['address_book_id'];
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '".$check['address_book_id']."'");
  } else {
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
    $_SESSION['sendto'] = xtc_db_insert_id();
  }

  $_SESSION['customer_country'] = $sql_data_array['entry_country_id'];
  $_SESSION['delivery_zone'] = $klarna_data['shipping_address']['country'];
}


if (isset($klarna_data['selected_shipping_option'])
    && array_key_exists('id', $klarna_data['selected_shipping_option'])
    )
{
  $order = new order();

  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();

  if ($order->delivery['country']['iso_code_2'] != '') {
    $_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
  }

  // load all enabled shipping modules
  require_once (DIR_WS_CLASSES.'shipping.php');
  $shipping_modules = new shipping;

  $free_shipping = false;
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    require_once (DIR_WS_CLASSES . 'order_total.php');
    $order_total_modules = new order_total();
    $order_total_modules->process();
  }

  $smarty = new Smarty();
  $_POST['shipping'] = $klarna_data['selected_shipping_option']['id'];
  require(DIR_WS_INCLUDES.'shipping_action.php');
}
