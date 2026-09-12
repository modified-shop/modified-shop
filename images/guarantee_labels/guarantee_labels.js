/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Opens the full GARAN label on the first click or touch of the compact one. The graphic is
// fetched from the cache, because it carries the title in every EU language and would weigh
// down every listing page. The template's colorbox or thickbox keeps precedence. Bootstrap
// 4/5 modals are used when available; without a supported overlay a dialog element takes over.
(function () {
  'use strict';

  function closest(node, selector) {
    return (node && node.closest) ? node.closest(selector) : null;
  }

  var prefixCounter = 0;

  // names a block that shares its id with a twin of the same article elsewhere in the page
  var contentCounter = 0;
  var modalCounter = 0;
  var openRequest = 0;
  var bootstrapState = null;

  function focusTrigger(trigger) {
    if (trigger && document.documentElement.contains(trigger) && typeof trigger.focus === 'function') {
      trigger.focus();
    }
  }

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

  function bootstrapApi() {
    var Modal = window.bootstrap && window.bootstrap.Modal;
    var jq = window.jQuery;

    // Bootstrap 4 also exports bootstrap.Modal. The major version, not the global's
    // presence, decides which public API and event system can be used.
    if (typeof Modal === 'function' && /^5\./.test(Modal.VERSION || '')) {
      return {version: 5, Modal: Modal};
    }

    if (typeof jq === 'function' && jq.fn && typeof jq.fn.modal === 'function'
        && jq.fn.modal.Constructor && /^4\./.test(jq.fn.modal.Constructor.VERSION || '')) {
      return {version: 4, jq: jq, plugin: jq.fn.modal};
    }

    return null;
  }

  function showBootstrap(content, title, trigger) {
    var api = bootstrapApi();

    if (!api || !content || !content.parentNode) {
      return false;
    }

    if (bootstrapState) {
      if (bootstrapState.content === content) return true;
      bootstrapState.hide();
      // A host handler can veto closing its modal. Never stack another one over it.
      if (bootstrapState) return true;
    }

    // Bootstrap supports one modal at a time. Keep an unrelated modal intact and use
    // the existing native-dialog fallback instead of taking over the template's window.
    if (document.querySelector('.modal.show, .modal.in')) {
      return false;
    }

    var sourceDialog = closest(content, '.guarantee-label__dialog');
    var sourceClose = sourceDialog && sourceDialog.querySelector('.guarantee-label__close');
    var closeLabel = sourceClose ? sourceClose.textContent.trim() : '';
    var id;
    do {
      id = 'guarantee-label-modal-' + (++modalCounter);
    } while (document.getElementById(id) || document.getElementById(id + '-title'));

    // No fade: hiding restores the original content before the next modal can open.
    var modal = document.createElement('div');
    modal.className = 'modal guarantee-label__bootstrap';
    modal.id = id;
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-labelledby', id + '-title');
    modal.setAttribute('aria-hidden', 'true');
    var dialog = document.createElement('div');
    dialog.className = 'modal-dialog modal-dialog-centered';
    var panel = document.createElement('div');
    panel.className = 'modal-content';
    var header = document.createElement('div');
    header.className = 'modal-header';
    var heading = document.createElement('h2');
    heading.className = 'guarantee-label__bootstrap-title';
    heading.id = id + '-title';
    heading.textContent = title;
    var close = document.createElement('button');
    close.type = 'button';
    close.className = 'guarantee-label__bootstrap-close';
    close.setAttribute('aria-label', closeLabel && closeLabel !== '\u00d7' ? closeLabel : 'Close');
    close.textContent = '\u00d7';
    var body = document.createElement('div');
    // Some templates write to every .modal-title/.modal-body when showing an alert.
    // Private inner classes keep those writes away from the label and the notice.
    body.className = 'guarantee-label__bootstrap-body';
    header.appendChild(heading);
    header.appendChild(close);
    panel.appendChild(header);
    panel.appendChild(body);
    dialog.appendChild(panel);
    modal.appendChild(dialog);
    document.body.appendChild(modal);

    // A JS-only Bootstrap include must not leave the label in an unstyled page block.
    if (window.getComputedStyle(modal).position !== 'fixed') {
      modal.parentNode.removeChild(modal);
      return false;
    }

    var placeholder = document.createComment('GARAN content');
    content.parentNode.insertBefore(placeholder, content);
    body.appendChild(content);
    var instance = null;
    var shown = false;
    var cleaned = false;
    var state = {content: content, trigger: trigger, hide: hide};
    bootstrapState = state;

    function hide() {
      if (api.version === 5) instance.hide();
      else api.plugin.call(api.jq(modal), 'hide');
    }

    function cleanup(restoreFocus) {
      if (cleaned) return;
      cleaned = true;
      if (placeholder.parentNode) placeholder.parentNode.replaceChild(content, placeholder);
      if (api.version === 5) {
        modal.removeEventListener('shown.bs.modal', onShown);
        modal.removeEventListener('hidden.bs.modal', onHidden);
        if (instance) instance.dispose();
      } else {
        api.jq(modal).off('shown.bs.modal', onShown).off('hidden.bs.modal', onHidden);
        api.plugin.call(api.jq(modal), 'dispose');
      }
      if (modal.parentNode) modal.parentNode.removeChild(modal);
      if (bootstrapState === state) bootstrapState = null;
      if (restoreFocus) focusTrigger(trigger);
    }

    function onShown() {
      shown = true;
    }

    function onHidden() {
      ++openRequest;
      cleanup(true);
    }

    close.addEventListener('click', function () {
      ++openRequest;
      hide();
    });

    try {
      if (api.version === 5) {
        modal.addEventListener('shown.bs.modal', onShown);
        modal.addEventListener('hidden.bs.modal', onHidden);
        instance = new api.Modal(modal, {backdrop: true, keyboard: true, focus: true});
        instance.show(trigger);
      } else {
        api.jq(modal).on('shown.bs.modal', onShown).on('hidden.bs.modal', onHidden);
        api.plugin.call(api.jq(modal), {show: false, backdrop: true, keyboard: true, focus: true});
        api.plugin.call(api.jq(modal), 'show', trigger);
      }
      // A prevented show event must not strand content in an invisible modal.
      if (!shown) cleanup(false);
    } catch (error) {
      cleanup(false);
      return false;
    }

    return true;
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

  function show(content, id, title, trigger) {
    if (hasColorbox()) {
      var onClosed = window.jQuery.colorbox.settings && window.jQuery.colorbox.settings.onClosed;
      window.jQuery.colorbox({
        inline: true,
        href: '#' + id,
        title: title,
        close: closeControl(),
        maxWidth: '100%',
        maxHeight: '100%',
        fixed: true,
        className: 'guarantee-label__colorbox',
        onClosed: function () {
          if (typeof onClosed === 'function') onClosed.apply(this, arguments);
          ++openRequest;
          focusTrigger(trigger);
        }
      });
      return;
    }

    if (hasThickbox()) {
      // thickbox moves the children of the referenced element into its box and back on close
      window.tb_show(title, '#TB_inline?inlineId=' + id + '&' + thickboxSize(), false);
      if (typeof window.jQuery === 'function') {
        window.jQuery('#TB_window').one('tb_unload', function () { ++openRequest; focusTrigger(trigger); });
      }
      return;
    }

    if (showBootstrap(content, title, trigger)) return;

    var dialog = content ? closest(content, '.guarantee-label__dialog') : null;

    if (!dialog) {
      return;
    }

    dialog.guaranteeLabelTrigger = trigger;
    if (!dialog.guaranteeLabelCloseBound) {
      dialog.guaranteeLabelCloseBound = true;
      dialog.addEventListener('close', function () { ++openRequest; focusTrigger(dialog.guaranteeLabelTrigger); });
      // A native dialog can sit inside a Bootstrap quick view. Escape closes this
      // dialog alone instead of bubbling into the enclosing Bootstrap modal.
      dialog.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.keyCode === 27) event.stopPropagation();
      });
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

    // A load already running for this label is asked first: the flag below is set when the
    // request goes out, not when the graphic is there, so a second click would open an empty box.
    var running = waitersFor(full);

    if (running) {
      running.callbacks.push(done);
      return;
    }

    if (!graphic || full.getAttribute('data-guarantee-label-loaded')) {
      done();
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
      var request = ++openRequest;

      // the content is found by its id, the label and the notice bring different wrappers
      // The same article can appear twice on a page, in the listing and in a box, and
      // product::buildDataArray() serves the cached markup for both - so the id is not unique.
      // The button and its dialogue sit in the same .guarantee-label, which is: the id only
      // answers for the notice block, which has no such wrapper and is never duplicated.
      // colorbox and thickbox address the content by its id, so it is read either way
      var id = compact.getAttribute('data-guarantee-label-content');
      var block = closest(compact, '.guarantee-label');
      var content = block ? block.querySelector('.guarantee-label__content') : null;

      if (bootstrapState && bootstrapState.trigger === compact) content = bootstrapState.content;

      if (!content) {
        content = id ? document.getElementById(id) : null;
      }

      // A lightbox looks the content up in the document and takes the first element with that
      // id. product::buildDataArray() serves the cached markup of one article for every place
      // it appears, so the same id can sit in the page twice: the clicked block would load its
      // graphic while the first, still empty one opens. A block whose id answers with another
      // element gets one of its own.
      if (content) {
        var duplicate = !content.id || document.getElementById(content.id) !== content;
        // Moving the first cached copy into a modal changes document order. Check
        // every matching ID, so the previously second copy cannot take its identity.
        if (!duplicate) {
          var elements = document.querySelectorAll('[id]');
          for (var i = 0; i < elements.length; i++) {
            if (elements[i] !== content && elements[i].id === content.id) {
              duplicate = true;
              break;
            }
          }
        }
        if (duplicate) {
          do {
            id = 'guarantee-label-content-js' + (++contentCounter);
          } while (document.getElementById(id));
          content.id = id;
        }

        id = content.id;
        compact.setAttribute('data-guarantee-label-content', id);
      }
      var full = content ? content.querySelector('.guarantee-label__full') : null;
      var title = compact.getAttribute('data-guarantee-label-title') || '';

      if (full) {
        load(full, function () {
          if (request === openRequest && document.documentElement.contains(compact)) show(content, id, title, compact);
        });
      } else {
        show(content, id, title, compact);
      }

      return;
    }

    var close = closest(event.target, '.guarantee-label__close');

    if (close) {
      event.preventDefault();
      ++openRequest;
      var dialog = closest(close, '.guarantee-label__dialog');

      if (dialog && typeof dialog.close === 'function') {
        dialog.close();
      } else if (dialog) {
        dialog.removeAttribute('open');
        focusTrigger(dialog.guaranteeLabelTrigger);
      }
    }
  });
}());
