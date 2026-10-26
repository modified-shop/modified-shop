<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // MODULE_COOKIE_CONSENT_VERSION of the visitor's last decision in the cookie consent (oil.js), -1 without one
  function get_cookie_consent_version() {
    if (!isset($_COOKIE['oil_data']) || !is_string($_COOKIE['oil_data'])) {
      return -1;
    }

    $oil_data = json_decode(stripslashes($_COOKIE['oil_data']), true);
    if (!is_array($oil_data) || !isset($oil_data['consentString']) || !is_string($oil_data['consentString'])) {
      return -1;
    }

    $consent_string = strtr($oil_data['consentString'], '-_', '+/');
    $consent_string = str_pad($consent_string, (int)ceil(strlen($consent_string) / 4) * 4, '=');
    $consent = base64_decode($consent_string, true);
    if ($consent === false || strlen($consent) < 17) {
      return -1;
    }

    $bits = '';
    for ($i = 0; $i < 17; $i ++) {
      $bits .= sprintf('%08b', ord($consent[$i]));
    }

    // IAB consent string version 1: bits 120 to 131 hold the vendor list version, set by the server
    if (bindec(substr($bits, 0, 6)) != 1) {
      return -1;
    }

    // includes/extra/ajax/get_cookie_consent.php sends the version with 1 added
    return (int)bindec(substr($bits, 120, 12)) - 1;
  }
