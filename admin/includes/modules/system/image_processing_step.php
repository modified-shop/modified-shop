<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

   new jquery image_processing / only missing image/ max images  by web28 2015-12-01
   fix only_missing_images/ support for logfile/ display image name  by web28 2016-02-20

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
      $this->sort_order = defined('MODULE_STEP_IMAGE_PROCESS_SORT_ORDER') ? MODULE_STEP_IMAGE_PROCESS_SORT_ORDER:'';
      $this->enabled = ((defined('MODULE_STEP_IMAGE_PROCESS_STATUS') && MODULE_STEP_IMAGE_PROCESS_STATUS == 'True') ? true : false);
      $this->module_filename = $current_page;
      $this->properties = array();
      $this->files = array();
      

      $this->logfile = DIR_FS_CATALOG.'log/image_processing_*.log';

      $this->images_type_array = array(
        '',
        '_list',
        '_mobile'
      );

      //define used get parameters
      $this->get_params = array();
      //define used post parameters
      $this->post_params = array();

      $this->properties['form_edit'] = xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=custom','post', 'id="form_image_processing"');
    }
    
    function get_images_files($filedir,$offset=1,$limit=1) {
      $this->data_volume = 0;
      $this->max_files = 0;
      
      $ext_array = array('gif','jpg','jpeg','png'); //G�ltige Dateiendungen
      $files = array();
      if ($dir = opendir($filedir)) {
        $max_files = 0;
        while  ($file = readdir($dir)) {
          $tmp = explode('.',$file);
          if(is_array($tmp)) {
            $ext = strtolower($tmp[count($tmp)-1]);
            if (is_file($filedir.$file) && in_array($ext,$ext_array) ){
              if ($max_files >= $offset && $max_files < $limit) {
                $files[$max_files]= array(
                                 'id' => $file,
                                 'text' =>$file);
              }
              $this->data_volume += filesize($filedir.$file);
              $max_files ++;
            }
          }
        }
        closedir($dir);
      }
      
      $this->max_files = $max_files;
      $this->files = $files;
    }

    function image_processing_do() {
      
      include ('includes/classes/'.FILENAME_IMAGEMANIPULATOR);

      $offset = (int)$_POST['start'];
      $step = (int)$_POST['max_datasets'];
      $count = isset($_POST['count']) ? (int)$_POST['count'] : 0;
      $limit = $offset + $step;
 
      $rData = array();
      
      $rData['file_time'] = isset($_POST['file_time']) ? $_POST['file_time'] : date("Y-m-d-His");
      
      $this->logfile = str_replace('*',$rData['file_time'],$this->logfile);
      
      ini_set('memory_limit','256M');
      xtc_set_time_limit(0);

      switch ($_POST['process_type']) {
        case 'products':
          $this->get_images_files(DIR_FS_CATALOG_IMAGES.'product_images/original_images/',$offset,$limit);
          break;
        case 'categories':
          $this->get_images_files(DIR_FS_CATALOG_IMAGES.'categories/original_images/',$offset,$limit);
          break;
        case 'manufacturers':
          $this->get_images_files(DIR_FS_CATALOG_IMAGES.'manufacturers/original_images/',$offset,$limit);
          break;
        case 'banners':
          $this->get_images_files(DIR_FS_CATALOG_IMAGES.'banner/original_images/',$offset,$limit);
          break;
      }

      $files = $this->files;

      $ext_search = array('.GIF','.JPG','.JPEG','.PNG');
      $ext_replace = array('.gif','.jpg','.jpeg','.png');
      
      for ($i = $offset; $i < $limit; $i++) {
        if ($i >= $this->max_files) {
          $rData['start'] = $limit;
          $rData['count'] = $count;
          return $rData; // step is done
        }
        $image_name = $files[$i]['text'];
        $image_name_process = (isset($_GET['lower_file_ext']) && $_GET['lower_file_ext'] == 1) ? str_replace($ext_search, $ext_replace , $image_name) : $image_name;

        $rData['imgname'] = encode_htmlentities($image_name_process);

        if (isset($_POST['logging']) && $_POST['logging'] == 1) {
          $handle = fopen($this->logfile, "a"); fwrite($handle, $image_name. '|read'."\n"); fclose($handle);
        }

        if (isset($_POST['only_missing_images']) && $_POST['only_missing_images'] == 1) {
          $flag = false;
          switch ($_POST['process_type']) {
            case 'products':
              if (isset($_POST['products'])) {
                foreach ($_POST['products'] as $images_type => $val) {
                  if (!is_file(DIR_FS_CATALOG_IMAGES.'product_images/'.$images_type.'/'.$image_name_process)) {
                    $products_image_name = $image_name;
                    $products_image_name_process = $image_name_process;
                    require(DIR_WS_INCLUDES.'product_'.$images_type.'.php'); 
                    $flag = true;
                  }
                }
              }
              break;

            case 'categories':
              if (isset($_POST['categories_image'])) {
                $image_process_type = $this->get_image_process_type($image_name);
                foreach ($_POST['categories_image'] as $images_type => $val) {
                  if ($images_type == $image_process_type
                      && !is_file(DIR_FS_CATALOG_IMAGES.'categories/'.$image_name_process)
                      )
                  {
                    $categories_image_name = $image_name;
                    $categories_image_name_process = $image_name_process;
                    require(DIR_WS_INCLUDES.'categories_image'.(($image_process_type != '_default') ? $image_process_type : '').'.php');
                    $flag = true;
                  }
                }
              }
              break;

            case 'manufacturers':
              if (isset($_POST['manufacturers'])
                  && !is_file(DIR_FS_CATALOG_IMAGES.'manufacturers/'.$image_name_process)
                  )
              {
                $manufacturers_image_name = $image_name;
                $manufacturers_image_name_process = $image_name_process;
                require(DIR_WS_INCLUDES . 'manufacturers_image.php'); 
              }
              break;

            case 'banners':
              if (isset($_POST['banners_image'])) {
                $image_process_type = $this->get_image_process_type($image_name);
                foreach ($_POST['banners_image'] as $images_type => $val) {
                  if ($images_type == $image_process_type
                      && !is_file(DIR_FS_CATALOG_IMAGES.'banner/'.$image_name_process)
                      )
                  {
                    $banners_image_name = $image_name;
                    $banners_image_name_process = $image_name_process;
                    require(DIR_WS_INCLUDES.'banners_image'.(($image_process_type != '_default') ? $image_process_type : '').'.php');
                    $flag = true;
                  }
                }
              }
              break;
          }
          if ($flag) {
            $count += 1;
            if (isset($_POST['logging']) && $_POST['logging'] == 1) {
              $handle = fopen($this->logfile, "a"); fwrite($handle, $rData['imgname'].'|process'."\n"); fclose($handle);
            }  
          }
        } else {
          switch ($_POST['process_type']) {
            case 'products':
              if (isset($_POST['products'])) {
                foreach ($_POST['products'] as $images_type => $val) {
                  $products_image_name = $image_name;
                  $products_image_name_process = $image_name_process;
                  require(DIR_WS_INCLUDES . 'product_'.$images_type.'.php'); 
                }
              }
              break;

            case 'categories':
              if (isset($_POST['categories_image'])) {
                $image_process_type = $this->get_image_process_type($image_name);
                foreach ($_POST['categories_image'] as $images_type => $val) {
                  if ($images_type == $image_process_type) {
                    $categories_image_name = $image_name;
                    $categories_image_name_process = $image_name_process;
                    require(DIR_WS_INCLUDES.'categories_image'.(($image_process_type != '_default') ? $image_process_type : '').'.php');
                  }
                }
              }
              break;

            case 'manufacturers':
              if (isset($_POST['manufacturers'])) {
                $manufacturers_image_name = $image_name;
                $manufacturers_image_name_process = $image_name_process;
                require(DIR_WS_INCLUDES . 'manufacturers_image.php'); 
              }
              break;
            
            case 'banners':
              if (isset($_POST['banners_image'])) {
                $image_process_type = $this->get_image_process_type($image_name);
                foreach ($_POST['banners_image'] as $images_type => $val) {
                  if ($images_type == $image_process_type) {
                    $banners_image_name = $image_name;
                    $banners_image_name_process = $image_name_process;
                    require(DIR_WS_INCLUDES.'banners_image'.(($image_process_type != '_default') ? $image_process_type : '').'.php');
                  }
                }
              }
              break;
          }
          $count += 1;
          if (isset($_POST['logging']) && $_POST['logging'] == 1) {
            $handle = fopen($this->logfile, "a"); fwrite($handle, $rData['imgname'].'|process'."\n"); fclose($handle);
          }
        }
      }

      $rData['start'] = $limit;
      $rData['count'] = $count;
      return $rData;
    }
    
    function get_image_process_type($image_name) {
      foreach ($this->images_type_array as $type) {
        if ($type != '' && strpos($image_name, $type.'.') !== false) {
          return $type;
        }
      }
      return '_default';
    }
    
    function custom() {
      $rData = $this->image_processing_do();
      $json = array_merge($_POST,$rData);
      echo json_encode($json);
      exit();
    }

    function process($file) {
      //do nothing
    }

    function display() {

      $max_array = array (array ('id' => '1', 'text' => '1'));
      $max_array[] = array ('id' => '5', 'text' => '5');
      $max_array[] = array ('id' => '10', 'text' => '10');
      $max_array[] = array ('id' => '15', 'text' => '15');
      $max_array[] = array ('id' => '20', 'text' => '20');
      $max_array[] = array ('id' => '50', 'text' => '50');
      
      $process_type_array = array();
      $process_type_array[] = array ('id' => 'products', 'text' => TEXT_PRODUCTS);
      $process_type_array[] = array ('id' => 'categories', 'text' => TEXT_CATEGORIES);
      $process_type_array[] = array ('id' => 'manufacturers', 'text' => TEXT_MANUFACTURERS);
      $process_type_array[] = array ('id' => 'banners', 'text' => TEXT_BANNERS);

      $this->get_images_files(DIR_FS_CATALOG_IMAGES.'product_images/original_images/', 1, 1);
      $max_files_products = $this->max_files;
      $data_volume_products = $this->data_volume;

      $this->get_images_files(DIR_FS_CATALOG_IMAGES.'categories/original_images/', 1, 1);
      $max_files_categories = $this->max_files;
      $data_volume_categories = $this->data_volume;

      $this->get_images_files(DIR_FS_CATALOG_IMAGES.'manufacturers/original_images/', 1, 1);
      $max_files_manufacturers = $this->max_files;
      $data_volume_manufacturers = $this->data_volume;

      $this->get_images_files(DIR_FS_CATALOG_IMAGES.'banner/original_images/', 1, 1);
      $max_files_banners = $this->max_files;
      $data_volume_banners = $this->data_volume;
      
      require (DIR_WS_INCLUDES.'javascript/jquery.image_processing.js.php');
      
      $ajax_img = '<img src="images/loading.gif" class="ajax_loading"> ';

      return array('text' => xtc_draw_hidden_field('process','module_processing_do').
                             xtc_draw_hidden_field('ajax_url',xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module='.$this->code). '&action=custom').
                             xtc_draw_hidden_field('ajax','1').
                             xtc_draw_hidden_field('start','0').
                             '<input id="total" type="hidden" name="total" value="'.(int)$max_files_products.'"/>'.
                             IMAGE_EXPORT_TYPE.'<br />'.
                             IMAGE_EXPORT.'<br />'.
                             
                             '<br />'.TEXT_MAX_IMAGES.
                             '<br />' . xtc_draw_pull_down_menu('max_datasets', $max_array, '5') . '<br />'.

                             '<br />'.TEXT_PROCESS_TYPE.
                             '<br />' . xtc_draw_pull_down_menu('process_type', $process_type_array, 'products', 'id="process_type"') . '<br />'.

                             '<div id="products">' . 
                               '<br />' . sprintf(IMAGE_COUNT_INFO, 'original_images', '<span id="products_total">'.$max_files_products.'</span>') . '['.$this->formatBytes($data_volume_products).']'.'<br />'.
                               '<br />' . xtc_draw_checkbox_field('products[mini_images]', '1', false, '', 'class="mini_images"') . ' ' . TEXT_PRODUCTS_MINI_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('products[thumbnail_images]', '1', false, '', 'class="thumbnail_images"') . ' ' . TEXT_PRODUCTS_THUMBNAIL_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('products[midi_images]', '1', false, '', 'class="midi_images"') . ' ' . TEXT_PRODUCTS_MIDI_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('products[info_images]', '1', false, '', 'class="info_images"') . ' ' . TEXT_PRODUCTS_INFO_IMAGES. 
                               '<br />' . xtc_draw_checkbox_field('products[popup_images]', '1', false, '', 'class="popup_images"') . ' ' . TEXT_PRODUCTS_POPUP_IMAGES. 
                             '</div>' .

                             '<div id="categories" style="display:none;">' . 
                               '<br />' . sprintf(IMAGE_COUNT_INFO, 'original_images', '<span id="categories_total">'.$max_files_categories.'</span>') . '['.$this->formatBytes($data_volume_categories).']'.'<br />'.
                               '<br />' . xtc_draw_checkbox_field('categories_image[_default]', '1', false) . ' ' . TEXT_CATEGORIES_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('categories_image[_list]', '1', false) . ' ' . TEXT_CATEGORIES_LIST_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('categories_image[_mobile]', '1', false) . ' ' . TEXT_CATEGORIES_MOBILE_IMAGES.
                             '</div>' .

                             '<div id="manufacturers" style="display:none;">' . 
                               '<br />' . sprintf(IMAGE_COUNT_INFO, 'original_images', '<span id="manufacturers_total">'.$max_files_manufacturers.'</span>') . '['.$this->formatBytes($data_volume_manufacturers).']'.'<br />'.
                               '<br />' . xtc_draw_checkbox_field('manufacturers', '1', false) . ' ' . TEXT_MANUFACTURERS_IMAGES.
                             '</div>' .

                             '<div id="banners" style="display:none;">' . 
                               '<br />' . sprintf(IMAGE_COUNT_INFO, 'original_images', '<span id="banners_total">'.$max_files_banners.'</span>') . '['.$this->formatBytes($data_volume_banners).']'.'<br />'.
                               '<br />' . xtc_draw_checkbox_field('banners_image[_default]', '1', false) . ' ' . TEXT_BANNERS_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('banners_image[_mobile]', '1', false) . ' ' . TEXT_BANNERS_MOBILE_IMAGES.
                             '</div>' .

                             '<br />'.TEXT_SETTINGS.
                             '<br />' . xtc_draw_checkbox_field('only_missing_images', '1', false, '', 'class="only_missing_images"') . ' ' . TEXT_ONLY_MISSING_IMAGES.
                             '<br />' . xtc_draw_checkbox_field('lower_file_ext', '1', false, '', 'class="lower_file_ext"') . ' ' . TEXT_LOWER_FILE_EXT. '<br />'.
                             
                             '<br />'.TEXT_LOGGING.
                             '<br />' . xtc_draw_checkbox_field('logging', '1', false, '', 'class="logfile"') . ' ' . TEXT_LOGFILE. '<br />'.
                             '<br />' . xtc_button(BUTTON_START). '&nbsp;' .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module='.$this->code)) .
                             
                             '<div class="ajax_responce" style="margin-bottom:15px;"><hr>'.
                             '<div class="ajax_imgname"></div>'.
                               sprintf(MODULE_STEP_READY_STYLE_TEXT,$ajax_img . IMAGE_STEP_INFO . '<span class="ajax_count"></span> / <span id="ajax_total">' .(int)$max_files_products . '</span><span class="ajax_ready_info">' . IMAGE_STEP_INFO_READY .'<span>') . 
                               '<div class="process_wrapper">
                                <div class="process_inner_wrapper">
                                  <div id="show_image_process" style="width:'. 0 .'%;"></div>
                                </div>
                               </div>
                               <div class="ajax_btn_back">'.sprintf(MODULE_STEP_READY_STYLE_BACK,xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))).'</div>
                             </div>
                             <script>
                              $("#process_type").on("change", function() {
                                var selector = $(this).val();
                                var total = parseInt($("#"+selector+"_total").text());
                                                        
                                $("#products, #categories, #manufacturers, #banners, .ajax_responce").hide();
                                $("#"+selector).show();
                                $("#total").val(total);
                                $("#ajax_total").text(total);
                              });
                             </script>
                             '
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