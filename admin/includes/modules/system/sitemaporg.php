<?php
/* -----------------------------------------------------------------------------------------
   
   $Id: sitemaporg.php 
   XML-Sitemap.org for xt:Commerce SP2.1a
   by Mathis Klooss
   V1.2
   -----------------------------------------------------------------------------------------
      Original Script:
   $Id: gsitemaps.php 
   Google Sitemaps by hendrik.koch@gmx.de
   V1.1 August 2006
   -----------------------------------------------------------------------------------------
   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );


require_once(DIR_FS_INC . 'xtc_href_link_from_admin.inc.php');
require_once(DIR_FS_INC . 'xtc_get_parent_categories.inc.php');
require_once(DIR_FS_INC . 'xtc_get_category_path.inc.php');

class sitemaporg {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

    $this->code = 'sitemaporg';
    $this->title = MODULE_SITEMAPORG_TEXT_TITLE;
    $this->description = MODULE_SITEMAPORG_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_SITEMAPORG_SORT_ORDER;
    $this->enabled = ((MODULE_SITEMAPORG_STATUS == 'True') ? true : false);

  }
  
  // -------------------- XML Generator ----------------------
  function xls_sitemap_top( ) {
    $ret ='<?xml version="1.0" encoding="utf-8"?>'."\n";
    $ret.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\n";
    return $ret;
  }
  
  function xls_sitemap_bottom() {
    $ret ='</urlset>'."\n";
    return $ret;
  }
  
  function gmt_diff() {
    preg_match_all("/([\+|\-][0-9][0-9])([0-9][0-9])/", date("O"), $ausgabe, PREG_PATTERN_ORDER);
    return $ausgabe[1][0] . ":" . $ausgabe[2][0];
  }

  function xls_sitemap_entry( $url, $lastmod='', $priority=MODULE_SITEMAPORG_PRIORITY_LIST, $changefreq=MODULE_SITEMAPORG_CHANGEFREQ ) {
    if( $lastmod!='' ) {
      $lastmod = str_replace(' ', 'T', $lastmod);
      $lastmod.= $this->gmt_diff();
    }
    
    $ret ="\t<url>\n";
    $ret.="\t\t<loc>" . $url . "</loc>\n";
    if( $lastmod != '' ) {
      $ret.="\t\t<lastmod>" . $lastmod . "</lastmod>\n";
    }
    //$ret.="\t\t<changefreq>" . $changefreq . "</changefreq>\n";
    //$ret.="\t\t<priority>" . $priority . "</priority>\n";
    $ret.="\t</url>\n";
    
    return $ret;
  }
  
  // -------------------- Contents ----------------------
  function process_contents( &$schema ) {
    global $_POST;
    $content_query = "SELECT content_id,
                             categories_id,
                             parent_id,
                             content_title,
                             content_group
                        FROM ".TABLE_CONTENT_MANAGER."
                       WHERE languages_id='".(int)$_SESSION['languages_id']."'
                             ".$group_check." 
                         and content_status = '1' 
                    order by sort_order";

    $content_query = xtDBquery($content_query);
    while ($content_data=xtc_db_fetch_array($content_query,true)) {
      $link = encode_htmlspecialchars(xtc_href_link_from_admin('shop_content.php','coID='.$content_data['content_group']));
      $entry = $this->xls_sitemap_entry($link, '', $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );     
      $schema .= $entry;          
    }
  }

  // ------------------- Manufacturer ---------------------
  function process_manufacturers( &$schema ) {
    global $_POST;
    $manufacturers_query = "SELECT manufacturers_id,
                                   manufacturers_name
                              FROM ". TABLE_MANUFACTURERS;

    $manufacturers_query = xtDBquery($manufacturers_query);
    while ($manufacturers_data=xtc_db_fetch_array($manufacturers_query,true)) {
      $link = encode_htmlspecialchars(xtc_href_link_from_admin('index.php','manufacturers_id='.$manufacturers_data['manufacturers_id']));
      $entry = $this->xls_sitemap_entry( $link, '', $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );     
      $schema .= $entry;          
    }
    
  }
    
  // -------------------- Categories ----------------------
  function process_categories( &$schema ) {
    global $_POST;
    $categories_query = "SELECT c.categories_image,
                                c.categories_id,
                                cd.categories_name,
                                c.date_added,
                                c.last_modified
                           FROM " . TABLE_CATEGORIES . " c 
                      LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION ." cd 
                                ON c.categories_id = cd.categories_id
                          WHERE c.categories_status = '1'                      
                            AND cd.language_id = ".(int)$_SESSION['languages_id']." 
                                ".$group_check."
                       ORDER BY c.sort_order ASC";

    $categories_query = xtDBquery($categories_query);
    while ($categories = xtc_db_fetch_array($categories_query,true)) {
      $catPath = xtc_get_category_path($categories['categories_id']);
      $link = encode_htmlspecialchars(xtc_href_link_from_admin('index.php', 'cPath='.$catPath));
      $date = (empty($categories['last_modified']) ? $categories['date_added'] : $categories['last_modified'] );
      $entry = $this->xls_sitemap_entry( $link, $date, $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );     
      $schema .= $entry;
    }
  }
  

  // -------------------- Products ----------------------
  function process_products( &$schema ) {      
    global $_POST;
    $export_query =xtc_db_query("SELECT p.products_id,
                                        p.products_last_modified,
                                        p.products_date_added,
                                        pd.products_name
                                   FROM " . TABLE_PRODUCTS . " p, 
                                        " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                  WHERE p.products_status = 1 and
                                        p.products_id=pd.products_id and
                                        pd.language_id=".(int)$_SESSION['languages_id']."
                               ORDER BY p.products_id");

    while ($products = xtc_db_fetch_array($export_query)) {
      $link = encode_htmlspecialchars(xtc_href_link_from_admin('product_info.php', 'products_id='.$products['products_id']));
      $date = (empty($products['products_last_modified']) ? $products['products_date_added'] : $products['products_last_modified'] );
      $entry = $this->xls_sitemap_entry( $link, $date, $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_PRODUCT']);     
      $schema .= $entry;
    }
  }


  function process($file) {
    global $_POST;
    $file = $_POST['configuration']['MODULE_SITEMAPORG_FILE'];
    @xtc_set_time_limit(0);
   
    $schema = $this->xls_sitemap_top();

    $schema.= $this->xls_sitemap_entry(xtc_href_link_from_admin('index.php'), '', $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );
    $this->process_contents($schema);
    $this->process_categories($schema);
    $this->process_products($schema);
    $this->process_manufacturers($schema);
    
    $schema.= $this->xls_sitemap_bottom();
  
    if( $_POST['configuration']['MODULE_SITEMAPORG_ROOT'] == 'yes' && $_POST['configuration']['MODULE_SITEMAPORG_EXPORT'] == 'no') {
      $filename = DIR_FS_DOCUMENT_ROOT.$_POST['configuration']['MODULE_SITEMAPORG_FILE']; 
    } else {
      $filename = DIR_FS_DOCUMENT_ROOT.'export/' . $_POST['configuration']['MODULE_SITEMAPORG_FILE'];
    }
  
    if($_POST['configuration']['MODULE_SITEMAPORG_EXPORT'] == 'yes') { $filename = $filename.'_tmp_'.time(); }
  
    if($_POST['configuration']['MODULE_SITEMAPORG_GZIP'] == 'yes') {
      $filename = $filename.'.gz';
      $gz = gzopen($filename,'w');
      gzwrite($gz, $schema);
      gzclose($gz);
      $file = $file.'.gz';
    
    } else {
      $fp = fopen($filename, "w");
      fputs($fp, $schema);
      fclose($fp);
    }
  
    switch ($_POST['configuration']['MODULE_SITEMAPORG_EXPORT']) {
      case 'yes':
        // send File to Browser
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename=' . $file);
        readfile ( $filename );
        unlink( $filename );
        exit;
        break;
      case 'no':
        $sitemap = HTTP_SERVER.DIR_WS_CATALOG.(($_POST['configuration']['MODULE_SITEMAPORG_ROOT']=='no') ? 'export/':'').$file;
        break;
    }
  }

  function display() {
    return array('text' => '<br />' . xtc_button(BUTTON_EXPORT) .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=sitemaporg')));
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SITEMAPORG_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_FILE', 'sitemap.xml',  '6', '1', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_ROOT', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_GZIP', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_EXPORT', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");
  }

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SITEMAPORG_%'");
  }

  function keys() {
    return array('MODULE_SITEMAPORG_FILE',
                 'MODULE_SITEMAPORG_STATUS',
                 'MODULE_SITEMAPORG_ROOT',
                 'MODULE_SITEMAPORG_GZIP',
                 'MODULE_SITEMAPORG_EXPORT');
  }
  
}
?>