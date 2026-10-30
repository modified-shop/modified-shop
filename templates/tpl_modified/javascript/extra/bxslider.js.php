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
      var slider = $(this);
      var media = $(this).find('img, iframe').get();
      var before = media.map(bxHandlers);
      var viewport = null;
      var bound = null;
      // the handlers of the slider on its viewport are those that appear between lead() and follow(), only its own code runs there
      var lead = function() {
        bound = bxHandlers(viewport);
      };
      var follow = function() {
        if (bound) {
          var own = slider.data('bxSliderHandlers');
          if (!own) {
            // the slider has just started, its touch handler binds more handlers on each touch
            $(viewport).on('touchstart MSPointerDown pointerdown', follow);
          }
          own = own || [];
          $.each(bxHandlers(viewport), function(i, handler) {
            if (bound.indexOf(handler) < 0 && own.indexOf(handler.handler) < 0) {
              own.push(handler.handler);
            }
          });
          slider.data('bxSliderHandlers', own);
          bound = null;
        }
      };
      $(this).bxSlider($.extend({}, options, {
        // runs right before the slider binds its handlers to the viewport
        onSliderLoad: function() {
          viewport = slider.parent()[0];
          lead();
          $(viewport).on('touchstart MSPointerDown pointerdown', lead);
        }
      }));
      follow();
      // the slider starts when each of its images has called its load handler, follow() is bound right behind it
      $(this).data('bxSliderLoad', media.map(function(element, i) {
        var handlers = function() {
          return bxHandlers(element).filter(function(handler) {
            return handler.type == 'load' && before[i].indexOf(handler) < 0;
          });
        };
        if (handlers().length) {
          $(element).one('load error', follow);
        }
        return {element: element, handlers: handlers()};
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
        var own = $(this).data('bxSliderHandlers') || [];
        $(this).removeData('bxSliderLoad bxSliderHandlers');
        var wrapped = $(this).parents('.bx-wrapper, .bx-viewport').is(root);
        // destroySlider() removes the jQuery data and handlers of the wrappers, those of the caller come back on root
        var data = wrapped ? $.extend({}, $(root).data()) : {};
        var handlers = wrapped ? bxHandlers(root).filter(function(handler) {
          // jQuery binds its own handler for focus and blur, it comes back with the handler of the caller
          return own.indexOf(handler.handler) < 0 && handler.namespace !== false;
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
