<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(conditions.php,v 1.21 2003/02/13); www.oscommerce.com
   (c) 2003 nextcommerce (shop_content.php,v 1.1 2003/08/19); www.nextcommerce.org
   (c) 2006 xt:Commerce (shop_content.php 148 2007-01-24); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
// create smarty elements
$smarty = new Smarty;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
//BOF - DokuMan - check for requested valid content, otherwise display FILENAME_DEFAULT
if (isset($_GET['coID']) && is_numeric($_GET['coID'])) {
//EOF - DokuMan - check for requested valid content, otherwise display FILENAME_DEFAULT

  if ($_GET['coID'] == 5) {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT, '', $request_type));
  }

  $content_body = ''; //DokuMan - set undefined variable
  $group_check = ''; //DokuMan - set undefined variable
  if (GROUP_CHECK == 'true') {
    $group_check = "and group_ids LIKE '%c_".$_SESSION['customers_status']['customers_status_id']."_group%'";
  }
  $shop_content_query = xtc_db_query("SELECT
                       content_id,
                       content_title,
                       content_heading,
                       content_text,
                       content_file
                       FROM ".TABLE_CONTENT_MANAGER."
                       WHERE content_group='".(int) $_GET['coID']."'"
                       .$group_check."
                       AND languages_id='".(int) $_SESSION['languages_id']."'");

  if ($shop_content_data = xtc_db_fetch_array($shop_content_query)) {
    $breadcrumb->add($shop_content_data['content_title'], xtc_href_link(FILENAME_CONTENT,'coID='.(int) $_GET['coID']));
  } else {
    $error = TEXT_CONTENT_NOT_FOUND;
    $shop_content_data['content_heading'] = TEXT_CONTENT_NOT_FOUND;
  }
  
  if ($_GET['coID'] != 7 || $_GET['coID'] == 7 && isset($_GET['action']) && $_GET['action'] == 'success') {
    require (DIR_WS_INCLUDES.'header.php');
  }

  $smarty->assign('CONTENT_HEADING', $shop_content_data['content_heading']);

  if ($_GET['coID'] == 7) {
    require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
    //BOF - web28 - 2010-04-03 - outsource email code
    include (DIR_WS_INCLUDES.'contact_us.php');
    //EOF - web28 - 2010-04-03 - outsource email code
  } else {
    if (!empty($shop_content_data['content_file'])) {
      ob_start();
      if (strpos($shop_content_data['content_file'], '.txt'))
        echo '<pre>';
      include (DIR_FS_CATALOG.'media/content/'.$shop_content_data['content_file']);
      if (strpos($shop_content_data['content_file'], '.txt'))
        echo '</pre>';
      $smarty->assign('file', ob_get_contents());
      ob_end_clean();

    } else {
      $content_body = $shop_content_data['content_text'];
    }
    $smarty->assign('CONTENT_BODY', $content_body);
    $smarty->assign('BUTTON_CONTINUE', '<a href="javascript:history.back(1)">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');

    // set cache ID
     if (!CacheCheck()) {
      $smarty->caching = 0;
      $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/content.html');
    } else {
      $smarty->caching = 1;
      $smarty->cache_lifetime = CACHE_LIFETIME;
      $smarty->cache_modified_check = CACHE_CHECK;
      //BOF - GTB - 2011-05-11 - change cache_id for Session Security
      //$cache_id = $_SESSION['language'].$shop_content_data['content_id'];
      $cache_id = $shop_content_data['content_id'].'_'.$_SESSION['customers_status']['customers_status_id'].'_'.$_SESSION['language'];
      //EOF - GTB - 2011-05-11 - change cache_id for Session Security
      $main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/content.html', $cache_id);
    }

  }
//BOF - DokuMan - check for requested valid content, otherwise display FILENAME_DEFAULT
} else {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
}
//EOF - DokuMan - check for requested valid content, otherwise display FILENAME_DEFAULT
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM')) {
  $smarty->load_filter('output', 'note');
}
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>