<?php
  /* --------------------------------------------------------------
   $Id: check_permissions.php 3584 2012-08-31 12:47:10Z web28 $
   
   modified 1.06 rev8

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------*/

  function scanDirectories($rootDir, $allData=array()) {
    $invisibleFileNames = array(".", "..", ".svn");
    if (file_exists($rootDir)) {
      $dirContent = scandir($rootDir);
      foreach($dirContent as $key => $content) {
        $path = $rootDir.'/'.$content;
        if (!in_array($content, $invisibleFileNames)) {
          if (is_file($path) && is_readable($path)) {
            $allData['files'][] = str_replace(DIR_FS_CATALOG, '', $path);
          } elseif (is_dir($path) && is_readable($path)) {
            $allData['dirs'][] = str_replace(DIR_FS_CATALOG, '', $path);
            $allData = scanDirectories($path, $allData);
          }
        }
      }
    }
    return $allData;
  }
   
  // file and folder permission checks
  $error_flag = false;
  $folder_flag = false;
  $message_arr = array();
  $ok_message='';

  //new permission handling and auto change system
  $file_flag = false;
  $ftp_message = '';
  $files_to_check = array('files' => array(DIR_ADMIN.'/magnalister.php',
                                           'includes/configure.php',
                                           'magnaCallback.php',
                                           'sitemap.xml',
                                          ),
                          'dirs' => array(DIR_ADMIN.'backups',
                                          DIR_ADMIN.'images/graphs',
                                          DIR_ADMIN.'images/icons',
                                          'cache',
                                          'export',
                                          'export/idealo_realtime',
                                          'images',
                                          'images/banner',
                                          'images/categories',
                                          'images/content',
                                          'images/product_images/info_images',
                                          'images/product_images/original_images',
                                          'images/product_images/popup_images',
                                          'images/product_images/thumbnail_images',
                                          'images/manufacturers',
                                          'images/icons',
                                          'import',
                                          'log',
                                          'media/content',
                                          'media/products',
                                          'media/products/backup',
                                          'templates_c',
                                     ),
                          'adirs' => array('includes/external/magnalister',
                                           'includes/external/shopgate/shopgate_library/config',
                                           'templates/tpl_modified',
                                     ),
                          'rdirs' => array('includes/external/magnalister',
                                     ),
                          );

  foreach ($files_to_check['adirs'] as $dir) {
    if (is_dir(DIR_FS_CATALOG.$dir)) {
      $files_to_check['dirs'][] = $dir;
    }
  }
  unset($files_to_check['adirs']);
  
  // login as ftp user to change permissions of every file and directory
  if (isset($_POST['action']) && $_POST['action']=='ftp' && !empty($_POST['login'])) {
    $host = $_POST['host'];
    $port = $_POST['port'];
    $path = $_POST['path'];
    $user = $_POST['login'];
    $pass = $_POST['password'];
    

    $ftp = ftp_connect($host, $port);
    if (!ftp_login($ftp, $user, $pass)) {
      $error_flag = true;
      $ftp_message = LOGIN_NOT_POSSIBLE;
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
            if (!ftp_site($ftp, 'CHMOD 0777 '.$path.$file)) {
              if ($type == 'files') $error_flag = true;
              if ($type == 'dirs') $folder_flag = true;
              $ftp_message .= CHMOD_WAS_NOT_SUCCESSFUL.'<br />';
            }
          }
        }
      }
    }
    ftp_close ($ftp);
  }
  
  // try to fix without ftp login - might fail very often depending of server setup
  /*
  if (isset($_GET['action']) && $_GET['action'] == 'fixperms') {
    if (!chmod(DIR_FS_CATALOG . 'includes/configure.php', 0777)) { 
      if ($type=='files') $error_flag = true;
      else if ($type='dirs') $folder_flag = true;
      echo CHMOD_WAS_NOT_SUCCESSFUL.'<br />';
    }
  }
  */
  // end action

  // new testing of file permissions
  foreach ($files_to_check as $type => $files) {
    foreach ($files as $file) {
      if ($type != 'rdirs') {
        if (!is_writeable(DIR_FS_CATALOG.$file)) {
          if ($type == 'files') {
            $error_flag = true;
            $file_flag = true;
            $message_arr['file_permission'][] = DIR_FS_CATALOG.$file;
          }
          if ($type == 'dirs') {
            $error_flag = true;
            $folder_flag = true;
            $message_arr['folder_permission'][] = DIR_FS_CATALOG.$file;
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
                if (!is_writeable(DIR_FS_CATALOG.$file) && $rfolder_flag != $key) {
                  $error_flag = true;
                  $rfolder_flag = $key;
                  $message_arr['rfolder_permission'][] = DIR_FS_CATALOG.$key;
                }
              }
            }
          }
        }
      }
    }
  }
  $messageStack = new messageStack();
  if (isset($message_arr['file_permission'])) {
    foreach ($message_arr['file_permission'] as $value) {
      $messageStack->add('file_permission', $value);
    }
  }

  if (isset($message_arr['folder_permission'])) {
    foreach ($message_arr['folder_permission'] as $value) {
      $messageStack->add('folder_permission', $value);
    }
  }

  if (isset($message_arr['rfolder_permission'])) {
    foreach ($message_arr['rfolder_permission'] as $value) {
      $messageStack->add('rfolder_permission', $value);
    }
  }
  
?>