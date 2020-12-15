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
<script>
  $(window).on('load',function() {
    $('.show_rating input').change(function () {
      var $radio = $(this);
      $('.show_rating .selected').removeClass('selected');
      $radio.closest('label').addClass('selected');
    });
  });     

  function alert(message, title) {
    title = title || "<?php echo TEXT_LINK_TITLE_INFORMATION; ?>";
    $.alertable.alert('<span id="alertable-title"></span><span id="alertable-content"></span>', { 
      html: true 
    });
    $('#alertable-content').html(message);
    $('#alertable-title').html(title);
  }

  <?php if (basename($PHP_SELF) != FILENAME_SHOPPING_CART && !strpos($PHP_SELF, 'checkout')) { ?>
    $(function() {
      $('#toggle_cart').click(function() {
        $('.toggle_cart').slideToggle('slow');
        $('.toggle_wishlist').slideUp('slow');
        ac_closing();
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
        ac_closing();
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
