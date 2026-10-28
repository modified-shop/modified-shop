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
  /* instances of the page, destroyed with their content */
  var splideInstances = [];

  function newSplide(elem, options) {
    var splide = new Splide(elem, options);
    splideInstances.push(splide);
    return splide;
  }

  function initSplide(root) {

    const elemts = root.querySelectorAll('.splide_big_image:not(.is-initialized)');
    if(elemts.length) {

      /* product info big image */
      var big = newSplide( elemts[0], {
        type: 'fade',
        role: 'group',
        heightRatio: 1.0,
        pagination: false,
        rewind: true,
        arrows: true,
        cover: true,
      } );

      /* product info small image thumbnail navigation */
      var small = newSplide( root.querySelector('.splide_small_image'), {
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

      /* prevent accessibility warning for vertical orientation */
      $(small.root).find('.splide__list').removeAttr('aria-orientation');
    }

    /* start slider */
    var elms = root.querySelectorAll(".splide_slider:not(.is-initialized)");
    for (var i = 0; i < elms.length; i++) {
      newSplide(elms[i], {
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
    var elms = root.querySelectorAll(".splide_size1:not(.is-initialized)");
    for (var i = 0; i < elms.length; i++) {
      newSplide(elms[i], {
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
    var elms = root.querySelectorAll(".splide_size2:not(.is-initialized)");
    for (var i = 0; i < elms.length; i++) {
      newSplide(elms[i], {
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
    var elms = root.querySelectorAll(".splide_size3:not(.is-initialized)");
    for (var i = 0; i < elms.length; i++) {
      newSplide(elms[i], {
        type: 'slide',
        role: 'group',
        speed: 1000,
        perPage: 1,
        mediaQuery: 'min',
      }).mount();
    }

  }

  document.addEventListener( 'DOMContentLoaded', function() {
    initSplide(document);
  });

  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function(event) {
    initSplide(event.detail.root);
  });

  // content that a script is about to replace or remove
  document.addEventListener('modified:before-content-update', function(event) {
    splideInstances = splideInstances.filter(function(splide) {
      if (event.detail.root.contains(splide.root)) {
        splide.destroy();
        splide.root.classList.remove('is-initialized');
        return false;
      }
      return true;
    });
  });

</script>
