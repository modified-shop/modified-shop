<?php
/* -----------------------------------------------------------------------------------------
   $Id: languages.php 1262 2005-09-30 10:00:32Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(languages.php,v 1.14 2003/02/12); www.oscommerce.com
   (c) 2003	 nextcommerce (languages.php,v 1.8 2003/08/17); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC . 'xtc_get_all_get_params.inc.php');

  //BOF - 2010-02-28 - Fix Undefined variable: lng
  //if (!isset($lng) && !is_object($lng)) {
  if (!isset($lng) || !is_object($lng)) {
  //EOF - 2010-02-28 - Fix Undefined variable: lng
    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }

  $languages_string = '';
  $count_lng='';
  reset($lng->catalog_languages);
  while (list($key, $value) = each($lng->catalog_languages)) {
  $count_lng++;
	//BOF - Dokuman - 2010-01-29 - code optimization
 	//$languages_string .= ' <a href="' . xtc_href_link(basename($PHP_SELF), 'language=' . $key.'&'.xtc_get_all_get_params(array('language', 'currency')), $request_type) . '">' . xtc_image('lang/' .  $value['directory'] .'/' . $value['image'], $value['name']) . '</a> ';
    if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
      $languages_string .= ' <a href="' . xtc_href_link(basename($PHP_SELF), 'language=' . $key.'&'.xtc_get_all_get_params(array('language', 'currency')), $request_type) . '">' . xtc_image('lang/' .  $value['directory'] .'/' . $value['image'], $value['name']) . '</a> ';
    } else {
      $languages_string .= ' <a href="' . xtc_href_link(basename($PHP_SELF), 'language=' . $key.'&amp;'.xtc_get_all_get_params(array('language', 'currency')), $request_type) . '">' . xtc_image('lang/' .  $value['directory'] .'/' . $value['image'], $value['name']) . '</a> ';
    }
	//EOF - Dokuman - 2010-01-29 - code optimization
  }

  // dont show box if there's only 1 language
  if ($count_lng > 1 ) {

 $box_smarty = new smarty;
 $box_smarty->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/'); 
 $box_content='';
 $box_smarty->assign('BOX_CONTENT', $languages_string);
 $box_smarty->assign('language', $_SESSION['language']);


    	  // set cache ID

  $box_smarty->caching = 0;
  $box_languages= $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_languages.html');
  	

    $smarty->assign('box_LANGUAGES',$box_languages);
  }
   ?>