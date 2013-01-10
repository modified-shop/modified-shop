<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_wysiwyg.inc.php 2867 2012-05-14 11:57:08Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2005 XT-Commerce & H.H.G. group
   (c) 2008 Hetfield - http://www.MerZ-IT-SerVice.de

   Released under the GNU General Public License
---------------------------------------------------------------------------------------*/

function xtc_wysiwyg($type, $lang, $langID = '') {

  $js_src = DIR_WS_MODULES .'fckeditor/fckeditor.js';
  $path = DIR_WS_MODULES .'fckeditor/';
  $filemanager = DIR_WS_ADMIN.'fck_wrapper.php?Connector='.DIR_WS_MODULES . 'fckeditor/editor/filemanager/connectors/php/connector.php&ServerPath='. DIR_WS_CATALOG;
  $file_path = '&Type=File';
  $image_path = '&Type=Image';
  $flash_path = '&Type=Flash';
  $media_path = '&Type=Media';

  $sid = '&'.session_name() . '=' . session_id(); //web28 security fix
  switch($type) {
    // WYSIWYG editor content manager textarea named cont
    case 'content_manager':
      $val ='<script type="text/javascript" src="'.$js_src.'"></script>
             <script type="text/javascript">
               window.onload = function() {
                 var oFCKeditor = new FCKeditor( \'cont\', \'100%\', \'400\'  ) ;
                 oFCKeditor.BasePath = "'.$path.'" ;
                 oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
                 oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
                 oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
                 oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
                 oFCKeditor.Config["AutoDetectLanguage"] = false ;
                 oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
                 oFCKeditor.ReplaceTextarea() ;
               }
             </script>';
      break;
    // WYSIWYG editor content manager products content section textarea named file_comment
    case 'products_content':
      $val ='<script type="text/javascript" src="'.$js_src.'"></script>
             <script type="text/javascript">
               window.onload = function() {
                 var oFCKeditor = new FCKeditor( \'file_comment\', \'100%\', \'400\'  ) ;
                 oFCKeditor.BasePath = "'.$path.'" ;
                 oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
                 oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
                 oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
                 oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
                 oFCKeditor.Config["AutoDetectLanguage"] = false ;
                 oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
                 oFCKeditor.ReplaceTextarea() ;
               }
             </script>';
      break;
    // WYSIWYG editor categories_description textarea named categories_description[langID]
    case 'categories_description':
      $val ='var oFCKeditor = new FCKeditor( \'categories_description['.$langID.']\', \'100%\', \'300\' ) ;
             oFCKeditor.BasePath = "'.$path.'" ;
             oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
             oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
             oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
             oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
             oFCKeditor.Config["AutoDetectLanguage"] = false ;
             oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
             oFCKeditor.ReplaceTextarea() ;
             ';
      break;
    // WYSIWYG editor products_description textarea named products_description_langID
    case 'products_description':
      $val ='var oFCKeditor = new FCKeditor( \'products_description_'.$langID.'\', \'100%\', \'400\'  ) ;
             oFCKeditor.BasePath = "'.$path.'" ;
             oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
             oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
             oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
             oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
             oFCKeditor.Config["AutoDetectLanguage"] = false ;
             oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
             oFCKeditor.ReplaceTextarea() ;
             ';
      break;
    // WYSIWYG editor products short description textarea named products_short_description_langID
    case 'products_short_description':
      $val ='var oFCKeditor = new FCKeditor( \'products_short_description_'.$langID.'\', \'100%\', \'300\'  ) ;
             oFCKeditor.BasePath = "'.$path.'" ;
             oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
             oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
             oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
             oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
             oFCKeditor.Config["AutoDetectLanguage"] = false ;
             oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
             oFCKeditor.ReplaceTextarea() ;
             ';
      break;
    // WYSIWYG editor newsletter textarea named newsletter_body
    case 'newsletter':
      $val ='<script type="text/javascript" src="'.$js_src.'"></script>
             <script type="text/javascript">
               window.onload = function() {
                 var oFCKeditor = new FCKeditor( \'newsletter_body\', \'700\', \'400\'  ) ;
                 oFCKeditor.BasePath = "'.$path.'" ;
                 oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
                 oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
                 oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
                 oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
                 oFCKeditor.Config["AutoDetectLanguage"] = false ;
                 oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
                 oFCKeditor.ReplaceTextarea() ;
               }
             </script>';
      break;
    // WYSIWYG editor mail textarea named message
    case 'mail':
      $val ='<script type="text/javascript" src="'.$js_src.'"></script>
             <script type="text/javascript">
               window.onload = function() {
                 var oFCKeditor = new FCKeditor( \'message\', \'700\', \'400\' ) ;
                 oFCKeditor.BasePath = "'.$path.'" ;
                 oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
                 oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
                 oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
                 oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
                 oFCKeditor.Config["AutoDetectLanguage"] = false ;
                 oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
                 oFCKeditor.ReplaceTextarea() ;
               }
             </script>';
      break;
    // WYSIWYG editor gv_mail textarea named message
    case 'gv_mail':
      $val ='<script type="text/javascript" src="'.$js_src.'"></script>
             <script type="text/javascript">
               window.onload = function() {
                 var oFCKeditor = new FCKeditor( \'message\', \'700\', \'400\' ) ;
                 oFCKeditor.BasePath = "'.$path.'" ;
                 oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
                 oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
                 oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
                 oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
                 oFCKeditor.Config["AutoDetectLanguage"] = false ;
                 oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
                 oFCKeditor.ReplaceTextarea() ;
               }
             </script>';
      break;
    // WYSIWYG editor shop offline
    case 'shop_offline':
      $val ='<script type="text/javascript" src="'.$js_src.'"></script>
             <script type="text/javascript">
               window.onload = function() {
                 var oFCKeditor = new FCKeditor( \'offline_msg\', \'800\', \'400\' ) ;
                 oFCKeditor.BasePath = "'.$path.'" ;
                 oFCKeditor.Config["LinkBrowserURL"] = "'.$filemanager.$file_path.$sid.'" ;
                 oFCKeditor.Config["ImageBrowserURL"] = "'.$filemanager.$image_path.$sid.'" ;
                 oFCKeditor.Config["FlashBrowserURL"] = "'.$filemanager.$flash_path.$sid.'" ;
                 oFCKeditor.Config["MediaBrowserURL"] = "'.$filemanager.$media_path.$sid.'" ;
                 oFCKeditor.Config["AutoDetectLanguage"] = false ;
                 oFCKeditor.Config["DefaultLanguage"] = "'.$lang.'" ;
                 oFCKeditor.ReplaceTextarea() ;
               }
             </script>';
      break;

  }
  return $val;
}
?>