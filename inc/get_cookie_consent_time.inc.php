<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // time of the visitor's last decision in the cookie consent (oil.js), 0 without one
  function get_cookie_consent_time() {
    if (!isset($_COOKIE['oil_data']) || !is_string($_COOKIE['oil_data'])) {
      return 0;
    }

    $oil_data = json_decode(stripslashes($_COOKIE['oil_data']), true);
    if (!is_array($oil_data) || !isset($oil_data['consentString']) || !is_string($oil_data['consentString'])) {
      return 0;
    }

    $consent_string = strtr($oil_data['consentString'], '-_', '+/');
    $consent_string = str_pad($consent_string, (int)ceil(strlen($consent_string) / 4) * 4, '=');
    $consent = base64_decode($consent_string, true);
    if ($consent === false || strlen($consent) < 10) {
      return 0;
    }

    $bits = '';
    for ($i = 0; $i < 10; $i ++) {
      $bits .= sprintf('%08b', ord($consent[$i]));
    }

    // IAB consent string version 1: bits 42 to 77 hold the last update in deciseconds
    if (bindec(substr($bits, 0, 6)) != 1) {
      return 0;
    }

    return (int)floor(bindec(substr($bits, 42, 36)) / 10);
  }
