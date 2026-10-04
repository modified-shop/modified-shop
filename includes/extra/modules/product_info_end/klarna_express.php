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
      && defined('MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID')
      && MODULE_PAYMENT_KLARNA_EXPRESS_CLIENT_ID != ''
      && (!defined('MODULE_PAYMENT_KLARNA_EXPRESS_BUTTON_LOCATION')
          || MODULE_PAYMENT_KLARNA_EXPRESS_BUTTON_LOCATION == 'product_page'
          )
      && $_SESSION['customers_status']['customers_status_show_price'] != '0'
      && (($_SESSION['customers_status']['customers_fsk18'] == '1' && $product->data['products_fsk18'] == '0')
          || $_SESSION['customers_status']['customers_fsk18'] != '1'
          )
      && $xtPrice->get_content_type_product($product->data['products_id']) != 'virtual'
      && !preg_match('/^GIFT/', $product->data['products_model'])
      )
  {
    // include needed classes
    include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/klarna_express.php');
    require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

    $klarna_express = new klarna_express();

    if ($klarna_express::express_configured() === true
        && $klarna_express->payment_allowed() === true
        && $klarna_express->checkout_enabled() === true
        )
    {
      $klarna_express_config = $klarna_express->get_express_config(
        null,
        str_replace('&amp;', '&', xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error=klarna_express', 'NONSSL'))
      );
      $klarna_express_config['ajax_url'] = str_replace('&amp;', '&', xtc_href_link('ajax.php', 'action=add_product&ext=klarna_express_payload', $request_type));
      $klarna_express_config['ajax_token'] = $klarna_express->get_ajax_token();
      $klarna_express_config['error_text'] = html_entity_decode(MODULE_PAYMENT_KLARNA_EXPRESS_TEXT_ERROR_ADD, ENT_QUOTES, 'UTF-8');

      // the product is added with the form data first, authorize() needs the real cart amount
      $klarna_express_on_click = $klarna_express->get_express_fetch_js('cart_quantity');

      $klarna_express_button = $klarna_express->get_express_button($klarna_express_config, $klarna_express_on_click);
      if ($klarna_express_button != '') {
        $info_smarty->assign('ADD_CART_BUTTON_KLARNA', '<div class="klarna_express_product">'.$klarna_express_button.'</div>');
      }
    }
  }
