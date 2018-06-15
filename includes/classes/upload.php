<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(upload.php,v 1.1 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (upload.php,v 1.7 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (upload.php 950 2005-05-14)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  
  class upload {
    var $file, $filename, $destination, $permissions, $extensions, $mime_types, $tmp_filename;

    function __construct($file = '', $destination = '', $permissions = '644', $extensions = '', $mime_types = '') {

      $this->set_file($file);
      $this->set_destination($destination);
      $this->set_permissions($permissions);
      $this->set_extensions($extensions);
      $this->set_mime_types($mime_types);
      
      if (xtc_not_null($this->file) && xtc_not_null($this->destination)) {
        if ( ($this->parse() == true) && ($this->save() == true) ) {
          return true;
        } else {
          return false;
        }
      }
    }

    function parse() {
    
      $file = array();

      if (isset($_FILES[$this->file])) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      }

      if (isset($file['tmp_name']) && !empty($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name'])) {
        if (sizeof($this->mime_types) > 0) {
          if (!in_array(strtolower($file['type']), $this->mime_types)) {
            $this->set_message(ERROR_FILETYPE_NOT_ALLOWED);
            return false;
          }
        }
        if (sizeof($this->extensions) > 0) {
          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
            $this->set_message(ERROR_FILETYPE_NOT_ALLOWED);
            return false;
          }
        }

        if ($file['name'] == '.htaccess' || $file['name'] == '.htpasswd') {
          $this->set_message(ERROR_FILETYPE_NOT_ALLOWED);
          return false;
        }

        $this->set_file($file);
        $this->set_filename($file['name']);
        $this->set_tmp_filename($file['tmp_name']);
        return $this->check_destination();
        
      } else {
        if ($file['tmp_name'] == 'none') {
          $this->set_message(WARNING_NO_FILE_UPLOADED);
        }
        return false;
      }
    }

    function save() {

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      // GDlib check
      if (!function_exists('imagecreatefromgif')) {

        // check if uploaded file = gif
        if ($this->destination==DIR_FS_CATALOG_ORIGINAL_IMAGES) {
          // check if merge image is defined .gif
          if (strstr(PRODUCT_IMAGE_THUMBNAIL_MERGE,'.gif') ||
              strstr(PRODUCT_IMAGE_INFO_MERGE,'.gif') ||
              strstr(PRODUCT_IMAGE_POPUP_MERGE,'.gif')) {
              $this->set_message(ERROR_GIF_MERGE);
              return false;
          }
          // check if uploaded image = .gif
          if (strstr($this->filename,'.gif')) {
           $this->set_message(ERROR_GIF_UPLOAD);
           return false;
          }

        }

      }
      
      // prevent overwriting existing files
      $name_arr = explode('.', $this->filename);
      $extension = '.'.array_pop($name_arr);
      $name = implode('.', $name_arr);
      $this->filename = $this->check_filename($name, $extension);
      
      if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
        chmod($this->destination . $this->filename, $this->permissions);
        $this->set_message(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        return true;
        
      } else {
        $this->set_message(ERROR_FILE_NOT_SAVED);
        return false;
      }
    }

    function set_file($file) {
      $this->file = $file;
    }

    function set_destination($destination) {
      $this->destination = $destination;
    }

    function set_permissions($permissions) {
      $this->permissions = octdec($permissions);
    }

    function set_filename($filename) {
      $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
      $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
      if (xtc_not_null($extensions)) {
        if (is_array($extensions)) {
          $this->extensions = $extensions;
        } else {
          $this->extensions = array($extensions);
        }
      } else {
        $this->extensions = array();
      }
    }

    function set_mime_types($mime_types) { 
      if (xtc_not_null($mime_types)) { 
        if (is_array($mime_types)) { 
          $this->mime_types = $mime_types; 
        } else { 
          $this->mime_types = array($mime_types); 
        } 
      } else { 
        $this->mime_types = array(); 
      } 
    }
 	
    function check_destination() {
      if (!is_writeable($this->destination)) {
        if (is_dir($this->destination)) {
          $this->set_message(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination));
        } else {
          $this->set_message(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination));
        }
        return false;
        
      } else {
        return true;
      }
    }
    
    function check_filename($name, $extension, $counter = '') {
      $this->counter = $counter;
      
      if (is_file($this->destination.$name.(($this->counter != '') ? '-'.$this->counter : '').$extension)) {
        $this->counter ++;
        $this->check_filename($name, $extension, $this->counter);
      }

      $filename = $name.(($this->counter != '') ? '-'.$this->counter : '').$extension;

      return $filename;		    
    }

    function set_message($text, $type = 'error') { 
      global $messageStack;
      
      if (is_object($messageStack)) {
        if (defined('RUN_MODE_ADMIN')) {
          $messageStack->add_session($text, $type); 
        } else {
          $messageStack->add_session('upload', $text, $type);
        }
      } 
    }
    
  }
?>