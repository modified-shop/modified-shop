<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');


class paypalacdc extends PayPalPaymentV2 {
  var $code, $title, $description, $extended_description, $enabled;


  function __construct() {
    global $order;

    PayPalPaymentV2::__construct('paypalacdc');

    if (PayPalPaymentBase::check_install() === true) {
      $this->tmpOrders = true;
      $this->tmpStatus = (($this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') > 0) ? $this->get_config('PAYPAL_ORDER_STATUS_PENDING_ID') : DEFAULT_ORDERS_STATUS_ID);
      $this->form_action_url = '';
    }
  }


  function update_status() {
    global $order;

    $this->enabled = false;
    if (in_array($order->billing['country']['iso_code_2'], array('DE', 'FR', 'IT', 'ES', 'US', 'GB', 'AU', 'CA'))
        && in_array($order->info['currency'], array('EUR'))
        )
    {
      $this->enabled = true;
    }

    parent::update_status();
  }


  function pre_confirmation_check() {
    global $order;

    $_SESSION['paypal'] = array(
      'cartID' => $_SESSION['cart']->cartID,
      'OrderID' => $this->CreateOrder(),
      'PayerID' => '',
      'Token' => $this->GenerateClientToken()
    );

    if ($_SESSION['paypal']['OrderID'] == '') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
    }
  }


  function confirmation() {
    return array ('title' => $this->description);
  }


  function process_button() {
    global $order;

    $paypal_smarty = new Smarty();
    $paypal_smarty->assign('language', $_SESSION['language']);
    $paypal_smarty->caching = 0;

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/acdc.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/acdc.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/acdc.html';
    }
    $process_button = $paypal_smarty->fetch($tpl_file);

    $process_button .= sprintf($this->get_js_sdk('true', $_SESSION['paypal']['Token']->client_token), "
      if (paypal.HostedFields.isEligible()) {
        let orderId;

        var el_acdc_error = document.querySelector('#apms_error');
        var el_acdc_overlay = document.querySelector('.apms_overlay');
        var el_checkout_confirmation = document.querySelector('#checkout_confirmation');
        var el_button_checkout_confirmation = document.querySelector('#button_checkout_confirmation');

        for (let i = 0; i < el_checkout_confirmation.children.length; i++) {
          if (el_checkout_confirmation.children[i].className != 'apms_form'
              && el_checkout_confirmation.children[i].tagName != 'SCRIPT'
              && el_checkout_confirmation.children[i].tagName != 'LINK'
              )
          {
            el_checkout_confirmation.children[i].classList.add('el_payment_confirmation');
          }
        }
        var el_payment_confirmation = document.querySelector('.el_payment_confirmation');
        var el_payment_confirmation_style = window.getComputedStyle(el_payment_confirmation, null).display;

        el_button_checkout_confirmation.addEventListener('click', (event) => {
          el_acdc_error.innerHTML = '';
          el_acdc_error.style = 'display: none';
          el_acdc_overlay.style = 'display: block';
        });

        paypal.HostedFields.render({
          createOrder: function () {
            orderId = '".$_SESSION['paypal']['OrderID']."';
            return orderId;
          },
          styles: {
            '.invalid': {
              'color': '#e74c3c'
            }
          },
          fields: {
            number: {
              selector: '#card-number',
              placeholder: '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_CARDNUMBER_PLACEHOLDER)."'
            },
            cvv: {
              selector: '#cvv',
              placeholder: '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_CVV_PLACEHOLDER)."'
            },
            expirationDate: {
              selector: '#expiration-date',
              placeholder: '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_EXPIRATION_PLACEHOLDER)."'
            }
          }
        }).then(function (cardFields) {
          el_acdc_overlay.style = 'display: none';

          el_checkout_confirmation.addEventListener('submit', (event) => {
            event.preventDefault();

            cardFields.submit({
              //contingencies: ['3D_SECURE'],
              contingencies: ['SCA_WHEN_REQUIRED'],
              cardholderName: document.getElementById('card-holder').value,
              billingAddress: {
                streetAddress: '".$this->encode_utf8($order->billing['street_address'])."',
                ".(($order->billing['suburb'] != '') ? "extendedAddress: '".$this->encode_utf8($order->billing['suburb'])."'," : '')."
                ".((isset($order->billing['state']) && $order->billing['state'] != '') ? "region: '".$this->encode_utf8(xtc_get_zone_code($order->billing['country_id'], $order->billing['zone_id'], $order->billing['state']))."'," : '')."
                locality: '".$this->encode_utf8($order->billing['city'])."',
                postalCode: '".$this->encode_utf8($order->billing['postcode'])."',
                countryCodeAlpha2: '".$this->encode_utf8($order->billing['country']['iso_code_2'])."'
              }
            }).then(function (data) {
              if (data.liabilityShift === 'POSSIBLE' && data.authenticationReason === 'SUCCESSFUL') {
                // redirect to complete order
                window.location.href = '".xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')."';
              } else {
                var msg = '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG)."';

                el_acdc_overlay.style = 'display: none';
                el_acdc_error.style = 'display: block';
                el_payment_confirmation.style = 'display: ' + el_payment_confirmation_style;
                return el_acdc_error.innerHTML = msg;
              }

            }).catch(function (err) {
              var errorDetail = Array.isArray(err.details) && err.details[0];

              var msg = '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG)."';

              el_acdc_overlay.style = 'display: none';
              el_acdc_error.style = 'display: block';
              el_payment_confirmation.style = 'display: ' + el_payment_confirmation_style;
              return el_acdc_error.innerHTML = msg;
            });
          });
        });
      } else {
        // redirects if the merchant isn't eligible
        window.location.href = '".xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL')."';
      }
    ");

    return $process_button;
  }


	function payment_action() {
    global $insert_id;

    $result = $this->FinishOrder($insert_id);
    if ($result->status == 'COMPLETED') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
    }

    // cancel pp order
    $this->remove_order($_SESSION['tmp_oID']);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
  }


  function after_process() {
    return false;
  }


  function success() {
    return false;
  }


  function install() {
    parent::install();
  }


  function keys() {
    return array(
      'MODULE_PAYMENT_PAYPALACDC_STATUS',
      'MODULE_PAYMENT_PAYPALACDC_ALLOWED',
      'MODULE_PAYMENT_PAYPALACDC_ZONE',
      'MODULE_PAYMENT_PAYPALACDC_SORT_ORDER'
    );
  }

}
?>