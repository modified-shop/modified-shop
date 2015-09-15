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

if ( !class_exists( "image_processing_step" ) ) {
  class image_processing_step {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $current_page;
      $this->code = 'image_processing_step';
      $this->title = MODULE_STEP_IMAGE_PROCESS_TEXT_TITLE;
      $this->description = sprintf(MODULE_STEP_IMAGE_PROCESS_TEXT_DESCRIPTION, 5);
      $this->sort_order = defined('MODULE_STEP_IMAGE_PROCESS_SORT_ORDER')?MODULE_STEP_IMAGE_PROCESS_SORT_ORDER:0;
      $this->enabled = ((MODULE_STEP_IMAGE_PROCESS_STATUS == 'True') ? true : false);
      $this->module_filename = $current_page;
      $this->properties = array();
      $this->files = array();

      //define used get parameters
      $this->get_params = array('set' => $_GET['set'],
                                'module'=> $this->code,
                                'start'=> 0,
                                'action' => 'module_processing_do'
                                );
      //define used post parameters
      $this->post_params = array('max_datasets','only_missing_images', 'lower_file_ext');

      if (isset($_GET['count'])) {
        $this->ready_text =  '<div class="ready_info">'. IMAGE_STEP_INFO . $_GET['count']. IMAGE_STEP_INFO_READY .'</div>';
        $this->ready_text .= '<div class="process_inner_wrapper"><div id="show_import_process" style="width:100%;"></div></div>';
      }
    }
    
    function get_images_files($offset,$limit) {
      $ext_array = array('gif','jpg','jpeg','png'); //Gültige Dateiendungen
      $files = array();
      $this->data_volume = 0;
      if ($dir = opendir(DIR_FS_CATALOG_ORIGINAL_IMAGES)) {
        $max_files = 0;
        while  ($file = readdir($dir)) {
          $tmp = explode('.',$file);
          if(is_array($tmp)) {
            $ext = strtolower($tmp[count($tmp)-1]);
            if (is_file(DIR_FS_CATALOG_ORIGINAL_IMAGES.$file) && in_array($ext,$ext_array) ){
              if ($max_files >= $offset && $max_files < $limit) {
                $files[$max_files]= array(
                                 'id' => $file,
                                 'text' =>$file);
              }
              $this->data_volume += filesize(DIR_FS_CATALOG_ORIGINAL_IMAGES.$file);
              $max_files ++;
            }
          }
        }
        closedir($dir);
      } 
      $this->max_files = $max_files;
      $this->files = $files;
    }

    function process($file) {

      include ('includes/classes/'.FILENAME_IMAGEMANIPULATOR);

      $offset = (int)$_GET['start'];
      $step = (int)$_GET['max_datasets'];
      $count = isset($_GET['count']) ? (int)$_GET['count'] : 0;
      $limit = $offset + $step;
      
      @ini_set('memory_limit','256M');
      @xtc_set_time_limit(0);
      
      $this->get_images_files($offset,$limit);
      $files = $this->files;

      $ext_search = array('.GIF','.JPG','.JPEG','.PNG');
      $ext_replace = array('.gif','.jpg','.jpeg','.png');

      for ($i=$offset; $i<$limit; $i++) {
        if ($i >= $this->max_files) { // FERTIG
          $get_params =
            'set='. $this->get_params['set'].
            '&action=ready'.
            '&module='. $this->code.
            '&count='. $count.
            '&max_datasets='. (int)$_GET['max_datasets'].
            '&only_missing_images='. (int)$_GET['only_missing_images'].
            '&lower_file_ext='. (int)$_GET['lower_file_ext'];
            
          xtc_redirect(xtc_href_link($this->module_filename, $get_params)); //FERTIG
        }
        $products_image_name = $files[$i]['text'];
        $products_image_name_process = ($_GET['lower_file_ext'] == 1) ? str_replace($ext_search, $ext_replace ,$files[$i]['text']) : $files[$i]['text'];

        if ($_GET['only_missing_images'] == 1) {
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
      $this->get_params['start'] = $limit;
      $this->get_params['count'] = $count;
      reset($this->post_params);
      while(list($key, $pparam) = each($this->post_params)) {
        $this->get_params[$pparam] = $_GET[$pparam];
      }
      //Animierte Gif-Datei und Hinweistext
      $info_wait = '<img src="images/loading.gif"> ';
      $this->infotext = sprintf(MODULE_STEP_READY_STYLE_TEXT,$info_wait . IMAGE_STEP_INFO . (int)$count . ' / ' .(int)$this->max_files);
      $percent = round($count *100/$this->max_files,1);
      $this->infotext .= '<div class="process_inner_wrapper"><div id="show_import_process" style="width:'. $percent .'%;"></div></div>';
      $this->recursive_call = '<script language="javascript" type="text/javascript">setTimeout("document.modul_continue.submit()", 3000);</script>';
    }

    function display() {

      //Array für max. Bilder pro Seitenreload
      $max_array = array (array ('id' => '5', 'text' => '5'));
      $max_array[] = array ('id' => '10', 'text' => '10');
      $max_array[] = array ('id' => '15', 'text' => '15');
      $max_array[] = array ('id' => '20', 'text' => '20');
      $max_array[] = array ('id' => '50', 'text' => '50');
      
      $this->get_images_files(1,1);

      return array('text' => xtc_draw_hidden_field('process','module_processing_do').
                             xtc_draw_hidden_field('max_images1','5').
                             IMAGE_EXPORT_TYPE.'<br />'.
                             IMAGE_EXPORT.'<br />'.
                             '<br />' . sprintf(IMAGE_COUNT_INFO, basename(DIR_FS_CATALOG_ORIGINAL_IMAGES), $this->max_files) . '['.$this->formatBytes($this->data_volume).']'.'<br />'.
                             '<br />' . xtc_draw_pull_down_menu('max_datasets', $max_array, isset($_GET['max_datasets']) ? (int)$_GET['max_datasets'] : '5'). ' ' . TEXT_MAX_IMAGES. '<br />'.
                             '<br />' . xtc_draw_checkbox_field('only_missing_images', '1', isset($_GET['only_missing_images']) ? (int)$_GET['only_missing_images'] : false) . ' ' . TEXT_ONLY_MISSING_IMAGES. '<br />'.
                             '<br />' . xtc_draw_checkbox_field('lower_file_ext', '1', isset($_GET['lower_file_ext']) ? (int)$_GET['lower_file_ext'] : false) . ' ' . TEXT_LOWER_FILE_EXT. '<br />'.
                             '<br />' . xtc_button(BUTTON_START). '&nbsp;' .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module='.$this->code))
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
    
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
  }
}
?>