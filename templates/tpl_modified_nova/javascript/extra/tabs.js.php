<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
  ?>
  <script>  
    $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css?v=<?php echo filemtime(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/jquery.easyTabs.css'); ?>", function(css) {
      $("head").append("<style type='text/css'>"+css+"<\/style>");
    });
    // started tabs have the .resp-accordion elements of the plugin
    function initTabs(root) {
      $(root).find('#horizontalTab:not(:has(.resp-accordion))').easyResponsiveTabs({
        type: 'default' //Types: default, vertical, accordion
      });
      $(root).find('#horizontalAccordion:not(:has(.resp-accordion))').easyResponsiveTabs({
        type: 'accordion' //Types: default, vertical, accordion
      });
    }
    $(document).ready(function () {
      initTabs(document);
    });
    // content that a script has inserted into the page
    document.addEventListener('modified:content-updated', function (event) {
      initTabs(event.detail.root);
    });
  </script>
  <?php
}

if (strpos($PHP_SELF, 'checkout')) {
  ?>
  <script>  
    $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css?v=<?php echo filemtime(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/css/jquery.easyTabs.css'); ?>", function(css) {
      $("head").append("<style type='text/css'>"+css+"<\/style>");
    });
    $(document).ready(function () {
      $('#horizontalAccordion').easyResponsiveTabs({
        type: 'accordion', //Types: default, vertical, accordion     
        closed: true,     
        activate: function(event) { // Callback function if tab is switched
          $(".resp-tab-active input[type=radio]").prop('checked', true);
        }
      });

      $('.cus_check_gift label').on('click', function() {
        $('#rd-cot_gv').prop('checked', !$('#rd-cot_gv').prop('checked'));
      });
   
      $('#horizontalTab').easyResponsiveTabs({
        type: 'default' //Types: default, vertical, accordion           
      });
    });
    $('#button_checkout_confirmation').on('click',function() {
      $('.cssButtonPos12').hide();
    });
  </script>
  <?php
}
