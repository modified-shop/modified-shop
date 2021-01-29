<?php
  /* --------------------------------------------------------------
   $Id: slick.js.php 12424 2019-11-29 16:36:29Z GTB $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script>
  $(document).ready(function() {

     $('.slider_home').slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      autoplay: true,
      autoplaySpeed: 4000,
      speed: 1000,
      cssEase: 'linear',
      arrows: true,
      dots: false,
      fade: true
    });
    $('.slider_home').show();


    $('.slider_bestseller').slick({
      dots: true,
      arrows: true,
      infinite: true,
      speed: 500,
      slidesToShow: 6,
      slidesToScroll: 6,
      responsive: [
        { breakpoint: 1060, settings: { slidesToShow: 5, slidesToScroll: 5, dots: true, arrows:false } },
        { breakpoint:  800, settings: { slidesToShow: 4, slidesToScroll: 4, dots: true, arrows:false } },
        { breakpoint:  600, settings: { slidesToShow: 3, slidesToScroll: 3, dots: true, arrows:false } },
        { breakpoint:  400, settings: { slidesToShow: 2, slidesToScroll: 2, dots: true, arrows:false } }
      ]
    });


  });
</script>