<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_PAYMENT_KLARNA_EXPRESS_STATUS')
      && MODULE_PAYMENT_KLARNA_EXPRESS_STATUS == 'True'
      && defined('MODULE_PAYMENT_KLARNA_EXPRESS_BUTTON_CART')
      && MODULE_PAYMENT_KLARNA_EXPRESS_BUTTON_CART == 'True'
      && defined('MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID')
      && MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID != ''
      && $_SESSION['cart']->show_total() > 0
      )
  {
    // include needed classes
    include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/klarna_express.php');
    require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

    $klarna_express = new klarna_express();

    if ($klarna_express::express_enabled() === true && $klarna_express->cart_requires_shipping() === true) {
      $klarna_express_order = $klarna_express->get_express_order_data();

      $klarna_express_config = json_encode(array(
        'client_id' => MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID,
        'locale' => $klarna_express_order['locale'],
        'payload' => array(
          'purchase_country' => $klarna_express_order['purchase_country'],
          'purchase_currency' => $klarna_express_order['purchase_currency'],
          'locale' => $klarna_express_order['locale'],
          'order_amount' => $klarna_express_order['order_amount'],
          'order_tax_amount' => $klarna_express_order['order_tax_amount'],
          'order_lines' => $klarna_express_order['order_lines'],
        ),
        'token' => $klarna_express->get_express_token(),
        'callback' => str_replace('&amp;', '&', xtc_href_link('callback/klarna/express.php', '', 'SSL')),
        'error_url' => str_replace('&amp;', '&', xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error=klarna_express', 'NONSSL')),
      ), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

      if ($klarna_express_config !== false) {
        $smarty->assign('BUTTON_KLARNA', '<link rel="stylesheet" property="stylesheet" href="'.DIR_WS_BASE.DIR_WS_EXTERNAL.'klarna/css/express.css?v=1" type="text/css" media="screen" />
          <div id="klarna-express-button"></div>
          <script>
            (function () {
              var cfg = '.$klarna_express_config.';
              var fail = function () {
                window.location.href = cfg.error_url;
              };
              var post = function (result) {
                var fields = {
                  token: cfg.token,
                  client_token: result.client_token,
                  session_id: result.session_id,
                  collected_shipping_address: JSON.stringify(result.collected_shipping_address || {}),
                  payment_method_categories: JSON.stringify(result.payment_method_categories || [])
                };
                var form = document.createElement("form");
                form.method = "post";
                form.action = cfg.callback;
                Object.keys(fields).forEach(function (name) {
                  var input = document.createElement("input");
                  input.type = "hidden";
                  input.name = name;
                  input.value = fields[name];
                  form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
              };
              window.klarnaAsyncCallback = function () {
                try {
                  window.Klarna.Payments.Buttons.init({client_id: cfg.client_id}).load({
                    container: "#klarna-express-button",
                    theme: "default",
                    shape: "default",
                    locale: cfg.locale,
                    on_click: function (authorize) {
                      authorize({auto_finalize: false, collect_shipping_address: true}, cfg.payload, function (result) {
                        if (result && result.approved === true) {
                          if (result.finalize_required === true && result.client_token && result.session_id) {
                            post(result);
                          } else {
                            fail();
                          }
                        } else if (result && result.show_form === false) {
                          fail();
                        }
                      });
                    }
                  });
                } catch (e) {}
              };
            })();
          </script>
          <script src="https://x.klarnacdn.net/kp/lib/v1/api.js" async></script>');
      }
    }

    if (isset($_GET['payment_error']) && $_GET['payment_error'] == 'klarna_express') {
      $smarty->assign('error_message', MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_MESSAGE);
    }
  }
