<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // wrapper to load right version
  require_once (DIR_FS_EXTERNAL . 'phpfastcache/3.0.0/phpfastcache.php');

  // setup
  phpFastCache::$config = array(

    'storage'       => (defined('DB_CACHE_TYPE') ? DB_CACHE_TYPE : 'auto'), // auto, files, sqlite, apc, cookie, memcache, memcached, predis, redis, wincache, xcache
    'default_chmod' => 0777, // For security, please use 0666 for module and 0644 for cgi.

    // create .htaccess to protect cache folder
    // By default the cache folder will try to create itself outside your public_html.
    // However an htaccess also created in case.
    'htaccess'      => false,

    // path to cache folder, leave it blank for auto detect
    'path'          =>  SQL_CACHEDIR,
    'securityKey'   =>  '', // auto will use domain name, set it to 1 string if you use alias domain name

    // memcache
    'memcache'      =>  array(
      array('127.0.0.1' ,11211, 1),
      // array('new.host.ip', 11211, 1),
    ),

    // redis
    'redis'         =>  array(
      'host'  => '127.0.0.1',
      'port'  =>  '',
      'password'  =>  '',
      'database'  =>  '',
      'timeout'   =>  ''
    ),
    
    // extensions
    'extensions'    =>  array(),

    // fallback
    'fallback'      => 'files',
    
    // skip subdir
    'skipSubdir'    => true,
    
  );


  // temporary disabled phpFastCache
  phpFastCache::$disabled = false;
?>