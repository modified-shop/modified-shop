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
   * @param string $types content (pages), products (product content) or file (saving, attachments, deleting)
   * @return string|false resolved absolute path of the file
   */
  function get_content_file_path($file, $folder = 'content', $types = '') {
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
    if (!is_file($path)) {
      return false;
    }

    // a symbolic link in the media folder must not lead to a file elsewhere,
    // and its target decides the type, not the name of the link
    $real_path = realpath($path);
    $real_folder = realpath(DIR_FS_CATALOG.'media/'.$folder);
    if ($real_path === false
        || $real_folder === false
        || strpos($real_path, $real_folder.DIRECTORY_SEPARATOR) !== 0
        )
    {
      return false;
    }

    $extension = strtolower(pathinfo($real_path, PATHINFO_EXTENSION));
    $types = (($types != '') ? $types : $folder);

    if ($types == 'file') {
      // a file that is only attached or deleted does not run, but script sources stay on the server
      if (preg_match('/^(php[0-9]*|phtml|phar|inc|pl|cgi|py|sh)$/', $extension)) {
        return false;
      }
    } else {
      // pages include PHP files and output these types as they are, other files could show source code
      $allowed = array('php', 'htm', 'html', 'txt');
      if ($types == 'products') {
        $allowed = array_merge($allowed, array(
          'jpg', 'jpeg', 'jpe', 'gif', 'png', 'tiff', 'tif', 'bmp', 'svg',
          'csv', 'tsv',
          'xls', 'xla', 'hlp', 'chm', 'ppt', 'ppz', 'pps', 'pot', 'doc', 'dot', 'pdf', 'rtf',
          'au', 'snd', 'mp2', 'rpm', 'stream', 'wav',
          'mpeg', 'mpg', 'mpe', 'qt', 'mov', 'avi', 'movie',
          'tar', 'zip', 'rar', '7z', 'cab',
        ));
      }
      if (!in_array($extension, $allowed, true)) {
        return false;
      }
    }

    // the resolved path, so the caller decides on include or output by the real type
    return $real_path;
  }
