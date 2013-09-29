<?php

/* -----------------------------------------------------------------------------------------
   $Id: add_a_quickie.php 1262 2005-09-30 10:00:32Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(add_a_quickie.php,v 1.10 2001/12/19); www.oscommerce.com 

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Add A Quickie v1.0 Autor  Harald Ponce de Leon
    
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// reset var
$box_smarty = new smarty;
$box_content = '';
$box_smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
// BOF - GTB - 2010-09-20 - correct the Formular in dependences of the request type SSL / NONSSL
$box_smarty->assign('FORM_ACTION','<form id="quick_add" method="post" action="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array ('action')) . 'action=add_a_quickie', $request_type) . '">');
//$box_smarty->assign('FORM_ACTION','<form id="quick_add" method="post" action="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array ('action')) . 'action=add_a_quickie', 'NONSSL') . '">');
// EOF - GTB - 2010-09-20 - correct the Formular in dependences of the request type SSL / NONSSL
$box_smarty->assign('INPUT_FIELD',xtc_draw_input_field('quickie','','style="width:170px"'));
$box_smarty->assign('SUBMIT_BUTTON', xtc_image_submit('button_add_quick.gif', BOX_HEADING_ADD_PRODUCT_ID));
$box_smarty->assign('FORM_END', '</form>');

$box_smarty->assign('BOX_CONTENT', $box_content);
$box_smarty->assign('language', $_SESSION['language']);

$box_smarty->caching = 0;
$box_add_a_quickie = $box_smarty->fetch(CURRENT_TEMPLATE . '/boxes/box_add_a_quickie.html');

$smarty->assign('box_ADD_QUICKIE', $box_add_a_quickie);
?>