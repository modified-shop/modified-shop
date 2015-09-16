<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2005 XT-Commerce & H.H.G. group
   (c) 2008 Hetfield - http://www.MerZ-IT-SerVice.de
   (c) 2014 web28 - http://www.rpa-com.de

   Released under the GNU General Public License
---------------------------------------------------------------------------------------*/

function xtc_wysiwyg($type, $lang, $langID = '',$addonType='') 
{

    $wysiwig_type = 'ckeditor';

    $filemanagerurl = DIR_WS_ADMIN. 'includes/modules/kcfinder/browse.php?opener=ckeditor&cms=modifiedshop&lang='.$lang;
    $js_src = DIR_WS_MODULES .'ckeditor/ckeditor.js';
    $file_path = '&opener=ckeditor&type=files';
    $image_path = '&opener=ckeditor&type=images';
    $flash_path = '&opener=ckeditor&type=flash';
    $media_path = '&opener=ckeditor&type=flash';

    $default_editor_width = '\'100%\''; //kama 850, moono 870;
    $default_editor_height = '400';

    $sid = ''; //'&'.session_name() . '=' . session_id();

    $view = '&view=thumbnail';

    $editor = '&editor='. $wysiwig_type;
    $language = '&language='. $_SESSION['language_code'];

    //Einrückung für Code
    $codetab = '            ';  

    //Custom config
    $customConfig = array();
    //$customConfig['customConfig'] = "customConfig : '../ckeditor/custom/ckeditor_config.js',";

    //extraPlugins
    $customConfig['extraPlugins'] = "extraPlugins: '',";
    
    //UTF-8 bzw keine Umwandlung in entities
    $customConfig['entities'] = "entities: false,";

    //CKEditor 4.1: Advanced Content Filter (ACF) - keine benutzerdefinierten Tags herausfiltern - Filter deaktivieren -> true
    $customConfig['allowedContent'] = "allowedContent: false,";
    
    //Buttons entfernen
    $customConfig['removeButtons'] = "removeButtons: 'PageBreak,Save',";
    
    //Upload Tab entfernen
    //$customConfig['removeDialogTabs'] = "removeDialogTabs: 'image:Link;link:Link',";
    
    //toolbarGroups
    $customConfig['toolbarGroups'] ="
    toolbarGroups : [
      { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
      { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
      { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
      { name: 'links' },
      { name: 'about' },
      /*{ name: 'forms' },*/
      '/',
      { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
      { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
      /*{ name: 'links' },*/
      { name: 'insert' },
      '/',
      { name: 'styles' },
      { name: 'colors' },
      { name: 'tools' },
      { name: 'others' }/*,
      { name: 'about' }*/
    ],";
    
    //Sprache aus Session
    $customConfig['language'] = 'language: "'.$_SESSION['language_code'].'",';

    //CSS Dateien aus template laden
    //$css_path = '../templates/'.CURRENT_TEMPLATE.'/stylesheet.css';
    //$css_path2 = '../templates/'.CURRENT_TEMPLATE.'/editor.css'; //Wichtig für Hintergrund: html,body definieren
    //$customConfig['contentsCss'] = "contentsCss: ['".$css_path."','".$css_path2."'],";

    //Smiley Path Frontend
    $customConfig['smiley_path'] =  "smiley_path : '".DIR_WS_CATALOG."images/smiley/',";
    
    //Filebrowser settings
    $filebrowser_settings = PHP_EOL .
                $codetab.'filebrowserBrowseUrl : "'.$filemanagerurl.$language.$editor.$flash_path.$sid.'",
                filebrowserImageBrowseUrl : "'.$filemanagerurl.$language.$editor.$image_path.$sid.$view.'",
                filebrowserFlashBrowseUrl : "'.$filemanagerurl.$language.$editor.$flash_path.$sid.'",
                filebrowserWindowWidth : "960",
                filebrowserWindowHeight : "640",';

    $add_init = '';
    $editorName = '';
    
    switch($type) {
        // WYSIWYG editor content manager textarea named cont
        case 'content_manager':
            $editorName = 'content_text['.$addonType.']['.$langID.']';
            $default_editor_height = 400;
            break;
            
        // WYSIWYG editor content manager products content section textarea named file_comment
        case 'products_content':
            $editorName = 'file_comment';
            $default_editor_height = 400;   
            break;
            
        // WYSIWYG editor categories_description textarea named categories_description[langID]
        case 'categories_description':
            $editorName = 'categories_description['.$langID.']';
            $default_editor_height = 300;
            break;
            
        // WYSIWYG editor products_description textarea named products_description_langID
        case 'products_description':
            $editorName = 'products_description['.$langID.']';
            $default_editor_height = 400;
            break;
        // WYSIWYG editor products short description textarea named products_short_description_langID
        case 'products_short_description':
            $editorName = 'products_short_description['.$langID.']';
            $default_editor_height = 300;
            break;
            
        // WYSIWYG editor newsletter textarea named newsletter_body
        case 'newsletter':
            $editorName = 'newsletter_body';
            $default_editor_height = 300;
            break;
                    
        // WYSIWYG editor mail textarea named message
        case 'mail':
            $editorName = 'message';
            $default_editor_height = 400;
            break;
                    
        // WYSIWYG editor gv_mail textarea named message
        case 'gv_mail':
            $editorName = 'message';
            $default_editor_height = 400;
            break;
                    
        // WYSIWYG editor offline_msg textarea named cont
        case 'shop_offline':
            $editorName = 'offline_msg';
            $default_editor_height = 400;
            break;
            
        // WYSIWYG editor categories_description textarea named manufacturers_description[langID]
        case 'manufacturers_description':
            $editorName = 'manufacturers_description['.$langID.']';
            $default_editor_height = 400;
            break;
    }
    
    $html = '';
    
    require_once(DIR_FS_INC.'auto_include.inc.php');
    foreach(auto_include(DIR_FS_CATALOG.'includes/extra/wysiwyg/','php') as $file) require ($file);
    
    $customConfig = implode(PHP_EOL.$codetab,$customConfig);
    
    if ($editorName != '') {
      $html .='
        CKEDITOR.replace( "'.$editorName.'",
        { '.$customConfig.$filebrowser_settings.'
          height: '.$default_editor_height.',
          width: '.$default_editor_width.'
        });
      ';
    }
    
    $html .=  $add_init ; 
    $html = wysiwyg_add_javascript($js_src,$html);
    return $html;
}

function editorJSLink($js_src)
{
    static $editorJSLinkCache;
    
    if (!isset ($editorJSLinkCache)) {
      $editorJSLinkCache =  PHP_EOL . '<script type="text/javascript" src="'.$js_src.'"></script>' . PHP_EOL;
    } else {
      $editorJSLinkCache = '';
    }
    return $editorJSLinkCache;
}

function wysiwyg_add_javascript($js_src,$html)
{
    $html = editorJSLink($js_src).
    '<script type="text/javascript">
      $(document).ready(function() {
       '.$html.'
      });
    </script>'. PHP_EOL;

    return $html;
}
?>