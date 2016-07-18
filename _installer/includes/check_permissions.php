<?php
  /* --------------------------------------------------------------
   $Id: check_permissions.php 3584 2012-08-31 12:47:10Z web28 $
   
   modified 1.06 rev8

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------*/
define('CHMOD_WRITEABLE', 0775);

  function scanDirectories($dir, $allData=array()) {
      foreach (glob($dir.'/*') as $file) {
          if (is_dir($file)) {
              $allData['dirs'][] = str_replace(DIR_FS_CATALOG, '', $file);
              $allData = scanDirectories($file, $allData);
          } else {
              $allData['files'][] = str_replace(DIR_FS_CATALOG, '', $file);
          }
      }
      return $allData;
  }

  function is_make_writeable($filename) {
    return (
      is_writable($filename)
      ? true
      : ( @chmod($filename, CHMOD_WRITEABLE) && is_writable($filename)
          ? true
          : false
        )
    );
  }

  // file and folder permission checks
  $error_flag = false;
  $folder_flag = false;
  $message_arr = array();
  $ok_message = '';

  //new permission handling and auto change system
  $file_flag = false;

  $files_to_check = array(
      'files' => array(
          DIR_ADMIN.'magnalister.php',
          'includes/configure.php',
          'magnaCallback.php',
          'sitemap.xml'
      ),
      'dirs' => array(
          DIR_ADMIN.'backups',
          DIR_ADMIN.'images/graphs',
          DIR_ADMIN.'images/icons',
          'cache',
          'export',
          'export/easybill',
          'export/idealo_realtime',
          'images',
          'images/banner',
          'images/categories',
          'images/content',
          'images/icons',
          'images/manufacturers',
          'images/product_images/info_images',
          'images/product_images/original_images',
          'images/product_images/popup_images',
          'images/product_images/thumbnail_images',
          'import',
          'log',
          'media/content',
          'media/products',
          'media/products/backup',
          'templates_c'
      ),
      'adirs' => array(
          'includes/external/magnalister',
          'includes/external/shopgate/shopgate_library/config',
          'templates/tpl_modified',
          'templates/xtc5'
      ),
      'rdirs' => array(
          'includes/external/magnalister'
      )
  );

  foreach ($files_to_check['adirs'] as $dir) {
    if (is_dir(DIR_FS_CATALOG.$dir)) {
      $files_to_check['dirs'][] = $dir;
    }
  }
  unset($files_to_check['adirs']);
  
  // login as ftp user to change permissions of every file and directory
  if (isset($_POST['action']) && $_POST['action']=='ftp') {
    $anonymous = false;
    if (empty($_POST['login'])) {
      $_POST['login'] = 'anonymous';
      $anonymous = true;
    }
    $host = $_POST['host'];
    $port = $_POST['port'];
    $path = trim($_POST['path'], '/');
    $user = $_POST['login'];
    $pass = $_POST['password'];
    

    $ftp = ftp_connect($host, $port);
    if (!ftp_login($ftp, $user, $pass) || !is_resource($ftp)) {
      $error_flag = true;
      $messageStack->add('ftp_message', LOGIN_NOT_POSSIBLE);
      if ($anonymous === true) {
        $_POST['login'] = '';
      }
    }
    
    if ($error_flag === false) {
      foreach ($files_to_check['rdirs'] as $dir) {
        if (is_dir(DIR_FS_CATALOG.$dir)) {
          $files_to_check = scanDirectories(DIR_FS_CATALOG.$dir, $files_to_check);
        }
      }
    
      foreach ($files_to_check as $type => $files) {
        if ($type != 'rdirs') {
          foreach ($files as $file) {
            if (ftp_chmod($ftp, CHMOD_WRITEABLE, '/'.$path.'/'.ltrim($file, '/')) === false) {
              if ($type == 'files') $error_flag = true;
              if ($type == 'dirs') $folder_flag = true;
              $messageStack->add('ftp_message', CHMOD_WAS_NOT_SUCCESSFUL);
              break 2;
            }
          }
        }
      }
    }
    ftp_close ($ftp);
  }

  // new testing of file permissions
  foreach ($files_to_check as $type => $files) {
    foreach ($files as $file) {
      if ($type != 'rdirs') {
        $current_permission = substr(sprintf('%o', fileperms(DIR_FS_CATALOG.$file)), -4);
        if (!is_make_writeable(DIR_FS_CATALOG.$file)) {
          if ($type == 'files') {
            $error_flag = true;
            $file_flag = true;
            $message_arr['file_permission'][] = '<img src="images/icons/error.png" />&nbsp;['.$current_permission.'] '.DIR_FS_CATALOG.$file;
          }
          if ($type == 'dirs') {
            $error_flag = true;
            $folder_flag = true;
            $message_arr['folder_permission'][] = '<img src="images/icons/error.png" />&nbsp;['.$current_permission.'] '.DIR_FS_CATALOG.$file;
          }
        }
      } else {
        foreach ($files_to_check['rdirs'] as $dir) {
          if (is_dir(DIR_FS_CATALOG.$dir)) {
            $rfiles_to_check[$dir] = scanDirectories(DIR_FS_CATALOG.$dir, array());
          }
        }
        if (is_array($rfiles_to_check)) {
          foreach ($rfiles_to_check as $key => $rdir) {
            foreach ($rdir as $type => $files) {
              foreach ($files as $file) {
                if (!is_make_writeable(DIR_FS_CATALOG.$file) && $rfolder_flag != $key) {
                  $error_flag = true;
                  $rfolder_flag = true;
                  $message_arr['rfolder_permission'][] = '<img src="images/icons/error.png" />&nbsp;'.DIR_FS_CATALOG.$key;
                }
              }
            }
          }
        }
      }
    }
  }
  if (isset($message_arr['file_permission'])) {
    $messageStack->add('file_permission', implode('<br>', $message_arr['file_permission']));
  }

  if (isset($message_arr['folder_permission'])) {
    $messageStack->add('folder_permission', implode('<br>', $message_arr['folder_permission']));
  }

  if (isset($message_arr['rfolder_permission'])) {
    $messageStack->add('rfolder_permission', implode('<br>', $message_arr['rfolder_permission']));
  }

?>