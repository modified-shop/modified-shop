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
?>

<script type="text/javascript">var DIR_WS_BASE="<?php echo DIR_WS_BASE ?>"</script>

<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.easyTabs.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.colorbox.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.unveil.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.alerts.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.bxslider.min.js" type="text/javascript"></script>

<script type="text/javascript">
  $(document).ready(function(){
    $(".cbimages").colorbox({rel:'cbimages', scalePhotos:true, maxWidth: "90%", maxHeight: "90%"});
    $(".iframe").colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%"});

    $(".unveil").show();
    $(".unveil").unveil();

    $('.bxcarousel_bestseller').bxSlider({
      minSlides: 6,
      maxSlides: 8,
      pager: true,
      slideWidth: 109,
      slideMargin: 18
    });
      
  });
</script>



<script type="text/javascript">
  /*BOC jQuery Alerts*/
  $.alerts.overlayOpacity = .2;
  $.alerts.overlayColor = '#000';
  function alert(message, title) {
    title = title || 'Information';
    jAlert(message, title);
  }
  /*EOC jQuery Alerts*/
	
	/* BOC jQuery Shopping Cart */
  $(function() {
    $('#toggle_cart').click(function() {
      $('.toggle_cart').slideToggle('slow');
      return false;
    });

    $("html").not('.toggle_cart').bind('click',function(e) {
      $('.toggle_cart').slideUp('slow');
    });
  });     
	/* EOC jQuery Shopping Cart */

</script>

<?php if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) { ?>
<script type="text/javascript">
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
<script type="text/javascript">
    $.get("<?php echo DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE; ?>"+"/css/jquery.easyTabs.css", function(css) {
      $("head").append("<style type='text/css'>"+css+"<\/style>");
    });
    $(document).ready(function () {
        $('#horizontalAccordion').easyResponsiveTabs({
            type: 'accordion' //Types: default, vertical, accordion     
            closed: true,     
            activate: function(event) { // Callback function if tab is switched
               $(".resp-tab-active input[type=radio]").prop('checked', true);
            }
        });
        $('#horizontalTab').easyResponsiveTabs({
            type: 'default' //Types: default, vertical, accordion           
        });
    });
</script>
<?php } ?>