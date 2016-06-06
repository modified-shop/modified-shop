<?php
/*-----------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------
   based on: (c) 2003 - 2006 XT-Commerce (general.js.php)
  -----------------------------------------------------------
   Released under the GNU General Public License
   -----------------------------------------------------------
*/
// this javascriptfile get includes at the BOTTOM of every template page in shop
// you can add your template specific js scripts here
$script_array = array(
  DIR_TMPL_JS.'thickbox.js',
  DIR_TMPL_JS.'jquery.alerts.min.js',
);
$script_min = DIR_TMPL_JS.'tpl_plugins.min.js';
  
if (COMPRESS_JAVASCRIPT == 'true') {
  require_once(DIR_FS_BOXES_INC.'combine_files.inc.php');
  $script_array = combine_files($script_array,$script_min,false);
}

foreach ($script_array as $script) {
  echo '<script src="'.DIR_WS_BASE.$script.'" type="text/javascript"></script>'.PHP_EOL;
}
?>

<script type="text/javascript">
  /*BOC jQuery Alerts*/
  $.alerts.overlayOpacity = .2;
  $.alerts.overlayColor = '#000';
  function alert(message, title) {
    title = title || 'Information';
    jAlert(message, title);
  }
  /*EOC jQuery Alerts*/
</script>

<?php if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) { // TABS/ACCORDION in product_info - web28 ?>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery-ui.js" type="text/javascript"></script>
<script type="text/javascript">
/* <![CDATA[ */
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/javascript.css", function(css) {
    $("head").append("<style type='text/css'>"+css+"<\/style>");
  });
  $(function() {
    $("#tabbed_product_info").tabs();
    $("#accordion_product_info").accordion({ autoHeight: false });
  });
/*]]>*/
</script>
<?php } // TABS/ACCORDION in product_info - web28 ?>

<?php require DIR_FS_CATALOG . DIR_TMPL_JS . 'get_states.js.php'; // Ajax State/District/Bundesland Updater - h-h-h ?>

