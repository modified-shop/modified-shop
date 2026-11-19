<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  function xtc_get_banners_url($banners_url) {
    global $http_domain, $https_domain, $session_started, $truncate_session_id, $cookie;
    
    // remove session id
    if (strrpos($banners_url, xtc_session_name()) !== false) {
      $banners_url = substr($banners_url, 0, strrpos($banners_url, xtc_session_name()));
    }
    $banners_url = rtrim($banners_url, '&?');
      
    // Add the session ID when SID is defined
    $banner_url = xtc_get_top_level_domain($banners_url);
    $shop_url = xtc_get_top_level_domain(HTTP_SERVER);

    if ((!isset($truncate_session_id) || $truncate_session_id === false)
        && (SESSION_FORCE_COOKIE_USE == 'False' && !$cookie)
        && $shop_url['domain'] == $banner_url['domain']
       )
    {
      $separator = ((strpos($banners_url, '?') === false) ? '?' : '&');
      if ($session_started == true) {
        $banners_url .= $separator . xtc_session_name() . '=' . xtc_session_id();
      } elseif ($http_domain != $https_domain) {
        $banners_url .= $separator . xtc_session_name() . '=' . xtc_session_id();
      }
    }
    
    return check_url_scheme($banners_url);
  }


  // the link in the page: only web, mail and phone addresses, encoded for the href attribute
  function xtc_get_banners_link($banners_url) {
    $banners_url = xtc_get_banners_url($banners_url);
    // the scheme from the start of the address, parse_url() has none for tel:112
    $scheme = (preg_match('/^([a-z][a-z0-9+.-]*):/i', $banners_url, $match) ? strtolower($match[1]) : '');
    if (!in_array($scheme, array('http', 'https', 'mailto', 'tel'), true)) {
      return '';
    }

    // HTML5 entities like &apos; stay as they are, so the link target does not change
    return htmlspecialchars($banners_url, ENT_QUOTES | ENT_HTML5, get_default_encoding(''), false);
  }


  function check_url_scheme($url) {
    // parse_url() reads short phone numbers like tel:112 as host and port
    if ($url != '' && !preg_match('/^(tel|mailto):/i', $url)) {
      $parse_url = parse_url($url);
      if (!isset($parse_url['scheme'])) {
        $shop_url = xtc_get_top_level_domain((isset($parse_url['host'])) ? $parse_url['host'] : ((strpos($parse_url['path'], '/') !== false) ? substr($parse_url['path'], 0, strpos($parse_url['path'], '/')) : $parse_url['path']));
        if (strpos(HTTP_SERVER, $shop_url['domain']) !== false) {
          $parse_url_host = parse_url(HTTP_SERVER);
          $url = $parse_url_host['scheme'].'://'.$url;
        } else {
          $url = 'http://'.$url;
        }
      }
    }
  
    return $url;
  }
