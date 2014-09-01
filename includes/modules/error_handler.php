<?php
/* -----------------------------------------------------------------------------------------
   $Id: error_handler.php 949 2005-05-14 16:44:33Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
  //header( 'HTTP/1.0 404 Not Found' );
  //header( 'Status: 404 Not Found' );

  $module_smarty= new Smarty;
  $module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');

  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('ERROR',$error);
  $module_smarty->assign('BUTTON','<a href="javascript:history.back(1)">'. xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>'); // Tomcraft - 2010-05-04 - Changed alternative text for the button
  $module_smarty->assign('language', $_SESSION['language']);

  // search field
  $module_smarty->assign('FORM_ACTION',xtc_draw_form('new_find', xtc_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get').xtc_hide_session_id()); //WEB28 change NONSSL to $request_type
  $module_smarty->assign('INPUT_SEARCH',xtc_draw_input_field('keywords', '', 'size="30" maxlength="30"'));
  $module_smarty->assign('BUTTON_SUBMIT',xtc_image_submit('button_quick_find.gif', IMAGE_BUTTON_SEARCH));
  $module_smarty->assign('LINK_ADVANCED',xtc_href_link(FILENAME_ADVANCED_SEARCH));
  $module_smarty->assign('FORM_END', '</form>');

  $module_smarty->caching = 0;
  $module_smarty->caching = 0;
  $module= $module_smarty->fetch(CURRENT_TEMPLATE.'/module/error_message.html');

  if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO))  $product_info=$module;

  $smarty->assign('main_content',$module);
?>
