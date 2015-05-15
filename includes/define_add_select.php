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
can be used in extra hook points:

if (!isset($add_select_default)) $add_select_default = array();
$add_select_default[] = 'p.products_extra_field';

*/
  // used in /includes/modules/default.php
  define('ADD_SELECT_DEFAULT', 'p.products_manufacturers_model, '.(isset($add_select_default) ? rtrim(implode(', ', $add_select_default), ',').', ' : ''));
  
  // used in /advanced_search_result.php
  define('ADD_SELECT_SEARCH', 'p.products_manufacturers_model, '.(isset($add_select_search) ? rtrim(implode(', ', $add_select_search), ',').', ' : ''));
  
  // used in /includes/classes/product.php
  define('ADD_SELECT_PRODUCT', (isset($add_select_product) ? rtrim(implode(', ', $add_select_product), ',').', ' : ''));
  
  // used in /includes/classes/shopping_cart.php
  define('ADD_SELECT_CART', (isset($add_select_cart) ? rtrim(implode(', ', $add_select_cart), ',').', ' : ''));
?>