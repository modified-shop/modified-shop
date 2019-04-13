<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class catCopyProductName {  //Important same name as filename
  
    //--- BEGIN DEFAULT CLASS METHODS ---//
    function __construct()
    {
        $this->code = 'catCopyProductName'; //Important same name as class name
        $this->name = 'MODULE_CATEGORIES_'.strtoupper($this->code);
        $this->title = constant($this->name.'_TITLE');
        $this->description = constant($this->name.'_DESCRIPTION');
        $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
        $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';        
    }
    
    function check() {
        if (!isset($this->_check)) {
          $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '".$this->name."_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
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
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('".$this->name."_SORT_ORDER', '10','6', '2', now())");
    }

    function remove() {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE '".$this->name."_%'");
    }
    
    
    //--- BEGIN CUSTOM  CLASS METHODS ---//

    function duplicate_product_desc($sql_data_array,$src_products_id,$dest_categories_id,$dup_products_id) 
    {
      $sql_data_array['products_name'] .= ' (copy)';
      return $sql_data_array;
    }

}