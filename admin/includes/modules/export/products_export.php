<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  define('MODULE_PRODUCTS_EXPORT_TEXT_DESCRIPTION', 'Artikel Export');
  define('MODULE_PRODUCTS_EXPORT_TEXT_TITLE', 'Artikel Export - CSV');

  define('MODULE_PRODUCTS_EXPORT_STATUS_DESC','Modulstatus');
  define('MODULE_PRODUCTS_EXPORT_STATUS_TITLE','Status');
  define('MODULE_PRODUCTS_EXPORT_FILE_TITLE' , 'Dateiname');
  define('MODULE_PRODUCTS_EXPORT_FILE_DESC' , 'Geben Sie einen Dateinamen ein.<br />(Verzeichnis export/)');

  define('MODULE_PRODUCTS_EXPORT_CUSTOMERS_STATUS_TITLE','<br><strong>Kundengruppe:</strong>');
  define('MODULE_PRODUCTS_EXPORT_CUSTOMERS_STATUS_DESC','Bitte w&auml;hlen Sie die Kundengruppe, die Basis f&uuml;r den Exportierten Preis bildet. (Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie <i>Gast</i>):</strong>');
  define('MODULE_PRODUCTS_EXPORT_LANGUAGE_TITLE','<br><strong>Sprache:</strong>');
  define('MODULE_PRODUCTS_EXPORT_LANGUAGE_DESC','Sprache in der Exportdatei');
  define('MODULE_PRODUCTS_EXPORT_CURRENCY_TITLE','<br><strong>W&auml;hrung:</strong>');
  define('MODULE_PRODUCTS_EXPORT_CURRENCY_DESC','W&auml;hrung in der Exportdatei');
  define('MODULE_PRODUCTS_EXPORT_CAMPAIGNS_TITLE','<br><strong>Kampagnen:</strong>');
  define('MODULE_PRODUCTS_EXPORT_CAMPAIGNS_DESC','Mit Kampagne zur Nachverfolgung verbinden.');
  define('MODULE_PRODUCTS_EXPORT_EXPORT_TITLE','<br><strong>Speicherart:</strong>');
  define('MODULE_PRODUCTS_EXPORT_EXPORT_DESC','Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');

  define('TEXT_EXPORT_YES','Nur Herunterladen');
  define('TEXT_EXPORT_NO','Am Server Speichern');


  class products_export {
    var $code, $title, $description, $enabled;


    function __construct() {
      $this->code = 'products_export';
      $this->title = MODULE_PRODUCTS_EXPORT_TEXT_TITLE;
      $this->description = MODULE_PRODUCTS_EXPORT_TEXT_DESCRIPTION;
      $this->sort_order = ((defined('MODULE_PRODUCTS_EXPORT_SORT_ORDER')) ? MODULE_PRODUCTS_EXPORT_SORT_ORDER : 0);
      $this->enabled = ((defined('MODULE_PRODUCTS_EXPORT_STATUS') && MODULE_PRODUCTS_EXPORT_STATUS == 'True') ? true : false);
    }


    function process($file) {

      xtc_set_time_limit(0);
      
      require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
      $xtPrice = new xtcPrice($_POST['currencies'], $_POST['status']);
      
      require_once (DIR_WS_CLASSES.'language.php');
      $lng = new language($_POST['language']);
      
      $export_query = xtc_db_query("SELECT p.*,
                                           pd.products_name,
                                           pd.products_description,
                                           m.manufacturers_name,
                                           p2c.categories_id,
                                           ss.shipping_status_name
                                      FROM ".TABLE_PRODUCTS." p 
                                      JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                           ON p.products_id = pd.products_id
                                              AND pd.language_id = '".$lng->language['id']."'
                                              AND trim(pd.products_name) != ''
                                      JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c
                                           ON p.products_id = p2c.products_id
                                      JOIN ".TABLE_CATEGORIES." c
                                           ON c.categories_id = p2c.categories_id
                                              AND c.categories_status = '1'
                                 LEFT JOIN ".TABLE_MANUFACTURERS." m
                                           ON p.manufacturers_id = m.manufacturers_id
                                 LEFT JOIN ".TABLE_SHIPPING_STATUS." ss
                                           ON p.products_shippingtime = ss.shipping_status_id
                                              AND ss.language_id= '".$lng->language['id']."'
                                     WHERE p.products_status = 1
                                  GROUP BY p.products_id");
      
      $i = 0;
      while ($export = xtc_db_fetch_array($export_query)) {
        $products_price = $xtPrice->xtcGetPrice($export['products_id'], false, 1, $export['products_tax_class_id']);

        $export_data_array = array(
          'id' => $export['products_id'],
          'ean' => $export['products_ean'],
          'model' => $export['products_model'],
          'brand' => $export['manufacturers_name'],
          'name' => $this->cleanText($export['products_name']),
          'description' => $this->cleanText($export['products_name']),
          'categories' => $this->buildCAT($export['categories_id'], $lng->language['id']),
          'link' => xtc_catalog_href_link('product_info.php', 'products_id='.$export['products_id'].'&language='.$lng->language['code'].((!empty($_POST['campaign'])) ? '&'.$_POST['campaign'] : ''), 'NONSSL', false),
          'image' => (($export['products_image'] != '') ? xtc_catalog_href_link(DIR_WS_CATALOG_POPUP_IMAGES . $export['products_image'], '', 'NONSSL', false) : ''),
          'shipping' => $export['shipping_status_name'],
          'price' => number_format($products_price, 2, '.', ''),
          'tax' => $xtPrice->TAX[$export['products_tax_class_id']],
          'currency' => $_POST['currencies'],
        );
          
        if ($i == 0) {
          $header = array();
          foreach ($export_data_array as $k => $v) {
            $header[] = $k;
          }
          file_put_contents(DIR_FS_CATALOG.'export/'.$file, implode(';', $header));
        }
        file_put_contents(DIR_FS_CATALOG.'export/'.$file, "\n".implode(';', $export_data_array), FILE_APPEND);
        $i ++;
      }

      switch ($_POST['export']) {
        case 'yes':
          $extension = substr($file, -3);
          $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file,"rb");
          $buffer = fread($fp, filesize(DIR_FS_DOCUMENT_ROOT.'export/' . $file));
          fclose($fp);
          header('Content-type: application/x-octet-stream');
          header('Content-disposition: attachment; filename=' . $file);
          echo $buffer;
          exit;
          break;
      }
    }


    function buildCAT($catID, $language_id) {
      if (isset($this->CAT[$catID])) {
        return $this->CAT[$catID];
      } else {
        $cat = array();
        $tmpID = $catID;

        while ($this->getParent($catID) != 0 || $catID != 0) {
          $cat_select = xtc_db_query("SELECT categories_name 
                                        FROM " . TABLE_CATEGORIES_DESCRIPTION . " 
                                       WHERE categories_id='" . $catID . "' 
                                         AND language_id='" . (int)$language_id . "'");
          $cat_data = xtc_db_fetch_array($cat_select);
          $catID = $this->getParent($catID);
          $cat[] = $cat_data['categories_name'];
        }
        $this->CAT[$tmpID] = implode(' > ', $cat);
        return $this->CAT[$tmpID];
      }
    }


    function getParent($catID) {
      if (!isset($this->PARENT[$catID])) {
        $parent_query = xtc_db_query("SELECT parent_id 
                                        FROM " . TABLE_CATEGORIES . " 
                                       WHERE categories_id='" . $catID . "'");
        $parent_data = xtc_db_fetch_array($parent_query);
        $this->PARENT[$catID] = $parent_data['parent_id'];        
      }
      
      return $this->PARENT[$catID];
    }


    function cleanText($string) {
      $string = strip_tags($string);
      $string = preg_replace ("/\s++/u", ' ', $string);
      $string = trim($string);

      return $string;
    }


    function display() {
      $customers_statuses_array = xtc_get_customers_statuses();

      $languages_array = array();
      $languages_query = xtc_db_query("SELECT *
                                         FROM ".TABLE_LANGUAGES."
                                        WHERE status = '1'
                                     ORDER BY sort_order");
      while ($languages = xtc_db_fetch_array($languages_query)) {
        $languages_array[] = array (
          'id' => $languages['code'],
          'text' => $languages['name'],
        );
      }

      $currencies_array = array();
      $currencies_query = xtc_db_query("SELECT code FROM ".TABLE_CURRENCIES);
      while ($currencies = xtc_db_fetch_array($currencies_query)) {
        $currencies_array[] = array(
          'id' => $currencies['code'], 
          'text' => $currencies['code'],
        );
      }

      $campaign_array = array(array('id' => '', 'text' => TEXT_NONE));
      $campaign_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_MODULE_EXPORT_CAMPAIGNS." 
                                    ORDER BY campaigns_id");
      while ($campaign = xtc_db_fetch_array($campaign_query)) {
        $campaign_array[] = array(
          'id' => 'refID='.$campaign['campaigns_refID'], 
          'text' => $campaign['campaigns_name'],
        );
      }

      return array('text' =>  MODULE_PRODUCTS_EXPORT_CUSTOMERS_STATUS_TITLE.'<br />'.
                              MODULE_PRODUCTS_EXPORT_CUSTOMERS_STATUS_DESC.'<br />'.
                              xtc_draw_pull_down_menu('status', $customers_statuses_array, DEFAULT_CUSTOMERS_STATUS_ID_GUEST).'<br />'.
                            
                              MODULE_PRODUCTS_EXPORT_LANGUAGE_TITLE.'<br />'.
                              MODULE_PRODUCTS_EXPORT_LANGUAGE_DESC.'<br />'.
                              xtc_draw_pull_down_menu('language', $languages_array, DEFAULT_LANGUAGE).'<br />'. 

                              MODULE_PRODUCTS_EXPORT_CURRENCY_TITLE.'<br />'.
                              MODULE_PRODUCTS_EXPORT_CURRENCY_DESC.'<br />'.
                              xtc_draw_pull_down_menu('currencies', $currencies_array, DEFAULT_CURRENCY).'<br />'. 
                            
                              MODULE_PRODUCTS_EXPORT_CAMPAIGNS_TITLE.'<br />'.
                              MODULE_PRODUCTS_EXPORT_CAMPAIGNS_DESC.'<br />'.
                              xtc_draw_pull_down_menu('campaign', $campaign_array).'<br />'. 
                                                      
                              MODULE_PRODUCTS_EXPORT_EXPORT_TITLE.'<br />'.
                              MODULE_PRODUCTS_EXPORT_EXPORT_DESC.'<br />'.
                              xtc_draw_radio_field('export', 'no', false).TEXT_EXPORT_NO.'<br />'.
                              xtc_draw_radio_field('export', 'yes', true).TEXT_EXPORT_YES.'<br />'.
                            
                              '<br />' . xtc_button(BUTTON_EXPORT) .
                              xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)));
    }


    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE configuration_key = 'MODULE_PRODUCTS_EXPORT_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }


    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PRODUCTS_EXPORT_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_PRODUCTS_EXPORT_FILE', 'products_export.csv',  '6', '1', '', now())");
    }


    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }


    function keys() {
      return array(
        'MODULE_PRODUCTS_EXPORT_STATUS',
        'MODULE_PRODUCTS_EXPORT_FILE'
      );
    }

  }
?>