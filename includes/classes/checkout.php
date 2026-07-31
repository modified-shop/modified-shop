<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class checkout
{
  private $customers_id;
  private $processing_key;

  function __construct($customers_id)
  {
    $this->customers_id = (int)$customers_id;
    $this->processing_key = $this->get_or_create_key();
  }

  function get_key()
  {
    return $this->processing_key;
  }

  function get_processing_parameters()
  {
    $status_query = xtc_db_query("SELECT status_token
                                    FROM " . TABLE_CHECKOUT_PROCESSING . "
                                   WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                                     AND customers_id = '" . $this->customers_id . "'
                                   LIMIT 1");

    if (xtc_db_num_rows($status_query) === 1) {
      $status = xtc_db_fetch_array($status_query);
      if (!preg_match('/^[a-f0-9]{64}$/', $status['status_token'])) {
        return false;
      }

      return 'checkout_key=' . rawurlencode($this->processing_key)
             . '&status_token=' . rawurlencode($status['status_token']);
    }

    return false;
  }

  function create_key()
  {
    $this->processing_key = bin2hex(random_bytes(32));
    $_SESSION['checkout_processing_key'] = $this->processing_key;
    unset($_SESSION['checkout_completed_order_id']);
    unset($_SESSION['checkout_processing_owner_token']);

    return $this->processing_key;
  }

  function find()
  {
    $processing_query = xtc_db_query("SELECT checkout_key,
                                             customers_id,
                                             orders_id,
                                             processing_status,
                                             date_added,
                                             last_modified
                                        FROM " . TABLE_CHECKOUT_PROCESSING . "
                                       WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                                         AND customers_id = '" . $this->customers_id . "'
                                       LIMIT 1");

    if (xtc_db_num_rows($processing_query) === 1) {
      return xtc_db_fetch_array($processing_query);
    }

    return false;
  }

  static function find_status($processing_key, $status_token)
  {
    if (!preg_match('/^[a-f0-9]{64}$/', $processing_key)
        || !preg_match('/^[a-f0-9]{64}$/', $status_token)
        )
    {
      return false;
    }

    $timeout = self::get_processing_timeout();
    xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                     SET processing_status = 'failed',
                         last_modified = NOW()
                   WHERE checkout_key = '" . xtc_db_input($processing_key) . "'
                     AND status_token = '" . xtc_db_input($status_token) . "'
                     AND processing_status = 'processing'
                     AND last_modified < DATE_SUB(NOW(), INTERVAL " . $timeout . " SECOND)");

    $processing_query = xtc_db_query("SELECT orders_id,
                                             processing_status
                                        FROM " . TABLE_CHECKOUT_PROCESSING . "
                                       WHERE checkout_key = '" . xtc_db_input($processing_key) . "'
                                         AND status_token = '" . xtc_db_input($status_token) . "'
                                       LIMIT 1");

    if (xtc_db_num_rows($processing_query) === 1) {
      return xtc_db_fetch_array($processing_query);
    }

    return false;
  }

  function claim()
  {
    $owner_token = bin2hex(random_bytes(32));
    $status_token = bin2hex(random_bytes(32));
    $request_fingerprint = $this->get_request_fingerprint();

    xtc_db_query("INSERT IGNORE INTO " . TABLE_CHECKOUT_PROCESSING . "
                              (checkout_key, customers_id, owner_token, status_token, request_fingerprint, processing_status, date_added, last_modified)
                       VALUES ('" . xtc_db_input($this->processing_key) . "',
                               '" . $this->customers_id . "',
                               '" . xtc_db_input($owner_token) . "',
                               '" . xtc_db_input($status_token) . "',
                               '" . xtc_db_input($request_fingerprint) . "',
                               'processing',
                               NOW(),
                               NOW())");

    if (xtc_db_affected_rows() === 1) {
      $_SESSION['checkout_processing_owner_token'] = $owner_token;

      return true;
    }

    $previous_owner_token = $this->get_owner_token();
    if ($previous_owner_token !== false) {
      xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                       SET owner_token = '" . xtc_db_input($owner_token) . "',
                           request_fingerprint = '" . xtc_db_input($request_fingerprint) . "',
                           last_modified = NOW()
                     WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                       AND customers_id = '" . $this->customers_id . "'
                       AND owner_token = '" . xtc_db_input($previous_owner_token) . "'
                       AND request_fingerprint != '" . xtc_db_input($request_fingerprint) . "'
                       AND processing_status = 'processing'");

      if (xtc_db_affected_rows() === 1) {
        $_SESSION['checkout_processing_owner_token'] = $owner_token;

        return true;
      }
    }

    return false;
  }

  function set_order($orders_id)
  {
    $owner_token = $this->get_owner_token();
    if ($owner_token === false) {
      return false;
    }

    xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                     SET orders_id = '" . (int)$orders_id . "',
                         last_modified = NOW()
                   WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                     AND customers_id = '" . $this->customers_id . "'
                     AND owner_token = '" . xtc_db_input($owner_token) . "'
                     AND processing_status = 'processing'");

    return xtc_db_affected_rows() === 1;
  }

  function complete($orders_id)
  {
    $owner_token = $this->get_owner_token();
    if ($owner_token === false) {
      return false;
    }

    xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                     SET orders_id = '" . (int)$orders_id . "',
                         processing_status = 'completed',
                         last_modified = NOW()
                   WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                     AND customers_id = '" . $this->customers_id . "'
                     AND owner_token = '" . xtc_db_input($owner_token) . "'
                     AND processing_status = 'processing'");

    if (xtc_db_affected_rows() === 1) {
      unset($_SESSION['checkout_processing_owner_token']);

      return true;
    }

    return false;
  }

  function fail()
  {
    $owner_token = $this->get_owner_token();
    if ($owner_token === false) {
      return false;
    }

    xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                     SET processing_status = 'failed',
                         last_modified = NOW()
                   WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                     AND customers_id = '" . $this->customers_id . "'
                     AND owner_token = '" . xtc_db_input($owner_token) . "'
                     AND processing_status = 'processing'");

    if (xtc_db_affected_rows() === 1) {
      unset($_SESSION['checkout_processing_owner_token']);

      return true;
    }

    return false;
  }

  function expire()
  {
    $timeout = self::get_processing_timeout();

    xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                     SET processing_status = 'failed',
                         last_modified = NOW()
                   WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                     AND customers_id = '" . $this->customers_id . "'
                     AND processing_status = 'processing'
                     AND last_modified < DATE_SUB(NOW(), INTERVAL " . $timeout . " SECOND)");

    if (xtc_db_affected_rows() === 1) {
      unset($_SESSION['checkout_processing_owner_token']);

      return true;
    }

    return false;
  }

  function javascript_confirmation()
  {
    $js = '      function showCheckoutProcessing() {' . "\n" .
          '        var overlay = document.getElementById("checkout-processing-overlay");' . "\n" .
          '        if (overlay) {' . "\n" .
          '          overlay.style.display = "block";' . "\n" .
          '          overlay.setAttribute("aria-hidden", "false");' . "\n" .
          '        }' . "\n" .
          '      }' . "\n\n" .
          '      document.addEventListener("click", function (event) {' . "\n" .
          '        if (event.target && event.target.closest && event.target.closest("#button_checkout_confirmation")) {' . "\n" .
          '          window.setTimeout(function () {' . "\n" .
          '            if (!event.defaultPrevented) {' . "\n" .
          '              showCheckoutProcessing();' . "\n" .
          '            }' . "\n" .
          '          }, 0);' . "\n" .
          '        }' . "\n" .
          '      });' . "\n\n" .
          '      var form = document.getElementById("checkout_confirmation");' . "\n" .
          '      if (form) {' . "\n" .
          '        form.addEventListener("submit", showCheckoutProcessing);' . "\n" .
          '        if (window.jQuery) {' . "\n" .
          '          window.jQuery(form).on("submit.checkoutProcessing", showCheckoutProcessing);' . "\n" .
          '        }' . "\n" .
          '      }' . "\n";

    return self::javascript_wrapper($js);
  }

  static function javascript_processing()
  {
    $js = '      var container = document.querySelector(".checkout_processing");' . "\n\n" .
          '      var status = window.location.hash.match(/^#checkout_key=([a-f0-9]{64})&status_token=([a-f0-9]{64})$/);' . "\n" .
          '      if (!status) {' . "\n" .
          '        window.location.replace(container.getAttribute("data-error-url"));' . "\n" .
          '        return;' . "\n" .
          '      }' . "\n" .
          '      var statusUrl = container.getAttribute("data-status-url")' . "\n" .
          '                      + "&checkout_key=" + encodeURIComponent(status[1])' . "\n" .
          '                      + "&status_token=" + encodeURIComponent(status[2]);' . "\n\n" .
          '      function poll() {' . "\n" .
          '        fetch(statusUrl, {' . "\n" .
          '          credentials: "omit",' . "\n" .
          '          cache: "no-store"' . "\n" .
          '        })' . "\n" .
          '          .then(function (response) { return response.json(); })' . "\n" .
          '          .then(function (result) {' . "\n" .
          '            if (result.status === "completed") {' . "\n" .
          '              window.location.replace(container.getAttribute("data-success-url"));' . "\n" .
          '              return;' . "\n" .
          '            }' . "\n" .
          '            if (result.status === "failed" || result.status === "unknown") {' . "\n" .
          '              window.location.replace(container.getAttribute("data-error-url"));' . "\n" .
          '              return;' . "\n" .
          '            }' . "\n" .
          '            window.setTimeout(poll, 2000);' . "\n" .
          '          })' . "\n" .
          '          .catch(function () {' . "\n" .
          '            window.setTimeout(poll, 4000);' . "\n" .
          '          });' . "\n" .
          '      }' . "\n\n" .
          '      window.setTimeout(poll, 1000);' . "\n";

    return self::javascript_wrapper($js);
  }

  private static function javascript_wrapper($javascript)
  {
    return '<script type="text/javascript">' . "\n" .
           '  (function () {' . "\n" .
           '    function initializeCheckoutProcessing() {' . "\n" .
           $javascript .
           '    }' . "\n\n" .
           '    if (document.readyState === "loading") {' . "\n" .
           '      document.addEventListener("DOMContentLoaded", initializeCheckoutProcessing);' . "\n" .
           '    } else {' . "\n" .
           '      initializeCheckoutProcessing();' . "\n" .
           '    }' . "\n" .
           '  }());' . "\n" .
           '</script>' . "\n";
  }

  private function get_or_create_key()
  {
    if (!isset($_SESSION['checkout_processing_key'])
        || !preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_key'])
        )
    {
      return $this->create_key();
    }

    return $_SESSION['checkout_processing_key'];
  }

  private function get_owner_token()
  {
    if (isset($_SESSION['checkout_processing_owner_token'])
        && preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_owner_token'])
        )
    {
      return $_SESSION['checkout_processing_owner_token'];
    }

    return false;
  }

  private function get_request_fingerprint()
  {
    $request_data = array(
      'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
      'script' => isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : '',
      'get' => $_GET,
      'post' => $_POST,
    );

    return hash('sha256', serialize($request_data));
  }

  private static function get_processing_timeout()
  {
    $timeout = defined('CHECKOUT_PROCESSING_TIMEOUT') ? (int)CHECKOUT_PROCESSING_TIMEOUT : 1800;

    return max(1, $timeout);
  }
}
