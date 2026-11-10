<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the readable cookie MODOilTrack is the only way for the server to see the visitor's decision, returns true when it switched the setting on
  function cookie_consent_readable_cookie() {
    global $modified_cache;

    // the values may be saved in this request, so read them from the database instead of the constants
    $config = array();
    $config_query = xtc_db_query("SELECT configuration_key,
                                         configuration_value
                                    FROM ".TABLE_CONFIGURATION."
                                   WHERE configuration_key IN ('MODULE_COOKIE_CONSENT_STATUS',
                                                               'MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE',
                                                               'MODULE_COOKIE_CONSENT_VERSION',
                                                               'MODULE_FACEBOOK_PIXEL_STATUS',
                                                               'MODULE_FACEBOOK_PIXEL_ID',
                                                               'MODULE_GOOGLE_ANALYTICS_STATUS',
                                                               'MODULE_GOOGLE_ANALYTICS_TAG_ID',
                                                               'MODULE_GOOGLE_ANALYTICS_ADS_ID',
                                                               'MODULE_CAMPAIGN_TRACKING_STATUS',
                                                               'MODULE_CAMPAIGN_TRACKING_COOKIE',
                                                               'DB_CACHE')");
    while ($configuration = xtc_db_fetch_array($config_query)) {
      $config[$configuration['configuration_key']] = $configuration['configuration_value'];
    }

    if (!isset($config['MODULE_COOKIE_CONSENT_STATUS'], $config['MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE'])
        || strtolower($config['MODULE_COOKIE_CONSENT_STATUS']) != 'true'
        )
    {
      return false;
    }

    // the same conditions as the storefront hooks of the modules
    $needed = ((isset($config['MODULE_FACEBOOK_PIXEL_STATUS'], $config['MODULE_FACEBOOK_PIXEL_ID'])
                && $config['MODULE_FACEBOOK_PIXEL_STATUS'] == 'true'
                && $config['MODULE_FACEBOOK_PIXEL_ID'] != ''
                )
               || (isset($config['MODULE_GOOGLE_ANALYTICS_STATUS'])
                   && $config['MODULE_GOOGLE_ANALYTICS_STATUS'] == 'true'
                   && ((isset($config['MODULE_GOOGLE_ANALYTICS_TAG_ID']) && $config['MODULE_GOOGLE_ANALYTICS_TAG_ID'] != '')
                       || (isset($config['MODULE_GOOGLE_ANALYTICS_ADS_ID']) && $config['MODULE_GOOGLE_ANALYTICS_ADS_ID'] != '')
                       )
                   )
               || (isset($config['MODULE_CAMPAIGN_TRACKING_STATUS'], $config['MODULE_CAMPAIGN_TRACKING_COOKIE'])
                   && $config['MODULE_CAMPAIGN_TRACKING_STATUS'] == 'true'
                   && $config['MODULE_CAMPAIGN_TRACKING_COOKIE'] == 'true'
                   )
               );
    if ($needed !== true) {
      return false;
    }

    $switched = false;
    if (strtolower($config['MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE']) != 'true') {
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                       SET configuration_value = 'true',
                           last_modified = now()
                     WHERE configuration_key = 'MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE'");
      $switched = true;
    }

    // the banner lists the cookie, and like admin/cookie_consent.php a changed list raises the version
    xtc_db_query("UPDATE ".TABLE_COOKIE_CONSENT_COOKIES."
                     SET status = '1',
                         last_modified = now()
                   WHERE cookies_list = 'MODOilTrack'
                     AND status != '1'");
    if (xtc_db_affected_rows() > 0) {
      if (isset($config['MODULE_COOKIE_CONSENT_VERSION'])) {
        xtc_db_perform(TABLE_CONFIGURATION, array('configuration_value' => (int)$config['MODULE_COOKIE_CONSENT_VERSION'] + 1, 'last_modified' => 'now()'), 'update', "configuration_key = 'MODULE_COOKIE_CONSENT_VERSION'");
        xtc_db_perform(TABLE_CONFIGURATION, array('configuration_value' => 'now()', 'last_modified' => 'now()'), 'update', "configuration_key = 'MODULE_COOKIE_CONSENT_LAST_UPDATE'");
      }

      // the shop reads the active entries through the query cache, the admin always defines DB_CACHE as false
      if (isset($config['DB_CACHE']) && $config['DB_CACHE'] == 'true') {
        require_once(DIR_FS_CATALOG.'includes/modified_cache.php');
        if (is_object($modified_cache)) {
          $modified_cache->clear();
        }
      }
    }

    return $switched;
  }
