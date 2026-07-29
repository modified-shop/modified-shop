<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function checkout_processing_get_key()
{
  if (!isset($_SESSION['checkout_processing_key'])
      || !preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_key'])
      )
  {
    $_SESSION['checkout_processing_key'] = bin2hex(random_bytes(32));
    unset($_SESSION['checkout_completed_order_id']);
  }

  return $_SESSION['checkout_processing_key'];
}

function checkout_processing_create_key()
{
  $_SESSION['checkout_processing_key'] = bin2hex(random_bytes(32));
  unset($_SESSION['checkout_completed_order_id']);

  return $_SESSION['checkout_processing_key'];
}

function checkout_processing_find($checkout_key, $customers_id)
{
  $processing_query = xtc_db_query("SELECT checkout_key,
                                           customers_id,
                                           orders_id,
                                           processing_status,
                                           date_added,
                                           last_modified
                                      FROM " . TABLE_CHECKOUT_PROCESSING . "
                                     WHERE checkout_key = '" . xtc_db_input($checkout_key) . "'
                                       AND customers_id = '" . (int)$customers_id . "'
                                     LIMIT 1");

  if (xtc_db_num_rows($processing_query) === 1) {
    return xtc_db_fetch_array($processing_query);
  }

  return false;
}

function checkout_processing_claim($checkout_key, $customers_id)
{
  xtc_db_query("INSERT IGNORE INTO " . TABLE_CHECKOUT_PROCESSING . "
                            (checkout_key, customers_id, processing_status, date_added, last_modified)
                     VALUES ('" . xtc_db_input($checkout_key) . "',
                             '" . (int)$customers_id . "',
                             'processing',
                             NOW(),
                             NOW())");

  return xtc_db_affected_rows() === 1;
}

function checkout_processing_set_order($checkout_key, $customers_id, $orders_id)
{
  xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                   SET orders_id = '" . (int)$orders_id . "',
                       last_modified = NOW()
                 WHERE checkout_key = '" . xtc_db_input($checkout_key) . "'
                   AND customers_id = '" . (int)$customers_id . "'
                   AND processing_status = 'processing'");
}

function checkout_processing_complete($checkout_key, $customers_id, $orders_id)
{
  xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                   SET orders_id = '" . (int)$orders_id . "',
                       processing_status = 'completed',
                       last_modified = NOW()
                 WHERE checkout_key = '" . xtc_db_input($checkout_key) . "'
                   AND customers_id = '" . (int)$customers_id . "'
                   AND processing_status = 'processing'");
}
