/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Opens the full GARAN label on the first click or touch of the compact one. The graphic is
// fetched from the cache, because it carries the title in every EU language and would weigh
// down every listing page. When the template brings colorbox the label uses it, so it behaves
// like every other overlay of the shop; otherwise a dialog element takes over.
(function () {
  'use strict';

  function closest(node, selector) {
    return (node && node.closest) ? node.closest(selector) : null;
  }

  function hasColorbox() {
    return (typeof window.jQuery === 'function' && typeof window.jQuery.colorbox === 'function');
  }

  function show(label, id, title) {
    if (hasColorbox()) {
      window.jQuery.colorbox({
        inline: true,
        href: '#' + id,
        title: title,
        maxWidth: '100%',
        maxHeight: '100%',
        fixed: true,
        className: 'guarantee-label__colorbox'
      });
      return;
    }

    var dialog = label.querySelector('.guarantee-label__dialog');

    if (!dialog) {
      return;
    }

    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
    } else {
      dialog.setAttribute('open', 'open');
    }
  }

  function load(full, done) {
    var source = full.getAttribute('data-guarantee-label-src');
    var graphic = full.querySelector('.guarantee-label__graphic');

    if (!source || !graphic || full.getAttribute('data-guarantee-label-loaded')) {
      done();
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
      done();
    }).catch(function () {
      // the cache was cleared between page load and click, a reload rebuilds it
      full.removeAttribute('data-guarantee-label-loaded');
      graphic.textContent = full.getAttribute('data-guarantee-label-error') || '';
      done();
    });
  }

  document.addEventListener('click', function (event) {
    var compact = closest(event.target, '.guarantee-label__compact');

    if (compact) {
      event.preventDefault();

      var label = closest(compact, '.guarantee-label');
      var full = label ? label.querySelector('.guarantee-label__full') : null;
      var id = compact.getAttribute('data-guarantee-label-content');
      var title = compact.getAttribute('data-guarantee-label-title') || '';

      if (full) {
        load(full, function () { show(label, id, title); });
      } else {
        show(label, id, title);
      }

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
