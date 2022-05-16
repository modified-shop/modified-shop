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
    if (!isset($_SESSION['paypal_instruments']) 
        && ((defined('MODULE_PAYMENT_PAYPALSEPA_STATUS') && MODULE_PAYMENT_PAYPALSEPA_STATUS == 'True')
            || (defined('MODULE_PAYMENT_PAYPALCARD_STATUS') && MODULE_PAYMENT_PAYPALCARD_STATUS == 'True')
            )
        && (isset($_SESSION['customer_id']) 
            || strpos(basename($PHP_SELF), 'account') !== false
            || basename($PHP_SELF) == FILENAME_SHOPPING_CART
            || basename($PHP_SELF) == FILENAME_LOGIN
            )
        )
    {
      $paypal = new PayPalPaymentV2('paypal');

      $paypalscript .= '
        var paypal_instruments_arr = [];
        await paypal.getFundingSources().forEach(function(fundingSource) {        
          var button = paypal.Buttons({fundingSource: fundingSource});
          if (button.isEligible()) {
            paypal_instruments_arr.push(fundingSource);
          }
        });
        $.post("'.DIR_WS_BASE.'ajax.php?ext=set_paypal_instruments", {paypal_instruments: paypal_instruments_arr});
      ';
    }

    if ((basename($PHP_SELF) == FILENAME_SHOPPING_CART 
         && $_SESSION['cart']->count_contents() > 0
         ) || basename($PHP_SELF) == FILENAME_PRODUCT_INFO 
        )
    {         
      $paypal = new PayPalPaymentV2('paypalexpress');
            
      if ($paypal->is_enabled()) {
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
            await paypal.Buttons({
              fundingSource: paypal.FUNDING.PAYPAL,
              style: {
                layout: "horizontal",
                shape: "rect",
                color: "gold",
                height: 35
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
            await paypal.Buttons({
              fundingSource: paypal.FUNDING.PAYLATER,
              style: {
                layout: "horizontal",
                shape: "rect",
                color: "blue",
                height: 35
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
          ';
        }
      }
    }
        
    if (basename($PHP_SELF) == FILENAME_CHECKOUT_PAYMENT
        || basename($PHP_SELF) == FILENAME_PRODUCT_INFO
        || (basename($PHP_SELF) == FILENAME_SHOPPING_CART 
            && $_SESSION['cart']->count_contents() > 0
            )
        )
    {
      $paypal = new PayPalPayment('paypalinstallment');
      
      if ($paypal->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY') == 1) {
        $total = 0;  
        if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO 
            && is_object($product) 
            && $product->isProduct() !== false
            )
        {
          $country = xtc_get_countriesList(((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)), true);
          $countries_iso_code_2 = $country['countries_iso_code_2'];
          $total = $xtPrice->xtcGetPrice($product->data['products_id'], false, 1, $product->data['products_tax_class_id'], $product->data['products_price']); 
        } elseif (basename($PHP_SELF) == FILENAME_SHOPPING_CART) {
          $country = xtc_get_countriesList(((isset($_SESSION['country'])) ? $_SESSION['country'] : ((isset($_SESSION['customer_country_id'])) ? $_SESSION['customer_country_id'] : STORE_COUNTRY)), true);
          $countries_iso_code_2 = $country['countries_iso_code_2'];
          $total = $_SESSION['cart']->show_total();
        } elseif (isset($order) && is_object($order)) {
          $countries_iso_code_2 = $order->billing["country"]["iso_code_2"];
          $total = $order->info['total'];
        }
        
        if ($total > 0) {
          $paypalscript .= '
            await paypal.Messages({
              amount: '.$total.',
              countryCode: "'.$countries_iso_code_2.'",
              style: {
                layout: "'.((basename($PHP_SELF) == FILENAME_PRODUCT_INFO) ? 'text' : 'flex').'",
                color: "'.$paypal->get_config('PAYPAL_INSTALLMENT_BANNER_COLOR').'",
                ratio: "8x1"
              },
              onError: function (err) {
                $(".pp-message").hide();
                console.error("failed to load PayPal banner", err);
              },
              onRender: function() { 
                '.((basename($PHP_SELF) == FILENAME_PRODUCT_INFO) ? '' : '$(".pp-message").css("margin-top", "20px");').'
              }
            }).render(".pp-message");
          ';
        }
      }
    }
    
    if ($paypalscript != '') {
      echo sprintf($paypal->get_js_sdk('false'), $paypalscript);
    }    
  }

  if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
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