<?php
/*-------------------------------
Custom config CKEDITOR

Hier können spezielle Einstellungen für den CKEDITOR definiert werden.
Alle $customConfig Einstellungen in der inc/xtc_wysiwig.inc.php können hier 
überschrieben und/oder neue Definitionen angelegt werden.
---------------------------------*/

//Custom Config Datei mit Einstellungen, die Datei muss sich in dem angegebenen Ordner befinden
//$customConfig['customConfig'] = "customConfig : '../ckeditor/custom/ckeditor_config.js',";

//skin  - muss für jede CKEditor Version separat aktualisiert werden
$customConfig['skin'] = "skin: '".(defined('WYSIWYG_SKIN') ? WYSIWYG_SKIN : moonocolor)."',";

//extraPlugins
//$customConfig['extraPlugins'] = "extraPlugins: '',";

//Plugins entfernen
//$customConfig['removePlugins'] = "removePlugins: '',";

//Eingabeoptionen
$customConfig['enterMode'] = "enterMode: CKEDITOR.ENTER_BR,";
$customConfig['shiftEnterMode'] = "shiftEnterMode: CKEDITOR.ENTER_P,";

?>