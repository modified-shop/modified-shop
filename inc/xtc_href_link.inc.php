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
  function xtc_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $urlencode=false) {
    global $request_type, $session_started, $http_domain, $https_domain,$truncate_session_id;

    $parameters = str_replace('&amp;', '&', $parameters); // web28 - 2010-09-02 -- making link W3C-Conform

    if (!xtc_not_null($page)) {
      //die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine the page link!<br /><br />');
      $page = FILENAME_DEFAULT;
    }
    
    // GTB - 2012-04-10 - remove index.php from Startpage
    if ($page == FILENAME_DEFAULT && !xtc_not_null($parameters)) {
      $page = '';
    }
    
    if ($connection == 'NONSSL' || $connection == '') {
      $link = HTTP_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
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
    if ((isset($_REQUEST['test']) && $_REQUEST['test'])
        || ((SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true)) ) {
        require_once(DIR_FS_INC . 'shopstat_functions.inc.php');

        $seolink = shopstat_getSEO( $page,
                                    $parameters,
                                    $connection,
                                    $add_session_id,
                                    $search_engine_safe,
                                    'user');
        if($seolink){
            $link       = $seolink;
            $elements   = parse_url($link);
            (isset($elements['query']))
                ? $separator = '&'
                : $separator = '?';
         }
    }
//--- SEO Hartmut König -----------------------------------------//

  // remove session if useragent is a known Spider
    if (isset($truncate_session_id) && $truncate_session_id) {
      $sid=NULL;
    }

    if (isset($sid)) {
      $link .= $separator . $sid;
    }
/*
    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);
      $separator = '?';
    }
*/

    //-- W3C-Conform
    if($urlencode) {
      $link = encode_htmlentities($link);
    } else {
      $link = str_replace('&', '&amp;', $link); // web28 - 2010-09-02 -- making link W3C-Conform
    }
    return $link;
  }

    function xtc_href_link_admin($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $http_domain, $https_domain;

    if (!xtc_not_null($page)) {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine the page link!<br /><br />');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
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

    if (isset($truncate_session_id) && $truncate_session_id) {
      $sid=NULL; // DokuMan - 2011-03-01 - reenabled
    }

    if (isset($sid)) {
      $link .= $separator . $sid;
    }

    return $link;
  }
?>