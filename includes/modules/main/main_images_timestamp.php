<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  class main_images_timestamp {

    var $code;
    var $name;
    var $title;
    var $description;
    var $enabled;
    var $sort_order;
    var $_check;
  
    function __construct() {
      $this->code = 'main_images_timestamp'; //Important same name as class name
      $this->name = 'MODULE_MAIN_'.strtoupper($this->code);
      $this->title = ((defined($this->name.'_TITLE')) ? constant($this->name.'_TITLE') : '');
      $this->description = ((defined($this->name.'_DESCRIPTION')) ? constant($this->name.'_DESCRIPTION') : '');        
      $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
      $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';        
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined($this->name.'_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = '".$this->name."_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }
    
    function keys() {
      defined($this->name.'_STATUS_TITLE') OR define($this->name.'_STATUS_TITLE', TEXT_DEFAULT_STATUS_TITLE);
      defined($this->name.'_STATUS_DESC') OR define($this->name.'_STATUS_DESC', TEXT_DEFAULT_STATUS_DESC);
      defined($this->name.'_SORT_ORDER_TITLE') OR define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
      defined($this->name.'_SORT_ORDER_DESC') OR define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);

      return array(
        $this->name.'_STATUS', 
        $this->name.'_SORT_ORDER'
      );
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_SORT_ORDER', '10','6', '2', now())");
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '".$this->name."_%'");
    }
    
    
    //--- BEGIN CUSTOM  CLASS METHODS ---//
    function getImage($image, $dir, $check, $noImg, $imageOrigin) {
      if (is_file(DIR_FS_CATALOG.$image)) {
        $filemtime = filemtime(DIR_FS_CATALOG.$image);
        
        $separator = '?';
        if (strpos($image, $separator) !== false) {
          $separator = '&';
        }
        
        $image .= $separator.'t='.$filemtime;
      }
            
      return $image;
    }

  }
