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

class google_mail {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $_check;

  function __construct() {
    $this->code = 'google_mail';
    $this->title = MODULE_GOOGLE_MAIL_TEXT_TITLE;
    $this->description = MODULE_GOOGLE_MAIL_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_GOOGLE_MAIL_SORT_ORDER')) ? MODULE_GOOGLE_MAIL_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_GOOGLE_MAIL_STATUS') && MODULE_GOOGLE_MAIL_STATUS == 'true') ? true : false);
  }

  function process($file) {
      //do nothing
  }

  // Shows the connection status and the "Connect with Google" button.
  // MODULE_GOOGLE_MAIL_REFRESH_TOKEN is intentionally not part of keys()
  // (not directly editable), so its status is read here directly.
  function display() {
    $connected = (defined('MODULE_GOOGLE_MAIL_REFRESH_TOKEN') && MODULE_GOOGLE_MAIL_REFRESH_TOKEN != '');
    $sender = (defined('MODULE_GOOGLE_MAIL_SENDER_EMAIL') ? MODULE_GOOGLE_MAIL_SENDER_EMAIL : '');

    $status_text = $connected
      ? sprintf(MODULE_GOOGLE_MAIL_TEXT_CONNECTED_AS, $sender)
      : MODULE_GOOGLE_MAIL_TEXT_NOT_CONNECTED;

    $connect_link = xtc_href_link(FILENAME_MODULES, 'set=system&module=' . $this->code . '&action=custom');

    return array('text' => '<br>' . $status_text .
                           '<br><br>' . xtc_button_link(MODULE_GOOGLE_MAIL_TEXT_CONNECT_BUTTON, $connect_link) .
                           '<br><br>' . MODULE_GOOGLE_MAIL_TEXT_FROM_ADDRESS_HINT
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
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_GOOGLE_MAIL_STATUS', 'false', '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_CLIENT_ID', '', '6', '2', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_CLIENT_SECRET', '', '6', '3', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_SENDER_EMAIL', '', '6', '4', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_GOOGLE_MAIL_REFRESH_TOKEN', '', '6', '5', now())");
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_GOOGLE_MAIL_%'");
  }

  function keys() {
    return array(
      'MODULE_GOOGLE_MAIL_STATUS',
      'MODULE_GOOGLE_MAIL_CLIENT_ID',
      'MODULE_GOOGLE_MAIL_CLIENT_SECRET',
      'MODULE_GOOGLE_MAIL_SENDER_EMAIL',
    );
  }

  // Stable, admin-folder-independent redirect_uri to register with Google.
  private function get_redirect_uri() {
    return HTTPS_SERVER . DIR_WS_CATALOG . 'callback/phpmailer/xoauth_callback.php?module=' . $this->code;
  }

  // Handles both directions of the OAuth dance via the same redirect_uri
  function custom() {
    global $messageStack;

    $redirect_uri = $this->get_redirect_uri();

    if (isset($_GET['code']) && $_GET['code'] != '') {
      if (!isset($_SESSION['google_mail_oauth_state'])
          || !isset($_GET['state'])
          || !hash_equals($_SESSION['google_mail_oauth_state'], $_GET['state'])
          )
      {
        unset($_SESSION['google_mail_oauth_state']);
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_OAUTH_STATE_ERROR, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULES, 'set=system&module=' . $this->code));
      }
      unset($_SESSION['google_mail_oauth_state']);

      $token_data = $this->exchange_code_for_tokens($_GET['code'], $redirect_uri);

      if ($token_data === false || !isset($token_data['refresh_token'])) {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_ERROR, 'error');
      } else {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value = '" . xtc_db_input($token_data['refresh_token']) . "'
                       WHERE configuration_key = 'MODULE_GOOGLE_MAIL_REFRESH_TOKEN'");
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_CONNECT_SUCCESS, 'success');
      }

      xtc_redirect(xtc_href_link(FILENAME_MODULES, 'set=system&module=' . $this->code));
    } else {
      if (!defined('MODULE_GOOGLE_MAIL_CLIENT_ID') || MODULE_GOOGLE_MAIL_CLIENT_ID == ''
          || !defined('MODULE_GOOGLE_MAIL_CLIENT_SECRET') || MODULE_GOOGLE_MAIL_CLIENT_SECRET == ''
          )
      {
        $messageStack->add_session(MODULE_GOOGLE_MAIL_TEXT_MISSING_CREDENTIALS, 'error');
        xtc_redirect(xtc_href_link(FILENAME_MODULES, 'set=system&module=' . $this->code));
      }

      $state = xtc_random_charcode(32);
      $_SESSION['google_mail_oauth_state'] = $state;

      $params = array(
        'client_id' => MODULE_GOOGLE_MAIL_CLIENT_ID,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'https://mail.google.com/',
        'access_type' => 'offline',
        'prompt' => 'consent',
        'state' => $state,
      );

      xtc_redirect('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }
  }

  private function exchange_code_for_tokens($code, $redirect_uri) {
    $post_fields = http_build_query(array(
      'grant_type' => 'authorization_code',
      'code' => $code,
      'client_id' => MODULE_GOOGLE_MAIL_CLIENT_ID,
      'client_secret' => MODULE_GOOGLE_MAIL_CLIENT_SECRET,
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
}
