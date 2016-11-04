<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
 -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (products_media.php,v 1.8 2003/08/25); www.nextcommerce.org
   (c) 2003 XT-Commerce (products_media.php 1259 2005-09-29 16:11:19Z mz)

   Released under the GNU General Public License
 ---------------------------------------------------------------------------------------*/

//get downloads
$content_query = xtDBquery("SELECT content_id, 
                                   content_name, 
                                   content_link, 
                                   content_file, 
                                   content_read, 
                                   file_comment
                              FROM ".TABLE_PRODUCTS_CONTENT."
                             WHERE products_id = '".$product->data['products_id']."'
                                   ".CONTENT_CONDITIONS."
                               AND languages_id = '".(int) $_SESSION['languages_id']."'");

if (xtc_db_num_rows($content_query, true) > 0) {

  // include needed functions
  require_once (DIR_FS_INC.'xtc_filesize.inc.php');

  $module_smarty = new Smarty;
  $module_content = array ();

  while ($content_data = xtc_db_fetch_array($content_query, true)) {
    
    $icon = xtc_image(DIR_WS_ICONS.'filetype/icon_link.gif');
    $filename = ($content_data['content_link'] != '') ? '<a href="'.$content_data['content_link'].'" target="_blank">'.$content_data['content_name'].'</a>' : $content_data['content_name'];
    
    $button = '';
    $filesize = '';
    if ($content_data['content_link'] == '') {
      $allowed_content_types = array('html','htm','txt','bmp','jpg','jpeg','gif','png','tif');
      $content_file_parts = explode('.', $content_data['content_file']);
      $content_file_type = end($content_file_parts);
      if (!is_file(DIR_WS_ICONS.'filetype/icon_'.$content_file_type.'.gif')) {
        $content_file_type = 'link';
      }
      $icon = xtc_image(DIR_WS_ICONS.'filetype/icon_'.$content_file_type.'.gif');
      if (in_array($content_file_type,$allowed_content_types)) {
        $btnlink_parameters = defined('TPL_POPUP_CONTENT_LINK_PARAMETERS') ? TPL_POPUP_CONTENT_LINK_PARAMETERS : POPUP_CONTENT_LINK_PARAMETERS;
        $btnlink_class = defined('TPL_POPUP_CONTENT_LINK_CLASS') ? TPL_POPUP_CONTENT_LINK_CLASS : POPUP_CONTENT_LINK_CLASS;
        $button = '<a target="_blank"'.
                  ' href="'.xtc_href_link(FILENAME_MEDIA_CONTENT, 'coID='.$content_data['content_id'].$btnlink_parameters).'"'.
                  ' class="'.$btnlink_class.'">'.
                  xtc_image_button('button_view.gif', TEXT_VIEW).
                  '</a>';
        $filesize = xtc_filesize($content_data['content_file']);
      } elseif ($content_data['content_file'] != '') {
        $button = '<a target="_blank"'.
                  ' href="'.xtc_href_link('media/products/'.$content_data['content_file']).'">'.
                  xtc_image_button('button_download.gif', TEXT_DOWNLOAD).
                  '</a>';
        $filesize = xtc_filesize($content_data['content_file']);
      }
    }
    $module_content[] = array (
        'ICON' => $icon,
        'FILENAME' => $filename,
        'DESCRIPTION' => $content_data['file_comment'],
        'FILESIZE' => $filesize,
        'BUTTON' => $button,
        'HITS' => $content_data['content_read']
      );
  }

  $module_smarty->assign('language', $_SESSION['language']);
  $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  $module_smarty->assign('module_content', $module_content);
  $module_smarty->caching = 0;
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_media.html');

  $info_smarty->assign('MODULE_products_media', $module);
}
?>