<?php
/* -----------------------------------------------------------------------------------------
   $Id: image_processing_step.php 2992 2012-06-07 16:59:49Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003	 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (image_processing_step.php 950 2005-05-14; www.xt-commerce.com
   --------------------------------------------------------------
   Contribution
   image_processing_step (step-by-step Variante B) by INSEH 2008-03-26

   new javascript reload / only missing image/ max images  by web28 2011-03-17

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_STEP_IMAGE_PROCESS_TEXT_DESCRIPTION', 'Es werden alle Bilder in den Verzeichnissen<br /><br />

/images/product_images/popup_images/<br />

/images/product_images/info_images/<br />

/images/product_images/thumbnail_images/ <br /> <br /> neu erstellt.<br /> <br />

Hierzu verarbeitet das Script nur eine begrenzte Anzahl von %s Bildern und ruft sich danach selbst wieder auf.<br /> <br />');
define('MODULE_STEP_IMAGE_PROCESS_TEXT_TITLE', 'Imageprocessing-New <b>-V2.01- Produktbilder</b>');
define('MODULE_STEP_IMAGE_PROCESS_STATUS_DESC','Modulstatus');
define('MODULE_STEP_IMAGE_PROCESS_STATUS_TITLE','Status');
define('IMAGE_EXPORT','Dr&uuml;cken Sie Ok um die Stapelverarbeitung zu starten, dieser Vorgang kann einige Zeit dauern, auf keinen Fall unterbrechen!.');
define('IMAGE_EXPORT_TYPE','<hr noshade><strong>Stapelverarbeitung:</strong>');

define('IMAGE_STEP_INFO','Bilder erstellt: ');
define('IMAGE_STEP_INFO_READY',' - Fertig!');
define('TEXT_MAX_IMAGES','max. Bilder pro Seitenreload');
define('TEXT_ONLY_MISSING_IMAGES','Nur fehlende Bilder erstellen');

if ( !class_exists( "image_processing_step" ) ) {
  class image_processing_step {
    var $code, $title, $description, $enabled;


    function image_processing_step() {
      global $order;

      $this->code = 'image_processing_step';
      $this->title = MODULE_STEP_IMAGE_PROCESS_TEXT_TITLE;
      $this->description = sprintf(MODULE_STEP_IMAGE_PROCESS_TEXT_DESCRIPTION, $_GET['max']);
      $this->sort_order = MODULE_STEP_IMAGE_PROCESS_SORT_ORDER;
      $this->enabled = ((MODULE_STEP_IMAGE_PROCESS_STATUS == 'True') ? true : false);

    }


    function process($file, $offset) {
      global $limit, $selbstaufruf, $infotext, $count;

      include ('includes/classes/'.FILENAME_IMAGEMANIPULATOR);

      $ext_array = array('gif','jpg','jpeg','png'); //Gültige Dateiendungen

      @xtc_set_time_limit(0);
      $files=array();
      if ($dir= opendir(DIR_FS_CATALOG_ORIGINAL_IMAGES)){
        while  ($file = readdir($dir)) {
          $tmp = explode('.',$file);
          if(is_array($tmp)) {
            $ext = strtolower($tmp[count($tmp)-1]);
            if (is_file(DIR_FS_CATALOG_ORIGINAL_IMAGES.$file) && in_array($ext,$ext_array) ){
              $files[]=array('id' => $file,
                             'text' =>$file);
            }
          }
        }
        closedir($dir);
      }

      $max_files = sizeof($files);
      $step = $_GET['max'];
      $count = $_GET['count'];
      $limit = $offset + $step;
      for ($i=$offset; $i<$limit; $i++) {
        if ($i >= $max_files) { // FERTIG
          $infotext = urlencode(strip_tags(IMAGE_STEP_INFO) . $count. strip_tags(IMAGE_STEP_INFO_READY)) ;
          xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code.'&infotext='.$infotext. '&max='. $_GET['max'])); //FERTIG
        }
        $products_image_name = $files[$i]['text'];

        if ($_GET['miss'] == 1) {
          $flag = false;
          if (!is_file(DIR_FS_CATALOG_THUMBNAIL_IMAGES.$files[$i]['text'])) {
            require(DIR_WS_INCLUDES . 'product_thumbnail_images.php'); $flag = true;
          }
          if (!is_file(DIR_FS_CATALOG_INFO_IMAGES.$files[$i]['text'])) {
            require(DIR_WS_INCLUDES . 'product_info_images.php'); $flag = true;
          }
          if (!is_file(DIR_FS_CATALOG_POPUP_IMAGES.$files[$i]['text'])) {
            require(DIR_WS_INCLUDES . 'product_popup_images.php'); $flag = true;
          }
          if ($flag) {
            $count += 1;
          }
        } else {
          require(DIR_WS_INCLUDES . 'product_thumbnail_images.php');
          require(DIR_WS_INCLUDES . 'product_info_images.php');
          require(DIR_WS_INCLUDES . 'product_popup_images.php');
          $count += 1;
        }
      }
      //xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&action=save&module=image_processing_new_step&start=' . $limit));
      //Animierte Gif-Datei und Hinweistext
      $info_wait = '<img src="images/loading.gif"> ';
      $infotext = '<div style="margin:10px; font-family:Verdana; font-size:15px; text-align:center;">'.$info_wait . IMAGE_STEP_INFO . $count.'</div>';
      $selbstaufruf = '<script language="javascript" type="text/javascript">setTimeout("document.modul_continue.submit()", 3000);</script>';
    }

    function display() {

      //Array für max. Bilder pro Seitenreload
      $max_array = array (array ('id' => '5', 'text' => '5'));
      $max_array[] = array ('id' => '10', 'text' => '10');
      $max_array[] = array ('id' => '15', 'text' => '15');
      $max_array[] = array ('id' => '20', 'text' => '20');
      $max_array[] = array ('id' => '50', 'text' => '50');

      return array('text' => xtc_draw_hidden_field('process','module_processing_do').
                             xtc_draw_hidden_field('max_images1','5').
                             IMAGE_EXPORT_TYPE.'<br />'.
                             IMAGE_EXPORT.'<br />'.
                             '<br />' . xtc_draw_pull_down_menu('max_datasets', $max_array, '5'). ' ' . TEXT_MAX_IMAGES. '<br />'.
                             '<br />' . xtc_draw_checkbox_field('only_missing_images', '1', false) . ' ' . TEXT_ONLY_MISSING_IMAGES. '<br />'.
                             '<br />' . xtc_button(BUTTON_START). '&nbsp;' .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=image_processing_step'))
                   );

    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_STEP_IMAGE_PROCESS_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_STEP_IMAGE_PROCESS_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_STEP_IMAGE_PROCESS_STATUS');
    }
  }
}
?>