<?php 

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$submenutabactiv = '';
if ($set == 'xtcPrice') {
  $submenutabactiv = ' activ';
  $module_type = 'xtcPrice';
  $module_directory = DIR_FS_CATALOG.'includes/extra/classes/'.$module_type.'/';
  $module_directory_include = DIR_FS_CATALOG.DIR_WS_MODULES .$module_type.'/';
  $module_key = 'MODULE_PRICE_INSTALLED';
  //define('HEADING_TITLE', 'Klassenerweiterungen "xtcPrice"');
  $check_language_file = false;
}  
        
$mTypeArr[] = '<a class="submenutab'.$submenutabactiv.'" href="' . xtc_href_link(FILENAME_MODULES, 'set=xtcPrice') . '">' . 'xtcPrice' . '</a>';