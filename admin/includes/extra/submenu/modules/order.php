<?php 

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$submenutabactiv = '';
if ($set == 'order') {
  $submenutabactiv = ' activ';
  $module_type = 'order';
  $module_key = 'MODULE_'.strtoupper($module_type).'_INSTALLED';
  $module_directory = DIR_FS_CATALOG.DIR_WS_MODULES .$module_type.'/';
  $module_directory_include = DIR_FS_CATALOG.DIR_WS_MODULES .$module_type.'/';
  //define('HEADING_TITLE', 'Klassenerweiterungen "order"');
  $check_language_file = false;
}

$mTypeArr[] = '<a class="submenutab'.$submenutabactiv.'" href="' . xtc_href_link(FILENAME_MODULES, 'set=order') . '">' . 'order' . '</a>';