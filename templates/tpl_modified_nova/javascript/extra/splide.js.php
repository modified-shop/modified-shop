<?php
  /* --------------------------------------------------------------
   $Id: splide.js.php   
   
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script>
  document.addEventListener( 'DOMContentLoaded', function() {

    const elemts = document.getElementsByClassName('splide_big_image');
    if(elemts.length) {

      /* product info big image */
      var big = new Splide( '.splide_big_image', {
        type: 'fade',
        role: 'group',
        heightRatio: 1.0,
        pagination: false,
        rewind: true,
        arrows: true,
        cover: true,
      } );

      /* product info small image thumbnail navigation */
      var small = new Splide( '.splide_small_image', {
        type: 'slide',
        role: 'group',
        rewind: true,
        heightRatio: 5,
        perPage: 5,
        direction: "ttb",
        isNavigation: true,
        gap: 10,
        pagination: false,
        cover: true,
      } );

      big.sync( small );
      big.mount();
      small.mount();
    }

    /* start slider */
    var elms = document.getElementsByClassName("splide_slider");
    for (var i = 0; i < elms.length; i++) {
      new Splide(elms[i], {
        type: 'fade',
        role: 'group',
        autoplay: true,
        interval: 5000,
        rewind:true,
        pagination: false,
        speed: 1000,
      }).mount();
    }

    /* carousel products */
    var elms = document.getElementsByClassName("splide_size1");
    for (var i = 0; i < elms.length; i++) {
      new Splide(elms[i], {
        type: 'slide',
        role: 'group',
        speed: 1000,
        perPage: 1,
        mediaQuery: 'min', 
        breakpoints: {
          340: { perPage: 2, },
          620: { perPage: 3, },
          920: { perPage: 4, },
         1160: { perPage: 5, },
        },
      }).mount();
    }

    /* carousel reviews */
    var elms = document.getElementsByClassName("splide_size2");
    for (var i = 0; i < elms.length; i++) {
      new Splide(elms[i], {
        type: 'slide',
        role: 'group',
        speed: 1000,
        perPage: 1,
        mediaQuery: 'min', 
        breakpoints: {
          800: { perPage: 2, },
        },
      }).mount();
    }

    /* carousel products row */
    var elms = document.getElementsByClassName("splide_size3");
    for (var i = 0; i < elms.length; i++) {
      new Splide(elms[i], {
        type: 'slide',
        role: 'group',
        speed: 1000,
        perPage: 1,
        mediaQuery: 'min', 
      }).mount();
    }

  }); 

  /* prevent accessibility warning for vertical orientation */
  $(document).ready(function(){
    $("#splide02-list").removeAttr('aria-orientation');
  });

</script>
