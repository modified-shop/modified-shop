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
      return $this->read_files($this->cache_dir.$hash.'/', $names);
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
      return $this->read_files($this->garan_dir.$hash.'/', array('colour.svg', 'nested.svg'));
    }

    function garan_write($hash, $files) {
      return $this->write_files($this->garan_dir, $hash, $files, 'garan');
    }

    function notice_read($hash) {
      return $this->read_files($this->notice_dir.$hash.'/', array('notice.svg', 'notice.json'));
    }

    function notice_write($hash, $files) {
      return $this->write_files($this->notice_dir, $hash, $files, 'notice');
    }

    function garan_path($hash) {
      return $this->garan_dir.$hash.'/';
    }

    function notice_path($hash) {
      return $this->notice_dir.$hash.'/';
    }

    function terms_path($hash, $filename) {
      return $this->terms_dir.$hash.'/'.$filename;
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

      if ($this->create_dir($this->terms_dir.$hash.'/') === false) {
        $this->fail('terms', $this->terms_dir.$hash.'/', 'directory cannot be created');
        return false;
      }

      $temp = $this->terms_dir.$hash.'/'.$this->temp_name();

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
      $files = array();

      foreach ($names as $name) {
        if (!is_file($dir.$name)) {
          return false;
        }

        $content = @file_get_contents($dir.$name);

        if ($content === false || $content === '') {
          return false;
        }

        $files[$name] = $content;
      }

      return $files;
    }

    /**
     * Writes all files of one hash into a temporary neighbour directory, verifies them and
     * renames the complete directory afterwards. An existing target is left untouched.
     *
     * @return bool
     */
    function write_files($base_dir, $hash, $files, $type) {
      $target = $base_dir.$hash.'/';

      if (is_dir($target)) {
        return ($this->read_files($target, array_keys($files)) !== false);
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

      foreach ($files as $name => $content) {
        if (@file_put_contents($temp.$name, $content, LOCK_EX) === false
            || @file_get_contents($temp.$name) !== $content
            )
        {
          $this->remove_dir($temp);
          $this->fail($type, $temp.$name, 'file cannot be written completely');
          return false;
        }
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

    function remove_dir($path) {
      if (!is_dir($path)) {
        return;
      }

      foreach ((array)@scandir($path) as $entry) {
        if ($entry != '.' && $entry != '..') {
          @unlink($path.$entry);
        }
      }

      @rmdir($path);
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
