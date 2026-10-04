<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the Klarna express request of the product page adds the product only with a valid token
  if (defined('RUN_MODE_AJAX')
      && isset($_GET['ext'])
      && $_GET['ext'] === 'klarna_express_payload'
      )
  {
    // include needed classes
    require_once(DIR_FS_EXTERNAL.'klarna/functions/klarna_express_language.php');
    klarna_express_include_language();
    require_once(DIR_FS_CATALOG.'includes/modules/payment/klarna_express.php');

    $klarna_express_ajax = new klarna_express();
    if (klarna_express::express_configured()
        && $klarna_express_ajax->is_valid_ajax_token()
        && isset($_POST['products_id'])
        && is_numeric($_POST['products_id'])
        )
    {
      $klarna_express_uprid = xtc_get_uprid($_POST['products_id'], isset($_POST['id']) ? $_POST['id'] : '');
      $klarna_express_cart_before = array(
        'id' => $klarna_express_uprid,
        'quantity' => $cart_object->get_quantity($klarna_express_uprid),
      );
    } else {
      unset($_POST['products_id']);
    }
  }
