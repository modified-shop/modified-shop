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

  /* matching elements of the root, the root itself included */
  function splideElements(root, selector) {
    var elems = Array.prototype.slice.call(root.querySelectorAll(selector));
    if (root.matches && root.matches(selector)) {
      elems.unshift(root);
    }
    return elems;
  }

  /* the big image and its synced thumbnail navigation start and stop together */
  function splideGallery() {
    return [document.querySelector('.splide_big_image'), document.querySelector('.splide_small_image')];
  }

  function initSplide(root) {

    var gallery = splideGallery();
    if (gallery[0] && gallery[1] && !gallery[0].classList.contains('is-initialized') && (root.contains(gallery[0]) || root.contains(gallery[1]))) {

      /* product info big image */
      var big = newSplide( gallery[0], {
        type: 'fade',
        role: 'group',
        heightRatio: 1.0,
        pagination: false,
        rewind: true,
        arrows: true,
        cover: true,
      } );

      /* product info small image thumbnail navigation */
      var small = newSplide( gallery[1], {
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
    var elms = splideElements(root, ".splide_slider:not(.is-initialized)");
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
    var elms = splideElements(root, ".splide_size1:not(.is-initialized)");
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
    var elms = splideElements(root, ".splide_size2:not(.is-initialized)");
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
    var elms = splideElements(root, ".splide_size3:not(.is-initialized)");
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
    var root = event.detail.root;
    var gallery = splideGallery();
    var withGallery = gallery.some(function(elem) {
      return elem && root.contains(elem);
    });
    splideInstances = splideInstances.filter(function(splide) {
      if (root.contains(splide.root) || (withGallery && gallery.indexOf(splide.root) >= 0)) {
        splide.destroy();
        splide.root.classList.remove('is-initialized');
        return false;
      }
      return true;
    });
  });

</script>
