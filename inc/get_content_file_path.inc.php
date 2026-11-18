<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * get_content_file_path()
   *
   * a stored content file is a plain file name in media/content/ or media/products/,
   * a path in the database must not lead to another file
   *
   * @param string $file stored file name
   * @param string $folder content or products
   * @return string|false absolute path of the file
   */
  function get_content_file_path($file, $folder = 'content') {
    if (!is_string($file)
        || $file == ''
        || $file != basename($file)
        || strpos($file, "\0") !== false
        || !in_array($folder, array('content', 'products'), true)
        )
    {
      return false;
    }

    $path = DIR_FS_CATALOG.'media/'.$folder.'/'.$file;

    return (is_file($path) ? $path : false);
  }

