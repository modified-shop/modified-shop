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

  var prefixCounter = 0;

  // The templates carry generic names, cls-1 and clippath-6. Two labels in one document would
  // otherwise share them, and a clip path or a gradient of one label would apply to the other.
  // The server does the same to every svg it embeds, see guarantee_labels_inline_svg().
  function prefixSvg(svg) {
    var prefix = 'gljs' + (++prefixCounter) + '-';
    var ids = [];
    var pattern = /\sid="([^"]+)"/g;
    var match;

    while ((match = pattern.exec(svg)) !== null) {
      if (ids.indexOf(match[1]) === -1) {
        ids.push(match[1]);
      }
    }

    ids.forEach(function (id) {
      var quoted = id.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

      // only the ids the file defines itself, so a foreign reference is never rewritten
      svg = svg.replace(new RegExp('(\\sid=")' + quoted + '(")', 'g'), '$1' + prefix + id + '$2');
      svg = svg.replace(new RegExp('url\\(#' + quoted + '\\)', 'g'), 'url(#' + prefix + id + ')');
      svg = svg.replace(new RegExp('((?:xlink:)?href=")#' + quoted + '(")', 'g'), '$1#' + prefix + id + '$2');
    });

    // class names inside the style block and on the elements
    svg = svg.replace(/\.(cls-[0-9]+)/g, '.' + prefix + '$1');
    svg = svg.replace(/\sclass="([^"]+)"/g, function (whole, value) {
      var classes = value.trim().split(/\s+/).map(function (name) {
        return (name.indexOf('cls-') === 0) ? prefix + name : name;
      });

      return ' class="' + classes.join(' ') + '"';
    });

    return svg;
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

  function show(content, id, title) {
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

    var dialog = content ? closest(content, '.guarantee-label__dialog') : null;

    if (!dialog) {
      return;
    }

    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
    } else {
      dialog.setAttribute('open', 'open');
    }
  }

  // A click while a load is still running must not open an empty box. The element keeps the
  // callbacks of everyone waiting for the same graphic; the flag says "content is there", not
  // "a request went out".
  var waiting = [];

  function waitersFor(full) {
    for (var i = 0; i < waiting.length; i++) {
      if (waiting[i].full === full) {
        return waiting[i];
      }
    }

    return null;
  }

  function settle(full) {
    var entry = waitersFor(full);

    if (!entry) {
      return;
    }

    waiting.splice(waiting.indexOf(entry), 1);

    for (var i = 0; i < entry.callbacks.length; i++) {
      entry.callbacks[i]();
    }
  }

  function load(full, done) {
    var source = full.getAttribute('data-guarantee-label-src');

    // the notice is a full page of outlined paths, an image keeps it out of the document and
    // out of the request until someone opens it
    var image = full.getAttribute('data-guarantee-label-img');
    var graphic = full.querySelector('.guarantee-label__graphic');

    if (!graphic || full.getAttribute('data-guarantee-label-loaded')) {
      done();
      return;
    }

    // a load is already running for this label, so this click waits for the same one
    var running = waitersFor(full);

    if (running) {
      running.callbacks.push(done);
      return;
    }

    waiting.push({full: full, callbacks: [done]});

    if (image) {
      full.setAttribute('data-guarantee-label-loaded', '1');

      var element = document.createElement('img');

      // the overlay measures its content when it opens, so it may only open once the graphic
      // knows its size
      element.onload = function () {
        settle(full);
      };
      element.onerror = function () {
        full.removeAttribute('data-guarantee-label-loaded');
        graphic.textContent = full.getAttribute('data-guarantee-label-error') || '';
        settle(full);
      };

      element.setAttribute('alt', full.getAttribute('data-guarantee-label-alt') || '');
      graphic.appendChild(element);
      element.setAttribute('src', image);

      return;
    }

    if (!source) {
      settle(full);
      return;
    }

    full.setAttribute('data-guarantee-label-loaded', '1');

    fetch(source).then(function (response) {
      if (!response.ok) {
        throw new Error(response.status);
      }
      return response.text();
    }).then(function (svg) {
      // Inserted into the dom instead of an img, so the label uses the fonts of the page. Its
      // own ids and classes are made unique first, the document may already hold another label.
      graphic.innerHTML = prefixSvg(svg);
      settle(full);
    }).catch(function () {
      // the cache was cleared between page load and click, a reload rebuilds it
      full.removeAttribute('data-guarantee-label-loaded');
      graphic.textContent = full.getAttribute('data-guarantee-label-error') || '';
      settle(full);
    });
  }

  document.addEventListener('click', function (event) {
    var compact = closest(event.target, '.guarantee-label__compact');

    if (compact) {
      event.preventDefault();

      // the content is found by its id, the label and the notice bring different wrappers
      var id = compact.getAttribute('data-guarantee-label-content');
      var content = id ? document.getElementById(id) : null;
      var full = content ? content.querySelector('.guarantee-label__full') : null;
      var title = compact.getAttribute('data-guarantee-label-title') || '';

      if (full) {
        load(full, function () { show(content, id, title); });
      } else {
        show(content, id, title);
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
