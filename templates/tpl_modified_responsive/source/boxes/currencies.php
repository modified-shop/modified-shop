<?php
/* -----------------------------------------------------------------------------------------
   $Id: currencies.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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
$cache_id = md5($_SESSION['language'] . $_SESSION['currency']);

if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_currencies.html', $cache_id) || !$cache) {

  $currencies_array = array();
  if (isset($xtPrice) && is_object($xtPrice)) {
    reset($xtPrice->currencies);
    while (list($key, $value) = each($xtPrice->currencies)) {
      $currencies_array[] = array('id' => $key, 'text' => $value['title']);
    }
  }

  // dont show box if there's only 1 currency
  if (count($currencies_array) > 1 ) {

    $hidden_get_variables = '';
    if (isset($_GET) && count($_GET) > 0) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if (is_string($value) && $key != 'currency' && $key != xtc_session_name() && $key != 'x' && $key != 'y' ) {
          $hidden_get_variables .= xtc_draw_hidden_field($key, $value);
        }
      }
    }

    $box_content = xtc_draw_form('currencies', xtc_href_link(basename($PHP_SELF), '', $request_type, false), 'get', 'class="box-currencies"')
                   . xtc_draw_pull_down_menu('currency', $currencies_array, $_SESSION['currency'], 'onchange="this.form.submit();"')
                   . $hidden_get_variables . xtc_hide_session_id()
                   . '</form>';

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