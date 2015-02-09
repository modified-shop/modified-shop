<?php
//Custom config
$customConfig = array();
//$customConfig['customConfig'] = "customConfig : '../ckeditor/custom/ckeditor_config.js',";

//skin
$customConfig['skin'] = "skin: 'moonocolor',";

//Spellchecker Autostart
$customConfig['scayt_autoStartup'] = "scayt_autoStartup: false,";

//extraPlugins
$customConfig['extraPlugins'] = "extraPlugins: '',";

//Buttons entfernen
//$customConfig['removeButtons'] = "removeButtons: 'Subscript,Superscript,PageBreak',";
$customConfig['removeButtons'] = "removeButtons: 'PageBreak',";

//Plugins entfernen
$customConfig['removePlugins'] = "removePlugins: 'smiley',";

//Diagloge einfacher gestalten
$customConfig['removeDialogTabs'] = "removeDialogTabs: 'image:advanced;link:advanced',";

//UTF-8 bzw keine Umwandlung in entities
$customConfig['entities'] = "entities: false,";

//CKEditor 4.1: Advanced Content Filter (ACF) - keine benutzerdefinierten Tags herausfiltern - Filter deaktivieren -> true
$customConfig['allowedContent'] = "allowedContent: false,";

//toolbarGroups
//Default Toolbar
/*
$customConfig['toolbarGroups'] = "
toolbarGroups : [
  { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
  { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
  { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
  { name: 'forms' },
  '/',
  { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
  { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
  { name: 'links' },
  { name: 'insert' },
  '/',
  { name: 'styles' },
  { name: 'colors' },
  { name: 'tools' },
  { name: 'others' },
  { name: 'about' }
],";
*/
//Simple Toolbar
$customConfig['toolbarGroups'] = "
toolbarGroups : [
  { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
  { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
  { name: 'editing',     groups: [ 'find', 'selection' ] },
  { name: 'links' },
  { name: 'about' },
  /*{ name: 'forms' },*/
  '/',
  { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
  { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
  { name: 'tools' },
  /*{ name: 'insert' },*/
  '/',
  { name: 'styles' },
  { name: 'colors' },
  { name: 'insert' },
  { name: 'others' },
],";

//Eingabeoption
$customConfig['enterMode'] = "enterMode: CKEDITOR.ENTER_BR,";
$customConfig['shiftEnterMode'] = "shiftEnterMode: CKEDITOR.ENTER_P,";

//Sprache aus Session
$customConfig['language'] = 'language: "'.$_SESSION['language_code'].'",';

//CSS Dateien aus template laden
//$css_path = '../templates/'.CURRENT_TEMPLATE.'/stylesheet.css';
//$css_path2 = '../templates/'.CURRENT_TEMPLATE.'/editor.css'; //Wichtig für Hintergrund: html,body definieren
//$customConfig['contentsCss'] = "contentsCss: ['".$css_path."','".$css_path2."'],";

//Smiley Path Frontend
$customConfig['smiley_path'] =  "smiley_path : '".DIR_WS_CATALOG."images/smiley/',";
?>