<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$error = array(
  '400' => TEXT_ERROR_HANDLER_400,
  '401' => TEXT_ERROR_HANDLER_401,
  '403' => TEXT_ERROR_HANDLER_403,
  '404' => TEXT_ERROR_HANDLER_404,
  '500' => TEXT_ERROR_HANDLER_500,
);    

$module_smarty = new Smarty();
$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

// set cache ID
if (!CacheCheck()) {
  $cache = false;
  $module_smarty->caching = 0;
  $cache_id = null;
} else {
  $cache = true;
  $module_smarty->caching = 1;
  $module_smarty->cache_lifetime = CACHE_LIFETIME;
  $module_smarty->cache_modified_check = CACHE_CHECK == 'true';
  $cache_id = md5('lID:'.$_SESSION['language'].'|error:'.((isset($_REQUEST['error'])) ? $_REQUEST['error'] : $site_error).'|keyword:'.((isset($_GET['keywords'])) ? $_GET['keywords'] : ''));
}

if (!$module_smarty->is_cached(CURRENT_TEMPLATE.'/module/error_message.html', $cache_id) || !$cache) {
  $module_smarty->assign('ERROR', ((isset($_REQUEST['error']) && isset($error[$_REQUEST['error']])) ? $error[$_REQUEST['error']] : $site_error));

  $link = 'javascript:history.back(1)';
  if (!isset($_SERVER['HTTP_REFERER']) 
      || strpos($_SERVER['HTTP_REFERER'], HTTP_SERVER) === false
      )
  {
    $link = xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL');
  } 
  $module_smarty->assign('BUTTON', '<a href="'.$link.'">'. xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');

  // search field
  $module_smarty->assign('FORM_ACTION', xtc_draw_form('new_find', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get').xtc_hide_session_id());
  $module_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', '', 'placeholder="'.IMAGE_BUTTON_SEARCH.'"'));
  $module_smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH));
  $module_smarty->assign('LINK_ADVANCED', xtc_href_link(FILENAME_ADVANCED_SEARCH));
  $module_smarty->assign('FORM_END', '</form>');
}

$module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/error_message.html', $cache_id);

$smarty->assign('main_content', $module);
$display_mode = 'error';

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/error_handler/','php') as $file) require_once ($file);
