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

define('MODULE_SITEMAPORG_TEXT_DESCRIPTION', 'Standard definition finden Sie hier: <a href="http://www.sitemaps.org/" target="_blank">www.sitemap.org</a>');
define('MODULE_SITEMAPORG_TEXT_TITLE', 'XML Sitemap.org');
define('MODULE_SITEMAPORG_FILE_TITLE' , '<hr />Dateiname');
define('MODULE_SITEMAPORG_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.<br />(Verzeichnis export/)');
define('MODULE_SITEMAPORG_STATUS_DESC','Modulstatus');
define('MODULE_SITEMAPORG_STATUS_TITLE','Status');
define('MODULE_SITEMAPORG_CHANGEFREQ_TITLE','Wechsel Frequenz');
define('MODULE_SITEMAPORG_CHANGEFREQ_DESC','Die H&auml;ufigkeit, mit der sich die Seite voraussichtlich &auml;ndern wird.');
define('MODULE_SITEMAPORG_ROOT_TITLE', '<hr /><b>Installation im Root?</b>');
define('MODULE_SITEMAPORG_ROOT_DESC', 'Soll die Ergebnisdatei gleich im Rootverzeichnis abgelegt werden?');
define('MODULE_SITEMAPORG_PRIORITY_LIST_TITLE', '<b>Priorit&auml;t f&uuml;r die Liste</b>');
define('MODULE_SITEMAPORG_PRIORITY_LIST_DESC', '');
define('MODULE_SITEMAPORG_PRIORITY_PRODUCT_TITLE', '<b>Priorit&auml;t f&uuml;r die Produkte</b>');
define('MODULE_SITEMAPORG_PRIORITY_PRODUCT_DESC', '');
define('MODULE_SITEMAPORG_GZIP_TITLE', '<b>gzip Komprimierung nutzen?</b>');
define('MODULE_SITEMAPORG_GZIP_DESC', 'Die Endung .gz wird automatisch ans Ende der Datei gesetzt!');
define('MODULE_SITEMAPORG_EXPORT_TITLE', '<hr /><b>Herunterladen?</b>');
define('MODULE_SITEMAPORG_EXPORT_DESC', 'm&ouml;chten Sie die Datei Herunterladen?');
define('MODULE_SITEMAPORG_YAHOO_TITLE', 'YahooID');
define('MODULE_SITEMAPORG_YAHOO_DESC','Geben Sie hier Ihre die Yahoo ID an! Diese wird ben&ouml;tigt, um Yahoo die Sitemap mitzuteilen');

// New function for List:
function xtc_sitemap_cfg_select_option($select_array, $key_value, $key = '') {
	$name = (($key) ? 'configuration['.$key.']' : 'configuration_value');
	$string = '<select name="'.$name.'">';
	for ($i = 0, $n = sizeof($select_array); $i < $n; $i ++) {
		$string .= '<option value="'.htmlspecialchars($select_array[$i]).'"';
		if ($key_value == $select_array[$i])
			$string .= ' selected="selected"';
		$string .= '> '.htmlspecialchars($select_array[$i]).'</option>';
	}
	$string .= '</select>';
	return $string;
}


