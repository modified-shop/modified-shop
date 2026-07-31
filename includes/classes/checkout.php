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

  function claim()
  {
    $owner_token = bin2hex(random_bytes(32));
    $request_fingerprint = $this->get_request_fingerprint();

    xtc_db_query("INSERT IGNORE INTO " . TABLE_CHECKOUT_PROCESSING . "
                              (checkout_key, customers_id, owner_token, request_fingerprint, processing_status, date_added, last_modified)
                       VALUES ('" . xtc_db_input($this->processing_key) . "',
                               '" . $this->customers_id . "',
                               '" . xtc_db_input($owner_token) . "',
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
    $timeout = defined('CHECKOUT_PROCESSING_TIMEOUT') ? (int)CHECKOUT_PROCESSING_TIMEOUT : 1800;
    $timeout = max(1, $timeout);

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
    $js = '<script type="text/javascript">' . "\n" .
          '  (function () {' . "\n" .
          '    function initializeCheckoutProcessing() {' . "\n" .
          '      function showCheckoutProcessing() {' . "\n" .
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
          '      }' . "\n" .
          '    }' . "\n\n" .
          '    if (document.readyState === "loading") {' . "\n" .
          '      document.addEventListener("DOMContentLoaded", initializeCheckoutProcessing);' . "\n" .
          '    } else {' . "\n" .
          '      initializeCheckoutProcessing();' . "\n" .
          '    }' . "\n" .
          '  }());' . "\n" .
          '</script>' . "\n";

    return $js;
  }

  function javascript_processing()
  {
    $js = '<script type="text/javascript">' . "\n" .
          '  (function () {' . "\n" .
          '    function initializeCheckoutProcessing() {' . "\n" .
          '      var container = document.querySelector(".checkout_processing");' . "\n\n" .
          '      function poll() {' . "\n" .
          '        fetch(container.getAttribute("data-status-url"), {' . "\n" .
          '          credentials: "same-origin",' . "\n" .
          '          cache: "no-store"' . "\n" .
          '        })' . "\n" .
          '          .then(function (response) { return response.json(); })' . "\n" .
          '          .then(function (result) {' . "\n" .
          '            if (result.status === "completed" && result.redirect) {' . "\n" .
          '              window.location.replace(result.redirect);' . "\n" .
          '              return;' . "\n" .
          '            }' . "\n" .
          '            if (result.status === "failed") {' . "\n" .
          '              window.location.replace(container.getAttribute("data-error-url"));' . "\n" .
          '              return;' . "\n" .
          '            }' . "\n" .
          '            window.setTimeout(poll, 2000);' . "\n" .
          '          })' . "\n" .
          '          .catch(function () {' . "\n" .
          '            window.setTimeout(poll, 4000);' . "\n" .
          '          });' . "\n" .
          '      }' . "\n\n" .
          '      window.setTimeout(poll, 1000);' . "\n" .
          '    }' . "\n\n" .
          '    if (document.readyState === "loading") {' . "\n" .
          '      document.addEventListener("DOMContentLoaded", initializeCheckoutProcessing);' . "\n" .
          '    } else {' . "\n" .
          '      initializeCheckoutProcessing();' . "\n" .
          '    }' . "\n" .
          '  }());' . "\n" .
          '</script>' . "\n";

    return $js;
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
}
