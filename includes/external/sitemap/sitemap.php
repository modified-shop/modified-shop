<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/


  // include needed functions
  require_once(DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
  require_once(DIR_FS_INC . 'xtc_get_category_path.inc.php');
  require_once(DIR_FS_INC . 'xtc_get_products_mo_images.inc.php');


  class sitemap {
  
    var $schema;
    var $language;
    var $group_id;
    var $image_url;
    var $image_path;
    var $url_param;
    var $url_function;

    function __construct() {
      $this->schema = '';
      
      $this->image_path = DIR_FS_CATALOG.DIR_WS_IMAGES;
      $this->image_url = HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES;

      if (defined('RUN_MODE_ADMIN')) {
        $this->url_function = 'xtc_href_link_from_admin';
      } else {
        $this->url_function = 'xtc_href_link';
      }      
    }

    function export() {
      $code = ((isset($_POST['configuration'])) ? $_POST['configuration']['MODULE_SITEMAPORG_LANGUAGE'] : MODULE_SITEMAPORG_LANGUAGE);
      $lang_query = xtc_db_query("SELECT *,
                                         languages_id as id,
                                         language_charset as charset
                                    FROM ".TABLE_LANGUAGES."
                                   WHERE code = '".xtc_db_input((defined('MODULE_MULTILANG_STATUS') && MODULE_MULTILANG_STATUS == 'true') ? $code : DEFAULT_LANGUAGE)."'");
      if (xtc_db_num_rows($lang_query) > 0) {
        while ($lang =  xtc_db_fetch_array($lang_query)) {
          $this->language = $lang;
          
          $this->url_param = '';
          if (defined('MODULE_MULTILANG_STATUS') && MODULE_MULTILANG_STATUS == 'true') {
            $this->url_param = 'language='.$this->language['code'].'&';
          }
          $this->group_id = ((isset($_POST['configuration'])) ? $_POST['configuration']['MODULE_SITEMAPORG_CUSTOMERS_STATUS'] : MODULE_SITEMAPORG_CUSTOMERS_STATUS);

          $this->xml_sitemap_top();
          $this->xml_sitemap_entry(($this->url_function)('index.php', $this->url_param));
    
          $this->process_contents();
          $this->process_categories();
          $this->process_products();
          $this->process_manufacturers();
    
          $this->xml_sitemap_bottom();

          $file = ((isset($_POST['configuration'])) ? $_POST['configuration']['MODULE_SITEMAPORG_FILE'] : MODULE_SITEMAPORG_FILE);

          if ((isset($_POST['configuration']) && $_POST['configuration']['MODULE_SITEMAPORG_ROOT'] == 'yes' || MODULE_SITEMAPORG_ROOT == 'yes')
              && (isset($_POST['configuration']) && $_POST['configuration']['MODULE_SITEMAPORG_EXPORT'] == 'no' || MODULE_SITEMAPORG_EXPORT == 'no')
              ) 
          {
            $filename = DIR_FS_DOCUMENT_ROOT.$file; 
          } else {
            $filename = DIR_FS_DOCUMENT_ROOT.'export/'.$file;
          }
  
          if (defined('RUN_MODE_ADMIN') && (isset($_POST['configuration']) && $_POST['configuration']['MODULE_SITEMAPORG_EXPORT'] == 'yes') || MODULE_SITEMAPORG_EXPORT == 'yes') { 
            $filename = $filename.'_tmp_'.time();
          }
  
          if ((isset($_POST['configuration']) && $_POST['configuration']['MODULE_SITEMAPORG_GZIP'] == 'yes') || MODULE_SITEMAPORG_GZIP == 'yes') {
            $filename = $filename.'.gz';
            $gz = gzopen($filename,'w');
            gzwrite($gz, $this->schema);
            gzclose($gz);
            $file = $file.'.gz';
          } else {
            $fp = fopen($filename, "w");
            fputs($fp, $this->schema);
            fclose($fp);
          }
          
          return array(
            'file' => $file,
            'filename' => $filename,
          );
        }
      }
    }

    function xml_sitemap_top() {
      $this->schema .= '<?xml version="1.0" encoding="utf-8"?>'."\n";
      $this->schema .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
    }

    function xml_sitemap_bottom() {
      $this->schema .= '</urlset>'."\n";
    }

    function xml_sitemap_entry($url, $lastmod = '', $images = '') { 
      if (trim($url) == '#') return; 
      
      $this->schema .= "\t<url>\n";
      $this->schema .= "\t\t<loc>" . $url . "</loc>\n";
      if ($this->check_date($lastmod) === true) {
        $this->schema .= "\t\t<lastmod>" . date('c', strtotime($lastmod)) . "</lastmod>\n";
      }
      if (is_array($images) && count($images) > 0) {
        foreach ($images as $link) {
          $this->xml_image_entry($link);
        }   
      }
      $this->schema .= "\t</url>\n";
    }
  
    function xml_image_entry($link) {
      $this->schema .= "\t\t<image:image>\n";
      $this->schema .= "\t\t\t<image:loc>".encode_utf8(decode_htmlentities($link), $this->language['charset'], true)."</image:loc>\n";
      $this->schema .= "\t\t</image:image>\n";
    }
  
    function process_contents() {

      $group_check = GROUP_CHECK == 'true' ? ' AND group_ids LIKE \'%c_'.$this->group_id.'_group%\' ' : '';

      $content_query = "SELECT content_id,
                               categories_id,
                               parent_id,
                               content_title,
                               content_group,
                               date_added,
                               last_modified
                          FROM ".TABLE_CONTENT_MANAGER."
                         WHERE languages_id = '".(int)$this->language['id']."'
                               ".$group_check." 
                           AND content_status = '1' 
                           AND content_meta_robots NOT LIKE '%noindex%' 
                      ORDER BY sort_order";

      $content_query = xtc_db_query($content_query);
      while ($content_data = xtc_db_fetch_array($content_query)) {
        $link = encode_htmlspecialchars(($this->url_function)('shop_content.php', $this->url_param . xtc_content_link($content_data['content_group'], $content_data['content_title']), 'NONSSL', false));
        $date = (($this->check_date($content_data['last_modified']) === true) ? $content_data['last_modified'] : $content_data['date_added']);
        $this->xml_sitemap_entry($link, $date);     
      }
    }

    function process_manufacturers() {
      $manufacturers_query = "SELECT DISTINCT m.manufacturers_id,
                                              m.manufacturers_name,
                                              m.manufacturers_image,
                                              m.date_added,
                                              m.last_modified
                                         FROM ".TABLE_MANUFACTURERS." as m
                                         JOIN ".TABLE_PRODUCTS." as p 
                                              ON m.manufacturers_id = p.manufacturers_id
                                                AND p.products_status = '1'
                                        WHERE trim(m.manufacturers_name) != ''
                                     ORDER BY m.manufacturers_name";

      $manufacturers_query = xtc_db_query($manufacturers_query);
      while ($manufacturers_data = xtc_db_fetch_array($manufacturers_query)) {
        $link = encode_htmlspecialchars(($this->url_function)('index.php', $this->url_param . xtc_manufacturer_link($manufacturers_data['manufacturers_id'], $manufacturers_data['manufacturers_name']), 'NONSSL', false));
        $date = (($this->check_date($manufacturers_data['last_modified']) === true) ? $manufacturers_data['last_modified'] : $manufacturers_data['date_added']);

        $images = array();
        if (is_file($this->image_path.'manufacturers/'.$manufacturers_data['manufacturers_image'])) {
          $images[] = $this->image_url.'manufacturers/'.urlencode($manufacturers_data['manufacturers_image']);
        }

        $this->xml_sitemap_entry($link, $date, $images);     
      }
    }

    function process_categories() {

      $c_group_check = GROUP_CHECK == 'true' ? ' AND c.group_permission_'.$this->group_id.' = 1 ' : '';

      $categories_query = "SELECT c.categories_image,
                                  c.categories_id,
                                  c.date_added,
                                  c.last_modified,
                                  cd.categories_name
                             FROM " . TABLE_CATEGORIES . " c 
                             JOIN " . TABLE_CATEGORIES_DESCRIPTION ." cd 
                                  ON c.categories_id = cd.categories_id
                                     AND cd.language_id = '".(int)$this->language['id']."'
                                     AND trim(cd.categories_name) != ''
                            WHERE c.categories_status = '1'                      
                                  ".$c_group_check."
                         ORDER BY c.sort_order ASC";

      $categories_query = xtc_db_query($categories_query);
      while ($categories = xtc_db_fetch_array($categories_query)) {
        $link = encode_htmlspecialchars(($this->url_function)('index.php', $this->url_param . xtc_category_link($categories['categories_id'], $categories['categories_name']), 'NONSSL', false));
        $date = (($this->check_date($categories['last_modified']) === true) ? $categories['last_modified'] : $categories['date_added']);

        $images = array();
        if (is_file($this->image_path.'categories/'.$categories['categories_image'])) {
          $images[] = $this->image_url.'categories/'.urlencode($categories['categories_image']);
        }

        $this->xml_sitemap_entry($link, $date, $images);     
      }
    }

    function process_products() {      

      $p_group_check = GROUP_CHECK == 'true' ? ' AND p.group_permission_'.$this->group_id.' = 1 ' : '';
      $c_group_check = GROUP_CHECK == 'true' ? ' AND c.group_permission_'.$this->group_id.' = 1 ' : '';
    
      $products_query = xtc_db_query("SELECT p.products_id,
                                             p.products_last_modified,
                                             p.products_date_added,
                                             p.products_image,
                                             pd.products_name
                                        FROM " . TABLE_PRODUCTS . " p
                                        JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                             ON p.products_id = pd.products_id
                                                AND pd.language_id = '".(int)$this->language['id']."'
                                                AND trim(pd.products_name) != ''
                                        JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                             ON p.products_id = p2c.products_id
                                        JOIN " . TABLE_CATEGORIES . " c 
                                             ON c.categories_id = p2c.categories_id
                                                AND c.categories_status = '1'
                                                    ".$c_group_check."
                                        JOIN " . TABLE_CATEGORIES_DESCRIPTION ." cd 
                                             ON c.categories_id = cd.categories_id
                                                AND cd.language_id = '".(int)$this->language['id']."'
                                                AND trim(cd.categories_name) != ''
                                       WHERE p.products_status = '1'
                                             ".$p_group_check."
                                    ORDER BY p.products_id");

      while ($products = xtc_db_fetch_array($products_query)) {
        $link = encode_htmlspecialchars(($this->url_function)('product_info.php', $this->url_param . xtc_product_link($products['products_id'], $products['products_name']), 'NONSSL', false));
        $date = (($this->check_date($products['products_last_modified']) === true) ? $products['products_last_modified'] : $products['products_date_added']);
        
        $images = array();
        if (is_file($this->image_path.'product_images/popup_images/'.$products['products_image'])) {
          $images[] = $this->image_url.'product_images/popup_images/'.urlencode($products['products_image']);
        }
        $mo_images = xtc_get_products_mo_images($products['products_id']);
        if ($mo_images != false) {
          foreach ($mo_images as $img) {
            if (is_file($this->image_path.'product_images/popup_images/'.$img['image_name'])) {
              $images[] = $this->image_url.'product_images/popup_images/'.urlencode($img['image_name']);
            }
          }
        }

        $this->xml_sitemap_entry($link, $date, $images);     
      }
    }

    function check_date($date) {
      if ($date != '' && strtotime($date) !== false && strtotime($date) > 0) {
        return true;
      }
      return false;
    }
    
  }