<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
/*
example for default.php
can be used in extra/define_add_select/yourfile.php

$add_select_default[] = 'p.products_extra_field';
$add_select_categories[] = 'c.categories_extra_field';

*/
  $add_select_default = array();
  $add_select_search = array();
  $add_where_search = array();
  $add_select_product = array();
  $add_select_cart = array();
  $add_select_content = array();
  $add_products_options_select = array();
  $add_tags_select = array();  
  $add_select_categories = array();      
  
  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/define_add_select/','php') as $file) require ($file);

  // used in /includes/modules/default.php - used for all product listings
  define('ADD_SELECT_DEFAULT', 'p.products_manufacturers_model, '.(count($add_select_default) ? rtrim(implode(', ', array_unique($add_select_default)), ',').', ' : ''));
  
  // used in /advanced_search_result.php - used for search results
  define('ADD_SELECT_SEARCH', 'p.products_manufacturers_model, '.(count($add_select_search) ? rtrim(implode(', ', array_unique($add_select_search)), ',').', ' : ''));
  
  // used in /includes/build_search_query - used for keyword search results
  define('ADD_WHERE_SEARCH', (count($add_where_search) ? rtrim(implode(', ', array_unique($add_where_search)), ',').', ' : ''));
  
  // used in /includes/classes/product.php - used for products
  define('ADD_SELECT_PRODUCT', (count($add_select_product) ? rtrim(implode(', ', array_unique($add_select_product)), ',').', ' : ''));
  
  // used in /includes/classes/shopping_cart.php -  used for cart details
  define('ADD_SELECT_CART', (count($add_select_cart) ? rtrim(implode(', ', array_unique($add_select_cart)), ',').', ' : ''));
  
  // used in shop_content.php -  used for shop_content
  define('ADD_SELECT_CONTENT', (count($add_select_content) ? rtrim(implode(', ', array_unique($add_select_content)), ',').', ' : ''));
  
  // used in default.php -  used for categorie/products listing
  define('ADD_SELECT_CATEGORIES', (count($add_select_categories) ? rtrim(implode(', ', array_unique($add_select_categories)), ',').', ' : ''));
  //PRODUCT OPTIONS
  // used in /includes/modules/product_attributes.php - used for products options data
  define('ADD_PRODUCT_OPTIONS_SELECT', (count($add_products_options_select) ? rtrim(implode(', ', array_unique($add_products_options_select)), ',').', ' : ''));
  
  //PRODUCT TAGS
  // used in /includes/modules/product_tags.php - used for product tags module_content
  define('ADD_TAGS_SELECT', (count($add_tags_select) ? rtrim(implode(', ', array_unique($add_tags_select)), ',').', ' : ''));
  
?>