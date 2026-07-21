<?php
/**
 * SMARTY PLUGIN: VERSIONING
 *
 * @version    Release: 1.0
 *
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPLv2
 *
 * Description:
 * Appends a cache-busting "?v=<mtime>" query string to a filename, based on
 * the file's last modification time in the current template's img/ folder.
 * Keeps mail clients / browsers from serving a stale cached copy after the
 * file on disk changes, while still allowing them to cache it as long as it
 * doesn't.
 *
 * Example of use:
 * <code>
 * <img src="{$logo_path}{'logo.gif'|versioning}" />
 * </code>
 */

/**
 * Function: append a cache-busting version query string to a filename
 * @param string $filename
 * @return string
 */
function smarty_modifier_versioning($filename) {
  $file_path = DIR_FS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/' . $filename;

  return $filename . (file_exists($file_path) ? '?v=' . filemtime($file_path) : '');
}
