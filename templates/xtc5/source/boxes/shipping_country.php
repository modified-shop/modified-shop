<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.16 2003/02/12); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.11 2003/08/17); www.nextcommerce.org
   (c) 2003-2006 XT-Commerce (currencies.php 1262 2005-09-30)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

$selected = isset($_SESSION['customer_country_id']) ? $_SESSION['customer_country_id'] : STORE_COUNTRY;
if (isset($_SESSION['country'])) {
  $selected = $_SESSION['country'];
}

// set cache id
$cache_id = md5('lID:'.$_SESSION['language'].'|sel:'.$selected.'|site:'.basename($PHP_SELF).'|params:'.xtc_get_all_get_params(array('currency', 'language')));

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_shipping_country.html', $cache_id) || !$cache) {  
  require_once (DIR_FS_INC.'xtc_get_country_list.inc.php');
  $countries_array = xtc_get_countriesList();

  // dont show box if there's only 1 currency
  if (count($countries_array) > 1 ) {
    $box_content = xtc_draw_form('countries', xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')).'action=shipping_country', $request_type, false), 'post', 'class="box-shipping_country"')
                   . xtc_get_country_list(array('name' => 'country'), (int)$selected, 'onchange="this.form.submit()" style="max-width:100%;"')
                   . xtc_hide_session_id();
    
    parse_str(xtc_get_all_get_params(array('currency', 'language')), $params_array);
    if (is_array($params_array) && count($params_array) > 0) {
      foreach ($params_array as $k => $v) {
        if (is_array($v)) {
          foreach ($v as $kk => $vv) {
            $box_content .= xtc_draw_hidden_field($k.'['.$kk.']', $vv);
          }
        } else {
          $box_content .= xtc_draw_hidden_field($k, $v);
        }
      }
    }
        
    $box_content .= '</form>';

    $box_smarty->assign('BOX_CONTENT', $box_content);
  }
}

if (!$cache) {
  $box_shipping_country = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_shipping_country.html');
} else {
  $box_shipping_country = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_shipping_country.html', $cache_id);
}

$smarty->assign('box_SHIPPING_COUNTRY', $box_shipping_country);
?>