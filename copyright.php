<?php
/* -----------------------------------------------------------------------------------------
   $Id: copyright.php 3896 2012-11-11 16:16:21Z Tomcraft1980 $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  include ('includes/application_top.php');

  // include needed function
  require_once (DIR_FS_INC.'get_external_content.inc.php');

  $smarty = new Smarty;
  // include boxes
  require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
  
  $breadcrumb->add('modified eCommerce Shopsoftware', xtc_href_link('copyright.php'));
  
  require (DIR_WS_INCLUDES.'header.php');
    
  $smarty->assign('CONTENT_HEADING', '<div style="width:40px; height:100%; float:left;"><img src="http://images.modified-shop.org/copyright.gif" border="0" alt=""/></div>'.'modified eCommerce Shopsoftware');
  $main_content = get_external_content('http://www.modified-shop.org/copyright.php');
  if (!xtc_not_null($main_content)) {
    $main_content = '<a style="text-decoration:none;" href="http://www.modified-shop.org" target="_blank"><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></a><span style="color:#555555;">' . '&nbsp;' . '&copy;' . date('Y') . '&nbsp;' . 'provides no warranty and is redistributable under the </span><a style="color:#555555;text-decoration:none;" href="http://www.fsf.org/licensing/licenses/gpl.txt" target="_blank">GNU General Public License</a>';  
  }
  $smarty->assign('CONTENT_BODY', $main_content);
  $smarty->assign('BUTTON_CONTINUE', '<a href="javascript:history.back(1)">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
  $smarty->assign('language', $_SESSION['language']);
  
  // set cache ID
   if (!CacheCheck()) {
    $smarty->caching = 0;
    $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/content.html');
  } else {
    $smarty->caching = 1;
    $smarty->cache_lifetime = CACHE_LIFETIME;
    $smarty->cache_modified_check = CACHE_CHECK;
    $cache_id = $_SESSION['language'];
    $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/content.html', $cache_id);
  }
  
  $smarty->assign('language', $_SESSION['language']);
  $smarty->assign('main_content', $main_content);
  $smarty->caching = 0;
  if (!defined('RM'))
    $smarty->load_filter('output', 'note');
  $smarty->display(CURRENT_TEMPLATE.'/index.html');
  include ('includes/application_bottom.php');
?>