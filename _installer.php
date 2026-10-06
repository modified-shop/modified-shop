<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2022 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // set the level of error reporting
  @ini_set('display_errors', false);
  error_reporting(0);
  
  // needed defines  
  define('DIR_FS_CATALOG', __DIR__.DIRECTORY_SEPARATOR);
  
  // check needed classes
  if (!class_exists('ZipArchive')) {
    die('needed class ZipArchive not exists');
  }

  // the web installer is only meant for an empty webspace and must not overwrite an installed shop
  foreach (array('includes/local/configure.php', 'includes/configure.php') as $configure) {
    if (is_file(DIR_FS_CATALOG.$configure)
        && preg_match("/define\(\s*'DB_SERVER_USERNAME'\s*,\s*'[^']+'/", (string)file_get_contents(DIR_FS_CATALOG.$configure))
        )
    {
      @unlink(__FILE__);
      die('Shop is already installed, the web installer has been removed');
    }
  }
  
  function rrmdir($dir) {    
    $dir = rtrim($dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    if (is_dir(DIR_FS_CATALOG.$dir)) {
      $files = new DirectoryIterator(DIR_FS_CATALOG.$dir);
    
      foreach ($files as $file) {
        $filename = $file->getFilename();

        if ($file->isDot() === false) {
          if(is_dir(DIR_FS_CATALOG.$dir.$filename)) {
            rrmdir($dir.$filename);
          } else {
            unlink(DIR_FS_CATALOG.$dir.$filename);
          }
        }
      }
      rmdir(DIR_FS_CATALOG.$dir);
    }
  }
  
  // cleanup
  rrmdir('tmp');

  // get latest version
  set_time_limit(0);
  $ch = curl_init('https://api.modified-shop.org/modified/version/install/');
  
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
  curl_setopt($ch, CURLOPT_USERAGENT, 'modified.eCommerce.Shopsoftware');

  $result = curl_exec($ch);
  $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  if ($httpStatus < 200 || $httpStatus >= 300) {
    die('Could not reach Install API. Exit with Status: '.$httpStatus.((curl_errno($ch) > 0) ? ' ('.curl_error($ch).')' : ''));
  }
  
  $response = json_decode($result, true);  
  
  if (!is_array($response)
      || !isset($response['download'])
      || !isset($response['filename'])
      || strpos($response['download'], 'https://') !== 0
      )
  {
    die('Invalid response from Install API');
  }
  
  // download
  if (mkdir(DIR_FS_CATALOG.'tmp', 0755)) {
    // save install
    $fp = fopen (DIR_FS_CATALOG.'tmp/'.$response['filename'], 'w+');
    $ch = curl_init($response['download']);
    curl_setopt($ch, CURLOPT_FILE, $fp); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
    $result = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    fclose($fp);

    if ($result === false || $httpStatus < 200 || $httpStatus >= 300) {
      rrmdir('tmp');
      die('Could not download install file. Exit with Status: '.$httpStatus.((curl_errno($ch) > 0) ? ' ('.curl_error($ch).')' : ''));
    }

    // extract install
    $zip = new ZipArchive();
    if ($zip->open(DIR_FS_CATALOG.'tmp/'.$response['filename']) === true) {
      if (is_dir(DIR_FS_CATALOG.'tmp/install')) {
        rrmdir('tmp/install');
      }
      mkdir(DIR_FS_CATALOG.'tmp/install', 0755, true);
    
      $extracted = $zip->extractTo(DIR_FS_CATALOG.'tmp/install');
      $zip->close();
    } else {
      rrmdir('tmp');
      die('Corrupted download file');
    }
    
    // delete install
    unlink(DIR_FS_CATALOG.'tmp/'.$response['filename']);

    // process
    $shoproot = DIR_FS_CATALOG.'tmp/install/'.substr($response['filename'], 0, -4).'/shoproot';
    if ($extracted === true && is_dir($shoproot)) {
      foreach ((new RecursiveIteratorIterator(new RecursiveDirectoryIterator($shoproot, RecursiveDirectoryIterator::SKIP_DOTS))) as $file) {
        $install_path = str_replace($shoproot, DIR_FS_CATALOG, $file->getPath());
        $install_path = rtrim($install_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $install_path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $install_path);
      
        if (!is_dir($install_path)) {
          mkdir($install_path, 0755, true);
        }
      
        rename($file->getPathname(), $install_path.$file->getFilename());
      }
    } else {
      rrmdir('tmp');
      die('Corrupted download file');
    }
  
    // cleanup
    rrmdir('tmp');

    // the web installer is only needed once
    @unlink(__FILE__);
  
    // redirect
    header('Location: _installer');
  } else {
    die('Could not create needed directory');
  }