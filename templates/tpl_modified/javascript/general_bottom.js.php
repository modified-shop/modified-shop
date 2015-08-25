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
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.colorbox.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.unveil.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.alerts.min.js" type="text/javascript"></script>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.bxslider.min.js" type="text/javascript"></script>
<?php require DIR_FS_CATALOG . DIR_TMPL_JS . 'get_states.js.php'; ?>
<script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.easyTabs.min.js" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $(".cbimages").colorbox({rel:'cbimages', scalePhotos:true, maxWidth: "90%", maxHeight: "90%", fixed: true});
    $(".iframe").colorbox({iframe:true, width:"780", height:"560", maxWidth: "90%", maxHeight: "90%", fixed: true});
    $(".unveil").show();
    $(".unveil").unveil(200);
    $('.bxcarousel_bestseller').bxSlider({
      minSlides: 6,
      maxSlides: 8,
      pager: ($(this).children('li').length > 1) ? true: false, //FIX for only one entry
      slideWidth: 109,
      slideMargin: 18
    });
    $('.bxcarousel_slider').bxSlider({
      adaptiveHeight: false,
      mode: 'fade',
      auto: true,
      speed: 2000,
      pause: 6000
    });
    $(document).bind('cbox_complete', function(){
      if($('#cboxTitle').height() > 20){
        $("#cboxTitle").hide();
        $("<div>"+$("#cboxTitle").html()+"</div>").css({color: $("#cboxTitle").css('color')}).insertAfter("#cboxPhoto");
        $.fn.colorbox.resize();
      }
    });   
  });
</script>
<script type="text/javascript">
  $.alerts.overlayOpacity = .2;
  $.alerts.overlayColor = '#000';
  function alert(message, title) {
    title = title || 'Information';
    jAlert(message, title);
  }
  <?php if (!strstr($PHP_SELF, FILENAME_SHOPPING_CART) && !strstr($PHP_SELF, 'checkout')) { ?>
    $(function() {
      $('#toggle_cart').click(function() {
        $('.toggle_cart').slideToggle('slow');
        $('.toggle_wishlist').slideUp('slow');
        return false;
      });
      $("html").not('.toggle_cart').bind('click',function(e) {
        $('.toggle_cart').slideUp('slow');
      });
      <?php if (DISPLAY_CART == 'false' && isset($_SESSION['new_products_id_in_cart'])) {
        unset($_SESSION['new_products_id_in_cart']); ?>
        $('.toggle_cart').slideToggle('slow');
        timer = setTimeout(function(){$('.toggle_cart').slideUp('slow');}, 3000);
        $('.toggle_cart').mouseover(function() {clearTimeout(timer);});
      <?php } ?>
    });     

    $(function() {
      $('#toggle_wishlist').click(function() {
        $('.toggle_wishlist').slideToggle('slow');
        $('.toggle_cart').slideUp('slow');
        return false;
      });
      $("html").not('.toggle_wishlist').bind('click',function(e) {
        $('.toggle_wishlist').slideUp('slow');
      });
      <?php if (DISPLAY_CART == 'false' && isset($_SESSION['new_products_id_in_wishlist'])) {
        unset($_SESSION['new_products_id_in_wishlist']); ?>
        $('.toggle_wishlist').slideToggle('slow');
        timer = setTimeout(function(){$('.toggle_wishlist').slideUp('slow');}, 3000);
        $('.toggle_wishlist').mouseover(function() {clearTimeout(timer);});
      <?php } ?>
    });     
  <?php } else {
    unset($_SESSION['new_products_id_in_cart']);
    unset($_SESSION['new_products_id_in_wishlist']);
  } ?>
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
            type: 'accordion', //Types: default, vertical, accordion     
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

<?php if (strstr($PHP_SELF, FILENAME_CONTENT) && $_GET['coID'] == 8) { ?>
  <!--[if lt IE 10]>
  <script src="<?php echo DIR_WS_BASE.DIR_TMPL_JS; ?>jquery.css3-multi-column.js"></script>
  <![endif]-->
<?php } ?>