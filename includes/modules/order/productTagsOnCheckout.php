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
        $this->title = 'ProductFeaturesOnCheckout';
        $this->description = 'Show product tags in checkout';        
        $this->name = 'MODULE_ORDER_'.strtoupper($this->code);
        $this->enabled = defined($this->name.'_STATUS') && constant($this->name.'_STATUS') == 'true' ? true : false;
        $this->sort_order = defined($this->name.'_SORT_ORDER') ? constant($this->name.'_SORT_ORDER') : '';
        
        $this->translate();
    }
    
    function translate() {
        switch ($_SESSION['language_code']) {
          case 'de':
            $this->title = 'Artikeleigenschaften im Checkout';
            $this->description = 'Artikeleigenschaften werden auf Bestellbest&auml;tigungsseite zus&auml;tzlich zur Bestellbeschreibung angezeigt';
            break;
          default:
            $this->title = 'Product features in checkout';
            $this->description = 'Product features are displayed additional to the order description on checkout comfirmation';
            break;
        }
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
      $tags_query = xtDBquery("SELECT pto.options_id,
                                      pto.options_name,
                                      ptv.values_id,
                                      ptv.values_name
                                 FROM ".TABLE_PRODUCTS_TAGS." pt
                                 JOIN ".TABLE_PRODUCTS_TAGS_OPTIONS." pto
                                      ON pt.options_id = pto.options_id
                                         AND pto.status = '1'
                                         AND pto.languages_id = '".(int)$_SESSION['languages_id']."'
                                 JOIN ".TABLE_PRODUCTS_TAGS_VALUES." ptv
                                      ON ptv.values_id = pt.values_id
                                         AND ptv.status = '1'
                                         AND ptv.languages_id = '".(int)$_SESSION['languages_id']."'
                                WHERE pt.products_id = '".xtc_get_prid($products_id)."'
                             ORDER BY pto.sort_order, ptv.sort_order");

      if (xtc_db_num_rows($tags_query, true) > 0) {
        $module_content = array();
        while ($tags = xtc_db_fetch_array($tags_query, true)) {
          if (!isset($module_content[$tags['options_id']])) {
            $module_content[$tags['options_id']] = array('OPTIONS_NAME' => $tags['options_name'],
                                                         'DATA' => array());
          }
          $module_content[$tags['options_id']]['DATA'][] = array('VALUES_NAME' => $tags['values_name']);
        }
  
        if (count($module_content) > 0) {
          foreach ($module_content as $option) {
            $products_data['order_description'] .= '<b>'.$option['OPTIONS_NAME'].':</b> ';
            foreach ($option['DATA'] as $value) {
              $products_data['order_description'] .= $value['VALUES_NAME'].', ';
            }
            $products_data['order_description'] = substr($products_data['order_description'], 0, -2).'<br/>';
          }
        }
      }
      return $products_data;    
    }

}