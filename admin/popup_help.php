<?php
 /* -----------------------------------------------------------------------------------------
   $Id: popup_help.php 3456 2012-08-21 14:25:08Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
$_GET['lng'] = encode_htmlspecialchars($_GET['lng']);
$_GET['type'] = encode_htmlspecialchars($_GET['type']);
$_GET['modul'] = encode_htmlspecialchars($_GET['modul']);

require('includes/configure.php');
include(DIR_FS_LANGUAGES . $_GET['lng'] . '/modules/' . $_GET['type'] . '/' . $_GET['modul'] . '.php');
if (defined('MODULE_'.strtoupper($_GET['type']).'_'.str_replace('OT_','',strtoupper($_GET['modul'])).'_HELP_TEXT')) {
  $const= constant('MODULE_'.strtoupper($_GET['type']).'_'.str_replace('OT_','',strtoupper($_GET['modul'])).'_HELP_TEXT');
}
?>
<html>
<head>
<title>Hilfe/Help</title>
<link rel="stylesheet" type="text/css" href="includes/popup_help.css">
</head>
<body>
<div style="width:97%; padding:10px;">
  <?php 
  if (isset($const)) {
    echo $const; 
  }
  ?>
</div>
<div style="width:97%; padding:10px; text-align:center;">
  <input type="button" value="Close Window" onclick="window.close()">
</div>
</body>
</html>