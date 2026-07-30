<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

if (basename($PHP_SELF) == FILENAME_CHECKOUT_CONFIRMATION) {
  ?>
  <script>
    (function () {
      function showCheckoutProcessing() {
        var overlay = document.getElementById('checkout-processing-overlay');
        if (overlay) {
          overlay.style.display = 'block';
          overlay.setAttribute('aria-hidden', 'false');
        }
      }

      document.addEventListener('click', function (event) {
        if (event.target && event.target.closest && event.target.closest('#button_checkout_confirmation')) {
          window.setTimeout(function () {
            if (!event.defaultPrevented) {
              showCheckoutProcessing();
            }
          }, 0);
        }
      });

      var form = document.getElementById('checkout_confirmation');
      if (form) {
        form.addEventListener('submit', showCheckoutProcessing);
        if (window.jQuery) {
          window.jQuery(form).on('submit.checkoutProcessing', showCheckoutProcessing);
        }
      }
    }());
  </script>
  <?php
}

if (basename($PHP_SELF) == FILENAME_CHECKOUT_PROCESSING) {
  ?>
  <script>
    (function () {
      var container = document.querySelector('.checkout_processing');

      function poll() {
        fetch(container.getAttribute('data-status-url'), {
          credentials: 'same-origin',
          cache: 'no-store'
        })
          .then(function (response) { return response.json(); })
          .then(function (result) {
            if (result.status === 'completed' && result.redirect) {
              window.location.replace(result.redirect);
              return;
            }
            if (result.status === 'failed') {
              window.location.replace(container.getAttribute('data-error-url'));
              return;
            }
            window.setTimeout(poll, 2000);
          })
          .catch(function () {
            window.setTimeout(poll, 4000);
          });
      }

      window.setTimeout(poll, 1000);
    }());
  </script>
  <?php
}
