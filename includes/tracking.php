<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce (tracking.php 1151 2005-08-12)

   Third Party contribution:
   Some ideas and code from TrackPro v1.0 Web Traffic Analyzer
   Copyright (C) 2004 Curve2 Design www.curve2.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (!isset($_SESSION['tracking'])) {
  $_SESSION['tracking'] = array();
}
  
// IP
if (!isset($_SESSION['tracking']['ip'])) {
  $_SESSION['tracking']['ip'] = xtc_get_ip_address();
}

// campaigns
if (!isset($_SESSION['tracking']['refID']) && isset($_GET['refID'])) {
  $campaign_query = xtDBquery("SELECT * 
                                 FROM ".TABLE_CAMPAIGNS." 
                                WHERE campaigns_refID = '".xtc_db_input($_GET['refID'])."'");
  if (xtc_db_num_rows($campaign_query, true) > 0) {
    $campaign = xtc_db_fetch_array($campaign_query, true);
    
    // include needed functions
    require_once (DIR_FS_INC.'ip_clearing.inc.php');
    
    $sql_data_array = array(
      'user_ip' => ip_clearing($_SESSION['tracking']['ip']),
      'campaign' => $campaign['campaigns_refID'],
      'time' => 'now()'
    );
    xtc_db_perform(TABLE_CAMPAIGNS_IP, $sql_data_array);

    $_SESSION['tracking']['refID'] = $campaign['campaigns_refID'];
  }
}

// request 
$req_url = strip_tags($_SERVER['REQUEST_URI']);
if (in_array(basename($PHP_SELF), array(FILENAME_LOGIN, FILENAME_LOGOFF))) {
  $req_url = basename($PHP_SELF);
}

// referrer
if (!isset($_SESSION['tracking']['http_referer'])) {
  $_SESSION['tracking']['http_referer'] = array(
    'host' => ((isset($_SERVER['HTTP_HOST'])) ? strip_tags($_SERVER['HTTP_HOST']) : '---'),
    'url' => '---',
  );
  if (isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['tracking']['http_referer'] = parse_url(strip_tags($_SERVER['HTTP_REFERER']));
    $_SESSION['tracking']['http_referer']['url'] = strip_tags($_SERVER['HTTP_REFERER']);
  }
}

// datetime
if (!isset($_SESSION['tracking']['date'])) {
  $_SESSION['tracking']['date'] = (date("Y-m-d H:i:s"));
}

// browser
if (!isset($_SESSION['tracking']['browser']) && isset($_SERVER['HTTP_USER_AGENT'])) {
  $_SESSION['tracking']['browser'] = strip_tags($_SERVER['HTTP_USER_AGENT']);
}

// pageview history
if (!isset($_SESSION['tracking']['pageview_history'])) {
  $_SESSION['tracking']['pageview_history'] = array();
}

if (!isset($forbidden_history_sites) || !is_array($forbidden_history_sites)) $forbidden_history_sites = array();
$forbidden_array = array(
  'ajax.php', 
  'autocomplete.php', 
  'login_admin.php', 
  FILENAME_COOKIE_USAGE, 
  FILENAME_GV_REDEEM,
  FILENAME_GV_SEND,
  FILENAME_REDIRECT, 
  FILENAME_POPUP_CONTENT,
  FILENAME_POPUP_COUPON_HELP,
  FILENAME_POPUP_SEARCH_HELP,
  FILENAME_PRINT_ORDER,
  FILENAME_PRINT_PRODUCT_INFO,
);
$forbidden_history_sites = array_merge($forbidden_array, $forbidden_history_sites);

if (!in_array(basename($PHP_SELF), $forbidden_history_sites) 
    && end($_SESSION['tracking']['pageview_history']) != $req_url
    )
{
  $url = parse_url($req_url);
  if (isset($url['path'])) {
    $info = pathinfo($url['path']);
    if (!isset($info['extension'])
        || in_array($info['extension'], array('php', 'html', 'htm'))
        )
    {
      array_push($_SESSION['tracking']['pageview_history'], $req_url);
    }
  } else {
    array_push($_SESSION['tracking']['pageview_history'], $req_url);
  }
}
if (count($_SESSION['tracking']['pageview_history']) > 6) {
  array_shift($_SESSION['tracking']['pageview_history']); 
}
$_SESSION['tracking']['pageview_history'] = array_values($_SESSION['tracking']['pageview_history']);

// order
if (!isset($_SESSION['tracking']['order'])) {
  $_SESSION['tracking']['order'] = array();
}

// allow
$_SESSION['tracking']['allow'] = array();
if (isset($_COOKIE['MODOilTrack'])) {
  $_SESSION['tracking']['allow'] = json_decode(stripslashes($_COOKIE['MODOilTrack']), true);
}

// allowed tracking
if (defined('MODULE_COOKIE_CONSENT_STATUS') 
    && MODULE_COOKIE_CONSENT_STATUS == 'true'
    )
{
  $consent_query = xtDBquery("SELECT DISTINCT cookies_id
                                FROM ".TABLE_COOKIE_CONSENT_COOKIES." 
                               WHERE status = 1");
  $_SESSION['tracking']['allowed'] = array();
  while ($consent = xtc_db_fetch_array($consent_query, true)) {
    $_SESSION['tracking']['allowed'][] = $consent['cookies_id'];
  }
}
