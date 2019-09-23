<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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
    global $request_type, $session_started, $http_domain, $https_domain, $truncate_session_id, $cookie, $products_link_cat_id;
    static $session_id, $href_link_array;
    
    if (!isset($href_link_array)) {
      $href_link_array = array();
    }
        
    $parameters = str_replace('&amp;', '&', $parameters); // undo W3C-Conform link

    $link = $connection == 'SSL' && (ENABLE_SSL || $request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER;

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
    
    parse_str($parameters, $params_array);
    ksort($params_array);
    $link_cache = md5('link_cache:'.$link);
    $param_cache = md5('param_cache:'.http_build_query($params_array, '', '|').((isset($params_array['products_id'])) ? $products_link_cat_id : ''));

    $separator = '?';
    if (xtc_not_null($parameters)) {
      $link .= '?' . $parameters;
      $separator = '&';
    }

    $link = rtrim($link, '&?'); // strip ?/& from the end of link

    if ($admin === false && SEARCH_ENGINE_FRIENDLY_URLS == 'true' && $search_engine_safe === true) {
      if (!isset($href_link_array[$link_cache][$param_cache])) {
        require_once (DIR_FS_INC . 'seo_url_mod.php');
        list($link, $separator) = seo_url_mod($link, $page, $parameters, $connection, $separator);
        if ($link == '#') {
          return $link;
        }
        $href_link_array[$link_cache][$param_cache] = array(
          'link' => $link,
          'separator' => $separator
        );
      }
      $link = $href_link_array[$link_cache][$param_cache]['link'];
      $separator = $href_link_array[$link_cache][$param_cache]['separator'];
    }

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( (!isset($truncate_session_id) || $truncate_session_id === false) # no session if useragent is a known Spider
        && $add_session_id === true 
        && $session_started === true
        && (SESSION_FORCE_COOKIE_USE == 'False' && ($admin === true || $cookie === false))
       ) 
    {
      if (!isset($session_id)) {
        if (defined('SID')
            && constant('SID') != ''
            && session_id() != '')
        {
          $session_id = session_name() . '=' . session_id();
        } elseif ( 
          ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) )
            || ( ($request_type == 'SSL') && ($connection == 'NONSSL') )
          ) && $http_domain != $https_domain) {
          $session_id = session_name() . '=' . session_id();
        }
      }
      $link .= $separator . $session_id;
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