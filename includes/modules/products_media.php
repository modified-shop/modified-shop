<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (products_media.php,v 1.8 2003/08/25); www.nextcommerce.org
   (c) 2006 xt:Commerce (cross_selling.php 1243 2005-09-25); www.xt-commerce.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $module_smarty = new Smarty;
  //BOF - GTB - 2010-08-03 - Security Fix - Base
  $module_smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  //$module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
  //EOF - GTB - 2010-08-03 - Security Fix - Base
  $module_content = array ();
  $filename = '';
  $group_check = '';

  // check if allowed to see
  $check_query = xtDBquery("SELECT DISTINCT products_id
                                      FROM ".TABLE_PRODUCTS_CONTENT."
                                      WHERE languages_id='".(int) $_SESSION['languages_id']."'");

  $check_data = array ();
  $i = '0';
  while ($content_data = xtc_db_fetch_array($check_query,true)) {
    $check_data[$i] = $content_data['products_id'];
    $i ++;
  }
  if (in_array($product->data['products_id'], $check_data)) {
    // get content data
    require_once (DIR_FS_INC.'xtc_filesize.inc.php');

    if (GROUP_CHECK == 'true') {
      $group_check = "and group_ids LIKE '%c_".$_SESSION['customers_status']['customers_status_id']."_group%'";
    }
    //get download
    $content_query = xtDBquery("SELECT content_id,
                                      content_name,
                                      content_link,
                                      content_file,
                                      content_read,
                                      file_comment
                                      FROM ".TABLE_PRODUCTS_CONTENT."
                                      WHERE products_id='".$product->data['products_id']."'
                                      ".$group_check."
                                      and languages_id='".(int) $_SESSION['languages_id']."'");

    while ($content_data = xtc_db_fetch_array($content_query,true)) {
      $filename = '';
      if ($content_data['content_link'] != '') {
        $icon = xtc_image(DIR_WS_CATALOG.'images/icons/icon_link.gif'); //DokuMan - 2011-09-06 - move images from admin directory to public images/icons/ directory
      } else {
        $icon = xtc_image(DIR_WS_CATALOG.'images/icons/icon_'.str_replace('.', '', strstr($content_data['content_file'], '.')).'.gif'); //DokuMan - 2011-09-06 - move images from admin directory to public images/icons/ directory
      }

      if ($content_data['content_link'] != '') {
        $filename = '<a href="'.$content_data['content_link'].'" target="new">';
      }
      $filename .= $content_data['content_name'];
      if ($content_data['content_link'] != '') {
        $filename .= '</a>';
      }
      $button = '';
      if ($content_data['content_link'] == '') {
        if (preg_match('/.html/i', $content_data['content_file'])
          or preg_match('/.htm/i', $content_data['content_file'])
          or preg_match('/.txt/i', $content_data['content_file'])
          or preg_match('/.bmp/i', $content_data['content_file'])
          or preg_match('/.jpg/i', $content_data['content_file'])
          or preg_match('/.gif/i', $content_data['content_file'])
          or preg_match('/.png/i', $content_data['content_file'])
          or preg_match('/.tif/i', $content_data['content_file'])) { // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3

          $button = '<a style="cursor:pointer" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_MEDIA_CONTENT, 'coID='.$content_data['content_id']).'\', \'popup\', \'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=640, height=600\')">'.xtc_image_button('button_view.gif', TEXT_VIEW).'</a>'; // Tomcraft - 2010-04-03 - unified popups with scrollbars and make them resizable

        } else {
          $button = '<a href="'.xtc_href_link('media/products/'.$content_data['content_file']).'" target="_blank">'.xtc_image_button('button_download.gif', TEXT_DOWNLOAD).'</a>';
        }
      }
      $module_content[] = array (
                            'ICON' => $icon,
                            'FILENAME' => $filename,
                            'DESCRIPTION' => $content_data['file_comment'],
                            'FILESIZE' => xtc_filesize($content_data['content_file']),
                            'BUTTON' => $button,
                            'HITS' => $content_data['content_read']
                            );
    }

    $module_smarty->assign('language', $_SESSION['language']);
    $module_smarty->assign('module_content', $module_content);
    // set cache ID
    $module_smarty->caching = 0;
    $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/products_media.html');

    $info_smarty->assign('MODULE_products_media', $module);
  }
?>