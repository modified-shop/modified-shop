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

require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/oauth_callback_state.php');
require_once(DIR_FS_EXTERNAL . 'phpmailer/classes/oauth_http_client.php');

class google_mail {

  var $code;
  var $title;
  var $description;
  var $sort_order;
  var $enabled;
  var $_check;
  var $properties;
  var $httpClient;

  function __construct() {
    $this->code = 'google_mail';
    $this->title = MODULE_GOOGLE_MAIL_TEXT_TITLE;
    $this->description = MODULE_GOOGLE_MAIL_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_GOOGLE_MAIL_SORT_ORDER')) ? MODULE_GOOGLE_MAIL_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_GOOGLE_MAIL_STATUS') && MODULE_GOOGLE_MAIL_STATUS == 'true') ? true : false);
    $this->properties = array(
      'form_restore' => xtc_draw_form(
        'modules',
        FILENAME_MODULE_EXPORT,
        'set=system&module=' . $this->code . '&action=custom&oauth_action=restore'
      ),
    );

    if (defined('MODULE_GOOGLE_MAIL_STATUS')) {
      $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' .
        xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&action=custom', 'SSL') .
        '">' . MODULE_GOOGLE_MAIL_TEXT_CONNECT_BUTTON . '</a>';
    }
  }

  function process($file) {
    if (isset($_POST['configuration']) && is_array($_POST['configuration'])) {
      $configuration = $this->normalize_configuration($_POST['configuration']);
      $this->invalidate_refresh_token_on_configuration_change($configuration);
      $this->save_configuration_values($configuration);

      if (isset($configuration['MODULE_GOOGLE_MAIL_STATUS'])
          && $configuration['MODULE_GOOGLE_MAIL_STATUS'] == 'true'
          )
      {
        $this->disable_office365_mail();
      }
    }
  }

  function display() {
    $oauth_error = MODULE_GOOGLE_MAIL_OAUTH_ERROR;
    $connected = ($oauth_error == ''
      && defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN')
      && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != ''
    );
    $sender = (defined('MODULE_GOOGLE_MAIL_SENDER_EMAIL') ? MODULE_GOOGLE_MAIL_SENDER_EMAIL : '');

    if ($oauth_error != '') {
      $status_text = sprintf(
        MODULE_GOOGLE_MAIL_TEXT_CONNECTION_ERROR,
        htmlspecialchars($oauth_error, ENT_QUOTES, 'UTF-8')
      );
    } else {
      $status_text = $connected
        ? sprintf(MODULE_GOOGLE_MAIL_TEXT_CONNECTED_AS, $sender)
        : MODULE_GOOGLE_MAIL_TEXT_NOT_CONNECTED;
    }

    $cancel_button = xtc_button_link(
      BUTTON_CANCEL,
      xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code)
    );

    return array('text' => '<br>' . $status_text .
                           '<br><br>' . MODULE_GOOGLE_MAIL_TEXT_FROM_ADDRESS_HINT .
                           '<br><br>' . sprintf(MODULE_GOOGLE_MAIL_TEXT_SETUP_GUIDE, $this->get_redirect_uri()) .
                           '<br><br><div align="center">' . xtc_button(BUTTON_SAVE) . $cancel_button . '</div>'
                 );
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_GOOGLE_MAIL_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value
                                       FROM " . TABLE_CONFIGURATION . "
                                      WHERE configuration_key = 'MODULE_GOOGLE_MAIL_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }

      if ($this->_check) {
        $secret_query = xtc_db_query("SELECT use_function, set_function
                                       FROM " . TABLE_CONFIGURATION . "
                                      WHERE configuration_key = 'MODULE_GOOGLE_MAIL_CLIENT_SECRET'");
        if (xtc_db_num_rows($secret_query) > 0) {
          $secret_config = xtc_db_fetch_array($secret_query);
          if ($secret_config['use_function'] !== 'xtc_cfg_display_password'
              || $secret_config['set_function'] !== 'xtc_cfg_password_field_module('
              )
          {
            $this->properties['button_update'] = (isset($this->properties['button_update'])
              ? $this->properties['button_update']
              : '') .
              '<a class="button btnbox" onclick="this.blur();" href="' .
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
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_MAIL_STATUS', 'false', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_CLIENT_ID', '', '6', '2', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_GOOGLE_MAIL_CLIENT_SECRET', '', '6', '3', 'xtc_cfg_display_password', 'xtc_cfg_password_field_module(', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_SENDER_EMAIL', '', '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_REFRESH_TOKEN', '', '6', '5', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_OAUTH_ERROR', '', '6', '6', now())");
  }

  function remove() {
    if (defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN') && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != '') {
      $this->revoke_token(MODULE_GOOGLE_MAIL_REFRESH_TOKEN);
    }
    $this->clear_oauth_state();
    oauth_callback_state::deleteModuleStates($this->code);
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_GOOGLE_MAIL_%'");
  }

  function update() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET use_function = 'xtc_cfg_display_password',
                         set_function = 'xtc_cfg_password_field_module('
                   WHERE configuration_key = 'MODULE_GOOGLE_MAIL_CLIENT_SECRET'");
  }

  function keys() {
    return array(
      'MODULE_GOOGLE_MAIL_STATUS',
      'MODULE_GOOGLE_MAIL_CLIENT_ID',
      'MODULE_GOOGLE_MAIL_CLIENT_SECRET',
      'MODULE_GOOGLE_MAIL_SENDER_EMAIL',
    );
  }

  function custom() {
    global $messageStack;

    if (isset($_GET['oauth_action']) && $_GET['oauth_action'] === 'restore') {
      if (defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN') && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != '') {
        $this->revoke_token(MODULE_GOOGLE_MAIL_REFRESH_TOKEN);
      }
      xtc_restore_configuration($this->keys());
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                       SET configuration_value = ''
                     WHERE configuration_key IN ('MODULE_GOOGLE_MAIL_REFRESH_TOKEN', 'MODULE_GOOGLE_MAIL_OAUTH_ERROR')");
      $messageStack->add_session(MODULE_RESTORE_CONFIRM, 'success');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    $posted = $this->save_posted_configuration();

    $status = isset($posted['MODULE_GOOGLE_MAIL_STATUS'])
      ? $posted['MODULE_GOOGLE_MAIL_STATUS']
      : (defined('MODULE_GOOGLE_MAIL_STATUS') ? MODULE_GOOGLE_MAIL_STATUS : 'false');
    if ($status == 'true') {
      $this->disable_office365_mail();
    }

    $client_id = $this->get_configuration_value($posted, 'MODULE_GOOGLE_MAIL_CLIENT_ID');
    $client_secret = $this->get_configuration_value($posted, 'MODULE_GOOGLE_MAIL_CLIENT_SECRET');
    $sender_email = $this->get_configuration_value($posted, 'MODULE_GOOGLE_MAIL_SENDER_EMAIL');

    $redirect_uri = $this->get_redirect_uri();
    $oauth_response = $this->get_oauth_response();

    if (isset($_GET['oauth_callback']) && $oauth_response === false) {
      $this->clear_oauth_state();
      $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if (is_array($oauth_response) && isset($oauth_response['error'])) {
      if (!$this->has_valid_oauth_state($oauth_response['state'])) {
        $this->clear_oauth_state();
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
      } else {
        $this->clear_oauth_state();
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_CANCELLED, 'error');
      }
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if (is_array($oauth_response) && isset($oauth_response['code']) && $oauth_response['code'] != '') {
      if (!$this->has_valid_oauth_state($oauth_response['state']))
      {
        $this->clear_oauth_state();
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
      }
      $this->clear_oauth_state();

      $token_data = $this->exchange_code_for_tokens(
        $oauth_response['code'],
        $redirect_uri,
        $client_id,
        $client_secret
      );

      $connected_email = (($token_data !== false) ? $this->get_connected_email($token_data) : false);

      if ($token_data === false || !isset($token_data['refresh_token'])) {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_ERROR, 'error');
      } elseif (!$this->has_required_mail_scope($token_data)) {
        $this->revoke_token($token_data['refresh_token']);
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_SCOPE_MISSING, 'error');
      } elseif ($connected_email === false || strcasecmp($connected_email, trim($sender_email)) !== 0) {
        $this->revoke_token($token_data['refresh_token']);
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_ACCOUNT_MISMATCH, 'error');
      } else {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = '" . xtc_db_input($token_data['refresh_token']) . "'
                       WHERE configuration_key = 'MODULE_GOOGLE_MAIL_REFRESH_TOKEN'");
        $this->clear_oauth_error();
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_SUCCESS, 'success');
      }

      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    } else {
      if ($client_id == '' || $client_secret == '' || $sender_email == '') {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_MISSING_CREDENTIALS, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
      }

      $state = oauth_callback_state::create($this->code, xtc_session_id());
      if ($state === false) {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
      }
      $_SESSION['google_mail_oauth_state'] = $state;
      $_SESSION['xoauth_callback'] = array(
        'module' => $this->code,
        'state' => $state,
      );
      unset($_SESSION['xoauth_callback_response']);

      $params = array(
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'openid email https://mail.google.com/',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $state,
      );

      xtc_redirect('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }
  }

  private function get_redirect_uri() {
    return HTTPS_SERVER . DIR_WS_CATALOG . 'callback/phpmailer/xoauth_callback.php';
  }

  private function save_posted_configuration() {
    $posted = array();
    if (isset($_POST['configuration']) && is_array($_POST['configuration'])) {
      $configuration = $this->normalize_configuration($_POST['configuration']);
      $this->invalidate_refresh_token_on_configuration_change($configuration);
      $posted = $this->save_configuration_values($configuration);
    }
    return $posted;
  }

  private function get_configuration_value($configuration, $key) {
    if (isset($configuration[$key])) {
      return trim((string)$configuration[$key]);
    }
    return (defined($key) ? trim((string)constant($key)) : '');
  }

  private function get_connection_keys() {
    return array(
      'MODULE_GOOGLE_MAIL_CLIENT_ID',
      'MODULE_GOOGLE_MAIL_CLIENT_SECRET',
      'MODULE_GOOGLE_MAIL_SENDER_EMAIL',
    );
  }

  private function normalize_configuration($configuration) {
    foreach ($this->get_connection_keys() as $key) {
      if (array_key_exists($key, $configuration)) {
        $configuration[$key] = is_scalar($configuration[$key])
          ? trim((string)$configuration[$key])
          : '';
      }
    }
    return $configuration;
  }

  private function save_configuration_values($configuration) {
    $saved = array();
    foreach ($configuration as $key => $value) {
      if (in_array($key, $this->keys(), true) && is_scalar($value)) {
        $value = (string)$value;
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = '" . xtc_db_input($value) . "'
                       WHERE configuration_key = '" . xtc_db_input($key) . "'");
        $saved[$key] = $value;
      }
    }
    return $saved;
  }

  private function invalidate_refresh_token_on_configuration_change($configuration) {
    foreach ($this->get_connection_keys() as $key) {
      if (array_key_exists($key, $configuration)
          && defined($key)
          && (string)$configuration[$key] !== (string)constant($key)
          )
      {
        if (defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN') && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != '') {
          $this->revoke_token(MODULE_GOOGLE_MAIL_REFRESH_TOKEN);
        }
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = ''
                       WHERE configuration_key IN ('MODULE_GOOGLE_MAIL_REFRESH_TOKEN', 'MODULE_GOOGLE_MAIL_OAUTH_ERROR')");
        break;
      }
    }
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

  private function has_valid_oauth_state($state) {
    return isset($_SESSION['google_mail_oauth_state'])
      && hash_equals($_SESSION['google_mail_oauth_state'], (string)$state);
  }

  private function clear_oauth_state() {
    unset(
      $_SESSION['google_mail_oauth_state'],
      $_SESSION['xoauth_callback'],
      $_SESSION['xoauth_callback_response']
    );
  }

  private function has_required_mail_scope($token_data) {
    if (!isset($token_data['scope'])) {
      return false;
    }

    $scopes = preg_split('/\s+/', trim($token_data['scope']));
    return in_array('https://mail.google.com/', $scopes, true);
  }

  private function exchange_code_for_tokens($code, $redirect_uri, $client_id, $client_secret) {
    $result = $this->get_http_client()->request(
      'POST',
      'https://oauth2.googleapis.com/token',
      array(
        'form_params' => array(
          'grant_type' => 'authorization_code',
          'code' => $code,
          'client_id' => $client_id,
          'client_secret' => $client_secret,
          'redirect_uri' => $redirect_uri,
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

  private function get_connected_email($token_data) {
    if (!isset($token_data['access_token'])
        || !is_string($token_data['access_token'])
        || $token_data['access_token'] == ''
        )
    {
      return false;
    }

    $result = $this->get_http_client()->request(
      'GET',
      'https://openidconnect.googleapis.com/v1/userinfo',
      array(
        'headers' => array(
          'Authorization' => 'Bearer ' . $token_data['access_token'],
          'Accept' => 'application/json',
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
    if (!is_array($data)
        || !isset($data['email'], $data['email_verified'])
        || !is_string($data['email'])
        || !in_array($data['email_verified'], array(true, 'true', 1, '1'), true)
        )
    {
      return false;
    }

    return $data['email'];
  }

  private function revoke_token($token) {
    if ($token == '') {
      return;
    }

    $this->get_http_client()->request(
      'POST',
      'https://oauth2.googleapis.com/revoke',
      array(
        'form_params' => array(
          'token' => $token,
        ),
        'timeout' => 10,
      )
    );
  }

  private function get_http_client() {
    if (!isset($this->httpClient)) {
      $this->httpClient = new oauth_http_client();
    }
    return $this->httpClient;
  }

  private function disable_office365_mail() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET configuration_value = 'false'
                   WHERE configuration_key = 'MODULE_OFFICE365_MAIL_STATUS'");
  }

  private function clear_oauth_error() {
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                     SET configuration_value = ''
                   WHERE configuration_key = 'MODULE_GOOGLE_MAIL_OAUTH_ERROR'");
  }
}
