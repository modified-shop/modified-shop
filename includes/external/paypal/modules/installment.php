<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  $installment_container = '<div class="pp-message"></div>';
  
  $installment_html = '
    <script src="https://www.paypal.com/sdk/js?client-id=%s&currency=%s&components=messages"></script>
    <div data-pp-message data-pp-amount="%s"></div>';
  
  $installment_js = "
    <script src=\"https://www.paypal.com/sdk/js?client-id=%s&currency=%s&components=messages\"></script>
    <script>
      paypal.Messages({
        amount: %s,
        countryCode: '%s',
        style: {
          layout: '%s',
          color: '%s',
          ratio: '8x1'
        },
        onRender: function() { 
          $('.pp-message').css('margin-top', '20px');
        }
      }).render('.pp-message');
    </script>";
