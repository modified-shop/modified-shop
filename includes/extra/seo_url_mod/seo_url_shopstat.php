<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('SEO_SEPARATOR') OR define('SEO_SEPARATOR',':');

defined('CAT_DIVIDER') OR define('CAT_DIVIDER',SEO_SEPARATOR.SEO_SEPARATOR.SEO_SEPARATOR);
defined('ART_DIVIDER') OR define('ART_DIVIDER',SEO_SEPARATOR.SEO_SEPARATOR);
defined('CNT_DIVIDER') OR define('CNT_DIVIDER',SEO_SEPARATOR.'_'.SEO_SEPARATOR);
defined('MAN_DIVIDER') OR define('MAN_DIVIDER',SEO_SEPARATOR.'.'.SEO_SEPARATOR);
defined('PAG_DIVIDER') OR define('PAG_DIVIDER',SEO_SEPARATOR);

defined('ADD_CAT_NAMES_TO_PRODUCT_LINK') OR defined('MODULE_SHOPSTAT_ADD_CAT_NAMES_TO_PRODUCT') ? define('ADD_CAT_NAMES_TO_PRODUCT_LINK', MODULE_SHOPSTAT_ADD_CAT_NAMES_TO_PRODUCT == 'true') : define('ADD_CAT_NAMES_TO_PRODUCT_LINK', true);


class seo_url_shopstat extends modified_seo_url {

  /**
   * instance
   *
   * @var Singleton
   */
  protected static $_instance = null;


  /**
   * get instance
   *
   * @return Singleton
   */
  public static function getInstance() {

    if (null === self::$_instance) {
      self::$_instance = new self;
    }

    return self::$_instance;
  }


