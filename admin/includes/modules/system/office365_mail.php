<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_FS_INC . 'xtc_random_charcode.inc.php');
require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/oauth_callback_state.php');
require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/oauth_http_client.php');

class office365_mail {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;
    var $properties;
    var $httpClient;

  function __construct() {
    $this->code = 'office365_mail';
    $this->title = MODULE_OFFICE365_MAIL_TEXT_TITLE;
    $this->description = MODULE_OFFICE365_MAIL_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_OFFICE365_MAIL_SORT_ORDER')) ? MODULE_OFFICE365_MAIL_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_OFFICE365_MAIL_STATUS') && MODULE_OFFICE365_MAIL_STATUS == 'true') ? true : false);
    $this->properties = array(
      'form_restore' => xtc_draw_form(
        'modules',
        FILENAME_MODULE_EXPORT,
        'set=system&module=' . $this->code . '&action=custom&oauth_action=restore'
      ),
    );
  }

  function process($file) {
    if (isset($_POST['configuration']) && is_array($_POST['configuration'])) {
      $this->invalidate_refresh_token_on_configuration_change($_POST['configuration']);

      if (isset($_POST['configuration']['MODULE_OFFICE365_MAIL_STATUS'])
          && $_POST['configuration']['MODULE_OFFICE365_MAIL_STATUS'] == 'true'
          )
      {
        $this->disable_google_mail();
      }
    }
  }

  function display() {
    $oauth_error = MODULE_OFFICE365_MAIL_OAUTH_ERROR;
    $connected = ($oauth_error == ''
      && defined('MODULE_OFFICE365_MAIL_REFRESH_TOKEN')
      && MODULE_OFFICE365_MAIL_REFRESH_TOKEN != ''
    );
    $sender = (defined('MODULE_OFFICE365_MAIL_SENDER_EMAIL') ? MODULE_OFFICE365_MAIL_SENDER_EMAIL : '');

    if ($oauth_error != '') {
      $status_text = sprintf(
        MODULE_OFFICE365_MAIL_TEXT_CONNECTION_ERROR,
        htmlspecialchars($oauth_error, ENT_QUOTES, 'UTF-8')
      );
    } else {
      $status_text = $connected
        ? sprintf(MODULE_OFFICE365_MAIL_TEXT_CONNECTED_AS, $sender)
        : MODULE_OFFICE365_MAIL_TEXT_NOT_CONNECTED;
    }

    $connect_href = xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&action=custom');
    $connect_button = '<button type="submit" class="button" onclick="this.blur();" formaction="' . $connect_href . '">' . MODULE_OFFICE365_MAIL_TEXT_CONNECT_BUTTON . '</button>';
    $cancel_button = xtc_button_link(
      BUTTON_CANCEL,
      xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code)
    );

    return array('text' => '<br>' . $status_text .
                           '<br><br>' . $connect_button . $cancel_button .
                           '<br><br>' . MODULE_OFFICE365_MAIL_TEXT_FROM_ADDRESS_HINT .
                           '<br><br>' . sprintf(MODULE_OFFICE365_MAIL_TEXT_SETUP_GUIDE, $this->get_redirect_uri())
                 );
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_OFFICE365_MAIL_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value
                                       FROM " . TABLE_CONFIGURATION . "
                                      WHERE configuration_key = 'MODULE_OFFICE365_MAIL_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }

      if ($this->_check) {
        $secret_query = xtc_db_query("SELECT use_function, set_function
                                       FROM " . TABLE_CONFIGURATION . "
                                      WHERE configuration_key = 'MODULE_OFFICE365_MAIL_CLIENT_SECRET'");
        if (xtc_db_num_rows($secret_query) > 0) {
          $secret_config = xtc_db_fetch_array($secret_query);
          if ($secret_config['use_function'] !== 'xtc_cfg_display_password'
              || $secret_config['set_function'] !== 'xtc_cfg_password_field_module('
              )
          {
            $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' .
              xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&action=update') .
              '">' . BUTTON_UPDATE . '</a>';
          }
        }
      }
    }
    return $this->_check;
  }

  function install() {
    oauth_callback_state::install();
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_OFFICE365_MAIL_STATUS', 'false', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_OFFICE365_MAIL_TENANT', '', '6', '2', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_OFFICE365_MAIL_CLIENT_ID', '', '6', '3', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_OFFICE365_MAIL_CLIENT_SECRET', '', '6', '4', 'xtc_cfg_display_password', 'xtc_cfg_password_field_module(', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_OFFICE365_MAIL_SENDER_EMAIL', '', '6', '5', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_OFFICE365_MAIL_REFRESH_TOKEN', '', '6', '6', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_OFFICE365_MAIL_OAUTH_ERROR', '', '6', '7', now())");
  }

  function remove() {
    $this->clear_oauth_state();
    oauth_callback_state::deleteModuleStates($this->code);
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_OFFICE365_MAIL_%'");
  }

  function update() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET use_function = 'xtc_cfg_display_password',
                         set_function = 'xtc_cfg_password_field_module('
                   WHERE configuration_key = 'MODULE_OFFICE365_MAIL_CLIENT_SECRET'");
  }

  function keys() {
    return array(
      'MODULE_OFFICE365_MAIL_STATUS',
      'MODULE_OFFICE365_MAIL_TENANT',
      'MODULE_OFFICE365_MAIL_CLIENT_ID',
      'MODULE_OFFICE365_MAIL_CLIENT_SECRET',
      'MODULE_OFFICE365_MAIL_SENDER_EMAIL',
    );
  }

  function custom() {
    global $messageStack;

    if (isset($_GET['oauth_action']) && $_GET['oauth_action'] === 'restore') {
      xtc_restore_configuration($this->keys());
      $this->delete_refresh_token();
      $this->clear_oauth_state();
      $messageStack->add_session(MODULE_RESTORE_CONFIRM, 'success');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    $posted = $this->save_posted_configuration();

    $status = isset($posted['MODULE_OFFICE365_MAIL_STATUS'])
      ? $posted['MODULE_OFFICE365_MAIL_STATUS']
      : (defined('MODULE_OFFICE365_MAIL_STATUS') ? MODULE_OFFICE365_MAIL_STATUS : 'false');
    if ($status == 'true') {
      $this->disable_google_mail();
    }

    $tenant = $this->get_configuration_value($posted, 'MODULE_OFFICE365_MAIL_TENANT');
    $client_id = $this->get_configuration_value($posted, 'MODULE_OFFICE365_MAIL_CLIENT_ID');
    $client_secret = $this->get_configuration_value($posted, 'MODULE_OFFICE365_MAIL_CLIENT_SECRET');
    $sender_email = $this->get_configuration_value($posted, 'MODULE_OFFICE365_MAIL_SENDER_EMAIL');
    $redirect_uri = $this->get_redirect_uri();
    $oauth_response = $this->get_oauth_response();

    if (isset($_GET['oauth_callback']) && $oauth_response === false) {
      $this->clear_oauth_state();
      $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if (is_array($oauth_response) && isset($oauth_response['error'])) {
      if (!$this->has_valid_oauth_state($oauth_response['state'], false)) {
        $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
      } else {
        $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_CONNECT_CANCELLED, 'error');
      }
      $this->clear_oauth_state();
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if (is_array($oauth_response) && isset($oauth_response['code']) && $oauth_response['code'] != '') {
      if (!$this->has_valid_oauth_state($oauth_response['state'], true)) {
        $this->clear_oauth_state();
        $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
      }

      $code_verifier = $_SESSION['office365_mail_code_verifier'];
      $this->clear_oauth_state();

      $token_data = $this->exchange_code_for_tokens(
        $oauth_response['code'],
        $redirect_uri,
        $tenant,
        $client_id,
        $client_secret,
        $code_verifier
      );

      if ($token_data === false || !isset($token_data['refresh_token'])) {
        $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_CONNECT_ERROR, 'error');
      } elseif (!$this->has_required_mail_scope($token_data)) {
        $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_SCOPE_MISSING, 'error');
      } else {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = '" . xtc_db_input($token_data['refresh_token']) . "'
                       WHERE configuration_key = 'MODULE_OFFICE365_MAIL_REFRESH_TOKEN'");
        $this->clear_oauth_error();
        $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_CONNECT_SUCCESS, 'success');
      }

      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if ($tenant == '' || $client_id == '' || $client_secret == '' || $sender_email == '') {
      $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_MISSING_CREDENTIALS, 'error');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if (!$this->is_valid_tenant($tenant)) {
      $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_INVALID_TENANT, 'error');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    $state = oauth_callback_state::create($this->code, xtc_session_id());
    if ($state === false) {
      $messageStack->add_session(MODULE_OFFICE365_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }
    $code_verifier = xtc_random_charcode(96);
    $_SESSION['office365_mail_oauth_state'] = $state;
    $_SESSION['office365_mail_code_verifier'] = $code_verifier;
    $_SESSION['xoauth_callback'] = array(
      'module' => $this->code,
      'state' => $state,
    );
    unset($_SESSION['xoauth_callback_response']);

    $params = array(
      'client_id' => $client_id,
      'redirect_uri' => $redirect_uri,
      'response_type' => 'code',
      'response_mode' => 'query',
      'scope' => $this->get_oauth_scope(),
      'prompt' => 'select_account',
      'state' => $state,
      'code_challenge' => $this->base64url_encode(hash('sha256', $code_verifier, true)),
      'code_challenge_method' => 'S256',
    );

    xtc_redirect(
      'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/authorize?' .
      http_build_query($params)
    );
  }

  private function get_redirect_uri() {
    return HTTPS_SERVER . DIR_WS_CATALOG . 'callback/phpmailer/xoauth_callback.php';
  }

  private function get_oauth_scope() {
    return 'offline_access https://outlook.office.com/SMTP.Send';
  }

  private function get_configuration_value($posted, $key) {
    if (isset($posted[$key])) {
      return trim($posted[$key]);
    }
    return (defined($key) ? trim(constant($key)) : '');
  }

  private function save_posted_configuration() {
    $posted = array();
    if (isset($_POST['configuration']) && is_array($_POST['configuration'])) {
      $this->invalidate_refresh_token_on_configuration_change($_POST['configuration']);
      foreach ($_POST['configuration'] as $key => $value) {
        if (in_array($key, $this->keys(), true)) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                           SET configuration_value = '" . xtc_db_input($value) . "'
                         WHERE configuration_key = '" . xtc_db_input($key) . "'");
          $posted[$key] = $value;
        }
      }
    }
    return $posted;
  }

  private function invalidate_refresh_token_on_configuration_change($configuration) {
    $connection_keys = array(
      'MODULE_OFFICE365_MAIL_TENANT',
      'MODULE_OFFICE365_MAIL_CLIENT_ID',
      'MODULE_OFFICE365_MAIL_CLIENT_SECRET',
      'MODULE_OFFICE365_MAIL_SENDER_EMAIL',
    );

    foreach ($connection_keys as $key) {
      if (array_key_exists($key, $configuration)
          && defined($key)
          && (string)$configuration[$key] !== (string)constant($key)
          )
      {
        $this->delete_refresh_token();
        break;
      }
    }
  }

  private function delete_refresh_token() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET configuration_value = ''
                   WHERE configuration_key IN ('MODULE_OFFICE365_MAIL_REFRESH_TOKEN', 'MODULE_OFFICE365_MAIL_OAUTH_ERROR')");
  }

  private function disable_google_mail() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET configuration_value = 'false'
                   WHERE configuration_key = 'MODULE_GOOGLE_MAIL_STATUS'");
  }

  private function is_valid_tenant($tenant) {
    return strlen($tenant) <= 253
      && strpos($tenant, '..') === false
      && preg_match('/^(?:organizations|common|consumers|[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?)$/i', $tenant);
  }

  private function get_oauth_response() {
    if (!isset($_GET['oauth_callback'])) {
      return null;
    }

    $response = isset($_SESSION['xoauth_callback_response'])
      && is_array($_SESSION['xoauth_callback_response'])
      ? $_SESSION['xoauth_callback_response']
      : false;
    unset($_SESSION['xoauth_callback_response']);

    if (!is_array($response)
        || !isset($response['module'], $response['state'])
        || !hash_equals($this->code, (string)$response['module'])
        )
    {
      return false;
    }

    return $response;
  }

  private function has_valid_oauth_state($state, $require_code_verifier) {
    $valid = isset($_SESSION['office365_mail_oauth_state'])
      && hash_equals($_SESSION['office365_mail_oauth_state'], (string)$state);

    if ($require_code_verifier) {
      $valid = $valid
        && isset($_SESSION['office365_mail_code_verifier'])
        && strlen($_SESSION['office365_mail_code_verifier']) >= 43;
    }

    return $valid;
  }

  private function clear_oauth_state() {
    unset(
      $_SESSION['office365_mail_oauth_state'],
      $_SESSION['office365_mail_code_verifier'],
      $_SESSION['xoauth_callback'],
      $_SESSION['xoauth_callback_response']
    );
  }

  private function has_required_mail_scope($token_data) {
    if (!isset($token_data['scope'])) {
      return false;
    }

    $scopes = preg_split('/\s+/', trim($token_data['scope']));
    foreach ($scopes as $scope) {
      if (strcasecmp($scope, 'https://outlook.office.com/SMTP.Send') === 0) {
        return true;
      }
    }
    return false;
  }

  private function exchange_code_for_tokens($code, $redirect_uri, $tenant, $client_id, $client_secret, $code_verifier) {
    $result = $this->get_http_client()->request(
      'POST',
      'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/token',
      array(
        'form_params' => array(
          'grant_type' => 'authorization_code',
          'code' => $code,
          'client_id' => $client_id,
          'client_secret' => $client_secret,
          'redirect_uri' => $redirect_uri,
          'scope' => $this->get_oauth_scope(),
          'code_verifier' => $code_verifier,
        ),
      )
    );

    if ($result['response'] === false
        || $result['http_code'] < 200
        || $result['http_code'] >= 300
        )
    {
      return false;
    }

    $data = json_decode($result['response'], true);
    return (is_array($data) ? $data : false);
  }

  private function get_http_client() {
    if (!isset($this->httpClient)) {
      $this->httpClient = new oauth_http_client();
    }
    return $this->httpClient;
  }

  private function base64url_encode($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }

  private function clear_oauth_error() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET configuration_value = ''
                   WHERE configuration_key = 'MODULE_OFFICE365_MAIL_OAUTH_ERROR'");
  }
}
