/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Opens the full GARAN label on the first click or touch of the compact one and fetches the
// graphic from the cache, because it carries the title in every EU language and would weigh
// down every listing page. Kept free of any framework, so every shipped template behaves the
// same without its own lightbox.
(function () {
  'use strict';

  function closest(node, selector) {
    return (node && node.closest) ? node.closest(selector) : null;
  }

  function load(full) {
    var source = full.getAttribute('data-guarantee-label-src');
    var graphic = full.querySelector('.guarantee-label__graphic');

    if (!source || !graphic || full.getAttribute('data-guarantee-label-loaded')) {
      return;
    }

    full.setAttribute('data-guarantee-label-loaded', '1');

    fetch(source).then(function (response) {
      if (!response.ok) {
        throw new Error(response.status);
      }
      return response.text();
    }).then(function (svg) {
      // inserted into the dom instead of an img, so the label uses the fonts of the page
      graphic.innerHTML = svg;
    }).catch(function () {
      // the cache was cleared between page load and click, a reload rebuilds it
      full.removeAttribute('data-guarantee-label-loaded');
      graphic.textContent = full.getAttribute('data-guarantee-label-error') || '';
    });
  }

  function open(label) {
    var dialog = label.querySelector('.guarantee-label__dialog');
    var full = label.querySelector('.guarantee-label__full');

    if (!dialog) {
      return;
    }

    if (full) {
      load(full);
    }

    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
      return;
    }

    // without dialog support the full label simply stays in the flow
    dialog.setAttribute('open', 'open');
  }

  document.addEventListener('click', function (event) {
    var compact = closest(event.target, '.guarantee-label__compact');

    if (compact) {
      event.preventDefault();
      open(compact.parentNode);
      return;
    }

    var close = closest(event.target, '.guarantee-label__close');

    if (close) {
      event.preventDefault();
      var dialog = closest(close, '.guarantee-label__dialog');

      if (dialog && typeof dialog.close === 'function') {
        dialog.close();
      } else if (dialog) {
        dialog.removeAttribute('open');
      }
    }
  });
}());
