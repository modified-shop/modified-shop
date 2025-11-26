<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_PAYMENT_PAYPAL_SECRET')
      && MODULE_PAYMENT_PAYPAL_SECRET != ''
      )
  {
    // include needed classes
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

    // include needed functions
    require_once (DIR_FS_INC.'xtc_get_countries.inc.php');

    $paypalscript = '';
    $paypal_user_token = false;
    if (!isset($_SESSION['paypal_instruments']) 
        && ((defined('MODULE_PAYMENT_PAYPALSEPA_STATUS') && MODULE_PAYMENT_PAYPALSEPA_STATUS == 'True')
            || (defined('MODULE_PAYMENT_PAYPALCARD_STATUS') && MODULE_PAYMENT_PAYPALCARD_STATUS == 'True')
            || (defined('MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS') && MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS == 'True')
            )
        && (isset($_SESSION['customer_id']) 
            || strpos(basename($PHP_SELF), 'account') !== false
            || strpos(basename($PHP_SELF), 'checkout') !== false
            || basename($PHP_SELF) == FILENAME_SHOPPING_CART
            || basename($PHP_SELF) == FILENAME_LOGIN
            )
        )
    {
      $paypal = new PayPalPaymentV2('paypal');

      $paypalscript .= '
        var paypal_instruments_arr = [];
        paypal.getFundingSources().forEach(function(fundingSource) {        
          var button = paypal.Buttons({fundingSource: fundingSource});
          if (button.isEligible()) {
            paypal_instruments_arr.push(fundingSource);
          }
        });
        $.post("'.DIR_WS_BASE.'ajax.php?ext=set_paypal_instruments", {paypal_instruments: paypal_instruments_arr});
      ';

      if (defined('MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS') && MODULE_PAYMENT_PAYPALAPPLEPAY_STATUS == 'True') {
      $paypalscript .= '
        const applepay = paypal.Applepay().config().then((data) => {
          if (data.isEligible === true) {
            if (typeof ApplePaySession != "undefined" 
                && ApplePaySession?.supportsVersion(4) 
                && ApplePaySession?.canMakePayments()
                )
            {
              paypal_instruments_arr.push("applepay");
              $.post("'.DIR_WS_BASE.'ajax.php?ext=set_paypal_instruments", {paypal_instruments: paypal_instruments_arr});
            }
          }
        });
      ';
      }
    }

    if ((basename($PHP_SELF) == FILENAME_SHOPPING_CART && $_SESSION['cart']->count_contents() > 0) 
         || (strpos(basename($PHP_SELF), 'checkout') === false && $_SESSION['cart']->count_contents() > 0)
         || basename($PHP_SELF) == FILENAME_PRODUCT_INFO 
        )
    {         
      $paypal = new PayPalPaymentV2('paypalexpress');
            
      if ($paypal->is_enabled()) {
        if ($paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SAVE_PAYMENT') == '1') {
          $paypal_user_token = $paypal->GenerateUserToken()->tokenId;
        }
        
        $action = '';
        if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
          $action = 'action=add_product&';
        }
        $url = str_replace('&amp;', '&', xtc_href_link('ajax.php', $action.'ext=create_paypal_order&payment_method='.$paypal->code));
        
        if (basename($PHP_SELF) == FILENAME_SHOPPING_CART 
            || $paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_PRODUCT') == '1'
            )
        {
          $paypalscript .= '
          if ($("#apms_button1").length) {
            paypal.Buttons({
              fundingSource: paypal.FUNDING.PAYPAL,
              style: {
                layout: "'.$paypal->get_config('PAYPAL_BUTTON_LAYOUT').'",
                shape: "'.$paypal->get_config('PAYPAL_BUTTON_SHAPE').'",
                color: "'.$paypal->get_config('PAYPAL_BUTTON_PRIMARY_COLOR').'",
                height: '.$paypal->get_config('PAYPAL_BUTTON_HEIGHT').'
              },
              createOrder: function(data, actions) {              
                var formdata = '.((basename($PHP_SELF) == FILENAME_PRODUCT_INFO) ? '$("#cart_quantity").serializeArray()' : "''").'; 

                return $.ajax({
                  type: "POST",
                  url: "'.$url.'",
                  data: formdata,
                  dataType: "json"
                });        
              },
              onApprove: function(data, actions) {
                window.location.href = "'.xtc_href_link('callback/paypal/paypalexpress.php').'";
              },
              onError: function (err) {
                $("#apms_buttons").hide();
                console.error("failed to load PayPal buttons", err);
              },
              onRender: function() { 
                $(".apms_form_button_overlay").hide();
              }
            }).render("#apms_button1");
          }
          ';
        }
        
        if ((basename($PHP_SELF) == FILENAME_SHOPPING_CART  
             && $paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_CART_BNPL') == '1'
             ) || (basename($PHP_SELF) == FILENAME_PRODUCT_INFO  
                   && $paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_PRODUCT_BNPL') == '1'
                   )
            )
        {
          $paypalscript .= '
          if ($("#apms_button2").length) {
            paypal.Buttons({
              fundingSource: paypal.FUNDING.PAYLATER,
              style: {
                layout: "'.$paypal->get_config('PAYPAL_BUTTON_LAYOUT').'",
                shape: "'.$paypal->get_config('PAYPAL_BUTTON_SHAPE').'",
                color: "'.$paypal->get_config('PAYPAL_BUTTON_SECONDARY_COLOR').'",
                height: '.$paypal->get_config('PAYPAL_BUTTON_HEIGHT').'
              },
              createOrder: function(data, actions) {              
                var formdata = '.((basename($PHP_SELF) == FILENAME_PRODUCT_INFO) ? '$("#cart_quantity").serializeArray()' : "''").'; 

                return $.ajax({
                  type: "POST",
                  url: "'.$url.'",
                  data: formdata,
                  dataType: "json"
                });        
              },
              onApprove: function(data, actions) {
                window.location.href = "'.xtc_href_link('callback/paypal/paypalexpress.php').'";
              },
              onRender: function() { 
                $("#apms_bnpl").show();
                $(".apms_form_button_overlay").hide();
              }
            }).render("#apms_button2");
          }
          ';
        }

        if ($paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_BOX_CART') == '1'
            && strpos(basename($PHP_SELF), 'checkout') === false
            && $_SESSION['cart']->count_contents() > 0
            )
        {
          $paypalscript .= '
          if ($("#apms_button3").length) {
            paypal.Buttons({
              fundingSource: paypal.FUNDING.PAYPAL,
              style: {
                layout: "'.$paypal->get_config('PAYPAL_BUTTON_LAYOUT').'",
                shape: "'.$paypal->get_config('PAYPAL_BUTTON_SHAPE').'",
                color: "'.$paypal->get_config('PAYPAL_BUTTON_PRIMARY_COLOR').'",
                height: '.$paypal->get_config('PAYPAL_BUTTON_HEIGHT').'
              },
              createOrder: function(data, actions) {              
                var formdata = \'\'; 

                return $.ajax({
                  type: "POST",
                  url: "'.$url.'",
                  data: formdata,
                  dataType: "json"
                });        
              },
              onApprove: function(data, actions) {
                window.location.href = "'.xtc_href_link('callback/paypal/paypalexpress.php').'";
              },
              onError: function (err) {
                $("#apms_buttons").hide();
                console.error("failed to load PayPal buttons", err);
              },
            }).render("#apms_button3");
          }
          ';
        }

        if ($paypal->get_config('MODULE_PAYMENT_'.strtoupper($paypal->code).'_SHOW_BOX_CART_BNPL') == '1'
            && strpos(basename($PHP_SELF), 'checkout') === false 
            && $_SESSION['cart']->count_contents() > 0
            )
        {
          $paypalscript .= '
          if ($("#apms_button4").length) {
            paypal.Buttons({
              fundingSource: paypal.FUNDING.PAYLATER,
              style: {
                layout: "'.$paypal->get_config('PAYPAL_BUTTON_LAYOUT').'",
                shape: "'.$paypal->get_config('PAYPAL_BUTTON_SHAPE').'",
                color: "'.$paypal->get_config('PAYPAL_BUTTON_SECONDARY_COLOR').'",
                height: '.$paypal->get_config('PAYPAL_BUTTON_HEIGHT').'
              },
              createOrder: function(data, actions) {              
                var formdata = \'\'; 

                return $.ajax({
                  type: "POST",
                  url: "'.$url.'",
                  data: formdata,
                  dataType: "json"
                });        
              },
              onApprove: function(data, actions) {
                window.location.href = "'.xtc_href_link('callback/paypal/paypalexpress.php').'";
              },
            }).render("#apms_button4");
          }
          ';
        }
      }
    }
        
    if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
      $paypal = new PayPalPayment('paypalinstallment');
      
      if ($paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_DISPLAY') == 1
          && $paypal->get_config('MODULE_PAYMENT_PAYPAL_SAVE_PAYMENT') != 1
          )
      {
        $total = 0;  
        if (is_object($product) 
            && $product->isProduct() !== false
            )
        {
          $country = xtc_get_countriesList(((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)), true);
          $countries_iso_code_2 = $country['countries_iso_code_2'];
          $total = $xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id'], $product->data['products_price']); 
        }
        
        if ($total > 0) {
          $paypalscript .= '
          if ($(".pp-message").length) {
            paypal.Messages({
              amount: '.sprintf($paypal->numberFormat, $total).',
              countryCode: "'.$countries_iso_code_2.'",
              placement: "product",
              style: {
                layout: "text",
                logo: {
                  type: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_LOGOTYPE').'",
                  position: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_LOGOPOSITION').'"
                },
                text: {
                  color: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_TEXTCOLOR').'",
                  size: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_PRODUCT_TEXTSIZE').'"
                }
              },
              onError: function (err) {
                $(".pp-message").hide();
                console.error("failed to load PayPal banner", err);
              }
            }).render(".pp-message");
          }
          ';
        }
      }
    }

    if (basename($PHP_SELF) == FILENAME_SHOPPING_CART 
        && $_SESSION['cart']->count_contents() > 0
        )
    {
      $paypal = new PayPalPayment('paypalinstallment');
      
      if ($paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CART_DISPLAY') == 1
          && $paypal->get_config('MODULE_PAYMENT_PAYPAL_SAVE_PAYMENT') != 1
          )
      {
        $country = xtc_get_countriesList(((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)), true);
        $countries_iso_code_2 = $country['countries_iso_code_2'];
        $total = $_SESSION['cart']->show_total();
        
        if ($total > 0) {
          $paypalscript .= '
          if ($(".pp-message").length) {
            paypal.Messages({
              amount: '.sprintf($paypal->numberFormat, $total).',
              countryCode: "'.$countries_iso_code_2.'",
              placement: "cart",
              style: {
                layout: "flex",
                color: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CART_COLOR').'",
                ratio: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CART_SIZE').'",
              },
              onError: function (err) {
                $(".pp-message").hide();
                console.error("failed to load PayPal banner", err);
              },
              onRender: function() { 
                $(".pp-message").css("margin-top", "20px");
              }
            }).render(".pp-message");
          }
          ';
        }
      }
    }

    if (basename($PHP_SELF) == FILENAME_CHECKOUT_PAYMENT) {
      $paypal = new PayPalPayment('paypalinstallment');
      
      if ($paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CHECKOUT_DISPLAY') == 1
          && $paypal->get_config('MODULE_PAYMENT_PAYPAL_SAVE_PAYMENT') != 1
          )
      {
        $countries_iso_code_2 = $order->billing["country"]["iso_code_2"];
        $total = $order->info['total'];
        
        if ($total > 0) {
          $paypalscript .= '
          if ($(".pp-message").length) {
            paypal.Messages({
              amount: '.sprintf($paypal->numberFormat, $total).',
              countryCode: "'.$countries_iso_code_2.'",
              placement: "cart",
              style: {
                layout: "flex",
                color: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CHECKOUT_COLOR').'",
                ratio: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_CHECKOUT_SIZE').'",
              },
              onError: function (err) {
                $(".pp-message").hide();
                console.error("failed to load PayPal banner", err);
              },
              onRender: function() { 
                $(".pp-message").css("margin-top", "20px");
              }
            }).render(".pp-message");
          }
          ';
        }
      }
    }
    
    if ($paypalscript != '') {
      echo sprintf($paypal->get_js_sdk('false', false, $paypal_user_token), $paypalscript, '');
    }    

    if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
      $paypal = new PayPalPayment('paypalsubscription');    
      if ($paypal->is_enabled()) {
        ?>
        <script>
          $(document).ready(function () {      
            if (typeof $.fn.easyResponsiveTabs === 'function') {
              $('#horizontalAccordionPlan').easyResponsiveTabs({
                type: 'accordion', //Types: default, vertical, accordion     
                closed: true,     
                activate: function(event) { // Callback function if tab is switched
                  $(".resp-tab-active input[type=radio]").prop('checked', true);
                }
              });
            }
          });
        </script>
        <?php
      }
    }
  }
