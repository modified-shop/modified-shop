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
define('DIR_TMPL_JS', 'templates/'.CURRENT_TEMPLATE. '/javascript/');

// this javascriptfile get includes at the BOTTOM of every template page in shop
// you can add your template specific js scripts here
?>
<script type="text/javascript">var DIR_WS_BASE="<?php echo DIR_WS_BASE ?>"</script>

<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>easyResponsiveTabs.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>thickbox.js" type="text/javascript"></script>


<?php require DIR_FS_CATALOG . DIR_TMPL_JS . 'get_states.js.php'; // Ajax State/District/Bundesland Updater - h-h-h ?>