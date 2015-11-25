<?php 

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$submenutabactiv = '';
if ($set == 'categories') {
  $submenutabactiv = ' activ';
  $module_type = 'categories';
  $module_directory = DIR_FS_ADMIN.'includes/extra/classes/categories/';
  $module_directory_include = DIR_FS_ADMIN.DIR_WS_MODULES . 'categories/';
  $module_key = 'MODULE_CATEGORIES_INSTALLED';
  //define('HEADING_TITLE', 'Klassenerweiterungen "Backend categories"');
  $check_language_file = false;
}  
        
$mTypeArr[] = '<a class="submenutab'.$submenutabactiv.'" href="' . xtc_href_link(FILENAME_MODULES, 'set=categories') . '">' . 'categories' . '</a>';