require_once(DIR_FS_INC . 'xtc_href_link_from_admin.inc.php');

  class sitemaporg {
    var $code, $title, $description, $enabled;


    function curl( $notify_url , $mixed=array() ) {
      $allow_url_fopen = ini_get("allow_url_fopen");
      foreach ($mixed as $value) {
        if($allow_url_fopen == 0 || function_exists('curl_exec') == true) {
          @ob_start();
          $ch = curl_init();
          @curl_setopt($ch, CURLOPT_URL, $value . urlencode($notify_url));
          $user_agent = 'Mozilla/4.0 (compatible; xtc; sitemap-submitter) xt:commerce sitemap-submitter';
          @curl_setopt ( $ch , CURLOPT_USERAGENT, $user_agent);
          $test = @curl_exec($ch);
          @curl_close($ch);
          $ob_get_contents = @ob_get_contents();
          @ob_end_clean();
        } elseif($allow_url_fopen == 1) {
          @fopen($value.urlencode($notify_url), 'r');
          @file_get_contents($value . urlencode($notify_url));
        }
      }
    }
	
    function sitemaporg() {
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
      $ret.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
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
      $ret.="\t\t<changefreq>" . $changefreq . "</changefreq>\n";
      $ret.="\t\t<priority>" . $priority . "</priority>\n";
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
        $link = htmlspecialchars(xtc_href_link_from_admin('shop_content.php','coID='.$content_data['content_group']));
        $entry=$this->xls_sitemap_entry($link, '', $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );     
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
        $link = htmlspecialchars(xtc_href_link_from_admin('index.php','manufacturers_id='.$manufacturers_data['manufacturers_id']));
        $entry=$this->xls_sitemap_entry( $link, '', $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );     
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
                        left join " . TABLE_CATEGORIES_DESCRIPTION ." cd 
                                  on c.categories_id = cd.categories_id
                            WHERE c.categories_status = '1'                      
                              and cd.language_id = ".$_SESSION['languages_id']." 
                              and c.parent_id = '0' 
                                  ".$group_check."
                         ORDER BY c.sort_order ASC";

      $categories_query = xtDBquery($categories_query);
      while ($categories = xtc_db_fetch_array($categories_query,true)) {
        $link = htmlspecialchars(xtc_href_link_from_admin('index.php', 'cPath='.$categories['categories_id']));
        $date = (empty($categories['last_modified']) ? $categories['date_added'] : $categories['last_modified'] );
        $entry=$this->xls_sitemap_entry( $link, $date, $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_LIST'] );     
        $schema .= $entry;
      }
    }
    

// -------------------- Products ----------------------
    function process_products( &$schema ) {      
      global $_POST;
      $export_query =xtc_db_query("SELECT p.products_id,
                                          p.products_last_modified, 
                                          pd.products_name
                                     FROM " . TABLE_PRODUCTS . " p, 
                                          " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                    WHERE p.products_status = 1 and
                                          p.products_id=pd.products_id and
                                          pd.language_id=".$_SESSION['languages_id']."
                                 ORDER BY p.products_id");

      while ($products = xtc_db_fetch_array($export_query)) {
          $link = htmlspecialchars(xtc_href_link_from_admin('product_info.php', 'products_id='.$products['products_id']));
          $entry=$this->xls_sitemap_entry( $link, $products['products_last_modified'], $_POST['configuration']['MODULE_SITEMAPORG_PRIORITY_PRODUCT']);     
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
          $seo[] = 'http://submissions.ask.com/ping?sitemap=';
          $seo[] = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=';
          $seo[] = 'http://webmaster.live.com/webmaster/ping.aspx?siteMap=';
          if($_POST['configuration']['MODULE_SITEMAPORG_YAHOO']!='YahooDemo' || !empty($_POST['configuration']['MODULE_SITEMAPORG_YAHOO'])) {
            $seo[] = 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid='.urlencode($_POST['configuration']['MODULE_SITEMAPORG_YAHOO']).'&url=';
          }
          $this->curl($sitemap, $seo);
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
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_CHANGEFREQ', 'weekly',  '6', '1', 'xtc_sitemap_cfg_select_option(array(\'always\', \'hourly\', \'daily\', \'weekly\', \'monthly\', \'yearly\', \'never\'), ', now())");     
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_PRIORITY_LIST', '0.5',  '6', '1', 'xtc_sitemap_cfg_select_option(array(\'0.1\', \'0.2\', \'0.3\', \'0.4\', \'0.5\', \'0.6\', \'0.7\', \'0.8\', \'0.9\', \'1\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_PRIORITY_PRODUCT', '0.8',  '6', '1', 'xtc_sitemap_cfg_select_option(array(\'0.1\', \'0.2\', \'0.3\', \'0.4\', \'0.5\', \'0.6\', \'0.7\', \'0.8\', \'0.9\', \'1\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_GZIP', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_EXPORT', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SITEMAPORG_YAHOO', 'YahooDemo',  '6', '1', '', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SITEMAPORG_STATUS','MODULE_SITEMAPORG_FILE','MODULE_SITEMAPORG_STATUS','MODULE_SITEMAPORG_ROOT','MODULE_SITEMAPORG_CHANGEFREQ','MODULE_SITEMAPORG_PRIORITY_LIST','MODULE_SITEMAPORG_PRIORITY_PRODUCT','MODULE_SITEMAPORG_GZIP','MODULE_SITEMAPORG_YAHOO','MODULE_SITEMAPORG_EXPORT');
    }
    
  }
?>