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
    global $request_type, $session_started, $http_domain, $https_domain, $truncate_session_id, $cookie;

    $parameters = str_replace('&amp;', '&', $parameters); // undo W3C-Conform link

    $page = (!xtc_not_null($page) ? FILENAME_DEFAULT : $page); // if page is not defined then use index.php

    $page = (($page == FILENAME_DEFAULT && !xtc_not_null($parameters)) ? '' : $page); // remove index.php from startpage

    $link = ($connection == 'SSL' && ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG;

    $link .= $page;
    $separator = '?';
    if (xtc_not_null($parameters)) {
      $link .= '?' . $parameters;
      $separator = '&';
    }

    $link = rtrim($link, '&?'); // strip ?/& from the end of link

    ### shopstat SEO URL
    if (!$admin && (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true)) {
        require_once(DIR_FS_INC . 'shopstat_functions.inc.php');
        $seolink = shopstat_getSEO($page, $parameters, $connection, $add_session_id, $search_engine_safe, 'user');
        if($seolink){
            $link      = $seolink;
            $elements  = parse_url($link);
            $separator = (isset($elements['query']) ? '&' : '?');
         }
    }
    ### shopstat SEO URL

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( (!isset($truncate_session_id) || $truncate_session_id === false) # no session if useragent is a known Spider
        && $add_session_id == true && $session_started == true
        && (SESSION_FORCE_COOKIE_USE == 'False' && ($admin || !$cookie))
       ) 
    {
      if (defined('SID') 
          && SID != '') 
      {
        $link .= $separator . SID;
      } elseif ( 
        ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) )
          || ( ($request_type == 'SSL') && ($connection == 'NONSSL') )
        ) && $http_domain != $https_domain) {
        $link .= $separator . session_name() . '=' . session_id();
      }
    }

    // W3C-Conform
    $link = ($urlencode !== false ? encode_htmlentities($link) : str_replace('&', '&amp;', $link));

    return $link;
  }

  // link to admin
  // used in source/boxes/admin.php, pn_sofortueberweisung.php, account_edit.php
  function xtc_href_link_admin($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $urlencode = false) {
    $link = xtc_href_link($page, $parameters, $connection, $add_session_id, $search_engine_safe, $urlencode, true);
    return $link;
  }
?>