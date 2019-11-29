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
  $(document).ready(function() {
    $('.bxcarousel_bestseller').bxSlider({
      nextText: '<i class="fas fa-chevron-right"></i>',
      prevText: '<i class="fas fa-chevron-left"></i>',
      minSlides: 2,
      maxSlides: 8,
      pager: ($(this).children('li').length > 1), //FIX for only one entry
      slideWidth: 124,
      slideMargin: 18
    });
  
    $('.bxcarousel_slider').bxSlider({
      nextText: '<i class="fas fa-chevron-right"></i>',
      prevText: '<i class="fas fa-chevron-left"></i>',
      adaptiveHeight: false,
      mode: 'fade',
      auto: true,
      speed: 2000,
      pause: 6000
    });
  });
</script>
