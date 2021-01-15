<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

      // get all available shipping quotes
      $quotes = $shipping_modules->quote();

      $module_error = $method_error = ($free_shipping != true);
      foreach ($quotes as $modules) {
        if ($modules['id'] == $module) {
          $module_error = false;
          foreach ($modules['methods'] as $methods) {
            if ($methods['id'] == $method) {
              $method_error = false;
            }
          }
        }
      }
      
      if ($module_error === true) {
        unset ($_SESSION['shipping']);
        $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_MODULE);
      }

      if ($method_error === true) {
        unset ($_SESSION['shipping']);
        $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
      }
      
      if ($method_error === false && $module_error === false) {
        if ((isset(${$module}) && is_object(${$module}) ) || ($_SESSION['shipping'] == 'free_free')) {
          if ($_SESSION['shipping'] == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote[0]['error'])) {
            unset ($_SESSION['shipping']);
          } else {
            if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
              if ($free_shipping == true) {
                $title = $quote[0]['methods'][0]['title'];
              } else {
                $title = $quote[0]['module'];
                if (!defined('SHOW_SHIPPING_TITLE') || SHOW_SHIPPING_TITLE == 'shipping_default') {
                  $title .= ((trim($quote[0]['methods'][0]['title']) != '') ? ' ('.$quote[0]['methods'][0]['title'].')' : '');
                } elseif (SHOW_SHIPPING_TITLE == 'shipping_custom' && parse_multi_language_value(CUSTOM_SHIPPING_TITLE, $_SESSION['language_code']) != '') {
                  $title = parse_multi_language_value(CUSTOM_SHIPPING_TITLE, $_SESSION['language_code']);
                }
              }
              $_SESSION['shipping'] = array (
                'id' => $_SESSION['shipping'], 
                'title' => $title, 
                'cost' => $quote[0]['methods'][0]['cost']
              );
              if (isset(${$module}) && is_object(${$module}) && method_exists(${$module}, 'session') ) {
                ${$module}->session($method, $module, $quote); 
              } 
              if (isset($redirect_link) && $redirect_link != '') {
                xtc_redirect($redirect_link);
              }
            }
          }
        } else {
          unset ($_SESSION['shipping']);
        }
			}
    } else {
      $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_METHOD);
		}
	} else {
		$_SESSION['shipping'] = false;
    $smarty->assign('error', ERROR_CHECKOUT_SHIPPING_NO_MODULE);
	}
?>