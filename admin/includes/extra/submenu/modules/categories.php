<?php 

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$submenutabactiv = '';
if ($set == 'categories') {
  $submenutabactiv = ' activ';
  $module_type = 'categories';
  $module_key = 'MODULE_'.strtoupper($module_type).'_INSTALLED';
  $module_directory = DIR_FS_ADMIN.DIR_WS_MODULES . $module_type.'/';
  $module_directory_include = DIR_FS_ADMIN.DIR_WS_MODULES . $module_type.'/';
  //define('HEADING_TITLE', 'Klassenerweiterungen "Backend categories"');
  $check_language_file = false;
}  
        
$mTypeArr[] = '<a class="submenutab'.$submenutabactiv.'" href="' . xtc_href_link(FILENAME_MODULES, 'set=categories') . '">' . 'categories' . '</a>';