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
    // remove session id
    if (strrpos($banners_url, session_name()) !== false) {
      $banners_url = substr($banners_url, 0, strrpos($banners_url, session_name()));
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
      if (defined('SID')
          && constant('SID') != '')
      {
        $banners_url .= $separator . session_name() . '=' . session_id();
      } elseif ($http_domain != $https_domain) {
        $banners_url .= $separator . session_name() . '=' . session_id();
      }
    }
    
    return check_url_scheme($banners_url);
  }


  function check_url_scheme($url) {
    $parse_url = parse_url($url);
    if (!isset($parse_url['scheme'])) {
      $shop_url = xtc_get_top_level_domain((isset($parse_url['host'])) ? $parse_url['host'] : substr($parse_url['path'], 0, strpos($parse_url['path'], '/')));
      if (strpos(HTTP_SERVER, $shop_url['domain']) !== false) {
        $parse_url_host = parse_url(HTTP_SERVER);
        $url = $parse_url_host['scheme'].'://'.$url;
      } else {
        $url = 'http://'.$url;
      }
    }
  
    return $url;
  }
?>