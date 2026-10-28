<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<?php if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) { ?>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery-ui.js" type="text/javascript"></script>
<script type="text/javascript">
/* <![CDATA[ */
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/javascript.css", function(css) {
    $("head").append("<style type='text/css'>"+css+"<\/style>");
  });
  function initTabs(root) {
    $(root).find("#tabbed_product_info:not(.ui-tabs)").tabs();
    $(root).find("#accordion_product_info:not(.ui-accordion)").accordion({ autoHeight: false });
  }
  $(function() {
    initTabs(document);
  });
  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function(event) {
    initTabs(event.detail.root);
  });
/*]]>*/
</script>
<?php }
