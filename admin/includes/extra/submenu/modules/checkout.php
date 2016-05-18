<?php 

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$submenutabactiv = '';
if ($set == 'checkout') {
  $submenutabactiv = ' activ';
  $module_type = 'checkout';
  $module_key = 'MODULE_'.strtoupper($module_type).'_INSTALLED';
  $module_directory = DIR_FS_CATALOG.DIR_WS_MODULES .$module_type.'/';
  $module_directory_include = DIR_FS_CATALOG.DIR_WS_MODULES .$module_type.'/';
  //define('HEADING_TITLE', 'Klassenerweiterungen "checkout"');
  $check_language_file = false;
}

$mTypeArr[] = '<a class="submenutab'.$submenutabactiv.'" href="' . xtc_href_link(FILENAME_MODULES, 'set=checkout') . '">' . 'checkout' . '</a>';