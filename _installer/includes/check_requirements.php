<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  
  $requirement_array = array();
  
  $php_flag = true;
  if(version_compare(phpversion(), PHP_VERSION_MIN, "<")){
    $error = true;
    $php_flag = false;
  }
  if(version_compare(phpversion(), PHP_VERSION_MAX, ">")){
    $php_flag = false;
    $error = true;
  }
  
  $requirement_array[] = array(
    'name' => 'PHP VERSION',
    'version' => phpversion(),
    'version_min' => PHP_VERSION_MIN,
    'version_max' => PHP_VERSION_MAX,
    'status' => $php_flag
  );
  
  
  $status = false;
  $status_tls = false;
  $ssl_version = 'undefined';
  $curl_version = array(
    'version' => 'undefined'
  );
  if (function_exists('curl_init')) {
    $status = true;
    $curl_version = curl_version();
    $remote_address = xtc_get_ip_address();
    
    if (substr($remote_address, 0, 4) != '127.' 
        && $remote_address != '::1' 
        && strpos($_SERVER['SERVER_NAME'], 'localhost') === false
        )
    {
      // check for SSL Version
      $ch = curl_init('https://www.howsmyssl.com/a/check');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 3);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
      $data = curl_exec($ch);
      curl_close($ch);
      $json = json_decode($data);
      if (is_object($json)) {
        $ssl_version = $json->tls_version;
      }
      if (version_compare(preg_replace('/[^0-9.]/', '', $ssl_version), SSL_VERSION_MIN, "<")) {
        $status_tls = false;
        $error = true;
      } else {
        $status_tls = true;
      }
    } else {
      $status_tls = false;
    }
  } else {
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'CURL VERSION',
    'version' => $curl_version['version'],
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );

  $requirement_array[] = array(
    'name' => 'SSL VERSION',
    'version' => $ssl_version,
    'version_min' => SSL_VERSION_MIN,
    'version_max' => '',
    'status' => $status_tls
  );

  if (class_exists('mysqli')) {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'MYSQLI',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );

  if (class_exists('finfo')) {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'FILEINFO',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );

  if (function_exists('fsockopen')) {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'FSOCKOPEN',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );
  
  if (function_exists('mb_get_info')) {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'MBSTRING',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );  
  
  $status = false;
  if (function_exists('gd_info')) {
    $gd = gd_info();
    if ($gd['GD Version'] == '') {
      $gd['GD Version'] = 'undefined';
    }
    if ($gd['GIF Read Support'] == 1 || $gd['GIF Create Support'] == 1) {
      $status = true;
    } else {
      $error = true;
    }
  } else {
    $gd = array(
      'GD Version' => 'undefined'
    );
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'GDlib VERSION',
    'version' => $gd['GD Version'],
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );

  if (class_exists('ZipArchive')) {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'ZIPARCHIVE',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );  

  if (function_exists('chmod')
      && is_make_nonwriteable(DIR_FS_INSTALLER.'includes/configure.php')
      && is_make_writeable(DIR_FS_INSTALLER.'includes/configure.php')
      )
  {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'CHMOD',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );  

  if (function_exists('rename')
      && rename(DIR_FS_INSTALLER.'includes/tmp.php', DIR_FS_INSTALLER.'includes/tmp.txt')
      && rename(DIR_FS_INSTALLER.'includes/tmp.txt', DIR_FS_INSTALLER.'includes/tmp.php')
      )
  {
    $status = true;
  } else {
    $status = false;
    $error = true;
  }

  $requirement_array[] = array(
    'name' => 'RENAME',
    'version' => '',
    'version_min' => '',
    'version_max' => '',
    'status' => $status
  );  

  if (isset($db_link) 
      && is_object($db_link) 
      && $db_link->errno == 0 
      && $db_link->connect_errno == 0
      )
  {  
    xtc_db_query("DROP TABLE IF EXISTS `engine`");
    xtc_db_query("CREATE TABLE IF NOT EXISTS `engine` (`type` VARCHAR( 16 ) NOT NULL)");
    
    $check_query = xtc_db_query("SHOW TABLE STATUS WHERE name LIKE 'engine'");
    $check = xtc_db_fetch_array($check_query);
    $engine = $check['Engine'];
    
    xtc_db_query("DROP TABLE IF EXISTS `engine`");
    
    $check_query = xtc_db_query("SELECT @@character_set_database as `charset`, @@collation_database as `collation`");
    $check = xtc_db_fetch_array($check_query);
    $check['engine'] = $engine;
    $check['collation_plain'] = substr($check['collation'], 0, strpos($check['collation'], '_'));
    
    if (($check['charset'] == 'utf8mb4' || $check['collation_plain'] == 'utf8mb4') && $check['engine'] != 'InnoDB') {
      $error = true;
      
      $requirement_array[] = array(
        'name' => 'DB INVALID ENGINE',
        'version' => $check['engine'],
        'version_min' => '',
        'version_max' => '',
        'status' => 0
      );
  
      $requirement_array[] = array(
        'name' => 'DB INVALID CHARSET',
        'version' => $check['charset'],
        'version_min' => '',
        'version_max' => '',
        'status' => 0
      );
  
      $requirement_array[] = array(
        'name' => 'DB INVALID COLLATION',
        'version' => $check['collation'],
        'version_min' => '',
        'version_max' => '',
        'status' => 0
      );
    }
    
    if ($check['charset'] != $check['collation_plain']) {
      $error = true;
  
      $requirement_array[] = array(
        'name' => 'DB MIXED CHARSET',
        'version' => $check['charset'],
        'version_min' => '',
        'version_max' => '',
        'status' => 0
      );
  
      $requirement_array[] = array(
        'name' => 'DB MIXED COLLATION',
        'version' => $check['collation'],
        'version_min' => '',
        'version_max' => '',
        'status' => 0
      );
    }
  }