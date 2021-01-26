<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include needed classes
require_once(DIR_FS_EXTERNAL.'semknox/Semknox.php');

class semknox_categories {

  function __construct() {
    $this->code = 'semknox_categories';
    $this->name = 'MODULE_CATEGORIES_'.strtoupper($this->code);
    $this->title = constant($this->name.'_TITLE');
    $this->description = constant($this->name.'_DESCRIPTION');
    $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
    $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : ''; 
    
    $this->languages = xtc_get_languages();
    $this->semknox = array();

    if ($this->check() > 0
        && defined('MODULE_SEMKNOX_SYSTEM_STATUS')
        && MODULE_SEMKNOX_SYSTEM_STATUS == 'true'
        )
    {      
      foreach ($this->languages as $language) {
        if (defined('MODULE_SEMKNOX_SYSTEM_API_'.$language['id'])
            && constant('MODULE_SEMKNOX_SYSTEM_API_'.$language['id']) != ''
            )
        {
          $this->semknox[$language['id']] = new Semknox($language['id']);
        }
      }
    }
  }
  
  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value 
                                     FROM " . TABLE_CONFIGURATION . " 
                                    WHERE configuration_key = '".$this->name."_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }
  
  function keys() {
    defined($this->name.'_STATUS_TITLE') OR define($this->name.'_STATUS_TITLE', TEXT_DEFAULT_STATUS_TITLE);
    defined($this->name.'_STATUS_DESC') OR define($this->name.'_STATUS_DESC', TEXT_DEFAULT_STATUS_DESC);
    defined($this->name.'_SORT_ORDER_TITLE') OR define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
    defined($this->name.'_SORT_ORDER_DESC') OR define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);
    
    $keys = array(
      $this->name.'_STATUS', 
      $this->name.'_SORT_ORDER'
    );
    
    return $keys;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_SORT_ORDER', '10','6', '2', now())");

    require_once(DIR_FS_ADMIN.DIR_WS_MODULES.'system/semknox_system.php');
    $semknox_system = new semknox_system();
    if ($semknox_system->check() < 1) {
      $semknox_system->install();
    }
  }
  
  
  function remove() {  
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '".$this->name."_%'");

    require_once(DIR_FS_ADMIN.DIR_WS_MODULES.'system/semknox_system.php');
    $semknox_system = new semknox_system();
    if ($semknox_system->check() > 0) {
      $semknox_system->remove();
    }
  }
  
  
  //--- BEGIN CUSTOM  CLASS METHODS ---//
  function remove_product($products_id) {
    if (count($this->semknox) > 0) {
      foreach ($this->semknox as $semknox) {
        $semknox->deleteProduct($products_id);
      }
    }
  }
  
  function insert_product_end($products_id) {
    if (count($this->semknox) > 0) {
      foreach ($this->semknox as $semknox) {
        $semknox->sendProduct($products_id);
      }
    }
  }
  
  function duplicate_product_end($products_id) {
    if (count($this->semknox) > 0) {
      foreach ($this->semknox as $semknox) {
        $semknox->sendProduct($products_id);
      }
    }
  }
}