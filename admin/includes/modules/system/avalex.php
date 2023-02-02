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
  
  // include needed classes
  require_once(DIR_FS_EXTERNAL.'avalex/avalex_update.php');
  
  class avalex {
    var $code, $title, $description, $enabled;

    function __construct() {
      global $order;
      
      $this->version = '1.00';
      $this->code = 'avalex';
      $this->title = MODULE_AVALEX_TEXT_TITLE;
      $this->description = MODULE_AVALEX_TEXT_DESCRIPTION.'<br><br><br><b>Version</b><br>'.$this->version;
      $this->enabled = ((defined('MODULE_AVALEX_STATUS') && MODULE_AVALEX_STATUS == 'True') ? true : false);
      $this->sort_order = '';
    }

    function process($file) {
      if ($this->enabled === true 
          && isset($_POST['import']) 
          && $_POST['import'] == 'yes'
          )
      {
        $avalex = new avalex_update();
        $avalex->check_update();
      }
    }

    function display() {    
      return array('text' =>  '<br/><b>'.MODULE_AVALEX_ACTION_TITLE.'</b><br/>'.
                              MODULE_AVALEX_ACTION_DESC.'<br>'.
                              xtc_draw_radio_field('import', 'no', true).NO.'<br>'.
                              xtc_draw_radio_field('import', 'yes', false).YES.'<br>'.

                             '<br /><div align="center">' . xtc_button('OK') .
                              xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=protectedshops')) . "</div>");
    }

    function check() {
      if (!isset($this->_check)) {
        if (defined('MODULE_AVALEX_STATUS')) {
          $this->_check = true;
        } else {
          $check_query = xtc_db_query("SELECT configuration_value 
                                         FROM " . TABLE_CONFIGURATION . " 
                                        WHERE configuration_key = 'MODULE_AVALEX_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_API', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_DOMAIN', '',  '6', '1', '', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_TYPE', 'Database',  '6', '4', 'xtc_cfg_select_option(array(\'File\', \'Database\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_AVALEX_LAST_UPDATED', '',  '6', '6', '', now())");

      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_AGB', '3',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_DSE', '2',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_WRB', '9',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_AVALEX_TYPE_IMP', '4',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array(
        'MODULE_AVALEX_STATUS',
        'MODULE_AVALEX_API',
        'MODULE_AVALEX_DOMAIN',
        'MODULE_AVALEX_TYPE',
        'MODULE_AVALEX_TYPE_AGB',
        'MODULE_AVALEX_TYPE_DSE',
        'MODULE_AVALEX_TYPE_WRB',
        'MODULE_AVALEX_TYPE_IMP',
      );
    }
  }
