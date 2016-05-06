<?php
/* -----------------------------------------------------------------------------------------
   $Id: tracking.php 2812 2012-05-02 09:26:43Z gtb-modified $

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
   
// IP
if (!isset($_SESSION['tracking']['ip'])) {
  $_SESSION['tracking']['ip'] = xtc_get_ip_address();
}

// campaigns
if (!isset($_SESSION['tracking']['refID']) && isset($_GET['refID'])) {
  $campaign_check_query = xtc_db_query("SELECT * 
                                          FROM ".TABLE_CAMPAIGNS." 
                                         WHERE campaigns_refID = '".xtc_db_input($_GET['refID'])."'");
  if (xtc_db_num_rows($campaign_check_query) > 0) {
    // include needed functions
    require_once (DIR_FS_INC.'ip_clearing.inc.php');
    $_SESSION['tracking']['refID'] = xtc_db_input($_GET['refID']);
    $sql_data_array = array(
      'user_ip' => ip_clearing($_SESSION['tracking']['ip']),
      'campaign' => xtc_db_input($_GET['refID']),
      'time' => 'now()'
    );
    xtc_db_perform(TABLE_CAMPAIGNS_IP, $sql_data_array);
  }
}

// referrer
$ref_url = parse_url((isset($_SERVER['HTTP_REFERER']) ? strip_tags($_SERVER['HTTP_REFERER']) : $current_domain.$_SERVER['REQUEST_URI']));
if (!isset($_SESSION['tracking']['http_referer']))  $_SESSION['tracking']['http_referer']= $ref_url;
// host
if (!isset ($_SESSION['tracking']['http_referer']['host']))  $_SESSION['tracking']['http_referer']['host'] = strip_tags($_SERVER['HTTP_HOST']);
// datetime
if (!isset ($_SESSION['tracking']['date']))  $_SESSION['tracking']['date'] = (date("Y-m-d H:i:s"));
// browser
if (!isset ($_SESSION['tracking']['browser']))  $_SESSION['tracking']['browser'] = strip_tags($_SERVER['HTTP_USER_AGENT']);

// pageview history
if (!isset($_SESSION['tracking']['pageview_history'])) $_SESSION['tracking']['pageview_history'] = array();
if (end($_SESSION['tracking']['pageview_history']) != $_SESSION['tracking']['http_referer']) {
  array_push($_SESSION['tracking']['pageview_history'], $ref_url);
}
if (count($_SESSION['tracking']['pageview_history']) > 6) {
  array_shift($_SESSION['tracking']['pageview_history']); 
}
$_SESSION['tracking']['pageview_history'] = array_values($_SESSION['tracking']['pageview_history']);
?>