<?php
/*-----------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------
   based on: (c) 2003 - 2006 XT-Commerce (general.js.php)
  -----------------------------------------------------------
   Released under the GNU General Public License
  -----------------------------------------------------------*/
   
  // this javascriptfile get includes at the BOTTOM of every template page in shop
  // you can add your template specific js scripts here
  defined('DIR_TMPL') OR define('DIR_TMPL', Template::url(''));
  defined('DIR_TMPL_JS') OR define('DIR_TMPL_JS', DIR_TMPL.'javascript/');
?>

<script src="<?php echo Template::url('javascript/jquery.min.js'); ?>" type="text/javascript"></script>
<?php
$script_array = array(
  'templates/' . Template::resolve('javascript/jquery.colorbox.min.js'),
  'templates/' . Template::resolve('javascript/jquery.lazysizes.min.js'),
  'templates/' . Template::resolve('javascript/jquery.bxslider.min.js'),
  'templates/' . Template::resolve('javascript/jquery.easyTabs.js'),
  'templates/' . Template::resolve('javascript/jquery.alertable.min.js'),
);
$script_min = DIR_TMPL_JS.'tpl_plugins.min.js';
  
$this_f_time = filemtime(Template::path('javascript/general_bottom.js.php'));
  
if (COMPRESS_JAVASCRIPT == 'true') {
  require_once(Template::path('source/inc/combine_files.inc.php'));
  $script_array = combine_files($script_array,$script_min,false,$this_f_time);
}

foreach ($script_array as $script) {
  $script .= strpos($script,$script_min) === false ? '?v=' . filemtime(DIR_FS_CATALOG.$script) : '';
  echo '<script src="'.DIR_WS_BASE.$script.'" type="text/javascript"></script>'.PHP_EOL;
}

ob_start();
foreach(auto_include(Template::path('javascript/extra/'),'php') as $file) require ($file);
$javascript = ob_get_clean();
if (COMPRESS_JAVASCRIPT == 'true') {
  require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
  $compactor = new Compactor(array('strip_php_comments' => false, 'compress_css' => false, 'compress_scripts' => true));
  $javascript = $compactor->squeeze($javascript);
}
echo $javascript.PHP_EOL;

if (basename($PHP_SELF) == FILENAME_CONTENT && isset($_GET['coID']) && $_GET['coID'] == 8) {
?>
<!--[if lt IE 10]>
<script src="<?php echo Template::url('javascript/jquery.css3-multi-column.js'); ?>"></script>
<![endif]-->
<?php 
}
?>
