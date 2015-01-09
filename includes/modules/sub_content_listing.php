<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$shop_content_smarty = new Smarty;
$shop_content_smarty->assign('tpl_path', DIR_WS_BASE . 'templates/'.CURRENT_TEMPLATE.'/');
                                                  
$shop_content_query = xtDBquery("SELECT content_title, 
                                        content_group
                                   FROM ".TABLE_CONTENT_MANAGER."
                                  WHERE parent_id = '".$shop_content_data['content_id']."'
                                        ".CONTENT_CONDITIONS."
                                    AND content_status = '1'
                                    AND content_active = '1'
                                    AND languages_id = '".$_SESSION['languages_id']."'
                               ORDER BY sort_order");

if (xtc_db_num_rows($shop_content_query, true) > 0) {
  $sub_content = array();
  while ($shop_content = xtc_db_fetch_array($shop_content_query, true)) {
    $sub_content[] = array('CONTENT_TITLE' => $shop_content['content_title'],
                           'CONTENT_LINK' => xtc_href_link(FILENAME_CONTENT, 'coID='.$shop_content['content_group'], 'NONSSL')
                           );
  }

  $shop_content_smarty->assign('sub_content', $sub_content);
  $shop_content_smarty->assign('language', $_SESSION['language']);
  $shop_content_smarty->caching = 0;
  $sub_content_listing = $shop_content_smarty->fetch(CURRENT_TEMPLATE.'/module/sub_content_listing.html');

  $smarty->assign('SUB_CONTENT_LISTING', $sub_content_listing);
}
?>