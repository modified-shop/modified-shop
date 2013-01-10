<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com
   (c) 2003  nextcommerce www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $box_smarty = new smarty;
  $box_content='';
  $rebuild = false;
  $box_smarty->assign('language', $_SESSION['language']);
  // set cache ID
  if (!CacheCheck()) {
   $cache=false;
   $box_smarty->caching = 0;
  } else {
   $cache=true;
   $box_smarty->caching = 1;
   $box_smarty->cache_lifetime = CACHE_LIFETIME;
   $box_smarty->cache_modified_check = CACHE_CHECK;
   $cache_id = $_SESSION['language'];
  }

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id) || !$cache) {
    //BOF - GTB - 2010-08-03 - Security Fix - Base
    $box_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
    //$box_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
    //EOF - GTB - 2010-08-03 - Security Fix - Base
    $rebuild = true;

    // BOF - GTB - 2010-09-20 - correct the Formular in dependences of the request type SSL / NONSSL
    $box_smarty->assign('FORM_ACTION', xtc_draw_form('sign_in', xtc_href_link(FILENAME_NEWSLETTER, '', $request_type)));
    //$box_smarty->assign('FORM_ACTION', xtc_draw_form('sign_in', xtc_href_link(FILENAME_NEWSLETTER, '', 'NONSSL')));
    // EOF - GTB - 2010-09-20 - correct the Formular in dependences of the request type SSL / NONSSL
    $box_smarty->assign('FIELD_EMAIL',xtc_draw_input_field('email', '', 'maxlength="50" style="width:170px;"'));
    $box_smarty->assign('BUTTON',xtc_image_submit('button_login_newsletter.gif', IMAGE_BUTTON_LOGIN));
    $box_smarty->assign('FORM_END','</form>');
  }
  $box_newsletter = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_newsletter.html', $cache_id);
  $smarty->assign('box_NEWSLETTER',$box_newsletter);
?>