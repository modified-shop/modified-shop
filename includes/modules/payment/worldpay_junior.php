<?php
/* -----------------------------------------------------------------------------------------
   $Id: worldpay_junior.php 4759 2013-05-10 14:00:56Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2008 osCommerce(worldpay_junior.php 1807 2008-01-13 ); www.oscommerce.com

   UPDATED 07-05-2011
   Updated RBS WorldPay form URL

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class worldpay_junior {
    var $code, $title, $description, $enabled;

    // class constructor
    function __construct() {
      global $order;

      $this->signature = 'worldpay|worldpay_junior|1.0|2.2';
      $this->code = 'worldpay_junior';
      $this->title = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_DESCRIPTION;
      $this->sort_order = defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER')?MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER:'';
      $this->enabled = ((defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS') && MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS == 'True') ? true : false);

      if (defined('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->form_action_url = 'https://secure.wp3.rbsworldpay.com/wcc/purchase';
    }

    // class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE > 0) ) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = xtc_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {

      if (! empty($_SESSION['cart_Worldpay_Junior_ID'])) {
        $order_id = substr($_SESSION['cart_Worldpay_Junior_ID'], strpos($_SESSION['cart_Worldpay_Junior_ID'], '-')+1);

        $check_query = xtc_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (xtc_db_num_rows($check_query) < 1) {
          xtc_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          xtc_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          xtc_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          unset($_SESSION['cart_Worldpay_Junior_ID']);
        }
      }

      return array('id' => $this->code,
                    'module' => $this->title,
                    'description'=>$this->description);
    }

    function pre_confirmation_check() {
      /*
      global $cartID, $cart;
      if (empty($cart->cartID)) {
        $cartID = $cart->cartID = $cart->generate_cart_id();
      }

      if (!xtc_session_is_registered('cartID')) {
        xtc_session_register('cartID');
      }
      */
      if (empty($_SESSION['cart']->cartID)) {
        $_SESSION['cart']->cartID = $_SESSION['cart']->generate_cart_id();
      }
      return false;
    }

    function confirmation() {
      global $order, $order_total_modules;

      $insert_order = false;

      //if (xtc_session_is_registered('cart_Worldpay_Junior_ID')) {
      if (! empty($_SESSION['cart_Worldpay_Junior_ID'])) {
        $order_id = substr($_SESSION['cart_Worldpay_Junior_ID'], strpos($_SESSION['cart_Worldpay_Junior_ID'], '-')+1);

        $cartID = substr($_SESSION['cart_Worldpay_Junior_ID'], 0, strlen($_SESSION['cart']->cartID));

        $curr_check = xtc_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        $curr = xtc_db_fetch_array($curr_check);

        if ( ($curr['currency'] != $order->info['currency']) || ($cartID != $_SESSION['cart']->cartID) ) {
          $check_query = xtc_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

          if (xtc_db_num_rows($check_query) < 1) {
            xtc_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
            xtc_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
            xtc_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
            xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
            xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
            xtc_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');
          }

          $insert_order = true;
        }
      } else {
        $insert_order = true;
      }

      if ($insert_order == true) {
        $order_totals = array();
        if (is_array($order_total_modules->modules)) {
          reset($order_total_modules->modules);
          while (list(, $value) = each($order_total_modules->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            if ($GLOBALS[$class]->enabled) {
              for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                if (xtc_not_null($GLOBALS[$class]->output[$i]['title']) && xtc_not_null($GLOBALS[$class]->output[$i]['text'])) {
                  $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                          'title' => $GLOBALS[$class]->output[$i]['title'],
                                          'text' => $GLOBALS[$class]->output[$i]['text'],
                                          'value' => $GLOBALS[$class]->output[$i]['value'],
                                          'sort_order' => $GLOBALS[$class]->sort_order);
                }
              }
            }
          }
        }

        $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                'customers_company' => $order->customer['company'],
                                'customers_street_address' => $order->customer['street_address'],
                                'customers_suburb' => $order->customer['suburb'],
                                'customers_city' => $order->customer['city'],
                                'customers_postcode' => $order->customer['postcode'],
                                'customers_state' => $order->customer['state'],
                                'customers_country' => $order->customer['country']['title'],
                                'customers_telephone' => $order->customer['telephone'],
                                'customers_email_address' => $order->customer['email_address'],
                                'customers_address_format_id' => $order->customer['format_id'],
                                'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                'delivery_company' => $order->delivery['company'],
                                'delivery_street_address' => $order->delivery['street_address'],
                                'delivery_suburb' => $order->delivery['suburb'],
                                'delivery_city' => $order->delivery['city'],
                                'delivery_postcode' => $order->delivery['postcode'],
                                'delivery_state' => $order->delivery['state'],
                                'delivery_country' => $order->delivery['country']['title'],
                                'delivery_address_format_id' => $order->delivery['format_id'],
                                'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                'billing_company' => $order->billing['company'],
                                'billing_street_address' => $order->billing['street_address'],
                                'billing_suburb' => $order->billing['suburb'],
                                'billing_city' => $order->billing['city'],
                                'billing_postcode' => $order->billing['postcode'],
                                'billing_state' => $order->billing['state'],
                                'billing_country' => $order->billing['country']['title'],
                                'billing_address_format_id' => $order->billing['format_id'],
                                'payment_method' => $order->info['payment_method'],
                                'cc_type' => $order->info['cc_type'],
                                'cc_owner' => $order->info['cc_owner'],
                                'cc_number' => $order->info['cc_number'],
                                'cc_expires' => $order->info['cc_expires'],
                                'date_purchased' => 'now()',
                                'orders_status' => $order->info['order_status'],
                                'currency' => $order->info['currency'],
                                'currency_value' => $order->info['currency_value']);

        xtc_db_perform(TABLE_ORDERS, $sql_data_array);

        $insert_id = xtc_db_insert_id();

        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
          $sql_data_array = array('orders_id' => $insert_id,
                                  'title' => $order_totals[$i]['title'],
                                  'text' => $order_totals[$i]['text'],
                                  'value' => $order_totals[$i]['value'],
                                  'class' => $order_totals[$i]['code'],
                                  'sort_order' => $order_totals[$i]['sort_order']);

          xtc_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }

        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
          $sql_data_array = array('orders_id' => $insert_id,
                                  'products_id' => xtc_get_prid($order->products[$i]['id']),
                                  'products_model' => $order->products[$i]['model'],
                                  'products_name' => $order->products[$i]['name'],
                                  'products_price' => $order->products[$i]['price'],
                                  'final_price' => $order->products[$i]['final_price'],
                                  'products_tax' => $order->products[$i]['tax'],
                                  'products_quantity' => $order->products[$i]['qty']);

          xtc_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

          $order_products_id = xtc_db_insert_id();

          $attributes_exist = '0';
          if (isset($order->products[$i]['attributes'])) {
            $attributes_exist = '1';
            for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
              if (DOWNLOAD_ENABLED == 'true') {
                $attributes_query = "select popt.products_options_name,
                                            poval.products_options_values_name,
                                            pa.options_values_price,
                                            pa.price_prefix,
                                            pad.products_attributes_maxdays,
                                            pad.products_attributes_maxcount,
                                            pad.products_attributes_filename
                                     from " . TABLE_PRODUCTS_OPTIONS . " popt,
                                          " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                          " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                     left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                     on pa.products_attributes_id=pad.products_attributes_id
                                     where pa.products_id = '" . $order->products[$i]['id'] . "'
                                     and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                     and pa.options_id = popt.products_options_id
                                     and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                     and pa.options_values_id = poval.products_options_values_id
                                     and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                     and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'";
                $attributes = xtc_db_query($attributes_query);
              } else {
                $attributes = xtc_db_query("select popt.products_options_name,
                                                   poval.products_options_values_name,
                                                   pa.options_values_price,
                                                   pa.price_prefix
                                            from " . TABLE_PRODUCTS_OPTIONS . " popt,
                                                 " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                                 " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                            where pa.products_id = '" . $order->products[$i]['id'] . "'
                                            and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                            and pa.options_id = popt.products_options_id
                                            and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                            and pa.options_values_id = poval.products_options_values_id
                                            and popt.language_id = '" . (int)$_SESSION['languages_id']. "'
                                            and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'");
              }
              $attributes_values = xtc_db_fetch_array($attributes);

              $sql_data_array = array('orders_id' => $insert_id,
                                      'orders_products_id' => $order_products_id,
                                      'products_options' => $attributes_values['products_options_name'],
                                      'products_options_values' => $attributes_values['products_options_values_name'],
                                      'options_values_price' => $attributes_values['options_values_price'],
                                      'price_prefix' => $attributes_values['price_prefix']);

              xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

              if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && xtc_not_null($attributes_values['products_attributes_filename'])) {
                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                        'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                        'download_count' => $attributes_values['products_attributes_maxcount']);

                xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
              }
            }
          }
        }

        //$cart_Worldpay_Junior_ID = $cartID . '-' . $insert_id;
        //xtc_session_register('cart_Worldpay_Junior_ID');
        $_SESSION['cart_Worldpay_Junior_ID'] = $cartID . '-' . $insert_id;
      }

      return false;
    }

    function process_button() {
      global $order;

      $order_id = substr($_SESSION['cart_Worldpay_Junior_ID'], strpos($_SESSION['cart_Worldpay_Junior_ID'], '-')+1);

      $lang_query = xtc_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$_SESSION['languages_id'] . "'");
      $lang = xtc_db_fetch_array($lang_query);

      $process_button_string = xtc_draw_hidden_field('instId', MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID) .
                               xtc_draw_hidden_field('amount', $this->format_raw($order->info['total'])) .
                               xtc_draw_hidden_field('currency', $_SESSION['currency']) .
                               xtc_draw_hidden_field('hideCurrency', 'true') .
                               xtc_draw_hidden_field('cartId', $order_id) .
                               xtc_draw_hidden_field('desc', STORE_NAME) .
                               xtc_draw_hidden_field('name', $order->billing['firstname'] . ' ' . $order->billing['lastname']) .
                               xtc_draw_hidden_field('address', $order->billing['street_address']) .
                               xtc_draw_hidden_field('postcode', $order->billing['postcode']) .
                               xtc_draw_hidden_field('country', $order->billing['country']['iso_code_2']) .
                               xtc_draw_hidden_field('tel', $order->customer['telephone']) .
                               xtc_draw_hidden_field('email', $order->customer['email_address']) .
                               xtc_draw_hidden_field('fixContact', 'Y') .
                               xtc_draw_hidden_field('lang', strtoupper($lang['code'])) .
                               xtc_draw_hidden_field('signatureFields', 'amount:currency:cartId') .
                               xtc_draw_hidden_field('signature', md5(MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD . ':' . $this->format_raw($order->info['total']) . ':' . $_SESSION['currency'] . ':' . $order_id)) .
                               xtc_draw_hidden_field('MC_callback', substr(xtc_href_link('callback/worldpay/junior_callback.php', '', 'NONSSL', false, false), strpos(xtc_href_link('callback/worldpay/junior_callback.php', '', 'NONSSL', false, false), '://')+3));

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD == 'Pre-Authorization') {
        $process_button_string .= xtc_draw_hidden_field('authMode', 'E');
      }

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
        $process_button_string .= xtc_draw_hidden_field('testMode', '100');
      }

      $process_button_string .= xtc_draw_hidden_field('M_sid', xtc_session_id()) .
                                xtc_draw_hidden_field('M_cid', $_SESSION['customer_id']) .
                                xtc_draw_hidden_field('M_lang', $_SESSION['language']) .
                                xtc_draw_hidden_field('M_hash', md5(xtc_session_id() . $_SESSION['customer_id'] . $order_id . $_SESSION['language'] . number_format($order->info['total'], 2) . MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD));

      return $process_button_string;
    }

    function before_process() {
      global $order, $order_totals, $payment, $currencies;
      global ${$payment};

      $order_id = substr($_SESSION['cart_Worldpay_Junior_ID'], strpos($_SESSION['cart_Worldpay_Junior_ID'], '-')+1);

      $check_query = xtc_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      if (xtc_db_num_rows($check_query)) {
        $check = xtc_db_fetch_array($check_query);

        if ($check['orders_status'] == MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID) {
          $hash_result = false;

          if (isset(${$_GET['hash']}) && !empty(${$_GET['hash']}) && (${$_GET['hash']} == md5(xtc_session_name() . $_SESSION['customer_id'] . $order_id . $_SESSION['language'] . number_format($order->info['total'], 2) . MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD))) {
            $hash_result = true;
          }

          $sql_data_array = array('orders_id' => $order_id,
                                  'orders_status_id' => MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => (($hash_result == true) ? 'WorldPay: Transaction Verified' : 'WorldPay: Incorrect Transaction Hash'));

          xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

          if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
            $sql_data_array = array('orders_id' => $order_id,
                                    'orders_status_id' => MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE);

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
          }
        }
      }

      xtc_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID) . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => (MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      // initialized for the email confirmation
      $products_ordered = '';
      $subtotal = 0;
      $total_tax = 0;

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        // Stock Update - Joao Correia
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                FROM " . TABLE_PRODUCTS . " p
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                ON p.products_id=pa.products_id
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                ON pa.products_attributes_id=pad.products_attributes_id
                                WHERE p.products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'";
            // Will work with only one option for downloadable products
            // otherwise, we have to build the query dynamically with a loop
            $products_attributes = $order->products[$i]['attributes'];
            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
            }
            $stock_query = xtc_db_query($stock_query_raw);
          } else {
            $stock_query = xtc_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");
          }
          if (xtc_db_num_rows($stock_query) > 0) {
            $stock_values = xtc_db_fetch_array($stock_query);
            // do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
            } else {
              $stock_left = $stock_values['products_quantity'];
            }
            xtc_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");
            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
              xtc_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");
            }
          }
        }

        // Update products_ordered (for bestsellers list)
        xtc_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . xtc_get_prid($order->products[$i]['id']) . "'");

        //------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = "select popt.products_options_name,
                                          poval.products_options_values_name,
                                          pa.options_values_price,
                                          pa.price_prefix,
                                          pad.products_attributes_maxdays,
                                          pad.products_attributes_maxcount,
                                          pad.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt,
                                        " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                        " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                   on pa.products_attributes_id=pad.products_attributes_id
                                   where pa.products_id = '" . $order->products[$i]['id'] . "'
                                   and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                   and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'";
              $attributes = xtc_db_query($attributes_query);
            } else {
              $attributes = xtc_db_query("select popt.products_options_name,
                                                 poval.products_options_values_name,
                                                 pa.options_values_price,
                                                 pa.price_prefix
                                          from " . TABLE_PRODUCTS_OPTIONS . " popt,
                                               " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval,
                                               " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                         where pa.products_id = '" . $order->products[$i]['id'] . "'
                                         and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                         and pa.options_id = popt.products_options_id
                                         and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                         and pa.options_values_id = poval.products_options_values_id
                                         and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                         and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'");
            }
            $attributes_values = xtc_db_fetch_array($attributes);

            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }
        //------insert customer choosen option eof ----
        $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
        $total_tax += xtc_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
        $total_cost += $total_products_price;

        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

      // lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= xtc_db_output($order->info['comments']) . "\n\n";
      }
      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        xtc_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      xtc_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n";

      if (is_object(${$payment})) {
        $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
        $payment_class = ${$payment};
        $email_order .= $payment_class->title . "\n\n";
        if ($payment_class->email_footer) {
          $email_order .= $payment_class->email_footer . "\n\n";
        }
      }

      xtc_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

      // send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        xtc_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

      // load the after_process function from the payment modules
      $this->after_process();

      $_SESSION['cart']->reset(true);

      // unregister session variables used during checkout
      // xtc_session_unregister('sendto');
      // xtc_session_unregister('billto');
      // xtc_session_unregister('shipping');
      // xtc_session_unregister('payment');
      // xtc_session_unregister('comments');
      // xtc_session_unregister('cart_Worldpay_Junior_ID');
      unset($_SESSION['sendto']);
      unset($_SESSION['billto']);
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
      unset($_SESSION['comments']);
      unset($_SESSION['cart_Worldpay_Junior_ID']);

      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }

    function after_process() {
      return false;
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      $check_query = xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Preparing [WorldPay]' limit 1");

      if (xtc_db_num_rows($check_query) < 1) {
        $status_query = xtc_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
        $status = xtc_db_fetch_array($status_query);

        $status_id = $status['status_id']+1;

        $languages = xtc_get_languages();

        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          xtc_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $languages[$i]['id'] . "', 'Preparing [WorldPay]')");
        }

        $flags_query = xtc_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (xtc_db_num_rows($flags_query) == 1) {
          xtc_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
        }
      } else {
        $check = xtc_db_fetch_array($check_query);

        $status_id = $check['orders_status_id'];
      }

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD', 'Capture', '6', '0', 'xtc_cfg_select_option(array(\'Pre-Authorization\', \'Capture\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID', '" . (int)$status_id . "', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID', '0', '6', '0', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_WORLDPAY_JUNIOR_STATUS',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_ALLOWED',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_INSTALLATION_ID',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_TRANSACTION_METHOD',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_ZONE',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID',
                   'MODULE_PAYMENT_WORLDPAY_JUNIOR_SORT_ORDER',
                   );
    }

    // format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies;

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(xtc_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
  }
?>