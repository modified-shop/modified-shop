<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  // include needed class
  require_once (DIR_FS_EXTERNAL . 'Phpfastcache/autoload.php');

  use Phpfastcache\CacheManager;
  use Phpfastcache\Config\ConfigurationOption;
  use Phpfastcache\Drivers\Files\Config as FilesConfig;
  use Phpfastcache\Exceptions\PhpfastcacheCorruptedDataException;
  use Phpfastcache\Exceptions\PhpfastcacheRootException;

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/cache/','php') as $file) require_once ($file);

  $_mod_cache_class = strtolower(DB_CACHE_TYPE).'_cache';
  if (!class_exists($_mod_cache_class)) {
    $_mod_cache_class = 'modified_cache';
  }
  
  $modified_cache = $_mod_cache_class::getInstance();


  class modified_cache {

    /**
     * instance
     *
     * @var Singleton
     */
    protected static $_instance = null;


    /**
     * objCache
     *
     * @var object
     */
    protected static $objCache = null;


    /**
     * itemCache
     *
     * @var object
     */
    protected static $itemCache = null;


    /**
     * get instance
     *
     * @return   Singleton
     */
    public static function getInstance($config = array()) {
      if (null === self::$_instance) {
        if (null === self::$objCache) {
          self::setConfig(new ConfigurationOption([
            'path' => self::get_cache_dir(),
          ]));

          // Get instance of files cache
          try {
            self::$objCache = CacheManager::getInstance('files', new FilesConfig([
              'path' => self::get_cache_dir(),
              'securityKey' => static::get_security_key(),
              'secureFileManipulation' => true,
            ]));
          } catch (PhpfastcacheRootException $e) {
            // an unwritable cache dir must not break every page
            trigger_error($e->getMessage(), E_USER_WARNING);
            self::$objCache = CacheManager::getInstance('devnull');
          }
        }
        self::$_instance = new self($config);
      }

      return self::$_instance;
    }

    
    /**
     * get_cache_dir
     *
     * @return cache
     */
    protected static function get_cache_dir() {
      $cache_dir = realpath(DIR_FS_CACHE);
      if (strpos($cache_dir, '/') === false
          || !is_dir($cache_dir) 
          || !is_writeable($cache_dir)
          )
      {
        $cache_dir = SQL_CACHEDIR;
      }      
      return $cache_dir;
    }
    
    
    /**
     * get_security_key
     *
     * @return string
     */
    protected static function get_security_key() {
      // fixed subdir instead of HTTP_HOST, cron jobs may run as another user
      return ((PHP_SAPI == 'cli') ? 'cli' : 'modified');
    }


    /**
     * clone
     */
    protected function __clone() {}


    /**
     * constructor
     */
    protected function __construct($config = array()) {}


    /**
     * clear
     */
    public function clear() {
      self::$objCache->clear();
    }


    /**
     * delete
     */
    public function delete($key) {
      self::$objCache->deleteItem($key);
    }


    /**
     * deleteByTag
     */
    public function deleteByTag($tag) {
      // without tags no item is tagged
      if (!defined('USE_CACHE_TAGS') || USE_CACHE_TAGS != 'true') {
        $this->clear();
        return;
      }

      $items = $this->getByTag($tag);

      if (count($items) > 0) {
        foreach ($items as $item) {
          $this->delete($item->getKey());
        }
      }
    }


    /**
     * isHit
     *
     * @return bool
     */
    public function isHit() {
      return self::$itemCache->isHit();
    }


    /**
     * setId
     */
    public function setId($id) {
      try {
        self::$itemCache = self::$objCache->getItem($id);
      } catch (PhpfastcacheCorruptedDataException $e) {
        // the pool keeps the empty item, so it is a miss and set() overwrites the file
        self::$itemCache = self::$objCache->getItem($id);
      }
    }


    /**
     * getId
     *
     * @return key
     */
    public function getId() {
      return self::$itemCache->getKey();
    }
  

    /**
     * set
     */
    public function set($data, $expires = DB_CACHE_EXPIRE) {
      // Phpfastcache turns a lifetime <= 0 into 150 days
      if ((int)$expires <= 0) {
        return;
      }

      self::$itemCache->set($data)->expiresAfter((int)$expires);
      try {
        self::$objCache->save(self::$itemCache);
      } catch (PhpfastcacheRootException $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
      }
    }


    /**
     * get
     *
     * @return cache
     */
    public function get() {
      return self::$itemCache->get();
    }


    /**
     * get
     *
     * @return cache
     */
    public function getByTag($tag) {
      return self::$objCache->getItemsByTag($tag);
    }


    /**
     * setTags
     */
    public function setTags($tags_array) {
      if (defined('USE_CACHE_TAGS') 
          && USE_CACHE_TAGS == 'true'
          )
      {
        self::$itemCache->setTags((array)$tags_array);
        self::$objCache->save(self::$itemCache);
      }
    }


    /**
     * getTags
     *
     * @return tags
     */
    public function getTags() {
      return self::$itemCache->getTags();
    }


    /**
     * decrement
     */
    public function decrement($step = 1) {
      self::$itemCache->decrement($step);
      self::$objCache->save(self::$itemCache);
    }


    /**
     * increment
     */
    public function increment($step = 1) {
      self::$itemCache->increment($step);
      self::$objCache->save(self::$itemCache);
    }


    /**
     * setConfig
     */
    public static function setConfig($config) {
      CacheManager::setDefaultConfig($config);
    }


    /**
     * getConfig
     *
     * @return config
     */
    public static function getConfig() {
      return CacheManager::getDefaultConfig();
    }

  }
