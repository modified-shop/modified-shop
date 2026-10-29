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
  function initBxSlider(root) {
    $(root).find('.bxcarousel_bestseller').addBack('.bxcarousel_bestseller').bxSlider({
      minSlides: 6,
      maxSlides: 8,
      pager: ($(this).children('li').length > 1), //FIX for only one entry
      slideWidth: 109,
      slideMargin: 18
    });
  
    $(root).find('.bxcarousel_slider').addBack('.bxcarousel_slider').bxSlider({
      adaptiveHeight: false,
      mode: 'fade',
      auto: true,
      speed: 2000,
      pause: 6000
    });
  }

  $(document).ready(function() {
    initBxSlider(document);
  });

  // content that a script has inserted into the page
  document.addEventListener('modified:content-updated', function(event) {
    initBxSlider(event.detail.root);
  });

  // content that a script is about to replace or remove
  document.addEventListener('modified:before-content-update', function(event) {
    var root = event.detail.root;
    $(root).find('.bxcarousel_bestseller, .bxcarousel_slider').addBack('.bxcarousel_bestseller, .bxcarousel_slider').each(function() {
      var slider = $(this).data('bxSlider');
      if (slider) {
        // a slider still waiting for its images ignores destroySlider() and would start later on removed content
        if ($(this).siblings('.bx-loading').length) {
          $(this).find('img, iframe').trigger('load');
        }
        var wrapped = $(this).parents('.bx-wrapper, .bx-viewport').is(root);
        slider.destroySlider();
        // destroySlider() removes the wrappers, as root they stay in the page as a plain container of the slider
        if (wrapped) {
          while (root.attributes.length) {
            root.removeAttribute(root.attributes[0].name);
          }
          $(root).empty().insertBefore(this).append(this);
        }
      }
    });
  });
</script>
