<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Usage: Put one of the following tags 
     into the templates/yourtemplate/product_info/
       {facebook_badge products_id=$PRODUCTS_ID}
     into the templates/yourtemplate/module/checkout_success.html
       {facebook_badge}

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// more informations: https://developers.facebook.com/docs/plugins/like-button/

define('IFRAME_WIDTH', '200'); // depend on style
define('IFRAME_HEIGHT', '60'); // depend on style
define('FB_WIDTH', '150'); // depend on style
define('FB_HEIGHT', '50'); // depend on style
define('FB_COLOR', 'light'); // light, dark
define('FB_FACES', 'true'); // true, false
define('FB_SHARE', 'true'); // true, false
define('FB_ACTION', 'like'); // like, recommend
define('FB_LAYOUT', 'button_count'); // standard, box_count, button_count


function smarty_function_facebook_badge($params, &$smarty) {
  global $PHP_SELF;
  
  $facebook_badge = '<iframe src="//www.facebook.com/plugins/like.php?href=%s&amp;width='.(isset($params['width']) ? $params['width'] : FB_WIDTH).'&amp;height='.(isset($params['height']) ? $params['height'] : FB_HEIGHT).'&amp;colorscheme='.(isset($params['color']) ? $params['color'] : FB_COLOR).'&amp;layout='.(isset($params['layout']) ? $params['layout'] : FB_LAYOUT).'&amp;action='.(isset($params['action']) ? $params['action'] : FB_ACTION).'&amp;show_faces='.(isset($params['faces']) ? $params['faces'] : FB_FACES).'&amp;send='.(isset($params['share']) ? $params['share'] : FB_SHARE).'&amp;appId=270892269593470" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.(isset($params['iframewidth']) ? $params['iframewidth'] : IFRAME_WIDTH).'px; height:'.(isset($params['iframeheight']) ? $params['iframeheight'] : IFRAME_HEIGHT).'px;" allowTransparency="true"></iframe>';
  
  if (isset($params['products_id'])) {
    $link = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($params['products_id']), 'NONSSL', false);
  }
  
  if (strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false && !isset($params['products_id'])) {
    $link = getOrderDetailsFacebook();
  }

  $facebook_badge = sprintf($facebook_badge, urlencode($link));

  return $facebook_badge;
}


function getOrderDetailsFacebook() {
  global $last_order;
  
  if (!$last_order) {
    return '';
  }
  
  $query = xtc_db_query("-- function.facebook_badge.php
                        SELECT products_id,
                               products_name
                          FROM " . TABLE_ORDERS_PRODUCTS . "
                         WHERE orders_id='" . $last_order . "'
                      GROUP BY products_id");
  if (xtc_db_num_rows($query) == 1) {
    $order = xtc_db_fetch_array($query);
    $link = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($order['products_id'], $order['products_name']), 'NONSSL', false);
  } else {
    $link = HTTP_SERVER;
  }

  return $link;
}