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

  if (isset($_POST['M_sid'])
      && is_scalar($_POST['M_sid'])
      && preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', (string)$_POST['M_sid'])
      )
  {
    define('SESSION_FORCE_COOKIE_USE', 'False');
    $_GET['MODsid'] = (string)$_POST['M_sid'];
  }

  chdir('../../');
  require('includes/application_top.php');
  require_once(DIR_WS_MODULES.'payment/worldpay_junior.php');

  $callback = worldpay_junior::validate_callback($_POST);
  if ($callback === false
      || !isset($_POST['transStatus'])
      || !is_scalar($_POST['transStatus'])
      || !isset($_SESSION['customer_id'])
      || (int)$_SESSION['customer_id'] !== (int)$callback['customers_id']
      || !hash_equals(xtc_session_id(), $callback['session_id'])
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

  $success = (string)$_POST['transStatus'] === 'Y';

  if ($callback['legacy'] === true) {
    // Resume legacy transactions created shortly before the update.
    $legacy_transaction = worldpay_junior::find_legacy_transaction(
      $callback['legacy_order_id'],
      $callback['customers_id'],
      $callback['session_id']
    );
    $legacy_order_query = xtc_db_query("SELECT o.orders_id,
                                               o.currency
                                          FROM ".TABLE_ORDERS." o
                                         WHERE o.orders_id = '".(int)$callback['legacy_order_id']."'
                                           AND o.customers_id = '".(int)$callback['customers_id']."'
                                           AND (o.payment_class = ''
                                                OR o.payment_class IS NULL
                                                OR o.payment_class = 'worldpay_junior')
                                           AND o.currency = '".xtc_db_input($callback['currency'])."'
                                           AND o.date_purchased >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                           AND NOT EXISTS (
                                                 SELECT 1
                                                   FROM ".TABLE_ORDERS_STATUS_HISTORY." osh
                                                  WHERE osh.orders_id = o.orders_id
                                               )
                                         LIMIT 1");
    $legacy_order_exists = xtc_db_num_rows($legacy_order_query) === 1;
    if ($legacy_order_exists === false && $legacy_transaction === false) {
      http_response_code(404);
      exit;
    }

    require_once(DIR_WS_CLASSES.'checkout.php');
    $checkout = new checkout($callback['customers_id']);
    if (is_array($legacy_transaction)) {
      $checkout_key = $legacy_transaction['checkout_key'];
      $checkout_token = $legacy_transaction['checkout_token'];
    } else {
      $checkout_key = $checkout->get_key();
      $checkout_token = isset($_SESSION['checkout_processing_phase_token'])
                        && is_string($_SESSION['checkout_processing_phase_token'])
        ? $_SESSION['checkout_processing_phase_token']
        : '';
    }
    if (!preg_match('/^[a-f0-9]{64}$/', $checkout_key)
        || !preg_match('/^[a-f0-9]{64}$/', $checkout_token)
        )
    {
      http_response_code(409);
      exit;
    }

    $transaction = worldpay_junior::migrate_legacy_transaction(
      $callback,
      $checkout_key,
      $checkout_token,
      $success
    );
    if (!is_array($transaction)) {
      http_response_code(409);
      exit;
    }

    if ($legacy_order_exists) {
      require_once(DIR_FS_INC.'xtc_remove_order.inc.php');
      xtc_remove_order((int)$callback['legacy_order_id'], false);
    }
    unset($_SESSION['cart_Worldpay_Junior_ID'], $_SESSION['tmp_oID'], $_SESSION['tmp_worldpay_oID']);
    $_SESSION['payment'] = 'worldpay_junior';

    if (in_array($transaction['transaction_status'], array('verified', 'completed'))) {
      $success = true;
    }
    $callback['checkout_token'] = $checkout_token;
  } else {
    $provider_transaction_id = isset($_POST['transId']) && is_scalar($_POST['transId'])
      ? substr(trim((string)$_POST['transId']), 0, 128)
      : '';
    $transaction = worldpay_junior::process_callback($callback, $success, $provider_transaction_id);
    if (!is_array($transaction)) {
      http_response_code(409);
      exit;
    }

    if (in_array($transaction['transaction_status'], array('verified', 'completed'))) {
      $success = true;
    }
  }

  if ($success === false) {
    if (!isset($checkout) || !is_object($checkout)) {
      require_once(DIR_WS_CLASSES.'checkout.php');
      $checkout = new checkout($callback['customers_id']);
    }
    if ($callback['legacy'] === true || hash_equals($checkout->get_key(), $callback['checkout_key'])) {
      $checkout->fail();
    }
  } else {
    $_SESSION['payment'] = 'worldpay_junior';
    $_SESSION['worldpay_junior_callback_transaction_id'] = (int)$transaction['worldpay_id'];
    if (isset($_SESSION['checkout_processing_phase_token'])
        && is_string($_SESSION['checkout_processing_phase_token'])
        && preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_phase_token'])
        )
    {
      $callback['checkout_token'] = $_SESSION['checkout_processing_phase_token'];
    }
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
  session_write_close();
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
