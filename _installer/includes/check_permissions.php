<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  $permission_array = array(
    'file_permission' => array(),
    'folder_permission' => array(),
    'rfolder_permission' => array(),
  );
  
  $files_to_check = array(
    'files' => array(
        DIR_ADMIN.'magnalister.php',
        'includes/configure.php',
        'magnaCallback.php',
        'sitemap.xml'
    ),
    'dirs' => array(
        DIR_MODIFIED_INSTALLER.'/update',
        DIR_ADMIN.'backups',
        DIR_ADMIN.'images/graphs',
        DIR_ADMIN.'images/icons',
        'cache',
        'export',
        'images',
        'images/banner',
        'images/categories',
        'images/content',
        'images/icons',
        'images/manufacturers',
        'images/product_images/original_images',
        'images/product_images/popup_images',
        'images/product_images/info_images',
        'images/product_images/midi_images',
        'images/product_images/thumbnail_images',
        'images/product_images/mini_images',
        'images/tags',
        'import',
        'log',
        'media/content',
        'media/content/backup',
        'media/products',
        'media/products/backup',
        'templates_c'
    ),
    'adirs' => array(
        'includes/external/magnalister',
        'templates/tpl_modified',
        'templates/xtc5'
    ),
    'rdirs' => array(
        'includes/external/magnalister'
    )
  );
  
  if (file_exists(DIR_FS_CATALOG.'/includes/local/configure.php')) {    
    $files_to_check['files'][] = 'includes/local/configure.php';
  }
  
  foreach ($files_to_check['adirs'] as $dir) {
    if (is_dir(DIR_FS_CATALOG.$dir)) {
      $files_to_check['dirs'][] = $dir;
    }
  }
  unset($files_to_check['adirs']);
  
  // new testing of file permissions
  foreach ($files_to_check as $type => $files) {
    foreach ($files as $file) {
      if ($type != 'rdirs') {
        $current_permission = substr(sprintf('%o', fileperms(DIR_FS_CATALOG.$file)), -4);
        if (!is_make_writeable(DIR_FS_CATALOG.$file)) {
          if ($type == 'files') {
            $error = true;
            $permission_array['file_permission'][] = $file;
          }
          if ($type == 'dirs') {
            $error = true;
            $permission_array['folder_permission'][] = $file;
          }
        }
      } else {
        foreach ($files_to_check['rdirs'] as $dir) {
          if (is_dir(DIR_FS_CATALOG.$dir)) {
            $rfiles_to_check[$dir] = scanDirectories(DIR_FS_CATALOG.$dir, false);
          }
        }
        if (is_array($rfiles_to_check)) {
          foreach ($rfiles_to_check as $key => $rdir) {
            foreach ($rdir as $type => $files) {
              foreach ($files as $file) {
                if (!is_make_writeable(DIR_FS_CATALOG.$file) && $rfolder_flag != $key) {
                  $error = true;
                  $rfolder_flag = true;
                  $permission_array['rfolder_permission'][] = $key;
                }
              }
            }
          }
        }
      }
    }
  }
?>