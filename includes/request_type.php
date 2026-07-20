<?php
/* -----------------------------------------------------------------------------------------
   $Id: request_type.php 1259 2010-09-03 12:01:51Z web28 $
 
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------------------
   based on:
   @copyright Copyright 2003-2010 Zen Cart Development Team
 
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// set the type of request (secure or not)

$is_https = isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1');

$is_forwarded_by_ssl = isset($_SERVER['HTTP_X_FORWARDED_BY'])
    && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false;

$https_server_host = defined('HTTPS_SERVER') ? str_replace('https://', '', HTTPS_SERVER) : ($_SERVER['HTTP_HOST'] ?? '');

$is_forwarded_host_ssl = isset($_SERVER['HTTP_X_FORWARDED_HOST'])
    && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false
        || strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), strtoupper($https_server_host)) !== false);

$is_script_uri_https = isset($_SERVER['SCRIPT_URI'])
    && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:';

$is_forwarded_ssl_flag = isset($_SERVER['HTTP_X_FORWARDED_SSL'])
    && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on');

$is_forwarded_proto_https = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
    && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'ssl' || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https');

$has_ssl_session_id = isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] != '';

$is_port_443 = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443';

$request_type = ($is_https
    || $is_forwarded_by_ssl
    || $is_forwarded_host_ssl
    || $is_script_uri_https
    || $is_forwarded_ssl_flag
    || $is_forwarded_proto_https
    || $has_ssl_session_id
    || $is_port_443) ? 'SSL' : 'NONSSL';
