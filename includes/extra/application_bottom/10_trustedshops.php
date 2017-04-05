<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  // include needed defaults
  require_once(DIR_FS_EXTERNAL.'trustedshops/trustedshops.php');

  if (defined('MODULE_TS_TRUSTEDSHOPS_ID')) {
    // trustedshops badge
    if (MODULE_TS_TRUSTBADGE_CODE != '' && MODULE_TS_TRUSTBADGE_VARIANT == 'custom') {
      echo sprintf(MODULE_TS_TRUSTBADGE_CODE).PHP_EOL;
    } else {
      echo sprintf($default_trustbadge_code, MODULE_TS_TRUSTEDSHOPS_ID, MODULE_TS_TRUSTBADGE_OFFSET, MODULE_TS_TRUSTBADGE_VARIANT).PHP_EOL;
    }
  
    // trustedshops snippets
    if (MODULE_TS_SNIPPETS != '') {
      $filename = strtok(basename($PHP_SELF), '.');
      if (basename($PHP_SELF) == FILENAME_DEFAULT && !isset($_GET['cPath']) && !isset($_GET['manufacturers_id'])) {
        $filename = 'home';
      }
      $ts_snippets = explode(', ', MODULE_TS_SNIPPETS);
      if (in_array($filename, $ts_snippets)) {
        if (!is_file(SQL_CACHEDIR.'reviews.xml') || time() - filemtime(SQL_CACHEDIR.'reviews.xml') > 3600) {
          // include needed functions
          require_once (DIR_FS_INC.'get_external_content.inc.php');
  
          $url = 'https://api.trustedshops.com/rest/public/v2/shops/'.MODULE_TS_TRUSTEDSHOPS_ID.'/quality/reviews.xml';
          $reviews_api = get_external_content($url, 3, false);
          file_put_contents(SQL_CACHEDIR.'reviews.xml', $reviews_api, LOCK_EX);
          $reviews_xml = simplexml_load_string($reviews_api);
        } else {
          $reviews_xml = simplexml_load_file(SQL_CACHEDIR.'reviews.xml');
        }
        $reviews_total = $reviews_xml->data->shop->qualityIndicators->reviewIndicator->overallMark;
        $reviews_count = $reviews_xml->data->shop->qualityIndicators->reviewIndicator->activeReviewCount;
        $reviews_max = '5.00';
        ?>
        <span xmlns:v="http://rdf.data-vocabulary.org/#" typeof="v:Review-aggregate">
          <span rel="v:rating">
            <meta property="v:value" content="<?php echo $reviews_total;?>" />
            <meta property="v:best" content="<?php echo $reviews_max;?>" />
          </span>
          <meta property="v:votes" content="<?php echo $reviews_count;?>" />
        </span>
      <?php
      }
    }
  
    // trustedshops widget
    if (MODULE_TS_WIDGET == '1') {
      if (!is_file(SQL_CACHEDIR.MODULE_TS_TRUSTEDSHOPS_ID.'.gif') || filemtime(SQL_CACHEDIR.MODULE_TS_TRUSTEDSHOPS_ID.'.gif') < (time() - 3600)) {
        // include needed functions
        require_once (DIR_FS_INC.'get_external_content.inc.php');

        $url = 'https://widgets.trustedshops.com/reviews/widgets/'.MODULE_TS_TRUSTEDSHOPS_ID.'.gif';
        $widget = get_external_content($url, 3, false);
        file_put_contents(SQL_CACHEDIR.MODULE_TS_TRUSTEDSHOPS_ID.'.gif', $widget, LOCK_EX);
      
        $image = @getimagesize(SQL_CACHEDIR.MODULE_TS_TRUSTEDSHOPS_ID.'.gif');
        $image_type = $image[2];
        if(!in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP))) {
          @unlink(SQL_CACHEDIR.MODULE_TS_TRUSTEDSHOPS_ID.'.gif');
        }
      }
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
      Moneybookers.com                MONEYBOOKERS
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
        case 'paypalinstallment':
        case 'paypallink':
        case 'paypalplus':
        case 'paypalpluslink':
        case 'payone_wlt':
          $paymenttype = 'PAYPAL';
          break;
        case 'billpaydebit':
        case 'masterpayment_credit_card':
        case 'masterpayment_debit_card':
        case 'mcp_creditcard':
        case 'mcp_debit':
        case 'payone_cc':
          $paymenttype = 'CREDIT_CARD';
          break;
        case 'banktransfer':
        case 'masterpayment_elv':
        case 'payone_elv':
          $paymenttype = 'DIRECT_DEBIT';
          break;
        case 'moneyorder':
        case 'eustandardtransfer':
        case 'mcp_prepay':
        case 'payone_prepay':
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
        case 'billpay':
        case 'klarna_invoice':
        case 'billsafe_2':
        case 'masterpayment_rechnungskauf':
        case 'payone_invoice':
          $paymenttype = 'INVOICE';
          break;
        case 'sofort_sofortueberweisung_classic':
        case 'sofort_sofortueberweisung_gateway':
        case 'masterpayment_sofortbanking':
        case 'mcp_ebank2pay':
          $paymenttype = 'DIRECT_E_BANKING';
          break;
        case 'amoneybookers':
        case 'moneybookers_cc':
        case 'moneybookers_cgb':
        case 'moneybookers_csi':
        case 'moneybookers_elv':
        case 'moneybookers_giropay':
        case 'moneybookers_ideal':
        case 'moneybookers_mae':
        case 'moneybookers_netpay':
        case 'moneybookers_psp':
        case 'moneybookers_pwy':
        case 'moneybookers_sft':
        case 'moneybookers_wlt':
          $paymenttype = 'MONEYBOOKERS';
          break;
        case 'billpaypaylater':
        case 'billpaytransactioncredit':
        case 'klarna_partPayment':
        case 'masterpayment_anzahlungskauf':
        case 'masterpayment_finanzierung':
        case 'masterpayment_ratenzahlung':
        case 'payone_installment':
          $paymenttype = 'FINANCING';
          break;
        case 'klarna_SpecCamp':
        case 'masterpayment_phone':
        case 'payone_otrans':
        case 'shopgate':
        case 'sofort_ideal':
        case 'worldpay_junior':
        case 'payone_paydirekt':
          $paymenttype = 'OTHER';
          break;
        default:
          $paymenttype = '';
          break;
      }
      ?>
      <div id="trustedShopsCheckout" style="display: none;"> 
        <span id="tsCheckoutOrderNr"><?php echo $last_order; ?></span>
        <span id="tsCheckoutBuyerEmail"><?php echo $orders['customers_email_address']; ?></span>
        <span id="tsCheckoutOrderAmount"><?php echo $total; ?></span>
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