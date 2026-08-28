/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Opens the full GARAN label on the first click or touch of the compact one. Kept free of any
// framework, so every shipped template behaves the same without its own lightbox.
(function () {
  'use strict';

  function open(label) {
    var dialog = label.querySelector('.guarantee-label__dialog');

    if (!dialog) {
      return;
    }

    if (typeof dialog.showModal === 'function') {
      dialog.showModal();
      return;
    }

    // without dialog support the full label simply stays in the flow
    dialog.setAttribute('open', 'open');
  }

  document.addEventListener('click', function (event) {
    var compact = event.target.closest ? event.target.closest('.guarantee-label__compact') : null;

    if (compact) {
      event.preventDefault();
      open(compact.parentNode);
      return;
    }

    var close = event.target.closest ? event.target.closest('.guarantee-label__close') : null;

    if (close) {
      event.preventDefault();
      var dialog = close.closest('.guarantee-label__dialog');
      if (dialog && typeof dialog.close === 'function') {
        dialog.close();
      } else if (dialog) {
        dialog.removeAttribute('open');
      }
    }
  });
}());
