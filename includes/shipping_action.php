<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

	if ((xtc_count_shipping_modules() > 0) || ($free_shipping == true)) {
		if ((isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_'))) {
			$_SESSION['shipping'] = $_POST['shipping'];#sec

			list ($module, $method) = explode('_', $_SESSION['shipping']);
			if ((isset(${$module}) && is_object(${$module}) ) || ($_SESSION['shipping'] == 'free_free')) {
				if ($_SESSION['shipping'] == 'free_free') {
					$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
					$quote[0]['methods'][0]['cost'] = '0';
				} else {
					$quote = $shipping_modules->quote($method, $module);
				}
				if (isset($quote['error'])) {
					unset ($_SESSION['shipping']);
				} else {
					if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
						$_SESSION['shipping'] = array (
              'id' => $_SESSION['shipping'], 
              'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'].(($quote[0]['methods'][0]['title'] != '') ? ' ('.$quote[0]['methods'][0]['title'].')' : '')), 
              'cost' => $quote[0]['methods'][0]['cost']
            );
            if (isset($redirect_link) && $redirect_link != '') {
						  xtc_redirect($redirect_link);
						}
					}
				}
			} else {
				unset ($_SESSION['shipping']);
			}
    } else {
      $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
		}
	} else {
		$_SESSION['shipping'] = false;
    $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_MODULE);
	}
?>