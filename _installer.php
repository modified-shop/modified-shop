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

  // reads the database settings of a configure file without running it, the file loads half the shop
  function installer_is_configured($file) {
    if (!is_file($file)) {
      return false;
    }
    $content = (string)file_get_contents($file);

    if (!function_exists('token_get_all')) {
      return (strpos($content, 'DB_SERVER_USERNAME') !== false || strpos($content, 'DB_DATABASE') !== false);
    }

    $tokens = array();
    foreach (token_get_all($content) as $token) {
      if (!is_array($token) || !in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
        $tokens[] = $token;
      }
    }

    $names = array('DB_SERVER_USERNAME', 'DB_DATABASE');
    for ($i = 0, $n = count($tokens); $i < $n; $i++) {
      $value = false;
      if (is_array($tokens[$i])
          && strtolower(ltrim($tokens[$i][1], '\\')) == 'define'
          && isset($tokens[$i + 3])
          && $tokens[$i + 1] === '('
          && is_array($tokens[$i + 2])
          && $tokens[$i + 2][0] == T_CONSTANT_ENCAPSED_STRING
          && in_array(substr($tokens[$i + 2][1], 1, -1), $names)
          && $tokens[$i + 3] === ','
          )
      {
        $value = $i + 4;
      } elseif (is_array($tokens[$i])
                && $tokens[$i][0] == T_CONST
                && isset($tokens[$i + 2])
                && is_array($tokens[$i + 1])
                && in_array($tokens[$i + 1][1], $names)
                && $tokens[$i + 2] === '='
                )
      {
        $value = $i + 3;
      }

      // anything but an empty string literal, e.g. getenv(), counts as configured
      if ($value !== false
          && !(isset($tokens[$value + 1])
               && is_array($tokens[$value])
               && $tokens[$value][0] == T_CONSTANT_ENCAPSED_STRING
               && strlen($tokens[$value][1]) == 2
               && in_array($tokens[$value + 1], array(')', ';'), true)
               )
          )
      {
        return true;
      }
    }

    return false;
  }

  // the web installer is only meant for an empty webspace and must not overwrite an installed shop
  $configure_files = array(DIR_FS_CATALOG.'includes/local/configure.php', DIR_FS_CATALOG.'includes/configure.php');
  // configure.php also loads these files, a shop may keep its database settings there
  $extra_configure_files = glob(DIR_FS_CATALOG.'includes/extra/configure/*.php');
  if (is_array($extra_configure_files)) {
    $configure_files = array_merge($configure_files, $extra_configure_files);
  }
  foreach ($configure_files as $configure) {
    if (installer_is_configured($configure)) {
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