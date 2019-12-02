<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function readfile_chunked($file, $chunksize) {
    $buffer = '';
    // Small files don't need to be chunked
    if (filesize($file) <= $chunksize) {
      $status = readfile($file);
      return $status;
    }
    $handle = fopen($file, 'rb');
    if ($handle === false)
      return false;
    while (!feof($handle)) {
      echo fread($handle, $chunksize);
    }
    $status = fclose($handle);

    return $status;
  }
?>