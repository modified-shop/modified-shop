<?php
/* -----------------------------------------------------------------------------------------
   $Id: checkout_shipping.php 2454 2011-12-06 14:44:38Z franky-n-xtcm $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(checkout_shipping.php,v 1.15 2003/04/08); www.oscommerce.com
   (c) 2003 nextcommerce (checkout_shipping.php,v 1.20 2003/08/20); www.nextcommerce.org
   (c) 2006 xtCommerce (checkout_shipping.php 1037 2005-07-17)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
include ('includes/application_top.php');

// pre-selection the cheapest shipping option
defined('CHECK_CHEAPEST_SHIPPING_MODUL') or define('CHECK_CHEAPEST_SHIPPING_MODUL', 'false'); // default: 'false'

// show selfpickup on free shipping
defined('SHOW_SELFPICKUP_FREE') or define('SHOW_SELFPICKUP_FREE', 'false'); // default: 'false'

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_address_label.inc.php');
require_once (DIR_FS_INC.'xtc_get_address_format_id.inc.php');
require_once (DIR_FS_INC.'xtc_count_shipping_modules.inc.php');

require (DIR_WS_INCLUDES.'checkout_requirements.php');

//express checkout
if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
  if (isset($_GET['express']) && $_GET['express'] == 'on') {
    $express_query = xtc_db_query("SELECT checkout_shipping,
                                        checkout_shipping_address
                                   FROM ".TABLE_CUSTOMERS_CHECKOUT." 
                                  WHERE customers_id = '".(int)$_SESSION['customer_id']."'");
    $express = xtc_db_fetch_array($express_query);
    if ($express['checkout_shipping_address'] != '') {
      $_SESSION['sendto'] = $express['checkout_shipping_address'];
    }
  }
}

// if no shipping destination address was selected, use the customers own address as default
if (!isset($_SESSION['sendto'])) {
	$_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
} else {
	// verify the selected shipping address
	$check_address_query = xtc_db_query("SELECT count(*) as total 
	                                       FROM ".TABLE_ADDRESS_BOOK." 
	                                      WHERE customers_id = '".(int) $_SESSION['customer_id']."' 
	                                        AND address_book_id = '".(int) $_SESSION['sendto']."'");
	$check_address = xtc_db_fetch_array($check_address_query);
	if ($check_address['total'] != '1') {
		$_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
		if (isset($_SESSION['shipping']))
			unset ($_SESSION['shipping']);
	}
}

require_once (DIR_WS_CLASSES.'order.php');
$order = new order();

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID !== $_SESSION['cartID']) {
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
    }
}

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
$_SESSION['cartID'] = $_SESSION['cart']->cartID;

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
if ($order->content_type == 'virtual' || ($order->content_type == 'virtual_weight') || ($_SESSION['cart']->count_contents_virtual() == 0)) { // GV Code added
	$_SESSION['shipping'] = false;
	$_SESSION['sendto'] = false;
	xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, xtc_get_all_get_params(), 'SSL'));
}

$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();

if ($order->delivery['country']['iso_code_2'] != '') {
	$_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];
}
// load all enabled shipping modules
require_once (DIR_WS_CLASSES.'shipping.php');
$shipping_modules = new shipping;

$free_shipping = false;
require_once (DIR_WS_MODULES.'order_total/ot_shipping.php');
include_once (DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/order_total/ot_shipping.php');
$ot_shipping = new ot_shipping;
$ot_shipping->process();

//express checkout
if (defined('MODULE_CHECKOUT_EXPRESS_STATUS') && MODULE_CHECKOUT_EXPRESS_STATUS == 'true') {
  if (isset($_GET['express']) && $_GET['express'] == 'on') {
    if ($express['checkout_shipping'] != '') {
      if ($free_shipping === false && $express['checkout_shipping'] == 'free_free') {
        unset($express['checkout_shipping']);
      } elseif ($free_shipping === false && $express['checkout_shipping'] == 'cheapest_cheapest') {
        // get all available shipping quotes
        $quotes = $shipping_modules->quote();
        $cheapest = $shipping_modules->cheapest();
        $express['checkout_shipping'] = $cheapest['id'];
      }
      $_POST['action'] = 'process';
      $_POST['shipping'] = (($free_shipping === true) ? 'free_free' : $express['checkout_shipping']);
    }
  }
}

// process the selected shipping method
if (isset($_POST['action']) && ($_POST['action'] == 'process')) {

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
						xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, xtc_get_all_get_params(), 'SSL'));
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
}

// get all available shipping quotes
$quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
if ((!isset($_SESSION['shipping']) && CHECK_CHEAPEST_SHIPPING_MODUL == 'true') || (isset($_SESSION['shipping']) && ($_SESSION['shipping'] == false) && (xtc_count_shipping_modules() > 1))) { //web28 - 2012-04-27 - pre-selection the cheapest shipping option
	$_SESSION['shipping'] = $shipping_modules->cheapest();
}
$breadcrumb->add(NAVBAR_TITLE_1_CHECKOUT_SHIPPING, xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_SHIPPING, xtc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('FORM_ACTION', xtc_draw_form('checkout_address', xtc_href_link(FILENAME_CHECKOUT_SHIPPING, xtc_get_all_get_params(), 'SSL'), 'post', 'onSubmit="return check_form();"').xtc_draw_hidden_field('action', 'process'));
$smarty->assign('ADDRESS_LABEL', xtc_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'));
$smarty->assign('BUTTON_ADDRESS', '<a href="'.xtc_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL').'">'.xtc_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS).'</a>');
$smarty->assign('BUTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));// 'BUTON_CONTINUE' to remain compatible to standard templates
$smarty->assign('BUTTON_CONTINUE', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$smarty->assign('BUTTON_CHECKOUT_STEP2', xtc_image_submit('button_checkout_step2.gif', IMAGE_BUTTON_CHECKOUT_STEP2));
$smarty->assign('FORM_END', '</form>');

if (SHOW_SELFPICKUP_FREE == 'true') {
  if ($free_shipping == true) {
    $free_shipping = false;
    $quotes = array_merge($ot_shipping->quote(), $shipping_modules->quote('selfpickup', 'selfpickup'));
  }                    
}

$module_smarty = new Smarty;
$shipping_block = '';
if (xtc_count_shipping_modules() > 0) {
	$showtax = $_SESSION['customers_status']['customers_status_show_price_tax'];
	$module_smarty->assign('FREE_SHIPPING', $free_shipping);
	# free shipping or not...
	if ($free_shipping == true) {
		$module_smarty->assign('FREE_SHIPPING_TITLE', FREE_SHIPPING_TITLE);
		$module_smarty->assign('FREE_SHIPPING_DESCRIPTION', sprintf(FREE_SHIPPING_DESCRIPTION, $xtPrice->xtcFormat($free_shipping_value_over, true, 0, true)).xtc_draw_hidden_field('shipping', 'free_free'));
		$module_smarty->assign('FREE_SHIPPING_ICON', $quotes[$i]['icon']);
	} else {
		$radio_buttons = 0;
		#loop through installed shipping methods...
		for ($i = 0, $n = sizeof($quotes); $i < $n; $i ++) {
			if (!isset($quotes[$i]['error'])) {
				for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j ++) {
					# set the radio button to be checked if it is the method chosen
					$quotes[$i]['methods'][$j]['radio_buttons'] = $radio_buttons;
					$checked = ((isset($_SESSION['shipping']) && $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) ? true : false);
					if (($checked == true) || ($n == 1 && $n2 == 1)) {
						$quotes[$i]['methods'][$j]['checked'] = 1;
					}
					if (($n > 1) || ($n2 > 1)) {
						if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 || !isset($quotes[$i]['tax'])) {
							$quotes[$i]['tax'] = 0;
            }
						$quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax']), true, 0, true);						
            $quotes[$i]['methods'][$j]['radio_field'] = xtc_draw_radio_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'], $checked, 'id="rd-'.($i+1).'"');
					} else {
						if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0) {
							$quotes[$i]['tax'] = 0;
            }
            $quotes[$i]['methods'][$j]['price'] = $xtPrice->xtcFormat(xtc_add_tax($quotes[$i]['methods'][$j]['cost'], isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0), true, 0, true).xtc_draw_hidden_field('shipping', $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id']);
					}
					$radio_buttons ++;
				}
			}
		}
		$module_smarty->assign('module_content', $quotes);
	}
	$module_smarty->caching = 0;
	$shipping_block = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_shipping_block.html');
}

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('SHIPPING_BLOCK', $shipping_block);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/checkout_shipping.html');
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
?>