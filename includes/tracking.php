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

// referrer #todo sec
$ref_url = parse_url((isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $current_domain.$_SERVER['REQUEST_URI']));
if (!isset($_SESSION['tracking']['http_referer']))  $_SESSION['tracking']['http_referer']= $ref_url;

// IP
if (!isset($_SESSION['tracking']['ip'])) $_SESSION['tracking']['ip'] = $_SERVER['REMOTE_ADDR'];

// campaigns
if (!isset ($_SESSION['tracking']['refID']) && isset($_GET['refID'])) {
  $campaign_check_query_raw = "SELECT * FROM ".TABLE_CAMPAIGNS." WHERE campaigns_refID = '".xtc_db_input($_GET['refID'])."'";
  $campaign_check_query = xtc_db_query($campaign_check_query_raw);
  if (xtc_db_num_rows($campaign_check_query) > 0) {
    $_SESSION['tracking']['refID'] = xtc_db_input($_GET['refID']);
    xtc_db_perform(TABLE_CAMPAIGNS_IP, array('user_ip'=>$_SESSION['tracking']['ip'],'campaign'=>xtc_db_input($_GET['refID']),'time'=>'now()'));
  }
}

// datetime
if (!isset ($_SESSION['tracking']['date']))  $_SESSION['tracking']['date'] = (date("Y-m-d H:i:s"));

// browser #todo sec
if (!isset ($_SESSION['tracking']['browser']))  $_SESSION['tracking']['browser'] = $_SERVER['HTTP_USER_AGENT'];

// pageview history
if (!isset($_SESSION['tracking']['pageview_history'])) $_SESSION['tracking']['pageview_history'] = array();
$i = count($_SESSION['tracking']['pageview_history']);
if ($i > 6) {
  array_shift($_SESSION['tracking']['pageview_history']);
  $_SESSION['tracking']['pageview_history'][6] = $ref_url;
} else {
  $_SESSION['tracking']['pageview_history'][$i] = $ref_url;
  if (isset($_SESSION['tracking']['http_referer']) && $_SESSION['tracking']['pageview_history'][$i] == $_SESSION['tracking']['http_referer']) {
    array_shift($_SESSION['tracking']['pageview_history']);
  }
}
?>