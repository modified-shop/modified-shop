<?php
/* ----------------------------------------------------------------------------
   $Id$
 
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------
  based on:
  (c) 2003 netz-designer
  (c) 2006 XT-Commerce

  Released under the GNU General Public License 
  ----------------------------------------------------------------------------*/

require ('includes/application_top.php');

require (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/cc.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<SCRIPT type="text/javascript">
<!-- 
// prevent click click
document.oncontextmenu = function(){return false}
if(document.layers) {
window.captureEvents(Event.MOUSEDOWN);
window.onmousedown = function(e){
if(e.target==document)return false;
}
}
else {
document.onmousedown = function(){return false}
}

var i=0;
function resize() {
  if (navigator.appName == 'Netscape') i=40;
  window.resizeTo(480, 460-i);
   self.focus();
}

//-->
</SCRIPT>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" /> 
<meta http-equiv="Content-Style-Type" content="text/css" />
<?php /*
//BOF - GTB - 2010-08-03 - Security Fix - Base
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
//EOF - GTB - 2010-08-03 - Security Fix - Base
*/ ?>
<title><?php echo TITLE; ?></title>
<?php /*
//BOF - GTB - 2010-08-03 - Security Fix - Base
<link rel="stylesheet" type="text/css" href="<?php echo 'templates/'.CURRENT_TEMPLATE.'/stylesheet.css'; ?>" />
*/ ?>
<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/stylesheet.css'; ?>" />
<?php /*
//EOF - GTB - 2010-08-03 - Security Fix - Base
*/ ?>
</head>

<style type="text/css"><!--
BODY { margin-bottom: 10px; margin-left: 10px; margin-right: 10px; margin-top: 10px; }
//--></style>

<body onload="resize();">
<?php

$info_box_contents = array ();
$info_box_contents[] = array ('align' => 'left', 'text' => HEADING_CVV);

new infoBoxHeading($info_box_contents);

$info_box_contents = array ();
$info_box_contents[] = array ('align' => 'left', 'text' => TEXT_CVV);

new infoBox($info_box_contents);
?>

<p class="smallText" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CLOSE_WINDOW . '</a>'; ?></p>

</body>
</html>

