<?php
  /* --------------------------------------------------------------
   $Id: check_permissions.php 3584 2012-08-31 12:47:10Z web28 $
   
   modified 1.06 rev8

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------*/
   
  // file and folder permission checks
  $error_flag = false;
  $folder_flag = false;
  $message='';
  $ok_message='';

  //new permission handling and auto change system
  $file_flag = false;
  $ftp_message = '';
  $files_to_check = array('files' => array('includes/configure.php',
                                           //'includes/configure.org.php',
                                           'admin/includes/configure.php',
                                           //'admin/includes/configure.org.php',    
                                           'sitemap.xml',
                                          ),
                          'dirs' => array('admin/backups',
                                          'admin/images/graphs',
                                          'admin/images/icons',
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
                                          'import',
                                          'media/content',
                                          'media/products',
                                          'media/products/backup',
                                          'log',
                                          'templates_c',
                                     ),
                          );
  
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

    foreach ($files_to_check as $type => $files) {
      foreach ($files as $file) {
        if (!ftp_site($ftp, 'CHMOD 0777 '.$path.$file)) {
          if ($type=='files') $error_flag = true;
          else if ($type='dirs') $folder_flag = true;
          $ftp_message .= CHMOD_WAS_NOT_SUCCESSFUL.'<br />';
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
      if (!is_writeable(DIR_FS_CATALOG.$file)) {
        if ($type=='files') {
          $error_flag = true;
          $file_flag = true;
          $message .= '<strong>'.TEXT_WRONG_FILE_PERMISSION.'</strong>'.DIR_FS_CATALOG.$file.'<br />';
        }
        else if ($type='dirs') {
          $error_flag=true;
          $folder_flag = true;
          $message .= '<strong>'.TEXT_WRONG_FOLDER_PERMISSION.'</strong>'.DIR_FS_CATALOG.$file.'<br />';
        }
      }
    }
  }