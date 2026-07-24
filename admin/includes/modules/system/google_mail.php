<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

require_once(DIR_FS_INC . 'xtc_random_charcode.inc.php');

class google_mail {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;
    var $properties;

  function __construct() {
    $this->code = 'google_mail';
    $this->title = MODULE_GOOGLE_MAIL_TEXT_TITLE;
    $this->description = MODULE_GOOGLE_MAIL_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_GOOGLE_MAIL_SORT_ORDER')) ? MODULE_GOOGLE_MAIL_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_GOOGLE_MAIL_STATUS') && MODULE_GOOGLE_MAIL_STATUS == 'true') ? true : false);
    $this->properties = array();
  }

  function process($file) {
    if (isset($_POST['configuration']) && is_array($_POST['configuration'])) {
      $this->invalidate_refresh_token_on_configuration_change($_POST['configuration']);
    }
  }

  function display() {
    $connected = (defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN') && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != '');
    $sender = (defined('MODULE_GOOGLE_MAIL_SENDER_EMAIL') ? MODULE_GOOGLE_MAIL_SENDER_EMAIL : '');

    $status_text = $connected
      ? sprintf(MODULE_GOOGLE_MAIL_TEXT_CONNECTED_AS, $sender)
      : MODULE_GOOGLE_MAIL_TEXT_NOT_CONNECTED;

    $connect_href = xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&action=custom');
    $connect_button = '<button type="submit" class="button" onclick="this.blur();" formaction="' . $connect_href . '">' . MODULE_GOOGLE_MAIL_TEXT_CONNECT_BUTTON . '</button>';

    return array('text' => '<br>' . $status_text .
                           '<br><br>' . $connect_button .
                           '<br><br>' . MODULE_GOOGLE_MAIL_TEXT_FROM_ADDRESS_HINT .
                           '<br><br>' . sprintf(MODULE_GOOGLE_MAIL_TEXT_SETUP_GUIDE, $this->get_redirect_uri())
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
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_MAIL_STATUS', 'false', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_CLIENT_ID', '', '6', '2', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('MODULE_GOOGLE_MAIL_CLIENT_SECRET', '', '6', '3', 'xtc_cfg_display_password', 'xtc_cfg_password_field_module(', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_SENDER_EMAIL', '', '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_REFRESH_TOKEN', '', '6', '5', now())");
  }

  function remove() {
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

  private function get_redirect_uri() {
    return HTTPS_SERVER . DIR_WS_CATALOG . 'callback/phpmailer/xoauth_callback.php?module=' . $this->code;
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
      'MODULE_GOOGLE_MAIL_CLIENT_ID',
      'MODULE_GOOGLE_MAIL_CLIENT_SECRET',
      'MODULE_GOOGLE_MAIL_SENDER_EMAIL',
    );

    foreach ($connection_keys as $key) {
      if (array_key_exists($key, $configuration)
          && defined($key)
          && (string)$configuration[$key] !== (string)constant($key)
          )
      {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = ''
                       WHERE configuration_key = 'MODULE_GOOGLE_MAIL_REFRESH_TOKEN'");
        break;
      }
    }
  }

  function custom() {
    global $messageStack;

    $posted = $this->save_posted_configuration();

    $client_id = isset($posted['MODULE_GOOGLE_MAIL_CLIENT_ID'])
      ? $posted['MODULE_GOOGLE_MAIL_CLIENT_ID']
      : (defined('MODULE_GOOGLE_MAIL_CLIENT_ID') ? MODULE_GOOGLE_MAIL_CLIENT_ID : '');
    $client_secret = isset($posted['MODULE_GOOGLE_MAIL_CLIENT_SECRET'])
      ? $posted['MODULE_GOOGLE_MAIL_CLIENT_SECRET']
      : (defined('MODULE_GOOGLE_MAIL_CLIENT_SECRET') ? MODULE_GOOGLE_MAIL_CLIENT_SECRET : '');

    $redirect_uri = $this->get_redirect_uri();

    if (isset($_GET['oauth_error'])) {
      if (!$this->has_valid_oauth_state()) {
        unset($_SESSION['google_mail_oauth_state']);
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
      } else {
        unset($_SESSION['google_mail_oauth_state']);
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_CANCELLED, 'error');
      }
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    }

    if (isset($_GET['code']) && $_GET['code'] != '') {
      if (!$this->has_valid_oauth_state())
      {
        unset($_SESSION['google_mail_oauth_state']);
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
      }
      unset($_SESSION['google_mail_oauth_state']);

      $token_data = $this->exchange_code_for_tokens($_GET['code'], $redirect_uri, $client_id, $client_secret);

      $connected_email = (($token_data !== false) ? $this->get_connected_email($token_data, $client_id) : false);
      $sender_email = (defined('MODULE_GOOGLE_MAIL_SENDER_EMAIL') ? MODULE_GOOGLE_MAIL_SENDER_EMAIL : '');

      if (isset($posted['MODULE_GOOGLE_MAIL_SENDER_EMAIL'])) {
        $sender_email = $posted['MODULE_GOOGLE_MAIL_SENDER_EMAIL'];
      }

      if ($token_data === false || !isset($token_data['refresh_token'])) {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_ERROR, 'error');
      } elseif (!$this->has_required_mail_scope($token_data)) {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_SCOPE_MISSING, 'error');
      } elseif ($connected_email === false || strcasecmp($connected_email, trim($sender_email)) !== 0) {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_ACCOUNT_MISMATCH, 'error');
      } else {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = '" . xtc_db_input($token_data['refresh_token']) . "'
                       WHERE configuration_key = 'MODULE_GOOGLE_MAIL_REFRESH_TOKEN'");
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_SUCCESS, 'success');
      }

      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
    } else {
      if ($client_id == '' || $client_secret == '') {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_MISSING_CREDENTIALS, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code));
      }

      $state = xtc_random_charcode(32);
      $_SESSION['google_mail_oauth_state'] = $state;

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

  private function has_valid_oauth_state() {
    return isset($_SESSION['google_mail_oauth_state'])
      && isset($_GET['state'])
      && hash_equals($_SESSION['google_mail_oauth_state'], $_GET['state']);
  }

  private function has_required_mail_scope($token_data) {
    if (!isset($token_data['scope'])) {
      return false;
    }

    $scopes = preg_split('/\s+/', trim($token_data['scope']));
    return in_array('https://mail.google.com/', $scopes, true);
  }

  private function exchange_code_for_tokens($code, $redirect_uri, $client_id, $client_secret) {
    $post_fields = http_build_query(array(
      'grant_type' => 'authorization_code',
      'code' => $code,
      'client_id' => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri' => $redirect_uri,
    ));

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $http_code < 200 || $http_code >= 300) {
      return false;
    }

    $data = json_decode($response, true);
    return (is_array($data) ? $data : false);
  }

  private function get_connected_email($token_data, $client_id) {
    if (!isset($token_data['id_token'])) {
      return false;
    }

    $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($token_data['id_token']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $http_code < 200 || $http_code >= 300) {
      return false;
    }

    $data = json_decode($response, true);
    if (!is_array($data)
        || !isset($data['aud'], $data['email'], $data['email_verified'])
        || !hash_equals((string)$client_id, (string)$data['aud'])
        || !in_array($data['email_verified'], array(true, 'true', 1, '1'), true)
        )
    {
      return false;
    }

    return $data['email'];
  }
}
