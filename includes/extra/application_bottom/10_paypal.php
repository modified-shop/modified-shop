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
      && basename($PHP_SELF) == FILENAME_CHECKOUT_PAYMENT
      )
  {
    // include needed classes
    require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');
    $paypal_installment = new PayPalPayment('paypalinstallment');
    
    if ($paypal_installment->check_install() === true
        && $paypal_installment->get_config('PAYPAL_MODE') == 'live'
        && $paypal_installment->get_config('PAYPAL_INSTALLMENT_BANNER_DISPLAY') == 1
        )
    {
      $client_id = $paypal_installment->get_config('PAYPAL_CLIENT_ID_'.strtoupper($paypal_installment->get_config('PAYPAL_MODE')));
    
      if ($client_id != '') {
        require (DIR_FS_EXTERNAL.'paypal/modules/installment.php');
        echo sprintf($installment_js, $client_id, $order->info['currency'], $order->info['total'], $order->billing["country"]["iso_code_2"], 'flex', $paypal_installment->get_config('PAYPAL_INSTALLMENT_BANNER_COLOR'));
      }
    }
  }

  if (strstr($PHP_SELF, FILENAME_PRODUCT_INFO )) {
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