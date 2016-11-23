<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
include('includes/application_top.php');


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');                                      


if (isset($_GET['amount']) 
    && is_numeric($_GET['amount']) 
    && isset($_GET['country']) 
    && $_GET['country'] == 'DE'
    ) 
{
  $paypal_installment = new PayPalPayment('paypalinstallment');

  if ($paypal_installment->enabled === true) {
    $presentment_array = $paypal_installment->get_presentment($_GET['amount'], ((isset($_GET['currency'])) ? $_GET['currency'] : $_SESSION['currency']), $_GET['country']);

    $pp_smarty = new Smarty();
    $pp_smarty->assign('logo_image', xtc_image(DIR_WS_IMAGES.'icons/pp_credit-german_v_rgb.png'));
    $pp_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
    $pp_smarty->assign('html_params', ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' '.HTML_PARAMS : ' lang="'.$_SESSION['language_code'].'"'));
    $pp_smarty->assign('doctype', ((TEMPLATE_HTML_ENGINE == 'xhtml') ? ' PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"' : ''));
    $pp_smarty->assign('charset', $_SESSION['language_charset']);
    if (DIR_WS_BASE == '') {
      $pp_smarty->assign('base', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);
    }

    $store_owner = explode("\n", STORE_NAME_ADDRESS);
    for ($i=0, $n=count($store_owner); $i<$n; $i++) {
      if (trim($store_owner[$i]) == '') {
        unset($store_owner[$i]);
      } else {
        $store_owner[$i] = trim($store_owner[$i]);
      }
    }
    $store_owner = implode(', ', $store_owner);

    $pp_smarty->assign('creditor', $store_owner);
    $pp_smarty->assign('total_amount', $xtPrice->xtcFormat($_GET['amount'], true));
    $pp_smarty->assign('presentment', $presentment_array);

    $pp_smarty->assign('language', $_SESSION['language']);
    $pp_smarty->display(DIR_FS_EXTERNAL.'paypal/templates/presentment.html');
  } else {
    die('Direct Access to this location is not allowed.');
  }
} else {
  die('Direct Access to this location is not allowed.');
}
?>