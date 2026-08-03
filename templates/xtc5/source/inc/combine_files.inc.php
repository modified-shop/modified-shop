<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

function combine_files($f_array,$f_min,$compress_css = false,$f_time = 0)
{
    $f_min_path = DIR_FS_CATALOG.$f_min;
    $f_min_ts = is_file($f_min_path) ? filemtime($f_min_path) : false;
    $f_min_directory_writable = is_writable(dirname($f_min_path));
    $f_min_writable = is_file($f_min_path)
      ? (is_writable($f_min_path) || (!is_link($f_min_path) && $f_min_directory_writable))
      : $f_min_directory_writable;
    $compactor_path = DIR_FS_EXTERNAL.'compactor/compactor.php';
    require_once($compactor_path);
    $compactor_time = Compactor::getImplementationTime();
    $f_time = max((int)$f_time, (int)$compactor_time);
    $compress = false;
    $sources_readable = true;
    foreach ($f_array as $f_plain) {
      $f_plain_path = DIR_FS_CATALOG.$f_plain;
      if (!is_file($f_plain_path) || !is_readable($f_plain_path)) {
        $sources_readable = false;
        break;
      }
      if ($f_min_ts === false || filemtime($f_plain_path) > $f_min_ts) {
        $compress = true;
      }
    }
    
    if ($sources_readable && $f_min_writable && ($f_min_ts === false || $compress || filesize($f_min_path) == 0 || $f_time > $f_min_ts)) {
      $compactor = new Compactor(array('compress_css' => $compress_css));
      foreach ($f_array as $f_plain) {
        $compactor->add(DIR_FS_CATALOG.$f_plain);
      }
      if ($compactor->save($f_min_path) === true) {
        $f_min_ts = filemtime($f_min_path);
        $f_array = array($f_min.'?v='.$f_min_ts);
      }
    } elseif ($f_min_ts !== false) {
      $f_array = array($f_min.'?v='.$f_min_ts);
    }
    
    return $f_array;
  
}
