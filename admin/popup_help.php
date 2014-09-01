<?php
 /* -----------------------------------------------------------------------------------------
   $Id: popup_help.php 4600 2013-04-10 13:02:31Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require('includes/configure.php');

$valid_signs = '/[^\w\-]/';
$_GET['lng'] = preg_replace($valid_signs, '', $_GET['lng']);
$_GET['type'] = preg_replace($valid_signs, '', $_GET['type']);
$_GET['modul'] = preg_replace($valid_signs, '', $_GET['modul']);

include(DIR_FS_LANGUAGES . $_GET['lng'] . '/modules/' . $_GET['type'] . '/' . $_GET['modul'] . '.php');

if (defined(strtoupper('MODULE_'.$_GET['type'].'_'.str_ireplace('OT_','',$_GET['modul']).'_HELP_TEXT'))) {
  $const= constant(strtoupper('MODULE_'.$_GET['type'].'_'.str_ireplace('OT_','',$_GET['modul']).'_HELP_TEXT'));
} else {
  die( 'No help file found!' );
}
?>
<html>
<head>
 <title>Hilfe/Help</title>
 <link rel="stylesheet" type="text/css" href="includes/popup_help.css">
 <meta name="robots" content="noindex" />
</head>
<body>
 <div style="width:97%; padding:10px;">
  <?php echo (isset($const) ? $const : ''); ?>
 </div>
 <div style="width:97%; padding:10px; text-align:center;">
  <input type="button" value="Close Window" onclick="window.close()">
 </div>
</body>
</html>