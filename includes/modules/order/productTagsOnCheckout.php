<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class productTagsOnCheckout {  //Important same name as filename
  
    //--- BEGIN DEFAULT CLASS METHODS ---//
    function __construct()
    {
        $this->code = 'productTagsOnCheckout'; //Important same name as class name
        $this->name = 'MODULE_ORDER_'.strtoupper($this->code);
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
        define($this->name.'_STATUS_TITLE', TEXT_DEFAULT_STATUS_TITLE);
        define($this->name.'_STATUS_DESC', TEXT_DEFAULT_STATUS_DESC);
        define($this->name.'_SORT_ORDER_TITLE', TEXT_DEFAULT_SORT_ORDER_TITLE);
        define($this->name.'_SORT_ORDER_DESC', TEXT_DEFAULT_SORT_ORDER_DESC);
        
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

    function cart_products($products_data, $products_id) 
    {
      $tags_query = xtDBquery("SELECT ".ADD_TAGS_SELECT."
                                      pto.options_id,
                                      pto.options_name,
                                      pto.options_description,
                                      pto.sort_order AS options_sort_order,
                                      pto.options_content_group,
                                      ptv.values_id,
                                      ptv.values_name,
                                      ptv.values_description,
                                      ptv.sort_order AS values_sort_order,
                                      ptv.values_image,
                                      ptv.values_content_group
                                 FROM ".TABLE_PRODUCTS_TAGS." pt
                                 JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                      ON pt.options_id = pto.options_id
                                         AND pto.status = '1'
                                         AND pto.languages_id = '".(int)$_SESSION['languages_id']."'
                                 JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                      ON ptv.values_id = pt.values_id
                                         AND ptv.status = '1'
                                         AND ptv.languages_id = '".(int)$_SESSION['languages_id']."'
                                WHERE pt.products_id = '".(int)$products_id."'
                             ORDER BY pt.sort_order, pto.sort_order, ptv.sort_order");

      if (xtc_db_num_rows($tags_query, true) > 0) {
        $module_content = array();
        while ($tags = xtc_db_fetch_array($tags_query, true)) {
          if (!isset($module_content[$tags['options_id']])) {
            $module_content[$tags['options_id']] = array('OPTIONS_NAME' => $tags['options_name'],
                                                         'OPTIONS_ID' => $tags['options_id'],
                                                         'OPTIONS_SORT_ORDER' => $tags['options_sort_order'],
                                                         'OPTIONS_DESCRIPTION' => $tags['options_description'],
                                                         'OPTIONS_CONTENT_LINK' => (($tags['options_content_group'] != '') ? xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.$tags['options_content_group'], 'NONSSL') : ''),
                                                         'DATA' => array());
          }
          $module_content[$tags['options_id']]['DATA'][] = array('VALUES_NAME' => $tags['values_name'],
                                                                 'VALUES_ID' => $tags['values_id'],
                                                                 'VALUES_SORT_ORDER' => $tags['values_sort_order'],
                                                                 'VALUES_DESCRIPTION' => $tags['values_description'],
                                                                 'VALUES_IMAGE' => (($tags['values_image'] != '' && is_file(DIR_FS_CATALOG.DIR_WS_IMAGES.$tags['values_image'])) ? DIR_WS_BASE.DIR_WS_IMAGES.$tags['values_image'] : ''),
                                                                 'VALUES_CONTENT_LINK' => (($tags['values_content_group'] != '') ? xtc_href_link(FILENAME_POPUP_CONTENT, 'coID='.$tags['values_content_group'], 'NONSSL') : ''),
                                                                 );
          foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/products_tags_data/','php') as $file) require ($file);
        }
        
        foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/products_tags_end/','php') as $file) require ($file);
        $products_data['products_tags'] = $module_content;        
      }
      return $products_data;    
    }

}