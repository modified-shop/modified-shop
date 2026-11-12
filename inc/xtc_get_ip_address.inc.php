<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_ip_address.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // Exact proxy IPs; these proxies must overwrite or append the client IP headers.
  defined('TRUSTED_PROXIES') or define('TRUSTED_PROXIES', array());

  function xtc_normalize_ip_address($ip) {
    if (!is_string($ip) || filter_var(trim($ip), FILTER_VALIDATE_IP) === false) {
      return '';
    }

    return inet_ntop(inet_pton(trim($ip)));
  }

  function xtc_is_trusted_proxy($ip, $trusted_proxies = null) {
    $ip = xtc_normalize_ip_address($ip);
    $trusted_proxies = $trusted_proxies ?? TRUSTED_PROXIES;
    if ($ip === '' || !is_array($trusted_proxies)) {
      return false;
    }
    foreach ($trusted_proxies as $proxy) {
      if ($ip === xtc_normalize_ip_address($proxy)) {
        return true;
      }
    }
    return false;
  }

  function xtc_get_ip_address($trusted_proxies = null) {
    $ip = xtc_normalize_ip_address($_SERVER['REMOTE_ADDR'] ?? getenv('REMOTE_ADDR'));
    if ($ip === '') {
      return '';
    }

    if (!xtc_is_trusted_proxy($ip, $trusted_proxies)) {
      return $ip;
    }

    foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_CLIENT_IP') as $header) {
      if (!empty($_SERVER[$header])) {
        if (!is_string($_SERVER[$header])) {
          return '';
        }
        $forwarded = array_reverse(explode(',', $_SERVER[$header]));
        foreach ($forwarded as $forwarded_ip) {
          $ip = xtc_normalize_ip_address($forwarded_ip);
          if ($ip === '') {
            return '';
          }
          // Only the trusted suffix of the proxy chain can identify the client.
          if (!xtc_is_trusted_proxy($ip, $trusted_proxies)) {
            return $ip;
          }
        }
        return '';
      }
    }

    // An unidentified client must not cause the shared proxy to be blacklisted.
    return '';
  }

  function xtc_get_ip_network($ip) {
    $ip = xtc_normalize_ip_address($ip);
    if ($ip === '') {
      return '';
    }
    $packed = inet_pton($ip);
    if (strlen($packed) == 16) {
      // dual stack servers report IPv4 clients as ::ffff:a.b.c.d
      if (substr($packed, 0, 12) === str_repeat("\0", 10)."\xff\xff") {
        return inet_ntop(substr($packed, 12));
      }
      // privacy extensions rotate the interface identifier, the /64 network stays
      return inet_ntop(substr($packed, 0, 8).str_repeat("\0", 8)).'/64';
    }
    return $ip;
  }
?>