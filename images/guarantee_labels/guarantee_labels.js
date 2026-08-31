/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Opens the full GARAN label on the first click or touch of the compact one. The graphic is
// fetched from the cache, because it carries the title in every EU language and would weigh
// down every listing page. The label uses the lightbox of the template, colorbox or the
// thickbox of xtc5, so it behaves like every other overlay of the shop; without one a dialog
// element takes over.
(function () {
  'use strict';

  function closest(node, selector) {
    return (node && node.closest) ? node.closest(selector) : null;
  }

  function hasColorbox() {
    return (typeof window.jQuery === 'function' && typeof window.jQuery.colorbox === 'function');
  }

  function hasThickbox() {
    return (typeof window.tb_show === 'function');
  }

  // Thickbox needs the size of the box before it opens it. The full label is 420 pixels wide
  // plus its padding and the link below it, and shrinks with the viewport instead of leaving
  // the screen.
  function thickboxSize() {
    var width = Math.min(460, Math.max(280, (window.innerWidth || 800) - 60));
    var height = Math.min(520, Math.max(280, (window.innerHeight || 600) - 100));

    return 'width=' + width + '&height=' + height;
  }

  // The shipped templates put a font awesome cross into the close control of colorbox. Whether
  // that font is there is asked once by reading the computed family of a probe element, so a
  // template without it still gets a visible control instead of an empty box.
  function closeControl() {
    if (closeControl.markup !== undefined) {
      return closeControl.markup;
    }

    var probe = document.createElement('i');
    probe.className = 'fa-solid fa-xmark';
    probe.style.display = 'none';
    document.body.appendChild(probe);

    var family = window.getComputedStyle(probe).getPropertyValue('font-family') || '';
    document.body.removeChild(probe);

    closeControl.markup = (family.toLowerCase().indexOf('awesome') !== -1)
      ? '<i class="fa-solid fa-xmark"></i>'
      : '<span aria-hidden="true">&times;</span>';

    return closeControl.markup;
  }

  function show(label, id, title) {
    if (hasColorbox()) {
      window.jQuery.colorbox({
        inline: true,
        href: '#' + id,
        title: title,
        close: closeControl(),
        maxWidth: '100%',
        maxHeight: '100%',
        fixed: true,
        className: 'guarantee-label__colorbox'
      });
      return;
    }

    if (hasThickbox()) {
      // thickbox moves the children of the referenced element into its box and back on close
      window.tb_show(title, '#TB_inline?inlineId=' + id + '&' + thickboxSize(), false);
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

    // the notice is a full page of outlined paths, an image keeps it out of the document and
    // out of the request until someone opens it
    var image = full.getAttribute('data-guarantee-label-img');

    if (image && graphic && !full.getAttribute('data-guarantee-label-loaded')) {
      full.setAttribute('data-guarantee-label-loaded', '1');

      var element = document.createElement('img');
      element.setAttribute('src', image);
      element.setAttribute('alt', full.getAttribute('data-guarantee-label-alt') || '');
      graphic.appendChild(element);
    }

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
