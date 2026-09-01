<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_CATALOG.'inc/guarantee_labels_log.inc.php');

  /**
   * Cache and archive of the EU guarantee labels.
   *
   * The cache may be dropped at any time and is rebuilt from the versioned templates.
   * The archive is written once per hash and never overwritten, because order rows point
   * at it. Every write goes through a temporary neighbour first and is renamed only after
   * the content was verified, so no database row can ever reference a half written file.
   */
  class guarantee_labels_archive {

    const CHECKSUM_FILE = 'checksums.json';

    var $cache_dir;
    var $garan_dir;
    var $notice_dir;
    var $terms_dir;
    var $errors;

    function __construct() {
      // delcache removes cache/guarantee_labels/ including the directory itself
      $this->cache_dir = DIR_FS_CATALOG.'cache/guarantee_labels/';
      $this->garan_dir = DIR_FS_CATALOG.'media/guarantee_labels/archive/garan/';
      $this->notice_dir = DIR_FS_CATALOG.'media/guarantee_labels/archive/notice/';
      // has to stay below the document root, check_attachments() prefixes it otherwise
      $this->terms_dir = DIR_FS_CATALOG.'media/products/garan_archive/';
      $this->errors = array();
    }

    // ---------------------------------------------------------------- cache --

    /**
     * @return mixed array of file name and content, false when the cache is missing or incomplete
     */
    function cache_read($hash, $names) {
      return $this->read_files($this->hash_path($this->cache_dir, $hash), $names);
    }

    /**
     * A failing cache write is not an error for the caller: the generated files can still be
     * used for the current request.
     *
     * @return bool
     */
    function cache_write($hash, $files) {
      return $this->write_files($this->cache_dir, $hash, $files, 'cache');
    }

    // -------------------------------------------------------------- archive --

    function garan_read($hash) {
      return $this->read_files($this->hash_path($this->garan_dir, $hash), array('colour.svg', 'nested.svg'));
    }

    function garan_write($hash, $files) {
      return $this->write_files($this->garan_dir, $hash, $files, 'garan');
    }

    function notice_read($hash) {
      return $this->read_files($this->hash_path($this->notice_dir, $hash), array('notice.svg', 'notice.json'));
    }

    function notice_write($hash, $files) {
      return $this->write_files($this->notice_dir, $hash, $files, 'notice');
    }

    /**
     * A hash directory name, refused when it is not one.
     *
     * Every hash this class handles is a sha256 written by the module itself, so this never
     * triggers in normal operation. It is here because the value comes out of a database column
     * and is put into a file path: a column that ever carries something else must not be able to
     * point the archive at another directory.
     *
     * @param string $hash
     * @return string empty when the value is not a hash
     */
    function hash_dir($hash) {
      return preg_match('/^[0-9a-f]{64}$/', (string)$hash) ? (string)$hash : '';
    }

    /**
     * The directory one hash names below a base, or an empty string when it names none.
     *
     * Every path this class builds goes through here, reading and writing alike. A guard that
     * only covers the path builders would leave the directory of a write untouched, which is
     * exactly where a bad value would do its damage.
     *
     * @param string $base_dir
     * @param string $hash
     * @return string
     */
    function hash_path($base_dir, $hash) {
      $hash = $this->hash_dir($hash);

      return ($hash === '') ? '' : $base_dir.$hash.'/';
    }

    function garan_path($hash) {
      return $this->hash_path($this->garan_dir, $hash);
    }

    function notice_path($hash) {
      return $this->hash_path($this->notice_dir, $hash);
    }

    function terms_path($hash, $filename) {
      $directory = $this->hash_path($this->terms_dir, $hash);
      $filename = (string)$filename;

      // the same rule the name was stored under, asked again on the way out
      if ($directory === '' || $filename !== basename($filename) || strpbrk($filename, ",/\\\0") !== false) {
        return '';
      }

      return $directory.$filename;
    }

    /**
     * Archives one guarantee document. Files with identical content but different names may
     * live in the same hash directory, so this works on file level instead of directory level.
     *
     * @param string $hash sha256 of the file content
     * @param string $filename sanitised name including its extension
     * @param string $source absolute path of the file to archive
     * @return bool
     */
    function terms_write($hash, $filename, $source) {
      $target = $this->terms_path($hash, $filename);

      // terms_path() refuses a name that is not a hash or not a plain file name, and the
      // directory below is built from the same value
      if ($target === '') {
        $this->fail('terms', $hash.'/'.$filename, 'not a usable archive path');
        return false;
      }

      if (is_file($target)) {
        if (hash_file('sha256', $target) === $hash) {
          return true;
        }
        // a mismatching file under a content hash means the archive is damaged, never overwrite it
        $this->fail('terms', $target, 'existing archive file does not match its hash');
        return false;
      }

      if (!is_file($source)) {
        $this->fail('terms', $source, 'source file is missing');
        return false;
      }

      $directory = $this->hash_path($this->terms_dir, $hash);

      if ($this->create_dir($directory) === false) {
        $this->fail('terms', $directory, 'directory cannot be created');
        return false;
      }

      $temp = $directory.$this->temp_name();

      if (@copy($source, $temp) === false) {
        $this->fail('terms', $temp, 'file cannot be written');
        return false;
      }

      if (hash_file('sha256', $temp) !== $hash) {
        @unlink($temp);
        $this->fail('terms', $temp, 'written file does not match its hash');
        return false;
      }

      if (@rename($temp, $target) === false) {
        @unlink($temp);
        // another request may have archived the same file in the meantime
        return (is_file($target) && hash_file('sha256', $target) === $hash);
      }

      return true;
    }

    // --------------------------------------------------------------- errors --

    function has_errors() {
      return (count($this->errors) > 0);
    }

    function get_errors() {
      return $this->errors;
    }

    // -------------------------------------------------------------- helpers --

    /**
     * @return mixed array of file name and content, false when one file is missing or empty
     */
    function read_files($dir, $names) {
      if ($dir === '' || substr($dir, -1) !== '/') {
        return false;
      }

      $files = array();
      $checksums = $this->read_checksums($dir);

      // a sidecar that cannot be read means the directory cannot be vouched for any more
      if ($checksums === false) {
        return false;
      }

      foreach ($names as $name) {
        if (!is_file($dir.$name)) {
          return false;
        }

        $content = @file_get_contents($dir.$name);

        if ($content === false || $content === '') {
          return false;
        }

        // Archives written before the sidecar existed carry none at all and stay readable. As
        // soon as one is there it has to cover every file, otherwise a replaced file could hide
        // behind a removed entry.
        if (count($checksums) > 0
            && (!isset($checksums[$name]) || hash('sha256', $content) !== $checksums[$name])
            )
        {
          return false;
        }

        $files[$name] = $content;
      }

      return $files;
    }

    /**
     * Reads the checksum sidecar of one hash directory. It is what makes a damaged archive
     * distinguishable from an intact one, because the directory hash covers the input data
     * of the label and not the bytes of the rendered files.
     *
     * @param string $dir
     * @return array name => sha256, empty when no sidecar is there
     */
    function read_checksums($dir) {
      if (!is_file($dir.self::CHECKSUM_FILE)) {
        return array();
      }

      $data = @json_decode((string)@file_get_contents($dir.self::CHECKSUM_FILE), true);

      // an unreadable sidecar is a damaged archive, not an archive without one
      return (is_array($data) && count($data) > 0) ? $data : false;
    }

    /**
     * Writes all files of one hash into a temporary neighbour directory, verifies them and
     * renames the complete directory afterwards. An existing target is left untouched.
     *
     * @return bool
     */
    function write_files($base_dir, $hash, $files, $type) {
      $target = $this->hash_path($base_dir, $hash);

      if ($target === '') {
        $this->fail($type, $hash, 'not a usable archive directory');
        return false;
      }

      if (is_dir($target)) {
        if ($this->read_files($target, array_keys($files)) !== false) {
          return true;
        }

        // The content of a hash directory follows from its name, so a damaged one can always be
        // rebuilt. Keeping it would serve the damaged files for good.
        if ($this->remove_dir($target) === false) {
          $this->fail($type, $target, 'damaged directory cannot be removed');
          return false;
        }
      }

      if ($this->create_dir($base_dir) === false) {
        $this->fail($type, $base_dir, 'directory cannot be created');
        return false;
      }

      $temp = $base_dir.$this->temp_name().'/';

      if ($this->create_dir($temp) === false) {
        $this->fail($type, $temp, 'temporary directory cannot be created');
        return false;
      }

      $checksums = array();

      foreach ($files as $name => $content) {
        if (@file_put_contents($temp.$name, $content, LOCK_EX) === false
            || @file_get_contents($temp.$name) !== $content
            )
        {
          $this->remove_dir($temp);
          $this->fail($type, $temp.$name, 'file cannot be written completely');
          return false;
        }

        $checksums[$name] = hash('sha256', $content);
      }

      if (@file_put_contents($temp.self::CHECKSUM_FILE, json_encode($checksums), LOCK_EX) === false) {
        $this->remove_dir($temp);
        $this->fail($type, $temp.self::CHECKSUM_FILE, 'checksums cannot be written');
        return false;
      }

      // The whole directory is read back the same way a later request reads it. A short write
      // can still report a byte count, and no database row may point at an archive that only
      // turns out to be damaged when it is needed.
      if ($this->read_files($temp, array_keys($files)) === false) {
        $this->remove_dir($temp);
        $this->fail($type, $temp, 'directory does not read back as written');
        return false;
      }

      if (@rename(rtrim($temp, '/'), rtrim($target, '/')) === false) {
        $this->remove_dir($temp);
        // another request may have created the same hash directory in the meantime
        return (is_dir($target) && $this->read_files($target, array_keys($files)) !== false);
      }

      return true;
    }

    function create_dir($path) {
      if (is_dir($path)) {
        return true;
      }

      return @mkdir($path, 0777, true);
    }

    /**
     * Removes one hash directory. The caller has to know whether it worked: a damaged directory
     * that survives would be served again, so a failure has to be reported instead of ignored.
     *
     * @param string $path
     * @return bool true when the directory is gone
     */
    function remove_dir($path) {
      if (!is_dir($path)) {
        return true;
      }

      foreach ((array)@scandir($path) as $entry) {
        if ($entry == '.' || $entry == '..') {
          continue;
        }

        // hash directories hold files only, a directory here is not ours to walk into
        if (is_dir($path.$entry) || @unlink($path.$entry) === false) {
          return false;
        }
      }

      return (@rmdir($path) !== false);
    }

    function temp_name() {
      return 'tmp_'.getmypid().'_'.md5(uniqid('', true));
    }

    function fail($type, $path, $reason) {
      $this->errors[] = $reason.': '.$path;

      guarantee_labels_log('error', 'guarantee labels {type} archive failed, {reason}: {path}', array(
        'type' => $type,
        'reason' => $reason,
        'path' => $path,
      ));
    }

  }
