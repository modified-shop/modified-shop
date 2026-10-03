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
    require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

    $klarna_express = new klarna_express();

    // spike only: ?klarna_spike=session uses a server-side session
    $klarna_express_variant = ((isset($_GET['klarna_spike']) && $_GET['klarna_spike'] == 'session') ? 'session' : 'client_id');

    $klarna_express_init = array();
    $klarna_express_payload = null;

    $klarna_express_order = $klarna_express->get_express_order_data();
    if ($klarna_express_variant == 'session') {
      $klarna_express_session = $klarna_express->get_express_session();
      if (is_array($klarna_express_session)) {
        $klarna_express_init = array('client_token' => $klarna_express_session['client_token']);
      }
    } else {
      $klarna_express_init = array('client_id' => MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID);
      $klarna_express_payload = array(
        'purchase_country' => $klarna_express_order['purchase_country'],
        'purchase_currency' => $klarna_express_order['purchase_currency'],
        'locale' => $klarna_express_order['locale'],
        'order_amount' => $klarna_express_order['order_amount'],
        'order_tax_amount' => $klarna_express_order['order_tax_amount'],
        'order_lines' => $klarna_express_order['order_lines'],
      );
    }

    $klarna_express_config = json_encode(array(
      'variant' => $klarna_express_variant,
      'init' => $klarna_express_init,
      'payload' => $klarna_express_payload,
      'locale' => $klarna_express_order['locale'],
      'log_url' => DIR_WS_BASE.'ajax.php?ext=klarna_express_spike',
    ), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    if ($klarna_express_config !== false && count($klarna_express_init) > 0) {
      $smarty->assign('BUTTON_KLARNA', '<div id="klarna-express-button"></div>
        <script>
          (function () {
            var cfg = '.$klarna_express_config.';
            var replacer = function (key, val) {
              return ((val instanceof Error) ? {name: val.name, message: val.message} : val);
            };
            var spikeLog = function (kind, data) {
              console.log("klarna express", cfg.variant, kind, data);
              try {
                fetch(cfg.log_url, {
                  method: "POST",
                  credentials: "same-origin",
                  headers: {"Content-Type": "application/json"},
                  body: JSON.stringify({variant: cfg.variant, kind: kind, result: data}, replacer)
                });
              } catch (e) {}
            };
            window.klarnaAsyncCallback = function () {
              try {
                window.Klarna.Payments.Buttons.init(cfg.init).load({
                  container: "#klarna-express-button",
                  theme: "default",
                  shape: "default",
                  locale: cfg.locale,
                  on_click: function (authorize) {
                    var options = {auto_finalize: false, collect_shipping_address: true};
                    authorize(options, cfg.payload, function (result) {
                      spikeLog("authorize", result);
                    });
                  }
                }, function (loadResult) {
                  spikeLog("load", loadResult);
                });
              } catch (e) {
                spikeLog("exception", e);
              }
            };
          })();
        </script>
        <script src="https://x.klarnacdn.net/kp/lib/v1/api.js" async></script>');
    }
  }
