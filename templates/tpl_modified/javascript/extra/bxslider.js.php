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
  // handlers on an element, e.g. the load handlers of the images a slider waits for
  function bxHandlers(element) {
    var handlers = [];
    $.each($._data(element, 'events') || {}, function(type, list) {
      handlers = handlers.concat(list);
    });
    return handlers;
  }

  function startBxSlider(sliders, options) {
    sliders.filter(function() {
      return !$(this).data('bxSlider');
    }).each(function() {
      var media = $(this).find('img, iframe').get();
      var before = media.map(bxHandlers);
      $(this).bxSlider(options);
      // the slider starts when each of its images has called its load handler
      $(this).data('bxSliderLoad', media.map(function(element, i) {
        return {element: element, handlers: bxHandlers(element).filter(function(handler) {
          return handler.type == 'load' && before[i].indexOf(handler) < 0;
        })};
      }));
    });
  }

  function initBxSlider(root) {
    startBxSlider($(root).find('.bxcarousel_bestseller').addBack('.bxcarousel_bestseller'), {
      minSlides: 6,
      maxSlides: 8,
      pager: ($(this).children('li').length > 1), //FIX for only one entry
      slideWidth: 109,
      slideMargin: 18
    });
  
    startBxSlider($(root).find('.bxcarousel_slider').addBack('.bxcarousel_slider'), {
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
        // a slider waiting for its images ignores destroySlider(), its own load handlers start it without a load event for other scripts
        if ($(this).siblings('.bx-loading').length) {
          $.each($(this).data('bxSliderLoad') || [], function(i, media) {
            $.each(media.handlers, function(j, handler) {
              if (bxHandlers(media.element).indexOf(handler) >= 0) {
                $(media.element).off('load error', handler.handler);
                handler.handler.call(media.element, $.Event('load'));
              }
            });
          });
        }
        $(this).removeData('bxSliderLoad');
        var wrapped = $(this).parents('.bx-wrapper, .bx-viewport').is(root);
        // destroySlider() removes the jQuery data and handlers of the wrappers, those of the caller come back on root
        var data = wrapped ? $.extend({}, $(root).data()) : {};
        var handlers = wrapped ? bxHandlers(root).filter(function(handler) {
          // without the touch handlers and the link click handler that the slider binds to its viewport
          var touch = !handler.selector && /^(touch|pointer|MSPointer)/.test(handler.origType);
          return !touch && !(handler.origType == 'click' && handler.selector == '.bxslider a');
        }) : [];
        slider.destroySlider();
        // destroySlider() removes the wrappers, as root they stay in the page as a plain container of the slider
        if (wrapped) {
          $(root).removeClass('bx-wrapper bx-viewport click-disabled').removeAttr('aria-live').css({maxWidth: '', width: '', overflow: '', position: '', height: ''});
          $(root).empty().insertBefore(this).append(this).data(data);
          $.each(handlers, function(i, handler) {
            $(root).on(handler.origType + (handler.namespace ? '.' + handler.namespace : ''), handler.selector, handler.data, handler.handler);
          });
        }
      }
    });
  });
</script>
