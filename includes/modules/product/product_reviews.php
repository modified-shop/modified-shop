<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  class product_reviews {
  
    function __construct() {
      $this->code = 'product_reviews'; //Important same name as class name
      $this->name = 'MODULE_PRODUCT_'.strtoupper($this->code);
      $this->title = constant($this->name.'_TITLE');
      $this->description = constant($this->name.'_DESCRIPTION');        
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
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('".$this->name."_STATUS', 'true','6', '1','xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('".$this->name."_SORT_ORDER', '10','6', '2', now())");
    }

    function remove() {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '".$this->name."_%'");
    }
    
    
    //--- BEGIN CUSTOM  CLASS METHODS ---//
    function buildDataArray($productData, $array, $image) {
      global $product;
      
      $productData['PRODUCTS_REVIEWS_COUNT'] = $product->getReviewsCount($array['products_id']);
      $productData['PRODUCTS_REVIEWS_AVERAGE'] = $product->getReviewsAverage($array['products_id']);
      
      $img = 'templates/'.CURRENT_TEMPLATE.'/img/stars_'.$productData['PRODUCTS_REVIEWS_AVERAGE'].'.gif';
      if (!is_file(DIR_FS_CATALOG.$img)) {
        $img = 'templates/'.CURRENT_TEMPLATE.'/img/stars_'.$productData['PRODUCTS_REVIEWS_AVERAGE'].'.png';        
      }
      $productData['PRODUCTS_RATING_STARS'] = xtc_image($img, sprintf(TEXT_OF_5_STARS, $productData['PRODUCTS_REVIEWS_AVERAGE']));
      $productData['PRODUCTS_RATING_STARS_MICROTAG'] = xtc_image($img, sprintf(TEXT_OF_5_STARS, $productData['PRODUCTS_REVIEWS_AVERAGE']),'','','itemprop="rating"');
      
      return $productData;
    }

  }
