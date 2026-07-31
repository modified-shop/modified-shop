<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function combine_files($f_array,$f_min,$compress_css = false,$f_time = 0) {
    $f_min_path = DIR_FS_CATALOG.$f_min;
    $f_min_dir = dirname($f_min_path);
    if (!is_dir($f_min_dir) && !mkdir($f_min_dir, 0777, true) && !is_dir($f_min_dir)) {
      return $f_array;
    }

    $f_min_ts = is_file($f_min_path)
      ? (is_writeable($f_min_path) ? filemtime($f_min_path) : false)
      : (is_writeable($f_min_dir) ? 0 : false);
    $compress = false;
    foreach ($f_array as $f_plain) {
      if (filemtime(DIR_FS_CATALOG.$f_plain) > $f_min_ts) {
        $compress = true;
        break;
      }
    }
    
    if ($f_min_ts !== false && ($f_min_ts === 0 || $compress === true || filesize($f_min_path) == 0 || $f_time > $f_min_ts)) {
      require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
      $compactor = new Compactor(array('strip_php_comments' => true, 'compress_css' => $compress_css));
      foreach ($f_array as $f_plain) {
        $compactor->add(DIR_FS_CATALOG.$f_plain);
      }
      if ($compactor->save($f_min_path) === true) {
        $f_min_ts = is_writeable($f_min_path) ? filemtime($f_min_path) : false;
        $f_array = array($f_min.'?v='.$f_min_ts);
      }
    } elseif ($f_min_ts) {
      $f_array = array($f_min.'?v='.$f_min_ts);
    }
    
    return $f_array; 
  }
