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
define('DIR_TMPL_JS', DIR_TMPL.'javascript/');
// this javascriptfile get includes at the TOP of every template page in shop
// you can add your template specific js scripts here
?>
<script type="text/javascript">var DIR_WS_BASE="<?php echo DIR_WS_BASE ?>"</script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery-1.8.3.min.js" type="text/javascript"></script>
