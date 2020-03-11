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

// set cache id
$cache_id = md5($_SESSION['currency'].$_SESSION['language'].xtc_get_all_get_params('currency', 'language'));

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_currencies.html', $cache_id) || !$cache) {

  $currencies_array = array();
  if (isset($xtPrice) && is_object($xtPrice)) {
    reset($xtPrice->currencies);
    foreach ($xtPrice->currencies as $key => $value) {
      $currencies_array[] = array('id' => $key, 'text' => $value['title']);
    }
  }

  // dont show box if there's only 1 currency
  if (count($currencies_array) > 1 ) {
    $box_content = xtc_draw_form('currencies', xtc_href_link(basename($PHP_SELF), '', $request_type, false), 'get', 'class="box-currencies"')
                   . xtc_draw_pull_down_menu('currency', $currencies_array, $_SESSION['currency'], 'onchange="this.form.submit();"')
                   . xtc_hide_session_id();
    
    parse_str(xtc_get_all_get_params('currency', 'language'), $params_array);
    if (is_array($params_array) && count($params_array) > 0) {
      foreach ($params_array as $k => $v) {
        $box_content .= xtc_draw_hidden_field($k, $v);
      }
    }
        
    $box_content .= '</form>';

    $box_smarty->assign('BOX_CONTENT', $box_content);
  }
}

if (!$cache) {
  $box_currencies = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_currencies.html');
} else {
  $box_currencies = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_currencies.html', $cache_id);
}

$smarty->assign('box_CURRENCIES', $box_currencies);
?>