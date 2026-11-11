<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the old file stays as it is unless the new content was written completely, returns false on failure
  function write_file_atomic($file, $data) {
    $tmp_file = $file.'.'.uniqid().'.tmp';

    $fp = @fopen($tmp_file, 'w');
    if (!is_resource($fp)) {
      return false;
    }
    $written = fwrite($fp, $data);
    $closed = fclose($fp);

    // the replacement keeps the permissions the old file was given
    if (is_file($file)) {
      @chmod($tmp_file, fileperms($file) & 0777);
    }

    if ($written !== strlen($data)
        || $closed === false
        || @rename($tmp_file, $file) === false
        )
    {
      @unlink($tmp_file);
      return false;
    }

    return true;
  }
