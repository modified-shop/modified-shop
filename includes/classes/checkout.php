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
  private $phase_token;
  private $processing_key;

  function __construct($customers_id)
  {
    $this->customers_id = (int)$customers_id;
    $this->processing_key = $this->get_or_create_key();
    $this->phase_token = $this->get_or_create_phase_token();
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

  function get_processing_url($language)
  {
    $url_parameters = 'language=' . rawurlencode($language);
    $session_parameters = self::get_url_session_parameters();
    if ($session_parameters !== '') {
      $url_parameters .= '&' . $session_parameters;
    }

    $processing_url = xtc_href_link(FILENAME_CHECKOUT_PROCESSING, $url_parameters, 'SSL', false);
    $processing_parameters = $this->get_processing_parameters();
    if ($processing_parameters !== false) {
      $processing_url .= '#' . $processing_parameters;
    }

    return $processing_url;
  }

  static function get_url_session_parameters()
  {
    global $cookie, $session_started;

    $session_name = xtc_session_name();
    $session_id = xtc_session_id();
    $session_id_in_url = isset($_GET[$session_name])
                         && is_string($_GET[$session_name])
                         && hash_equals($_GET[$session_name], $session_id);
    if (($session_id_in_url || ($session_started === true && $cookie === false))
        && preg_match('/^(?:[a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i', $session_id)
        )
    {
      return $session_name . '=' . rawurlencode($session_id);
    }

    return '';
  }

  function create_key()
  {
    $this->processing_key = bin2hex(random_bytes(32));
    $_SESSION['checkout_processing_key'] = $this->processing_key;
    unset($_SESSION['checkout_completed_order_id']);
    unset($_SESSION['checkout_processing_owner_token']);
    unset($_SESSION['checkout_processing_retry_token']);
    $this->rotate_phase_token();

    return $this->processing_key;
  }

  function prepare_retry()
  {
    $_SESSION['checkout_processing_retry_token'] = bin2hex(random_bytes(32));
    $this->rotate_phase_token();
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
    if (!is_string($processing_key)
        || !is_string($status_token)
        || !preg_match('/^[a-f0-9]{64}$/', $processing_key)
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

    $processing_query = xtc_db_query("SELECT customers_id,
                                             orders_id,
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
    $request_token = '';
    if (isset($_GET['checkout_token']) && is_string($_GET['checkout_token'])) {
      $request_token = $_GET['checkout_token'];
    } elseif (isset($_POST['checkout_token']) && is_string($_POST['checkout_token'])) {
      $request_token = $_POST['checkout_token'];
    }
    if (!hash_equals($this->phase_token, $request_token)) {
      return false;
    }

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
      $this->rotate_phase_token();

      return true;
    }

    xtc_db_query("UPDATE " . TABLE_CHECKOUT_PROCESSING . "
                     SET owner_token = '" . xtc_db_input($owner_token) . "',
                         status_token = '" . xtc_db_input($status_token) . "',
                         request_fingerprint = '" . xtc_db_input($request_fingerprint) . "',
                         processing_status = 'processing',
                         last_modified = NOW()
                   WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                     AND customers_id = '" . $this->customers_id . "'
                     AND processing_status = 'failed'");

    if (xtc_db_affected_rows() === 1) {
      $_SESSION['checkout_processing_owner_token'] = $owner_token;
      $this->rotate_phase_token();

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
        $this->rotate_phase_token();

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

    if (xtc_db_affected_rows() === 1) {
      return true;
    }

    $processing_query = xtc_db_query("SELECT orders_id
                                        FROM " . TABLE_CHECKOUT_PROCESSING . "
                                       WHERE checkout_key = '" . xtc_db_input($this->processing_key) . "'
                                         AND customers_id = '" . $this->customers_id . "'
                                         AND owner_token = '" . xtc_db_input($owner_token) . "'
                                         AND orders_id = '" . (int)$orders_id . "'
                                         AND processing_status = 'processing'
                                       LIMIT 1");

    return xtc_db_num_rows($processing_query) === 1;
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
      unset($_SESSION['checkout_processing_retry_token']);

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
      unset($_SESSION['checkout_processing_retry_token']);

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
    $js = '      function getCheckoutProcessingOverlay() {' . "\n" .
          '        var overlay = document.getElementById("checkout-processing-overlay");' . "\n" .
          '        if (!overlay) {' . "\n" .
          '          overlay = document.createElement("div");' . "\n" .
          '          overlay.id = "checkout-processing-overlay";' . "\n" .
          '          overlay.setAttribute("role", "status");' . "\n" .
          '          overlay.setAttribute("aria-live", "polite");' . "\n" .
          '          overlay.setAttribute("aria-hidden", "true");' . "\n" .
          '          var content = document.createElement("div");' . "\n" .
          '          content.className = "checkout-processing-overlay-content";' . "\n" .
          '          var spinner = document.createElement("div");' . "\n" .
          '          spinner.className = "checkout-processing-spinner";' . "\n" .
          '          var title = document.createElement("h2");' . "\n" .
          '          title.textContent = ' . json_encode(TEXT_CHECKOUT_PROCESSING_TITLE, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';' . "\n" .
          '          var message = document.createElement("p");' . "\n" .
          '          message.textContent = ' . json_encode(TEXT_CHECKOUT_PROCESSING_MESSAGE, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';' . "\n" .
          '          content.appendChild(spinner);' . "\n" .
          '          content.appendChild(title);' . "\n" .
          '          content.appendChild(message);' . "\n" .
          '          overlay.appendChild(content);' . "\n" .
          '          document.body.appendChild(overlay);' . "\n" .
          '        }' . "\n" .
          '        return overlay;' . "\n" .
          '      }' . "\n\n" .
          '      function showCheckoutProcessing() {' . "\n" .
          '        var overlay = getCheckoutProcessingOverlay();' . "\n" .
          '        if (overlay) {' . "\n" .
          '          overlay.style.display = "block";' . "\n" .
          '          overlay.setAttribute("aria-hidden", "false");' . "\n" .
          '        }' . "\n" .
          '      }' . "\n\n" .
          '      function handleCheckoutSubmit(event) {' . "\n" .
          '        window.setTimeout(function () {' . "\n" .
          '          var originalEvent = event.originalEvent;' . "\n" .
          '          var defaultPrevented = event.defaultPrevented' . "\n" .
          '                                 || (originalEvent && originalEvent.defaultPrevented)' . "\n" .
          '                                 || (typeof event.isDefaultPrevented === "function" && event.isDefaultPrevented());' . "\n" .
          '          if (!defaultPrevented) {' . "\n" .
          '            showCheckoutProcessing();' . "\n" .
          '          }' . "\n" .
          '        }, 0);' . "\n" .
          '      }' . "\n\n" .
          '      var form = document.getElementById("checkout_confirmation");' . "\n" .
          '      getCheckoutProcessingOverlay();' . "\n" .
          '      if (form) {' . "\n" .
          '        if (window.jQuery) {' . "\n" .
          '          window.jQuery(form).on("submit.checkoutProcessing", handleCheckoutSubmit);' . "\n" .
          '        } else {' . "\n" .
          '          form.addEventListener("submit", handleCheckoutSubmit);' . "\n" .
          '        }' . "\n" .
          '      }' . "\n\n" .
          '      document.addEventListener("checkout:processing", showCheckoutProcessing);' . "\n";

    return self::javascript_wrapper($js);
  }

  static function javascript_processing()
  {
    $js = '      var container = document.querySelector(".checkout_processing");' . "\n\n" .
          '      function postRedirect(url, parameters) {' . "\n" .
          '        var form = document.createElement("form");' . "\n" .
          '        form.method = "post";' . "\n" .
          '        form.action = url;' . "\n" .
          '        Object.keys(parameters).forEach(function (name) {' . "\n" .
          '          var input = document.createElement("input");' . "\n" .
          '          input.type = "hidden";' . "\n" .
          '          input.name = name;' . "\n" .
          '          input.value = parameters[name];' . "\n" .
          '          form.appendChild(input);' . "\n" .
          '        });' . "\n" .
          '        document.body.appendChild(form);' . "\n" .
          '        form.submit();' . "\n" .
          '      }' . "\n\n" .
          '      function redirectToSuccess(checkoutKey, statusToken) {' . "\n" .
          '        postRedirect(container.getAttribute("data-success-url"), {' . "\n" .
          '          processing_success: "1",' . "\n" .
          '          checkout_key: checkoutKey,' . "\n" .
          '          status_token: statusToken' . "\n" .
          '        });' . "\n" .
          '      }' . "\n\n" .
          '      function redirectToError(checkoutKey, statusToken) {' . "\n" .
          '        postRedirect(container.getAttribute("data-error-url"), {' . "\n" .
          '          processing_error: "1",' . "\n" .
          '          checkout_key: checkoutKey,' . "\n" .
          '          status_token: statusToken' . "\n" .
          '        });' . "\n" .
          '      }' . "\n\n" .
          '      var status = new URLSearchParams(window.location.hash.substring(1));' . "\n" .
          '      var checkoutKey = status.get("checkout_key");' . "\n" .
          '      var statusToken = status.get("status_token");' . "\n" .
          '      if (!/^[a-f0-9]{64}$/.test(checkoutKey) || !/^[a-f0-9]{64}$/.test(statusToken)) {' . "\n" .
          '        window.location.replace(container.getAttribute("data-error-url"));' . "\n" .
          '        return;' . "\n" .
          '      }' . "\n" .
          '      var statusData = new FormData();' . "\n" .
          '      statusData.append("checkout_key", checkoutKey);' . "\n" .
          '      statusData.append("status_token", statusToken);' . "\n\n" .
          '      function poll() {' . "\n" .
          '        fetch(container.getAttribute("data-status-url"), {' . "\n" .
          '          method: "POST",' . "\n" .
          '          body: statusData,' . "\n" .
          '          credentials: "omit",' . "\n" .
          '          cache: "no-store"' . "\n" .
          '        })' . "\n" .
          '          .then(function (response) { return response.json(); })' . "\n" .
          '          .then(function (result) {' . "\n" .
          '            if (result.status === "completed") {' . "\n" .
          '              redirectToSuccess(checkoutKey, statusToken);' . "\n" .
          '              return;' . "\n" .
          '            }' . "\n" .
          '            if (result.status === "failed" || result.status === "unknown") {' . "\n" .
          '              redirectToError(checkoutKey, statusToken);' . "\n" .
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
        || !is_string($_SESSION['checkout_processing_key'])
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
        && is_string($_SESSION['checkout_processing_owner_token'])
        && preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_owner_token'])
        )
    {
      return $_SESSION['checkout_processing_owner_token'];
    }

    return false;
  }

  private function get_or_create_phase_token()
  {
    if (!isset($_SESSION['checkout_processing_phase_token'])
        || !is_string($_SESSION['checkout_processing_phase_token'])
        || !preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_phase_token'])
        )
    {
      return $this->rotate_phase_token();
    }

    return $_SESSION['checkout_processing_phase_token'];
  }

  private function rotate_phase_token()
  {
    $this->phase_token = bin2hex(random_bytes(32));
    $_SESSION['checkout_processing_phase_token'] = $this->phase_token;

    return $this->phase_token;
  }

  private function get_request_fingerprint()
  {
    $request_data = array(
      'method' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '',
      'script' => isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : '',
      'get' => $_GET,
      'post' => $_POST,
      'retry' => isset($_SESSION['checkout_processing_retry_token']) ? $_SESSION['checkout_processing_retry_token'] : '',
    );

    return hash('sha256', serialize($request_data));
  }

  private static function get_processing_timeout()
  {
    $timeout = defined('CHECKOUT_PROCESSING_TIMEOUT') ? (int)CHECKOUT_PROCESSING_TIMEOUT : 1800;

    return max(1, $timeout);
  }
}
