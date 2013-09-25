<?php
/*-----------------------------------------------------------------------
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

   xtC-SEO-Module by www.ShopStat.com (Hartmut König)
   http://www.shopstat.com - info@shopstat.com
   (c) 2004 ShopStat.com - All Rights Reserved.
   ---------------------------------------------------------------------------------------*/

// The HTML href link wrapper function
    function xtc_href_link_from_admin (
       $page               = '',
       $parameters         = '',
       $connection         = 'NONSSL',
       $add_session_id     = true,
       $search_engine_safe = true)
    {

    global $request_type, $session_started, $http_domain, $https_domain;

    require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');

    if (!xtc_not_null($page)) {
      die('<br><b style="color:#ff0000">Error!</b><br><br><b>Unable to determine the page link ('.$page.')!</b>');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      //BOF - DokuMan - 2011-12-20 - fix ticket #88
      if (defined('ENABLE_SSL_CATALOG') && ENABLE_SSL_CATALOG == true) {
        $link = (defined('HTTPS_CATALOG_SERVER') ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER) . DIR_WS_CATALOG;
      //EOF - DokuMan - 2011-12-20 - fix ticket #88
      } else {
        $link = HTTP_SERVER . DIR_WS_CATALOG;
      }
    } else {
      die('<br><b style="color:#ff0000">Error!</b><br><br><b>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</b>');
    }

    if (xtc_not_null($parameters)) {
      $link .= $page . '?' . $parameters;
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) {
      $link = substr($link, 0, -1);
    }

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (defined('SID') && xtc_not_null(SID)) {
        $sid = SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if ($http_domain != $https_domain) {
          $sid = session_name() . '=' . session_id();
        }
      }
    }

//--- SEO Hartmut König -----------------------------------------//
    if ($_REQUEST['test']
       || ((SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true)) ) {
        require_once(DIR_FS_INC . 'shopstat_functions.inc.php');

        $seolink = shopstat_getSEO( $page,
                                    $parameters,
                                    $connection,
                                    $add_session_id,
                                    $search_engine_safe,
                                    'admin');
      if($seolink) {
            $link       = $seolink;
            $elements   = parse_url($link);
            (isset($elements['query']))
                ? $separator = '&'
                : $separator = '?';
       }
    }
//--- SEO Hartmut König -----------------------------------------//

    if (xtc_check_agent()==1) {
      $sid=NULL;
    }

    if (isset($sid)) {
      $link .= $separator . $sid;
    }

    return $link;
  }
?>