<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class campaign_tracking {

  var $code;
  var $title;
  var $description;
  var $sort_order;
  var $enabled;
  var $_check;

  var $cookie_name = 'MODcampaign';

  var $consent_texts = array(
    'english' => array(
      'name' => 'Campaign attribution',
      'desc' => 'Stores the campaign link you used to visit the shop, so a later order can be attributed to this campaign.',
    ),
    'german' => array(
      'name' => 'Kampagnen-Zuordnung',
      'desc' => 'Speichert, über welchen Kampagnen-Link Sie in den Shop gekommen sind, damit eine spätere Bestellung dieser Kampagne zugeordnet werden kann.',
    ),
  );

  function __construct() {
    $this->code = 'campaign_tracking';
    $this->title = MODULE_CAMPAIGN_TRACKING_TEXT_TITLE;
    $this->description = MODULE_CAMPAIGN_TRACKING_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_CAMPAIGN_TRACKING_SORT_ORDER')) ? MODULE_CAMPAIGN_TRACKING_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_CAMPAIGN_TRACKING_STATUS') && MODULE_CAMPAIGN_TRACKING_STATUS == 'true') ? true : false);
  }

  function process($file) {
    $this->sync_consent();
  }

  function display() {
    return array('text' => '<br>' . xtc_button(BUTTON_SAVE) . '&nbsp;' .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                 );
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_CAMPAIGN_TRACKING_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value
                                       FROM " . TABLE_CONFIGURATION . "
                                      WHERE configuration_key = 'MODULE_CAMPAIGN_TRACKING_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAMPAIGN_TRACKING_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS', '365',  '6', '2', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAMPAIGN_TRACKING_COOKIE', 'false',  '6', '3', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS', '30',  '6', '4', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_CAMPAIGN_TRACKING_CONSENT_ID', '0',  '6', '5', '', now())");
  }

  function remove() {
    $this->remove_consent();
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_CAMPAIGN_TRACKING_%'");
  }

  function keys() {
    // MODULE_CAMPAIGN_TRACKING_CONSENT_ID is deliberately absent, the module sets it itself
    return array(
      'MODULE_CAMPAIGN_TRACKING_STATUS',
      'MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS',
      'MODULE_CAMPAIGN_TRACKING_COOKIE',
      'MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS',
    );
  }

  function validate_configuration($config_values) {
    // browsers keep a cookie for 400 days at most
    $limits = array(
      'MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS' => array(0, 0),
      'MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS' => array(1, 400),
    );
    foreach ($limits as $key => $limit) {
      if (!isset($config_values[$key])) {
        continue;
      }
      $days = trim($config_values[$key]);
      if (!preg_match('/^[0-9]+$/', $days)
          || (int)$days < $limit[0]
          || ($limit[1] > 0 && (int)$days > $limit[1])
          )
      {
        return constant($key.'_ERROR');
      }
    }
    return '';
  }

  function sync_consent() {
    global $messageStack;

    // the values are already saved when this runs, so read them from the database instead of the stale constants
    $config = array();
    $config_query = xtc_db_query("SELECT configuration_key,
                                         configuration_value
                                    FROM ".TABLE_CONFIGURATION."
                                   WHERE configuration_key IN ('MODULE_CAMPAIGN_TRACKING_STATUS', 'MODULE_CAMPAIGN_TRACKING_COOKIE', 'MODULE_CAMPAIGN_TRACKING_CONSENT_ID', 'MODULE_COOKIE_CONSENT_STATUS', 'MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE')");
    while ($configuration = xtc_db_fetch_array($config_query)) {
      $config[$configuration['configuration_key']] = $configuration['configuration_value'];
    }

    $wanted = (isset($config['MODULE_CAMPAIGN_TRACKING_STATUS'], $config['MODULE_CAMPAIGN_TRACKING_COOKIE'])
               && $config['MODULE_CAMPAIGN_TRACKING_STATUS'] == 'true'
               && $config['MODULE_CAMPAIGN_TRACKING_COOKIE'] == 'true'
               );

    if ($wanted === true
        && (!isset($config['MODULE_COOKIE_CONSENT_STATUS']) || strtolower($config['MODULE_COOKIE_CONSENT_STATUS']) != 'true')
        )
    {
      $messageStack->add_session(MODULE_CAMPAIGN_TRACKING_ERROR_CONSENT_MODULE, 'warning');
    }

    $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_COOKIE_CONSENT_COOKIES."'");
    if (xtc_db_num_rows($check_query) < 1) {
      return;
    }

    $rows = array();
    $rows_query = xtc_db_query("SELECT cookies_id,
                                       languages_id,
                                       cookies_list
                                  FROM ".TABLE_COOKIE_CONSENT_COOKIES);
    while ($row = xtc_db_fetch_array($rows_query)) {
      $rows[(int)$row['cookies_id']][(int)$row['languages_id']] = $row['cookies_list'];
    }

    // the stored ID only counts while its entry still holds the cookie of the module
    $consent_id = ((isset($config['MODULE_CAMPAIGN_TRACKING_CONSENT_ID'])) ? (int)$config['MODULE_CAMPAIGN_TRACKING_CONSENT_ID'] : 0);
    if ($consent_id < 1 || !isset($rows[$consent_id]) || !in_array($this->cookie_name, $rows[$consent_id], true)) {
      if ($wanted !== true) {
        return;
      }

      // oil.js keeps the purposes in 24 bits, a higher ID can never be agreed to
      $consent_id = 0;
      for ($id = 1; $id <= 24; $id ++) {
        if (!isset($rows[$id])) {
          $consent_id = $id;
          break;
        }
      }
      if ($consent_id < 1) {
        $messageStack->add_session(MODULE_CAMPAIGN_TRACKING_ERROR_CONSENT_ID, 'warning');
        return;
      }

      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = '".(int)$consent_id."',
                           last_modified = now()
                     WHERE configuration_key = 'MODULE_CAMPAIGN_TRACKING_CONSENT_ID'");
    }

    $changed = false;
    $status = (($wanted === true) ? 1 : 0);

    // one row per language, a language added later gets its row on the next save
    $languages_query = xtc_db_query("SELECT languages_id,
                                            directory,
                                            language_charset
                                       FROM ".TABLE_LANGUAGES);
    while ($language = xtc_db_fetch_array($languages_query)) {
      if (isset($rows[$consent_id][(int)$language['languages_id']])) {
        continue;
      }
      $texts = ((isset($this->consent_texts[$language['directory']])) ? $this->consent_texts[$language['directory']] : $this->consent_texts['english']);

      $sql_data_array = array(
        'cookies_id' => $consent_id,
        'categories_id' => 4,
        'cookies_name' => decode_utf8($texts['name'], $language['language_charset']),
        'cookies_description' => decode_utf8($texts['desc'], $language['language_charset']),
        'cookies_list' => $this->cookie_name,
        'sort_order' => 3,
        'languages_id' => (int)$language['languages_id'],
        'status' => $status,
        'date_added' => 'now()',
        'fixed' => 1,
      );
      xtc_db_perform(TABLE_COOKIE_CONSENT_COOKIES, $sql_data_array);
      $changed = true;
    }

    xtc_db_query("UPDATE ".TABLE_COOKIE_CONSENT_COOKIES."
                     SET status = '".$status."',
                         last_modified = now()
                   WHERE cookies_id = '".(int)$consent_id."'
                     AND status != '".$status."'");
    if (xtc_db_affected_rows() > 0) {
      $changed = true;
    }

    // the readable cookie MODOilTrack is the only way for the server to see the decision
    if ($wanted === true) {
      if (isset($config['MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE'])
          && strtolower($config['MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE']) != 'true'
          )
      {
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = 'true',
                             last_modified = now()
                       WHERE configuration_key = 'MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE'");
        $messageStack->add_session(MODULE_CAMPAIGN_TRACKING_INFO_READABLE_COOKIE, 'warning');
      }

      xtc_db_query("UPDATE ".TABLE_COOKIE_CONSENT_COOKIES."
                       SET status = '1',
                           last_modified = now()
                     WHERE cookies_list = 'MODOilTrack'
                       AND status != '1'");
      if (xtc_db_affected_rows() > 0) {
        $changed = true;
      }
    }

    if ($changed === true) {
      // a new version asks the visitors again
      if ($wanted === true) {
        $this->update_consent_version();
      }
      $this->clear_query_cache();
    }
  }

  function remove_consent() {
    if (!defined('MODULE_CAMPAIGN_TRACKING_CONSENT_ID') || (int)MODULE_CAMPAIGN_TRACKING_CONSENT_ID < 1) {
      return;
    }

    $check_query = xtc_db_query("SHOW TABLES LIKE '".TABLE_COOKIE_CONSENT_COOKIES."'");
    if (xtc_db_num_rows($check_query) < 1) {
      return;
    }

    xtc_db_query("DELETE FROM ".TABLE_COOKIE_CONSENT_COOKIES."
                        WHERE cookies_id = '".(int)MODULE_CAMPAIGN_TRACKING_CONSENT_ID."'
                          AND cookies_list = '".$this->cookie_name."'");
    if (xtc_db_affected_rows() > 0) {
      $this->clear_query_cache();
    }
  }

  function update_consent_version() {
    $version_query = xtc_db_query("SELECT configuration_value
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = 'MODULE_COOKIE_CONSENT_VERSION'");
    if (xtc_db_num_rows($version_query) < 1) {
      return;
    }
    $version = xtc_db_fetch_array($version_query);

    xtc_db_perform(TABLE_CONFIGURATION, array('configuration_value' => (int)$version['configuration_value'] + 1, 'last_modified' => 'now()'), 'update', "configuration_key = 'MODULE_COOKIE_CONSENT_VERSION'");
    xtc_db_perform(TABLE_CONFIGURATION, array('configuration_value' => 'now()', 'last_modified' => 'now()'), 'update', "configuration_key = 'MODULE_COOKIE_CONSENT_LAST_UPDATE'");
  }

  function clear_query_cache() {
    global $modified_cache;

    // the shop reads the active entries through the query cache
    if (defined('DB_CACHE') && DB_CACHE == 'true') {
      require_once(DIR_FS_CATALOG.'includes/modified_cache.php');
      if (is_object($modified_cache)) {
        $modified_cache->clear();
      }
    }
  }
}
