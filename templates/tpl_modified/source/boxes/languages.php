<?php
  /* -----------------------------------------------------------------------------------------
   $Id: languages.php 5581 2013-09-08 21:26:38Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(languages.php,v 1.14 2003/02/12); www.oscommerce.com
   (c) 2003 nextcommerce (languages.php,v 1.8 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }

  $languages_string = '';
  $count_lng = 0;
  reset($lng->catalog_languages);
  while (list($key, $value) = each($lng->catalog_languages)) {
    $count_lng++;
    $lng_link_txt = file_exists('lang/' .  $value['directory'] .'/' . $value['image']) ? xtc_image('lang/' .  $value['directory'] .'/' . $value['image'], $value['name']) : $value['name'];
    $languages_string .= ' <a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type) . '">' . $lng_link_txt . '</a> ';
  }

  // dont show box if there's only 1 language
  if ($count_lng > 1 ) {
    $box_smarty = new smarty;
    $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

    $box_content='';
    $box_smarty->assign('BOX_CONTENT', $languages_string);
    $box_smarty->assign('language', $_SESSION['language']);

    // set cache ID
    $box_smarty->caching = 0;
    $box_languages= $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_languages.html');

    $smarty->assign('box_LANGUAGES',$box_languages);
  }
?>