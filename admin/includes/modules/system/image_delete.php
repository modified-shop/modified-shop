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

  class image_delete {

    var $code;
    var $title;
    var $description;
    var $sort_order;
    var $enabled;
    var $properties;
    var $_check;

    var $files;
    var $logfile;
    var $module_filename;
    var $get_params;
    var $post_params;
    var $max_files;

    function __construct() {
      global $current_page;
      
      $this->code = 'image_delete';
      $this->title = MODULE_IMAGE_DELETE_TEXT_TITLE;
      $this->description = sprintf(MODULE_IMAGE_DELETE_TEXT_DESCRIPTION, 20);
      $this->sort_order = defined('MODULE_IMAGE_DELETE_SORT_ORDER') ? MODULE_IMAGE_DELETE_SORT_ORDER : '';
      $this->enabled = ((defined('MODULE_IMAGE_DELETE_STATUS') && MODULE_IMAGE_DELETE_STATUS == 'True') ? true : false);
      
      $this->module_filename = $current_page;
      $this->logfile = DIR_FS_CATALOG.'log/mod_image_delete_'.date('Y-m-d').'.log';
      
      $this->properties = array();
      $this->files = array();
      $this->get_params = array();
      $this->post_params = array();

      $this->properties['form_edit'] = xtc_draw_form('modules', FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=custom', 'post', 'id="form_image_processing"');
    }
    
    function get_images_files($filedir, $offset = 1, $limit = 1) {      
      $ext_array = array('gif','jpg','jpeg','png');
      if (defined('IMAGE_TYPE_EXTENSION') 
          && IMAGE_TYPE_EXTENSION != 'default'
          )
      {
        $ext_array[] = IMAGE_TYPE_EXTENSION;
      }
      
      $max_files = 0;
      $files = array();
      if ($dir = opendir($filedir)) {
        while  ($file = readdir($dir)) {
          $tmp = explode('.',$file);
          if (is_array($tmp)) {
            $ext = strtolower($tmp[count($tmp)-1]);
            if (is_file($filedir.$file) && in_array($ext, $ext_array)) {
              if ($max_files >= $offset && $max_files < $limit) {
                $files[$max_files] = $file;
              }
              $max_files ++;
            }
          }
        }
        closedir($dir);
      }
      
      if ($offset == 1 && $limit == 1) {
        $this->max_files = $max_files;
      }
      $this->files = $files;
    }

    function get_all_valid_images($type) {
      static $pics_array;
      
      if (!isset($pics_array)) {
        $pics_array = array();
      
        switch ($type) {
          case 'product_images':
            $pics_query = xtc_db_query("SELECT products_image 
                                          FROM ".TABLE_PRODUCTS);
            while ($pics = xtc_db_fetch_array($pics_query)) {
              if ($pics['products_image'] != '' || $pics['products_image'] != NULL) {
                $pics_array[] = $pics['products_image'];

                if (defined('IMAGE_TYPE_EXTENSION') 
                    && IMAGE_TYPE_EXTENSION != 'default'
                    )
                {
                  $pics_array[] = substr($pics['products_image'], 0, strrpos($pics['products_image'], '.')).'.'.IMAGE_TYPE_EXTENSION;
                }
              }
            }
          
            $pics_query = xtc_db_query("SELECT image_name 
                                          FROM ".TABLE_PRODUCTS_IMAGES);
            while ($pics = xtc_db_fetch_array($pics_query)) {
              if ($pics['image_name'] != '' || $pics['image_name'] != NULL) {
                $pics_array[] = $pics['image_name'];

                if (defined('IMAGE_TYPE_EXTENSION') 
                    && IMAGE_TYPE_EXTENSION != 'default'
                    )
                {
                  $pics_array[] = substr($pics['image_name'], 0, strrpos($pics['image_name'], '.')).'.'.IMAGE_TYPE_EXTENSION;
                }
              }
            }
            break;
          
          case 'categories':
            $pics_query = xtc_db_query("SELECT categories_image,
                                               categories_image_mobile,
                                               categories_image_list 
                                          FROM ".TABLE_CATEGORIES);
            while ($pics = xtc_db_fetch_array($pics_query)) {
              foreach ($pics as $key => $val) {
                if ($pics[$key] != '' || $pics[$key] != NULL) {
                  $pics_array[] = $val;
                }             
              }              
            }
            break;

          case 'manufacturers':
            $pics_query = xtc_db_query("SELECT manufacturers_image
                                          FROM ".TABLE_MANUFACTURERS);
            while ($pics = xtc_db_fetch_array($pics_query)) {
              foreach ($pics as $key => $val) {
                if ($pics[$key] != '' || $pics[$key] != NULL) {
                  $pics_array[] = $val;
                }             
              }              
            }
            break;

          case 'banner':
            $pics_query = xtc_db_query("SELECT banners_image,
                                               banners_image_mobile
                                          FROM ".TABLE_BANNERS);
            while ($pics = xtc_db_fetch_array($pics_query)) {
              foreach ($pics as $key => $val) {
                if ($pics[$key] != '' || $pics[$key] != NULL) {
                  $pics_array[] = $val;
                }             
              }              
            }
            break;
        }
      }
      
      return $pics_array;
    }
        
    function getMemoryLimitBytes() {
      $limit = ini_get('memory_limit');
      if ($limit == -1) return 0;
    
      $units = array(1 => 'K', 'M', 'G');
      $unit = strtoupper(substr($limit, -1));
      if ($exp = array_search($unit, $units)) {
        return (int)substr($limit, 0, -1) * pow(1024, $exp);
      } else {
        return (int)$limit;
      }
    }

    function image_delete_do() {
      $offset = (int)$_POST['start'];
      $step = (int)$_POST['max_datasets'];
      $count = isset($_POST['count']) ? (int)$_POST['count'] : 0;
      $limit = $offset + $step;      
      
      // set memory limit
      $memory_limit = $this->getMemoryLimitBytes();
      if ($memory_limit > 0
          && $memory_limit < (256 * 1024 * 1024)
          )
      {
        ini_set('memory_limit', '256M');
      }
      
      // set timeout
      xtc_set_time_limit(0);

      $rData = array();    
      $rData['valid_files'] = isset($_POST['valid_files']) ? json_decode(base64_decode($_POST['valid_files']), true) : $this->get_all_valid_images($_POST['process_type']);
      $rData['remove_files'] = isset($_POST['remove_files']) ? json_decode(base64_decode($_POST['remove_files']), true) : array();
            
      if (isset($_POST['image_path'])) {
        $this->path = $_POST['path'];
        $this->image_path = $_POST['image_path'];
      } else {
        $this->get_image_path($_POST);
        unset($_POST[$_POST['process_type']][$this->path]);
      }
      
      $rData['path'] = $this->path;
      $rData['image_path'] = $this->image_path;

      if (isset($_POST['total'])) {
        $this->max_files = (int)$_POST['total'];   
      } else {
        $this->get_images_files($this->image_path);
      }
      
      $rData['total'] = $this->max_files;
      
      $this->get_images_files($this->image_path, $offset, $limit);
      
      for ($i = $offset; $i < $limit; $i++) {
        if ($i >= $this->max_files) {
          $rData['start'] = $limit;
          $rData['count'] = $count;
          return $rData;
        }
        
        if (!in_array($this->files[$i], $rData['valid_files'])) {
          if (isset($_POST['logging']) && $_POST['logging'] == 1) {
            error_log("[".date('Y-m-d H:i:s')."]\t/images/".$_POST['process_type'].'/'.(($this->path != 'default') ? $this->path.'/' : '').$this->files[$i]."\n", 3, $this->logfile);
          }
          $rData['remove_files'][] = $this->image_path.$this->files[$i];
        }
        
        $count ++;
      }

      $rData['start'] = $limit;
      $rData['count'] = $count;
      
      return $rData;
    }
    
    function get_image_path($data) {    
      $this->path = array_key_first($data[$data['process_type']]);
      $this->image_path = DIR_FS_CATALOG_IMAGES.$data['process_type'].'/'.(($this->path != 'default') ? $this->path.'/' : '');
    }
    
    function delete_images($remove_files_array) {
      if (is_array($remove_files_array)
          && count($remove_files_array) > 0
          )
      {
        foreach ($remove_files_array as $remove_file) {
          if (is_file($remove_file)) {
            unlink($remove_file);
          }
        }
      }
    }
        
    function custom() {
      $rData = $this->image_delete_do();      
      $json = array_merge($_POST,$rData);

      if ($json['start'] >= $json['total']) {
        $this->delete_images($rData['remove_files']);
        
        $key = $json['process_type'];
        if (isset($json[$key])
            && is_array($json[$key])
            && count($json[$key]) > 0
            )
        {          
          $this->get_image_path($json);
          $this->get_images_files($this->image_path);
          
          $json['path'] = $this->path;
          $json['image_path'] = $this->image_path;
          $json['total'] = $this->max_files;

          unset($json[$json['process_type']][$this->path]);
          
          $json['start'] = 0;
          $json['count'] = 0;
        }
      }

      $json['valid_files'] = base64_encode(json_encode($json['valid_files']));
      $json['remove_files'] = base64_encode(json_encode($json['remove_files']));
            
      echo json_encode($json);
      exit();
    }

    function process($file) {
      //do nothing
    }

    function display() {
      $max_array = array();
      $max_array[] = array('id' => '10', 'text' => '10');
      $max_array[] = array('id' => '20', 'text' => '20');
      $max_array[] = array('id' => '50', 'text' => '50');
      $max_array[] = array('id' => '100', 'text' => '100');
      
      $process_type_array = array();
      $process_type_array[] = array ('id' => 'product_images', 'text' => MODULE_IMAGE_DELETE_TEXT_PRODUCTS);
      $process_type_array[] = array ('id' => 'categories', 'text' => MODULE_IMAGE_DELETE_TEXT_CATEGORIES);
      $process_type_array[] = array ('id' => 'manufacturers', 'text' => MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS);
      $process_type_array[] = array ('id' => 'banner', 'text' => MODULE_IMAGE_DELETE_TEXT_BANNER);
      
      require (DIR_WS_INCLUDES.'javascript/jquery.image_processing.js.php');
      
      $ajax_img = '<img src="images/loading.gif" class="ajax_loading">';

      return array('text' => xtc_draw_hidden_field('process','module_processing_do').
                             xtc_draw_hidden_field('ajax_url',xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module='.$this->code). '&action=custom').
                             xtc_draw_hidden_field('ajax','1').
                             xtc_draw_hidden_field('start','0').
                             
                             MODULE_IMAGE_DELETE_EXPORT_TEXT_TYPE.'<br />'.
                             MODULE_IMAGE_DELETE_EXPORT_TEXT.'<br />'.
                             
                             '<br />'.MODULE_IMAGE_DELETE_TEXT_MAX_IMAGES.
                             '<br />' . xtc_draw_pull_down_menu('max_datasets', $max_array, '5') . '<br />'.

                             '<br />'.MODULE_IMAGE_DELETE_TEXT_PROCESS_TYPE.
                             '<br />' . xtc_draw_pull_down_menu('process_type', $process_type_array, 'product_images', 'id="process_type"') . '<br />'.

                             '<div id="product_images">' . 
                               '<br />' . xtc_draw_checkbox_field('product_images[mini_images]', '1', false, '', 'class="mini_images"') . ' ' . MODULE_IMAGE_DELETE_TEXT_PRODUCTS_MINI_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('product_images[thumbnail_images]', '1', false, '', 'class="thumbnail_images"') . ' ' . MODULE_IMAGE_DELETE_TEXT_PRODUCTS_THUMBNAIL_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('product_images[midi_images]', '1', false, '', 'class="midi_images"') . ' ' . MODULE_IMAGE_DELETE_TEXT_PRODUCTS_MIDI_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('product_images[info_images]', '1', false, '', 'class="info_images"') . ' ' . MODULE_IMAGE_DELETE_TEXT_PRODUCTS_INFO_IMAGES. 
                               '<br />' . xtc_draw_checkbox_field('product_images[popup_images]', '1', false, '', 'class="popup_images"') . ' ' . MODULE_IMAGE_DELETE_TEXT_PRODUCTS_POPUP_IMAGES. 
                               '<br />' . xtc_draw_checkbox_field('product_images[original_images]', '1', false, '', 'class="popup_images"') . ' ' . MODULE_IMAGE_DELETE_TEXT_PRODUCTS_ORIGINAL_IMAGES. 
                             '</div>' .

                             '<div id="categories" style="display:none;">' . 
                               '<br />' . xtc_draw_checkbox_field('categories[default]', '1', false) . ' ' . MODULE_IMAGE_DELETE_TEXT_CATEGORIES_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('categories[original_images]', '1', false) . ' ' . MODULE_IMAGE_DELETE_TEXT_CATEGORIES_ORIGINAL_IMAGES.
                             '</div>' .

                             '<div id="manufacturers" style="display:none;">' . 
                               '<br />' . xtc_draw_checkbox_field('manufacturers[default]', '1', false) . ' ' . MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('manufacturers[original_images]', '1', false) . ' ' . MODULE_IMAGE_DELETE_TEXT_MANUFACTURERS_ORIGINAL_IMAGES.
                             '</div>' .

                             '<div id="banner" style="display:none;">' . 
                               '<br />' . xtc_draw_checkbox_field('banner[default]', '1', false) . ' ' . MODULE_IMAGE_DELETE_TEXT_BANNER_IMAGES.
                               '<br />' . xtc_draw_checkbox_field('banner[original_images]', '1', false) . ' ' . MODULE_IMAGE_DELETE_TEXT_BANNER_ORIGINAL_IMAGES.
                             '</div>' .

                             '<br />'.MODULE_IMAGE_DELETE_TEXT_LOGGING.
                             '<br />' . xtc_draw_checkbox_field('logging', '1', false, '', 'class="logfile"') . ' ' . MODULE_IMAGE_DELETE_TEXT_LOGFILE. '<br />'.

                             '<br />' . xtc_button(BUTTON_START). '&nbsp;' .
                             xtc_button_link(BUTTON_CANCEL, xtc_href_link($this->module_filename, 'set=' . $_GET['set'] . '&module='.$this->code)) .
                             
                             '<div class="ajax_response" style="margin-bottom:15px;"><hr>'.
                             '<div class="ajax_imgname"></div>'.
                               sprintf(MODULE_IMAGE_DELETE_TEXT_READY, $ajax_img . sprintf(MODULE_IMAGE_DELETE_IMAGE_STEP_INFO, '<span class="ajax_path"></span>') . '<span class="ajax_count"></span> / <span id="ajax_total"></span><span class="ajax_ready_info">' . MODULE_IMAGE_DELETE_IMAGE_STEP_INFO_READY .'<span>') . 
                               '<div class="process_wrapper">
                                <div class="process_inner_wrapper">
                                  <div id="show_image_process" style="width:0%;"></div>
                                </div>
                               </div>
                               <div class="ajax_btn_back">'.sprintf(MODULE_IMAGE_DELETE_TEXT_READY_BACK,xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))).'</div>
                             </div>
                             <script>
                              $("#process_type").on("change", function() {
                                var selector = $(this).val();
                                $("#product_images, #categories, #manufacturers, #banner, .ajax_response").hide();
                                $("#"+selector).show();
                              });
                             </script>
                             '
                   );
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_IMAGE_DELETE_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_IMAGE_DELETE_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_IMAGE_DELETE_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_IMAGE_DELETE_STATUS');
    }
  }
