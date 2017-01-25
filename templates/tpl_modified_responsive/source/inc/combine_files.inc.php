<?php
  /* --------------------------------------------------------------
   $Id: combine_files.inc.php 48 2016-04-26 09:54:10Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

function combine_files($f_array,$f_min,$compress_css = false)
{
    $f_min_ts = is_writeable(DIR_FS_CATALOG.$f_min) ? filemtime(DIR_FS_CATALOG.$f_min) : false;
    $compress = false;
    foreach ($f_array as $f_plain) {
      if (filemtime(DIR_FS_CATALOG.$f_plain) > $f_min_ts) {
        $compress = true;
        break;
      }
    }
    if ($f_min_ts && ($compress === true || filesize(DIR_FS_CATALOG.$f_min) == 0)) {
      require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
      $compactor = new Compactor(array('strip_php_comments' => true, 'compress_css' => $compress_css));
      foreach ($f_array as $f_plain) {
        $compactor->add(DIR_FS_CATALOG.$f_plain);
      }
      if ($compactor->save($f_min) === true) {
        $f_array = array($f_min.'?v='.$f_min_ts);
      }
    } elseif ($f_min_ts) {
      $f_array = array($f_min.'?v='.$f_min_ts);
    }
    
    return $f_array;
  
}