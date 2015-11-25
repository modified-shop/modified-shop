<?php 

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$submenutabactiv = '';
if ($set == 'product') {
  $submenutabactiv = ' activ';
  $module_type = 'product';
  $module_directory = DIR_FS_CATALOG.'includes/extra/classes/product/';
  $module_directory_include = DIR_WS_CATALOG.DIR_WS_MODULES . 'product/';
  $module_key = 'MODULE_PRODUCT_INSTALLED';
  //define('HEADING_TITLE', 'Klassenerweiterungen "product"');
  $check_language_file = false;
}

$mTypeArr[] = '<a class="submenutab'.$submenutabactiv.'" href="' . xtc_href_link(FILENAME_MODULES, 'set=product') . '">' . 'product' . '</a>';