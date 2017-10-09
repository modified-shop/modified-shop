<?php
/* -----------------------------------------------------------------------------------------
   $Id: error_handler.php

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  //header( 'HTTP/1.0 404 Not Found' );
  //header( 'Status: 404 Not Found' );

  $module_smarty = new Smarty;
  $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('ERROR', $site_error);

  $link = 'javascript:history.back(1)';
  if (!isset($_SERVER['HTTP_REFERER']) 
      || strpos($_SERVER['HTTP_REFERER'], HTTP_SERVER) === false
      )
  {
    $link = xtc_href_link(FILENAME_DEFAULT, '', 'NONSSL');
  } 
  $module_smarty->assign('BUTTON', '<a href="'.$link.'">'. xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
  $module_smarty->assign('language', $_SESSION['language']);

  // search field
  $module_smarty->assign('FORM_ACTION', xtc_draw_form('new_find', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get').xtc_hide_session_id());
  $module_smarty->assign('INPUT_SEARCH', xtc_draw_input_field('keywords', IMAGE_BUTTON_SEARCH, 'size="30" maxlength="30" onfocus="if(this.value==this.defaultValue) this.value=\'\';" onblur="if(this.value==\'\') this.value=this.defaultValue;"'));
  $module_smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH));
  $module_smarty->assign('LINK_ADVANCED', xtc_href_link(FILENAME_ADVANCED_SEARCH));
  $module_smarty->assign('FORM_END', '</form>');

  $module_smarty->caching = 0;
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/error_message.html');
  
  if (isset($smarty) && is_object($smarty)) {
    $smarty->assign('bestseller', true);
    $smarty->assign('main_content', $module);
  }
?>