  /**
   * create link
   *
   * @return SEO link
   */
  public function create_link($page = '', $parameters = '', $connection = 'NONSSL') {

    parse_str($parameters, $this->params_array);

    if (isset($this->params_array['language']) 
        && strlen($this->params_array['language']) > 0
        )
    {
      $this->language_id = parent::$language[$this->params_array['language']];
    } else {
      $this->language_id = $_SESSION['languages_id'];
    }
    
    $link = '';
        
    switch ($page) {
  
      case '':
      case 'index.php':
        if (isset($this->params_array['cPath'])) {
          if (!isset(self::$links_array['categories'][$this->language_id][$this->params_array['cPath']])) {
            self::$links_array['categories'][$this->language_id][$this->params_array['cPath']] = self::create_catagory_link();
          }
        
          $link = self::$links_array['categories'][$this->language_id][$this->params_array['cPath']];
          if ($link !== false) {
            $link .= self::get_link_params();
          }
        } elseif (isset($this->params_array['manufacturers_id'])) {
          if (!isset(self::$links_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']])) {
            self::$links_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']] = self::create_manufacturers_link();
          }
        
          $link = self::$links_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']];
          if ($link !== false) {
            $link .= self::get_link_params();
          }
        } elseif (defined('ADD_LANGUAGE_TO_LINK')
                  && ADD_LANGUAGE_TO_LINK === true
                  )
        {
          if (!isset(self::$host_array[$this->language_id][$connection])) {
            self::get_host($connection);
          }
          return self::$host_array[$this->language_id][$connection];
        }
        break;
      
      case 'shop_content.php':
        if (isset($this->params_array['coID'])) {
          if (!isset(self::$links_array['content'][$this->language_id][$this->params_array['coID']])) {
            self::$links_array['content'][$this->language_id][$this->params_array['coID']] = self::create_content_link();
          }
        
          $link = self::$links_array['content'][$this->language_id][$this->params_array['coID']];
          if ($link !== false) {
            $link .= self::get_link_params();
          }
        }
        break;

      case 'product_info.php':
        if (!isset($this->params_array['action'])
            && isset($this->params_array['products_id'])
            && strpos($this->params_array['products_id'], '{') === false
            )
        {
          $id = xtc_get_product_path($this->params_array['products_id']);
          
          if (!isset(self::$links_array['products'][$this->language_id][$this->params_array['products_id']][$id])) {
            self::$links_array['products'][$this->language_id][$this->params_array['products_id']][$id] = self::create_products_link();
          }
        
          $link = self::$links_array['products'][$this->language_id][$this->params_array['products_id']][$id];
          if ($link !== false) {
            $link .= self::get_link_params();
          }
        }
        break;

      case 'specials.php':
        $link = 'specials.php' . self::get_link_params(false, '?page=');
        break;
      
      case 'products_new.php':
        $link = 'products_new.php' . self::get_link_params(false, '?page=');
        break;
    }
  
    if (!empty($link)) {
      if (!isset(self::$host_array[$this->language_id][$connection])) {
        self::get_host($connection);
      }
      if (defined('LOWERCASE_SEO_URL')
          && LOWERCASE_SEO_URL === true
          )
      {
        $link = strtolower($link);
      }
      return self::$host_array[$this->language_id][$connection].$link;
    } elseif ($link === false) {
      return '#';
    }
  }


  /**
   * create products link
   *
   * @return products link
   */
  protected function create_products_link() {
    
    if (defined('RUN_MODE_ADMIN')) {
      require_once(DIR_FS_INC . 'xtc_parse_category_path.inc.php');
      require_once(DIR_FS_INC . 'xtc_get_product_path.inc.php');
      require_once(DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
      require_once(DIR_FS_INC . 'xtc_check_agent.inc.php');
    }
    
    $products_link_array = array();    
    
    if (!isset(self::$names_array['products'][$this->language_id][$this->params_array['products_id']])) {
      $products_name_query = xtDBquery("SELECT products_name
                                         FROM ".TABLE_PRODUCTS_DESCRIPTION."
                                        WHERE products_id = '".(int)$this->params_array['products_id']."'
                                          AND language_id = '".(int)$this->language_id."'");
      $products_name = xtc_db_fetch_array($products_name_query, true);
      self::$names_array['products'][$this->language_id][$this->params_array['products_id']] = self::seo_url_href_mask($products_name['products_name']);
    }
    
    if (!empty(self::$names_array['products'][$this->language_id][$this->params_array['products_id']])) {
      $products_link_array[$this->params_array['products_id']] = self::$names_array['products'][$this->language_id][$this->params_array['products_id']];
    } else {
      return false;
    }
    
    if (!defined('ADD_CAT_NAMES_TO_PRODUCT_LINK')
        || ADD_CAT_NAMES_TO_PRODUCT_LINK === true
        )
    {
      $this->params_array['cPath'] = xtc_get_product_path($this->params_array['products_id']);
      $category_link_array = self::create_catagory_link(true);
      $products_link_array = array_merge($category_link_array, $products_link_array);
    }
    
    $link = implode('/', $products_link_array).ART_DIVIDER.$this->params_array['products_id'];
    
    if (defined('ADD_CAT_NAMES_TO_PRODUCT_LINK')
        && ADD_CAT_NAMES_TO_PRODUCT_LINK === false
        && defined('ADD_CAT_ID_TO_PRODUCT_LINK')
        && ADD_CAT_ID_TO_PRODUCT_LINK === true
        )
    {
      $this->params_array['cPath'] = xtc_get_product_path($this->params_array['products_id']);
      $cat_path_array = explode('_', $this->params_array['cPath']);
      $cat_id = array_pop($cat_path_array);
      if ($cat_id != '') {
        $link .= SEO_SEPARATOR.$cat_id;
      }
    }
    
    return $link;
  }


  /**
   * create content link
   *
   * @return content link
   */
  protected function create_content_link() {

    $content_link_array = array();    
    
    if (!isset(self::$names_array['content'][$this->language_id][$this->params_array['coID']])) {
      $content_name_query = xtDBquery("SELECT content_title
                                         FROM ".TABLE_CONTENT_MANAGER."
                                        WHERE content_group = '".(int)$this->params_array['coID']."'
                                          AND languages_id = '".(int)$this->language_id."'");
      $content_name = xtc_db_fetch_array($content_name_query, true);
      self::$names_array['content'][$this->language_id][$this->params_array['coID']] = self::seo_url_href_mask($content_name['content_title']);
    }
    
    if (!empty(self::$names_array['content'][$this->language_id][$this->params_array['coID']])) {
      $content_link_array[$this->params_array['coID']] = self::$names_array['content'][$this->language_id][$this->params_array['coID']];
    } else {
      return false;
    }

    $link = implode('/', $content_link_array).CNT_DIVIDER.$this->params_array['coID'];
    
    return $link;
  }


  /**
   * create manufacturers link
   *
   * @return manufacturers link
   */
  protected function create_manufacturers_link() {

    $manufacturers_link_array = array();    
    
    if (!isset(self::$names_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']])) {
      $manufacturers_name_query = xtDBquery("SELECT manufacturers_name
                                               FROM ".TABLE_MANUFACTURERS."
                                              WHERE manufacturers_id = '".(int)$this->params_array['manufacturers_id']."'");
      $manufacturers_name = xtc_db_fetch_array($manufacturers_name_query, true);
      self::$names_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']] = self::seo_url_href_mask($manufacturers_name['manufacturers_name']);
    }
    
    if (!empty(self::$names_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']])) {
      $manufacturers_link_array[$this->params_array['manufacturers_id']] = self::$names_array['manufacturers'][$this->language_id][$this->params_array['manufacturers_id']];
    } else {
      return false;
    }

    $link = implode('/', $manufacturers_link_array).MAN_DIVIDER.$this->params_array['manufacturers_id'];
    
    return $link;
  }


  /**
   * create category link
   *
   * @return category link
   */
  protected function create_catagory_link($plain = false) {

    $category_link_array = array();    
    $cat_path_array = explode('_', $this->params_array['cPath']);
    
    foreach ($cat_path_array as $categories_id) {
      if (!isset(self::$names_array['categories'][$this->language_id][$categories_id])) {
        $categories_name_query = xtDBquery("SELECT categories_name
                                              FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                             WHERE categories_id = '".(int)$categories_id."'
                                               AND language_id = '".(int)$this->language_id."'");
        $categories_name = xtc_db_fetch_array($categories_name_query, true);
        self::$names_array['categories'][$this->language_id][$categories_id] = self::seo_url_href_mask($categories_name['categories_name']);
      }
      
      if (!empty(self::$names_array['categories'][$this->language_id][$categories_id])) {
        $category_link_array[$categories_id] = self::$names_array['categories'][$this->language_id][$categories_id];
      }
    }
    
    if ($plain === true) {
      return $category_link_array;
    }

    $link = false;
    if (count($category_link_array) > 0) {    
      $link = implode('/', $category_link_array).CAT_DIVIDER.$this->params_array['cPath'];
    }
    
    return $link;
  }
  
  
  /**
   * clear params
   *
   * @return cleared params
   */
  protected function get_link_params($add_suffix = true, $page_divider = PAG_DIVIDER) {
    
    $link = '';
    $separator  = '?';

    if (isset($this->params_array['page'])
        && $this->params_array['page'] > 1
        )
    {
      $link .= $page_divider.$this->params_array['page'];
      
      if (strpos($page_divider, $separator) !== false) {
        $separator = '&';
      }
    }
    
    if ($add_suffix === true) {
      $link .= '.html';
    }
    
    if (!defined('ADD_LANGUAGE_TO_LINK')
        || ADD_LANGUAGE_TO_LINK === false
        ) 
    {
      if (isset($this->params_array['language'])) {
        $link .= $separator.'language='.$this->params_array['language'];
        $separator  = '&';
      }
    }
    
    // unset not needed params
    unset($this->params_array['language']);
    unset($this->params_array['cPath']);
    unset($this->params_array['manufacturers_id']);
    unset($this->params_array['products_id']);
    unset($this->params_array['coID']);
    unset($this->params_array['page']);
    unset($this->params_array['content']);
    unset($this->params_array['product']);

    if (count($this->params_array) > 0) {
      $link .= $separator.http_build_query($this->params_array, '', '&');
      $separator  = '&';
    }
    
    return $link;
  }
  
}
?>