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
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_USERAGENT, 'modified.eCommerce.Shopsoftware');

  $result = curl_exec($ch);
  $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($httpStatus < 200 || $httpStatus >= 300) {
    die('Could not reach Install API. Exit with Status: '.$httpStatus);
  }
  
  $response = json_decode($result, true);  
  
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    // extract install
    $zip = new ZipArchive();
    if ($zip->open(DIR_FS_CATALOG.'tmp/'.$response['filename']) === true) {
      if (is_dir(DIR_FS_CATALOG.'tmp/install')) {
        rrmdir('tmp/install');
      }
      mkdir(DIR_FS_CATALOG.'tmp/install', 0755, true);
    
      $zip->extractTo(DIR_FS_CATALOG.'tmp/install');
      $zip->close();
    } else {
      die('Corrupted download file');
    }
    
    // delete install
    unlink(DIR_FS_CATALOG.'tmp/'.$response['filename']);

    // process
    $shoproot = DIR_FS_CATALOG.'tmp/install/'.substr($response['filename'], 0, -4).'/shoproot';
    if (is_dir($shoproot)) {
      foreach ((new RecursiveIteratorIterator(new RecursiveDirectoryIterator($shoproot, RecursiveDirectoryIterator::SKIP_DOTS))) as $file) {
        $install_path = str_replace($shoproot, DIR_FS_CATALOG, $file->getPath());
        $install_path = rtrim($install_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $install_path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $install_path);
      
        if (!is_dir($install_path)) {
          mkdir($install_path, 0755, true);
        }
      
        rename($file->getPathname(), $install_path.$file->getFilename());
      }
    }
  
    // cleanup
    rrmdir('tmp');
  
    // redirect
    header('Location: _installer');
  } else {
    die('Could not create needed directory');
  }