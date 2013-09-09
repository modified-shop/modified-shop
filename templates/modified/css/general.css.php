<?php
/* -----------------------------------------------------------------------------------------
   $Id: general.css.php 151 2012-12-07 09:43:36Z Markus $

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2010 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   // Put CSS-Definitions here, these CSS-files will be loaded at the TOP of every page
?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/stylesheet.css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/colorbox.css" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery.alerts.css" />

<?php
  if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) {
?>
  <link rel="stylesheet" type="text/css" media="screen" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/jquery-ui.css" />
<?php
  //BOF - DokuMan - 2011-05-12 - load jQuery-UI CSS from faster Google CDN
  //echo '<link rel="stylesheet" type="text/css" media="screen" href="http' . (($request_type=='SSL')?'s':'') . '://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/smoothness/jquery-ui.css" />';
  //EOF - DokuMan - 2011-05-12 - load jQuery-UI CSS from faster Google CDN
}
?>  
  
<?php
if ($_SESSION['customers_status']['customers_status_id'] == 0) {
  ?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>/css/toggle.css" />
  <?php
  }
?>