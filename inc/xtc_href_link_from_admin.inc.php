<?php
/*-----------------------------------------------------------------------
   $Id: xtc_href_link_from_admin.inc.php 2539 2011-12-20 15:31:37Z dokuman $

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

   xtC-SEO-Module by www.ShopStat.com (Hartmut König)
   http://www.shopstat.com - info@shopstat.com
   (c) 2004 ShopStat.com - All Rights Reserved.
   ---------------------------------------------------------------------------------------*/

  function xtc_href_link_from_admin($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = false, $search_engine_safe = true) {
    global $request_type, $session_started, $http_domain, $https_domain;

    $page = ($page == FILENAME_DEFAULT && !xtc_not_null($parameters) ? '' : $page);

    $link = $connection == 'SSL' && ENABLE_SSL_CATALOG ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER;
    $link .= DIR_WS_CATALOG . $page;

    $separator = '?';
    if (xtc_not_null($parameters)) {
      $link .= '?' . $parameters;
      $separator = '&';
    }

    $link = rtrim($link, '&?'); // strip ?/& from the end of link

    if (SEARCH_ENGINE_FRIENDLY_URLS == 'true' && $search_engine_safe) {
      require_once (DIR_FS_INC . 'seo_url_mod.php');
      list($link, $separator) = seo_url_mod($link, $page, $parameters, $connection, $separator);
    }

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ($add_session_id == true && $session_started == true
        && (SESSION_FORCE_COOKIE_USE == 'False')
       ) 
    {
      if (defined('SID')
          && constant('SID') != '')
      {
        $link .= $separator . session_name() . '=' . session_id();
      } elseif ( 
        ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) )
          || ( ($request_type == 'SSL') && ($connection == 'NONSSL') )
        ) && $http_domain != $https_domain) {
        $link .= $separator . session_name() . '=' . session_id();
      }
    }

    return $link;
  }
?>