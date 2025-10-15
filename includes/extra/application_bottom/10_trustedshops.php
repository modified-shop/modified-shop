<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (defined('MODULE_TS_TRUSTEDSHOPS_ID')
      && $shop_is_offline === false
      )
  {  
    // include needed defaults
    require_once(DIR_FS_EXTERNAL.'trustedshops/trustedshops.php');

    // trustedshops badge
    if (MODULE_TS_TRUSTBADGE_CODE != '' && MODULE_TS_TRUSTBADGE_VARIANT == 'custom') {
      echo sprintf(MODULE_TS_TRUSTBADGE_CODE, MODULE_TS_TRUSTEDSHOPS_ID).PHP_EOL;
    } else {
      echo sprintf($default_trustbadge_code, MODULE_TS_TRUSTBADGE_OFFSET, MODULE_TS_TRUSTBADGE_OFFSET, (MODULE_TS_TRUSTBADGE_VARIANT == 'default' ? 'true' : 'false'), (MODULE_TS_TRUSTBADGE_VARIANT == 'default' ? 'true' : 'false'), MODULE_TS_TRUSTBADGE_POSITION, MODULE_TS_TRUSTBADGE_POSITION_MOBILE, MODULE_TS_TRUSTEDSHOPS_ID).PHP_EOL;
    }
          
    // trustedshops trustcard
    if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) {
      // includes needed functions
      require_once (DIR_FS_INC.'get_order_total.inc.php');
    
      $total = get_order_total($last_order);
  
      $orders_query = xtc_db_query("SELECT customers_email_address,
                                           payment_class,
                                           currency
                                      FROM ".TABLE_ORDERS."
                                     WHERE orders_id = '".(int)$last_order."'");
      $orders = xtc_db_fetch_array($orders_query);
      
      $payment_class = $orders['payment_class'];
      /* ZAHLUNGSART
      Lastschrift/Bankeinzug          DIRECT_DEBIT
      Kreditkarte                     CREDIT_CARD
      Rechnung                        INVOICE
      Nachnahme                       CASH_ON_DELIVERY
      Vorauskasse / Überweisung       PREPAYMENT
      Verrechnungsscheck              CHEQUE
      Paybox                          PAYBOX
      PayPal                          PAYPAL
      Amazon Payments                 AMAZON_PAYMENTS
      Zahlung bei Abholung            CASH_ON_PICKUP
      Finanzierung                    FINANCING
      Leasing                         LEASING
      T-Pay                           T_PAY
      Click&Buy (Firstgate)           CLICKANDBUY
      Giropay                         GIROPAY
      Google Checkout                 GOOGLE_CHECKOUT
      Online Shop Zahlungskarte       SHOP_CARD
      Sofortüberweisung.de            DIRECT_E_BANKING
      Dotpay                          DOTPAY
      Płatności                       PLATNOSCI
      Przelewy24                      PRZELEWY24
      Andere Zahlungsart              OTHER
      */
      switch ($orders['payment_class'])
      {
        case 'paypal':
        case 'paypal_ipn':
        case 'paypalcart':
        case 'paypalclassic':
        case 'paypallink':
        case 'paypalplus':
        case 'paypalpluslink':
        case 'paypalsubscription':
        case 'payone_wlt':
          $paymenttype = 'PAYPAL';
          break;
        case 'mcp_creditcard':
        case 'mcp_debit':
        case 'payone_cc':
          $paymenttype = 'CREDIT_CARD';
          break;
        case 'banktransfer':
        case 'payone_elv':
          $paymenttype = 'DIRECT_DEBIT';
          break;
        case 'moneyorder':
        case 'eustandardtransfer':
        case 'mcp_prepay':
        case 'payone_prepay':
        case 'klarna_directdebit':
          $paymenttype = 'PREPAYMENT';
          break;
        case 'cash':
          $paymenttype = 'CASH_ON_PICKUP';
          break;
        case 'cod':
        case 'payone_cod':
          $paymenttype = 'CASH_ON_DELIVERY';
          break;
        case 'invoice':
        case 'klarna_paylater':
        case 'payone_invoice':
          $paymenttype = 'INVOICE';
          break;
        case 'klarna_directbanktransfer':
        case 'klarna_paynow':
        case 'mcp_ebank2pay':
          $paymenttype = 'DIRECT_E_BANKING';
          break;
        case 'klarna_payovertime':
        case 'payone_installment':
          $paymenttype = 'FINANCING';
          break;
        default:
          $paymenttype = 'OTHER';
          break;
      }
      ?>
      <div id="trustedShopsCheckout" style="display: none;"> 
        <span id="tsCheckoutOrderNr"><?php echo $last_order; ?></span>
        <span id="tsCheckoutBuyerEmail"><?php echo $orders['customers_email_address']; ?></span>
        <span id="tsCheckoutOrderAmount"><?php echo number_format($total, 2, '.', ''); ?></span>
        <span id="tsCheckoutOrderCurrency"><?php echo $orders['currency']; ?></span>
        <span id="tsCheckoutOrderPaymentType"><?php echo $paymenttype; ?></span>
        <?php
        $item_query = xtc_db_query("SELECT op.products_id,
                                           op.orders_products_id,
                                           op.products_model,
                                           op.products_name,
                                           op.products_price,
                                           op.products_quantity,
                                           p.products_image,
                                           p.products_ean,
                                           p.products_manufacturers_model,
                                           m.manufacturers_name
                                      FROM ".TABLE_ORDERS_PRODUCTS." op
                                      JOIN ".TABLE_PRODUCTS." p
                                           ON op.products_id = p.products_id
                                 LEFT JOIN ".TABLE_MANUFACTURERS." m
                                           ON p.manufacturers_id = m.manufacturers_id
                                     WHERE op.orders_id='" . (int)$last_order . "'");
  
        while ($item = xtc_db_fetch_array($item_query)) {
        ?>
          <span class="tsCheckoutProductItem">
            <span class="tsCheckoutProductUrl"><?php echo xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$item['products_id'], 'NONSSL', false, false); ?></span>
            <span class="tsCheckoutProductImageUrl"><?php echo (($item['products_image'] != '') ? xtc_href_link(DIR_WS_INFO_IMAGES.$item['products_image'], '', 'NONSSL', false, false) : ''); ?></span>
            <span class="tsCheckoutProductName"><?php echo $item['products_name']; ?></span>
            <span class="tsCheckoutProductSKU"><?php echo $item['products_model']; ?></span>
            <span class="tsCheckoutProductGTIN"><?php echo $item['products_ean']; ?></span>
            <span class="tsCheckoutProductMPN"><?php echo $item['products_manufacturers_model']; ?></span>
            <span class="tsCheckoutProductBrand"><?php echo $item['manufacturers_name']; ?></span>
          </span>
        <?php
        }
      ?>
      </div>
    <?php
    }
  }
?>