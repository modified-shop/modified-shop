<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // removes every parameter PHP reads as the session id, also encoded ones like %4DODsid or +MODsid
  function strip_session_id_from_url($url) {
    if (($pos = strpos($url, '?')) === false) {
      return $url;
    }

    $separators = (string)ini_get('arg_separator.input');
    if ($separators == '') {
      $separators = '&';
    }
    $parts = preg_split('/(['.preg_quote($separators, '/').'])/', substr($url, $pos + 1), -1, PREG_SPLIT_DELIM_CAPTURE);

    $query = '';
    $kept = 0;
    foreach ($parts as $i => $part) {
      if ($i % 2 == 1) {
        continue;
      }
      // parse_str() decodes the name exactly like the request does
      $param = array();
      parse_str($part, $param);
      if (array_key_exists(xtc_session_name(), $param)) {
        continue;
      }
      $query .= (($kept > 0) ? $parts[$i - 1] : '').$part;
      $kept ++;
    }

    return substr($url, 0, $pos).(($kept > 0) ? '?'.$query : '');
  }
