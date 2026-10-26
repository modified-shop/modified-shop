<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function campaign_tracking_cookie($path, $domain, $secure) {
    $cookie_name = 'MODcampaign';
    $has_cookie = (isset($_COOKIE[$cookie_name]) && is_string($_COOKIE[$cookie_name]));
    $consent_id = ((defined('MODULE_CAMPAIGN_TRACKING_CONSENT_ID')) ? (int)MODULE_CAMPAIGN_TRACKING_CONSENT_ID : 0);

    $active = (defined('MODULE_CAMPAIGN_TRACKING_STATUS')
               && MODULE_CAMPAIGN_TRACKING_STATUS == 'true'
               && defined('MODULE_CAMPAIGN_TRACKING_COOKIE')
               && MODULE_CAMPAIGN_TRACKING_COOKIE == 'true'
               && defined('MODULE_COOKIE_CONSENT_STATUS')
               && MODULE_COOKIE_CONSENT_STATUS == 'true'
               && $consent_id > 0
               && isset($_SESSION['tracking']['allowed'])
               && in_array($consent_id, $_SESSION['tracking']['allowed'])
               );

    // without a decision for the entry the cookie is neither read, set nor removed
    if ($active === true && !isset($_SESSION['tracking']['allow'][$consent_id])) {
      return;
    }

    if ($active !== true || $_SESSION['tracking']['allow'][$consent_id] !== true) {
      if ($has_cookie === true) {
        xtc_setcookie($cookie_name, '', time() - 3600, $path, $domain, $secure, true);
      }
      return;
    }

    $lifetime = ((defined('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS')) ? (int)MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS : 0) * 86400;

    if (!isset($_SESSION['tracking']['refID']) && $has_cookie === true) {
      $cookie = explode(':', $_COOKIE[$cookie_name], 2);
      $click_time = (int)$cookie[0];
      if (count($cookie) == 2
          && $click_time <= time()
          && $click_time + $lifetime > time()
          )
      {
        $campaign_query = xtDBquery("SELECT campaigns_refID
                                       FROM ".TABLE_CAMPAIGNS."
                                      WHERE campaigns_refID = '".xtc_db_input($cookie[1])."'");
        if (xtc_db_num_rows($campaign_query, true) > 0) {
          $campaign = xtc_db_fetch_array($campaign_query, true);

          // a campaign brought back from the cookie is no new hit
          $_SESSION['tracking']['refID'] = $campaign['campaigns_refID'];
          $_SESSION['tracking']['refID_time'] = $click_time;
        }
      }

      if (!isset($_SESSION['tracking']['refID'])) {
        xtc_setcookie($cookie_name, '', time() - 3600, $path, $domain, $secure, true);
        return;
      }
    }

    if (isset($_SESSION['tracking']['refID'])) {
      // the campaign was in the session before the module was switched on
      if (!isset($_SESSION['tracking']['refID_time'])) {
        $_SESSION['tracking']['refID_time'] = time();
      }

      $value = $_SESSION['tracking']['refID_time'].':'.$_SESSION['tracking']['refID'];
      $expire = $_SESSION['tracking']['refID_time'] + $lifetime;
      if ($expire > time()
          && ($has_cookie !== true || $_COOKIE[$cookie_name] !== $value)
          )
      {
        xtc_setcookie($cookie_name, $value, $expire, $path, $domain, $secure, true);
      }
    }
  }
