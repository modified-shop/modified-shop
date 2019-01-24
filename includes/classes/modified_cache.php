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
  require_once (DIR_FS_EXTERNAL . 'phpfastcache/src/autoload.php');

  use phpFastCache\CacheManager;

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
          self::setConfig([
            'path' => self::get_cache_dir(),
          ]);

          // Get instance of files cache
          self::$objCache = CacheManager::getInstance('files');
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
     * clone
     */
    protected function __clone() {}


    /**
     * constructor
     */
    protected function __construct($config = array()) {}


    /**
     * clone
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
      self::$itemCache = self::$objCache->getItem($id);
    }


    /**
     * getId
     */
    public function getId($id) {
      return self::$itemCache->getKey();
    }
  

    /**
     * set
     */
    public function set($data, $expires = DB_CACHE_EXPIRE) {
      self::$itemCache->set($data)->expiresAfter((int)$expires);
      self::$objCache->save(self::$itemCache);
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
     * setConfig
     */
    public static function setConfig($name, $value = null) {
      CacheManager::setDefaultConfig($name, $value);
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
?>