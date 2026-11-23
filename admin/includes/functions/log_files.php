<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the extensions of the files the log page lists
  function logs_file_extensions() {
    return array("log","log\.zip","log\.gz", "log\.[0-9]+");
  }

  // true for a name as the list shows it: a plain file name with one of the extensions
  function logs_file_name($file) {
    if (!is_string($file)
        || $file === ''
        || basename($file) !== $file
        || preg_match('/[\\\\\x00-\x1f\x7f]/', $file)
        )
    {
      return false;
    }
    foreach (logs_file_extensions() as $value) {
      if (xtc_CheckExt($file, $value)) {
        return true;
      }
    }
    return false;
  }

  // resolved path of a listed file that lies in the log directory itself, false when it leads elsewhere
  function logs_file_path($file) {
    $dir = realpath(DIR_FS_LOG);
    if (!logs_file_name($file) || $dir === false) {
      return false;
    }
    $path = realpath($dir . DIRECTORY_SEPARATOR . $file);

    return (($path !== false && dirname($path) === $dir && is_file($path)) ? $path : false);
  }

  /**
   * logs_delete_all()
   *
   * the button for all logs: removes the files the log page lists, only in the log directory itself.
   * Folders, links to folders and links that lead out of the directory stay, and so do their targets.
   * The result goes to the message stack.
   *
   * @return boolean true when nothing the cleanup should have removed was left
   */
  function logs_delete_all() {
    global $messageStack;

    $dir = rtrim(DIR_FS_LOG, '/'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $kept = array('.htaccess', 'index.html', 'xss_blacklist.log');
    $delete = array();
    $left = array();

    if (!($handle = @opendir($dir))) {
      $left[] = DIR_FS_LOG;
    } else {
      // every entry is judged before anything is removed, a link must not depend on the order
      while (false !== ($entry = readdir($handle))) {
        if ($entry == '.' || $entry == '..' || in_array($entry, $kept, true)) {
          continue;
        }
        if (logs_file_path($entry) !== false) {
          $delete[] = $entry;
        } elseif (logs_file_name($entry) || is_dir($dir.$entry) || is_link($dir.$entry)) {
          // a folder may hold logs as well, the administrator has to know it is still there
          $left[] = $entry;
        }
      }
      closedir($handle);
    }

    foreach ($delete as $entry) {
      // the entry itself, a link without its target
      if (!@unlink($dir.$entry)) {
        $left[] = $entry;
      }
    }

    if (count($left) == 0) {
      $messageStack->add_session(DELETE_LOGS_SUCCESSFUL, 'success');
      return true;
    }
    sort($left);
    $names = array();
    foreach ($left as $entry) {
      $names[] = encode_htmlspecialchars($entry, ENT_COMPAT | ENT_SUBSTITUTE);
    }
    $messageStack->add_session(sprintf(DELETE_LOGS_INCOMPLETE, implode(', ', $names)), 'error');

    return false;
  }
