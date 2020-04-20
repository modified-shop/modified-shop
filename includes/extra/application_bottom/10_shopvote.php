<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_SHOPVOTE_STATUS')
      && MODULE_SHOPVOTE_STATUS == 'true'
      )
  {
    $language_array = array('DE', 'EN', 'FR', 'IT', 'NL', 'ES');
    $language_code = strtoupper((in_array(strtoupper($_SESSION['language_code']), $language_array)) ? $_SESSION['language_code'] : DEFAULT_LANGUAGE);

    if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS
        && isset($last_order)
        && MODULE_SHOPVOTE_API != ''
        )
    {
      $item_query = xtc_db_query("SELECT op.products_id,
                                         op.products_model,
                                         op.products_name,
                                         op.products_ean,
                                         o.customers_email_address,
                                         p.products_image,
                                         m.manufacturers_name
                                    FROM ".TABLE_ORDERS." o
                                    JOIN ".TABLE_ORDERS_PRODUCTS." op
                                         ON o.orders_id = op.orders_id
                                    JOIN ".TABLE_PRODUCTS." p
                                         ON op.products_id = p.products_id
                               LEFT JOIN ".TABLE_MANUFACTURERS." m
                                         ON m.manufacturers_id = p.manufacturers_id
                                   WHERE o.orders_id='".(int)$last_order."'
                                GROUP BY op.products_id");
      
      $data = $customers_email_address = '';
      while ($item = xtc_db_fetch_array($item_query)) {
        $data .= '
        <span class="SVCheckoutProductItem">
          <span class="sv-i-product-url">'.xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$item['products_id']).'</span>
          <span class="sv-i-product-image-url">'.$product->productImage($item['products_image'], 'info').'</span>
          <span class="sv-i-product-name">'.$item['products_name'].'</span>
          <span class="sv-i-product-gtin">'.$item['products_ean'].'</span>
          <span class="sv-i-product-sku">'.$item['products_id'].'</span>
          <span class="sv-i-product-brand">'.$item['manufacturers_name'].'</span>
        </span>';
        
        $customers_email_address = $item['customers_email_address'];
      }
      
      echo '  
      <div id="srt-customer-data" style="display:none;">
        <span id="srt-customer-email">'.$customers_email_address.'</span>
        <span id="srt-customer-reference">'.(int)$last_order.'</span>
      </div>
      <div id="SHOPVOTECheckoutProducts" style="display: none;" translate="no">
        '.$data.'
      </div>';
    
      echo '
      <script src="https://feedback.shopvote.de/srt-v4.min.js"></script>
      <script type="text/javascript">
        var myToken = "'.MODULE_SHOPVOTE_API.'";
        var myLanguage = "'.$language_code.'";
        var mySrc = ("https:" === document.location.protocol ? "https" : "http");
        loadSRT(myToken, mySrc);
      </script>';
    }
    
    if (MODULE_SHOPVOTE_SHOPID != '') {
      echo '
      <script src="https://widgets.shopvote.de/js/reputation-badge-v2.min.js"></script>
      <script>
        var myShopID = '.(int)MODULE_SHOPVOTE_SHOPID.';
        var myBadgetType = 1;
        var myLanguage = "'.$language_code.'";
          var mySrc = ("https:" === document.location.protocol ? "https" : "http");
        createRBadge(myShopID, myBadgetType, mySrc);
      </script>';
    }
  }
?>