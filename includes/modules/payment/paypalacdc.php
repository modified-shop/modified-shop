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

  var $code;
  var $description;
  var $enabled;
  var $tmpOrders;
  var $tmpStatus;
  var $form_action_url;
  var $allowed_zones;

  function __construct() {
    global $order;

    $this->allowed_zones = array('DE', 'FR', 'IT', 'ES', 'US', 'GB', 'AU', 'CA', 'JP', 'MX');

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
    if (isset($order->billing['country']['iso_code_2'])
        && in_array($order->billing['country']['iso_code_2'], $this->allowed_zones)
        && in_array($order->info['currency'], array('EUR'))
        )
    {
      $this->enabled = true;
    }

    parent::update_status();
  }


  function confirmation() {
    return array ('title' => $this->description);
  }


  function process_button() {
    global $order, $smarty;
    
    $paypal_smarty = new Smarty();
    $paypal_smarty->assign('language', $_SESSION['language']);
    $paypal_smarty->caching = 0;

    if ($this->get_config('MODULE_PAYMENT_'.strtoupper($this->code).'_SAVE_PAYMENT') == '1'
        && function_exists('css_button')
        )
    {
      $paypal_smarty->assign('SAVE_PAYMENT_CHECKBOX', xtc_draw_checkbox_field('save_payment', 'save_payment', false, 'id="save_payment"').xtc_draw_hidden_field('payment_method', $this->code));
    
      $vault_id = $this->getVaultId($_SESSION['customer_id'], 'card');
      if (!is_null($vault_id)) {
        $result = $this->GetVaultDetails($vault_id);          
        
        if (isset($result->id) 
            && $result->id == $vault_id
            && isset($result->payment_source->card)
            )
        {          
          $card_details = array(
            'last_digits' => $result->payment_source->card->last_digits,
            'brand' => $result->payment_source->card->brand,
            'expiry' => $result->payment_source->card->expiry,
          );
          
          $button = xtc_image_submit('button_confirm_order.gif', '%s', ' id="button_checkout_confirmation_vault"');
          $button = str_replace(array('title="%s"', '%s</button>'), array('title="'.IMAGE_BUTTON_CONFIRM_ORDER.'"', IMAGE_BUTTON_CONFIRM_ORDER.'</button>'), $button);
          $button = str_replace('%s', sprintf('Kaufen mit <span class="brand">%s</span> <span class="expiry">%s</span> <span class="last_digits">%s</span>', $card_details['brand'], $card_details['expiry'], $card_details['last_digits']), $button);
            
          $paypal_smarty->assign('VAULT_FORM_ACTION', xtc_draw_form('checkout_confirmation_vault', xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'), 'post', 'name="checkout_confirmation_vault"').xtc_draw_hidden_field('payment_method', $this->code));
          $paypal_smarty->assign('VAULT_BUTTON', $button);
          $paypal_smarty->assign('VAULT_FORM_END', '</form>');
          
          $smarty->clear_assign('CHECKOUT_BUTTON');
          $smarty->clear_assign('CHECKOUT_FORM_END');
          
          $paypal_smarty->assign('CHECKOUT_BUTTON', xtc_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER, ' id="button_checkout_confirmation"'));
          $paypal_smarty->assign('CHECKOUT_FORM_END', '</form>');
        }
      }
    }
    
    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/acdc.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/acdc.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/acdc.html';
    }
    $process_button = $paypal_smarty->fetch($tpl_file);

    $tpl_file = DIR_FS_EXTERNAL.'paypal/templates/pui_error.html';
    if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui_error.html')) {
      $tpl_file = DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/paypal/pui_error.html';
    }
    $paypal_smarty->assign('error_message', TEXT_PAYPAL_ERROR_NOT_AVAILABLE);
    $info = $paypal_smarty->fetch($tpl_file);
    $info = trim(str_replace(array("\r", "\n"), '', $info));

    $order_url = DIR_WS_BASE.'ajax.php?ext=create_paypal_order';
    $error_url = xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL');

    $process_button .= sprintf($this->get_js_sdk('true', $this->GenerateClientToken()->client_token), "
      if (paypal.HostedFields.isEligible()) {
        let orderId;

        var el_acdc_error = document.querySelector('#apms_error');
        var el_acdc_overlay = document.querySelector('.apms_overlay');
        var el_checkout_confirmation = document.querySelector('#checkout_confirmation');
        var el_button_checkout_confirmation = document.querySelector('#button_checkout_confirmation');

        for (let i = 0; i < el_checkout_confirmation.children.length; i++) {
          if (el_checkout_confirmation.children[i].className != 'apms_form'
              && el_checkout_confirmation.children[i].tagName != 'apms_alternate'
              && el_checkout_confirmation.children[i].className.indexOf('apms_notice') < 0
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
            var formdata = $('#checkout_confirmation').serializeArray(); 
            return $.ajax({
              type: 'POST',
              url: '".$order_url."',
              data: formdata,
              dataType: 'json'
            });
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
              if (data.liabilityShift === 'POSSIBLE') {
                $.get('".DIR_WS_BASE."ajax.php', {ext: 'check_paypal_order', payment_method: 'paypalacdc'}, function(result) {
                  if (result === true) {
                    // redirect to complete order
                    window.location.href = '".xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')."';
                  } else {
                    var msg = '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG)."';
                    display_error_acdc(msg);
                  }
                }).fail(function() {
                  var msg = '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG)."';
                  display_error_acdc(msg);
                });
              } else {
                var msg = '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG)."';
                display_error_acdc(msg);
              }
            }).catch(function (err) {
              var msg = '".decode_htmlentities(MODULE_PAYMENT_PAYPALACDC_TEXT_ERROR_MSG)."';
              display_error_acdc(msg);
            });
          });
        });
      } else {
        // redirects if the merchant isn't eligible
        window.location.href = '".$error_url."';
      }
      
      function display_error_acdc(msg) {
        el_acdc_overlay.style = 'display: none';
        el_acdc_error.style = 'display: block';
        el_payment_confirmation.style = 'display: ' + el_payment_confirmation_style;
        el_acdc_error.innerHTML = msg;
      }
    ", "$('#checkout_confirmation').replaceWith('".$info."');");

    return $process_button;
  }

  
  function before_process() {
    global $messageStack;
    
    if (isset($_POST['payment_method'])
        && $_POST['payment_method'] == $this->code
        )
    {
      $vault_id = $this->getVaultId($_SESSION['customer_id'], 'card');
      
      if (!is_null($vault_id)) {
        $payment_source = array(
          'payment_source' => array(
            'card' => array(
              'vault_id' => $vault_id,
              'attributes' => array(
                'verification' => array(
                  'method' => 'SCA_WHEN_REQUIRED',
                )
              ),
              'stored_credential' => array(
                'payment_initiator' => 'CUSTOMER',
                'payment_type' => 'UNSCHEDULED',
                'usage' => 'SUBSEQUENT',
              )
            )
          )
        );

        $_SESSION['paypal'] = array(
          'cartID' => $_SESSION['cart']->cartID,
          'OrderID' => $this->CreateOrder($payment_source)
        );

        if ($_SESSION['paypal']['OrderID'] == '') {
          xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
        }        
      } else {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
      }
    }
  }
  
  
	function payment_action() {
    global $insert_id;

    $result = $this->FinishOrder($insert_id);
    if ($result->status == 'COMPLETED' && $result->transaction_status == 'COMPLETED') {
      xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
    }

    // cancel pp order
    $this->remove_order($_SESSION['tmp_oID']);
    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));
  }


  function after_process() {
    unset($_SESSION['paypal']);
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
