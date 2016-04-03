<?php
/*
   $Id: $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
*/

// for short lines :)
$customers_status_id = $_SESSION['customers_status']['customers_status_id'];


# CONTENT
########################

# group check
$group_check = GROUP_CHECK == 'true' ? ' AND c1.group_ids LIKE \'%c_'.$customers_status_id.'_group%\' ' : '';
$content_conditions_c1 = $group_check . (isset($content_conditions_c1) ? $content_conditions_c1 : '');
define('CONTENT_CONDITIONS_C1', $content_conditions_c1);
define('CONTENT_CONDITIONS', str_replace('c1.', '', $content_conditions_c1));


# PRODUCTS
########################

# fsk18 lock
$fsk_lock = $_SESSION['customers_status']['customers_fsk18_display'] == '0' ? ' AND p.products_fsk18 != 1 ' : '';

# group check
$p_group_check = GROUP_CHECK == 'true' ? ' AND p.group_permission_'.$customers_status_id.' = 1 ' : '';

$products_conditions_p = $fsk_lock . $p_group_check . (isset($products_conditions_p) ? $products_conditions_p : '');
define('PRODUCTS_CONDITIONS_P', $products_conditions_p);
define('PRODUCTS_CONDITIONS', str_replace('p.','', $products_conditions_p));


# CATEGORIES
########################

# group check
$c_group_check = GROUP_CHECK == 'true' ? " AND c.group_permission_".$customers_status_id." = 1 " : "";

$categories_conditions_c = $c_group_check . (isset($categories_conditions_c) ? $categories_conditions_c : '');
define('CATEGORIES_CONDITIONS_C', $categories_conditions_c);
define('CATEGORIES_CONDITIONS', str_replace('c.','', $categories_conditions_c));


  
?>