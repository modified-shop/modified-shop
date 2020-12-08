<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (basename($PHP_SELF) == FILENAME_CHECKOUT_PAYMENT
    && isset($_SESSION['klarna'])
    && array_key_exists('script', $_SESSION['klarna'])
    )
{
  echo '
    <script>
      window.klarnaAsyncCallback = function () {
        Klarna.Payments.init({
          client_token: "'.$_SESSION['klarna']['client_token'].'"
        });
        '.implode('', $_SESSION['klarna']['script']).'
      }
    </script>
    <script src="https://x.klarnacdn.net/kp/lib/v1/api.js" async></script>';
}
