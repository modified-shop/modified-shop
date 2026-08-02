<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2011 osCommerce(advanced_search_result.php,v 1.68 2003/05/14)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  chdir('../../');
  require('includes/application_top.php');
  require_once(DIR_WS_MODULES.'payment/worldpay_junior.php');

  $callback = worldpay_junior::validate_callback($_POST);
  if ($callback === false
      || !isset($_POST['transStatus'])
      || !is_scalar($_POST['transStatus'])
      )
  {
    http_response_code(403);
    exit;
  }

  $language = $callback['language'];
  $language_file = DIR_WS_LANGUAGES.$language.'/modules/payment/worldpay_junior.php';
  if (is_file($language_file)) {
    require_once($language_file);
  } else {
    require_once(DIR_WS_LANGUAGES.'english/modules/payment/worldpay_junior.php');
  }

  $order_query = xtc_db_query("SELECT orders_id,
                                      orders_status,
                                      currency,
                                      payment_class
                                 FROM ".TABLE_ORDERS."
                                WHERE orders_id = '".(int)$callback['order_id']."'
                                  AND customers_id = '".(int)$callback['customers_id']."'
                                  AND payment_class = 'worldpay_junior'
                                LIMIT 1");
  if (xtc_db_num_rows($order_query) !== 1) {
    http_response_code(404);
    exit;
  }

  $order = xtc_db_fetch_array($order_query);
  if ((string)$order['currency'] !== $callback['currency']) {
    http_response_code(403);
    exit;
  }

  $success = (string)$_POST['transStatus'] === 'Y';
  $verified = false;
  $prepare_status = MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID > 0
    ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID
    : (int)DEFAULT_ORDERS_STATUS_ID;
  $success_status = MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID > 0
    ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID
    : (int)DEFAULT_ORDERS_STATUS_ID;

  $verified_query = xtc_db_query("SELECT orders_status_history_id
                                   FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                   WHERE orders_id = '".(int)$callback['order_id']."'
                                     AND comments = '".xtc_db_input(worldpay_junior::VERIFIED_COMMENT)."'
                                   LIMIT 1");
  $already_verified = xtc_db_num_rows($verified_query) === 1;

  if ($already_verified === true) {
    $success = true;
    $verified = true;
  } elseif ($success === true) {
    xtc_db_query("UPDATE ".TABLE_ORDERS."
                     SET orders_status = '".$success_status."',
                         last_modified = NOW()
                   WHERE orders_id = '".(int)$callback['order_id']."'
                     AND customers_id = '".(int)$callback['customers_id']."'
                     AND payment_class = 'worldpay_junior'
                     AND orders_status = '".$prepare_status."'");

    $success_order_query = xtc_db_query("SELECT orders_id
                                           FROM ".TABLE_ORDERS."
                                          WHERE orders_id = '".(int)$callback['order_id']."'
                                            AND customers_id = '".(int)$callback['customers_id']."'
                                            AND payment_class = 'worldpay_junior'
                                            AND orders_status = '".$success_status."'
                                          LIMIT 1");
    if (xtc_db_num_rows($success_order_query) === 1) {
      $sql_data_array = array(
        'orders_id' => (int)$callback['order_id'],
        'orders_status_id' => $success_status,
        'date_added' => 'now()',
        'customer_notified' => '0',
        'comments' => worldpay_junior::VERIFIED_COMMENT,
      );
      xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
        $sql_data_array['comments'] = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE;
        xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
    }

    $verified_query = xtc_db_query("SELECT o.orders_id
                                      FROM ".TABLE_ORDERS." o
                                     WHERE o.orders_id = '".(int)$callback['order_id']."'
                                       AND o.customers_id = '".(int)$callback['customers_id']."'
                                       AND o.payment_class = 'worldpay_junior'
                                       AND o.orders_status = '".$success_status."'
                                       AND EXISTS (
                                             SELECT 1
                                              FROM ".TABLE_ORDERS_STATUS_HISTORY." osh
                                              WHERE osh.orders_id = o.orders_id
                                                AND osh.comments = '".xtc_db_input(worldpay_junior::VERIFIED_COMMENT)."'
                                           )
                                     LIMIT 1");
    $verified = xtc_db_num_rows($verified_query) === 1;
  } elseif ((int)$order['orders_status'] === $prepare_status) {
    require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
    xtc_remove_order((int)$callback['order_id'], STOCK_LIMITED == 'true' ? 'on' : false);
  }

  if ($success === true && $verified === false) {
    http_response_code(409);
    exit;
  }

  if ($success === true) {
    $parameters = xtc_session_name().'='.rawurlencode($callback['session_id'])
                  .'&checkout_token='.rawurlencode($callback['checkout_token']);
    $return_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, $parameters, 'SSL', false);
    $message = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_SUCCESSFUL_TRANSACTION;
  } else {
    $parameters = xtc_session_name().'='.rawurlencode($callback['session_id'])
                  .'&payment_error=worldpay_junior';
    $return_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $parameters, 'SSL', false);
    $message = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_UNSUCCESSFUL_TRANSACTION;
  }

  $charset = isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : 'UTF-8';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="<?php echo htmlspecialchars($charset, ENT_QUOTES, $charset); ?>">
  <meta http-equiv="refresh" content="5;url=<?php echo $return_url; ?>">
  <title><?php echo htmlspecialchars(STORE_NAME, ENT_QUOTES, $charset); ?></title>
  <style>
    .pageHeading {
      font-family: Verdana, Arial, sans-serif;
      font-size: 20px;
      font-weight: bold;
      color: #9a9a9a;
    }
    .main {
      font-family: Verdana, Arial, sans-serif;
      font-size: 11px;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <p class="pageHeading"><?php echo htmlspecialchars(STORE_NAME, ENT_QUOTES, $charset); ?></p>
  <p class="main" align="center"><?php echo $message; ?></p>
  <form action="<?php echo $return_url; ?>" method="post">
    <div align="center">
      <input name="submit" type="submit" value="<?php echo htmlspecialchars(sprintf(MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONTINUE_BUTTON, STORE_NAME), ENT_QUOTES, $charset); ?>">
    </div>
  </form>
  <p>&nbsp;</p>
  <WPDISPLAY ITEM=banner>
</body>
</html>
