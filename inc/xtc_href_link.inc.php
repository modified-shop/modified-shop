<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_href_link.inc.php 4256 2013-01-11 16:23:35Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_href_link.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_href_link.inc.php)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // The HTML href link wrapper function
  function xtc_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $urlencode = false, $admin = false) {
    global $request_type, $session_started, $http_domain, $https_domain, $truncate_session_id, $cookie;

    $parameters = str_replace('&amp;', '&', $parameters); // undo W3C-Conform link

    $link = $connection == 'SSL' && ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER;

    if (defined('RUN_MODE_ADMIN') && $admin === false) {
      $link .= DIR_WS_ADMIN;
      $page = (($page == '') ? FILENAME_START : $page);
      $search_engine_safe = false;
    } else {
      $link .= DIR_WS_CATALOG;
      $page = (($page == FILENAME_DEFAULT && !xtc_not_null($parameters)) ? '' : $page);
      if (defined('RUN_MODE_ADMIN')) {
        $admin = false;
      }
    }

    $link .= $page;

    $separator = '?';
    if (xtc_not_null($parameters)) {
      $link .= '?' . $parameters;
      $separator = '&';
    }

    $link = rtrim($link, '&?'); // strip ?/& from the end of link

    if ($admin === false && SEARCH_ENGINE_FRIENDLY_URLS == 'true' && $search_engine_safe === true) {
      require_once (DIR_FS_INC . 'seo_url_mod.php');
      list($link, $separator) = seo_url_mod($link, $page, $parameters, $connection, $separator);
    }

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( (!isset($truncate_session_id) || $truncate_session_id === false) # no session if useragent is a known Spider
        && $add_session_id === true 
        && $session_started === true
        && (SESSION_FORCE_COOKIE_USE == 'False' && ($admin === true || $cookie === false))
       ) 
    {
      if (defined('SID')
          && constant('SID') != ''
          && session_id() != '')
      {
        $link .= $separator . session_name() . '=' . session_id();
      } elseif ( 
        ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) )
          || ( ($request_type == 'SSL') && ($connection == 'NONSSL') )
        ) && $http_domain != $https_domain) {
        $link .= $separator . session_name() . '=' . session_id();
      }
    }

    // W3C-Conform
    if ($admin === false && !defined('RUN_MODE_ADMIN')) {
      $link = ($urlencode !== false ? encode_htmlentities($link) : str_replace('&', '&amp;', $link));
    }
    
    return $link;
  }

  // link to admin - used in source/boxes/admin.php, account_edit.php
  function xtc_href_link_admin($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $urlencode = false) {
    return xtc_href_link($page, $parameters, $connection, $add_session_id, $search_engine_safe, $urlencode, true);
  }
?>