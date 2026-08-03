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

  // Authenticate and persist the provider result before accessing a customer session.
  define('SESSION_FORCE_COOKIE_USE', 'False');
  define('RUN_MODE_NOSESSION', true);

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

  $provider_success = (string)$_POST['transStatus'] === 'Y';
  $provider_transaction_id = isset($_POST['transId']) && is_scalar($_POST['transId'])
    ? (string)$_POST['transId']
    : '';
  $result = worldpay_junior::process_callback(
    $callback,
    $provider_success,
    $provider_transaction_id
  );
  if (!is_array($result)) {
    http_response_code(404);
    exit;
  }

  // A verified payment always wins over a later duplicate cancellation callback.
  $success = $result['success'] === true;
  if ($success === false && !worldpay_junior::cancel_temporary_order($callback)) {
    http_response_code(409);
    exit;
  }

  $return_session_id = $callback['session_id'];
  $checkout_token = isset($callback['checkout_token'])
                    && is_string($callback['checkout_token'])
    ? $callback['checkout_token']
    : '';

  // Payment persistence does not depend on the customer session. If it is still
  // available, resume it after authentication and use its current checkout token.
  if ($success === true) {
    $_SESSION = array();
    xtc_session_id($return_session_id);
    if (xtc_session_start()
        && isset($_SESSION['customer_id'])
        && (int)$_SESSION['customer_id'] === (int)$callback['customers_id']
        )
    {
      $checkout_matches = $callback['legacy'] === true
                          || (isset($_SESSION['checkout_processing_key'])
                              && is_string($_SESSION['checkout_processing_key'])
                              && hash_equals($_SESSION['checkout_processing_key'], $callback['checkout_key']));
      if ($checkout_matches) {
        require_once(DIR_WS_CLASSES.'checkout.php');
        $checkout = new checkout($callback['customers_id']);
        $_SESSION['payment'] = 'worldpay_junior';
        $_SESSION['tmp_oID'] = (int)$callback['orders_id'];
        $checkout_token = isset($_SESSION['checkout_processing_phase_token'])
                          && is_string($_SESSION['checkout_processing_phase_token'])
          ? $_SESSION['checkout_processing_phase_token']
          : '';
      }
    }
  }

  if ($success === true) {
    $parameters = xtc_session_name().'='.rawurlencode($return_session_id);
    if (preg_match('/^[a-f0-9]{64}$/', $checkout_token)) {
      $parameters .= '&checkout_token='.rawurlencode($checkout_token);
    }
    $return_url = xtc_href_link(FILENAME_CHECKOUT_PROCESS, $parameters, 'SSL', false);
    $message = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_SUCCESSFUL_TRANSACTION;
  } else {
    $parameters = xtc_session_name().'='.rawurlencode($return_session_id)
                  .'&payment_error=worldpay_junior';
    $return_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $parameters, 'SSL', false);
    $message = MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_UNSUCCESSFUL_TRANSACTION;
  }

  $charset = isset($_SESSION['language_charset']) ? $_SESSION['language_charset'] : 'UTF-8';
  if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
  }
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
