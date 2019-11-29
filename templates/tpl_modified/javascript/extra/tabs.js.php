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
<?php if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) { ?>
<script>  
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css", function(css) {
    $("head").append("<style type='text/css'>"+css+"<\/style>");
  });
  $(document).ready(function () {
    $('#horizontalTab').easyResponsiveTabs({
      type: 'default' //Types: default, vertical, accordion           
    });
    $('#horizontalAccordion').easyResponsiveTabs({
      type: 'accordion' //Types: default, vertical, accordion           
    });
  });
</script>
<?php } ?>
<?php if (strstr($PHP_SELF, 'checkout')) { ?>
<script>  
  $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css", function(css) {
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

    $('.cus_check_gift label').click(function() {
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
<?php } ?>
