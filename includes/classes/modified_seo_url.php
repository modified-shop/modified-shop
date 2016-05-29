<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('SPECIAL_CHAR_FR') OR define('SPECIAL_CHAR_FR', true);
defined('SPECIAL_CHAR_ES') OR define('SPECIAL_CHAR_ES', true);
defined('SPECIAL_CHAR_PL') OR define('SPECIAL_CHAR_PL', true);
defined('SPECIAL_CHAR_CZ') OR define('SPECIAL_CHAR_CZ', true);
defined('SPECIAL_CHAR_MORE') OR define('SPECIAL_CHAR_MORE', true);


class modified_seo_url {

  /**
   * instance
   *
   * @var Singleton
   */
  protected static $_instance = null;


  /**
   * host
   *
   * @var host array
   */
  public static $language = array();

  /**
   * links
   *
   * @var links array
   */
  public static $names_array = array(
    'categories' => array(),
    'products' => array(),
    'content' => array(),
    'manufacturers' => array(),
  );

  /**
   * links
   *
   * @var links array
   */
  public static $links_array = array(
    'categories' => array(),
    'products' => array(),
    'content' => array(),
    'manufacturers' => array(),
  );


  /**
   * host
   *
   * @var host array
   */
  public static $host_array = array();


  /**
   * get instance
   *
   * @return   Singleton
   */
  public static function getInstance() {

    if (null === self::$_instance) {
      self::$_instance = new self;
    }

    return self::$_instance;
  }


  /**
   * clone
   */
  protected function __clone() {}


  /**
   * constructor
   */
  protected function __construct() {
  
    self::get_languages();
  }

  /**
   * get host
   *
   * @return host
   */
  protected function get_host($connection) {

    $host = HTTP_SERVER;

    if ($connection == 'SSL'
        && ENABLE_SSL == true
        )
    {
      $host = HTTPS_SERVER;
    }
      
    self::$host_array[$this->language_id][$connection] = $host.DIR_WS_CATALOG;

    if (defined('ADD_LANGUAGE_TO_LINK')
        && ADD_LANGUAGE_TO_LINK === true
        )
    {
      self::$host_array[$this->language_id][$connection] .= self::$language[$this->language_id].'/';
    }
  }


  /**
   * clear link
   *
   * @return cleared link
   */
  protected function seo_url_href_mask($link) {
    include_once (DIR_FS_INC . 'seo_url_href_mask.php');
  
    return seo_url_href_mask($link);
  }


  /**
   * get language id
   *
   * @return language id
   */
  protected function get_languages() {
  
    require_once(DIR_WS_CLASSES.'language.php');
    $lang = new language();
  
    foreach ($lang->catalog_languages as $code => $values) {
      self::$language[$code] = $lang->catalog_languages[$code]['id'];
      self::$language[$lang->catalog_languages[$code]['id']] = $code;     
    }
  }


  /**
   * create link
   *
   * @return SEO link
   */
  public function create_link($page = '', $parameters = '', $connection = 'NONSSL') {}

}
?>